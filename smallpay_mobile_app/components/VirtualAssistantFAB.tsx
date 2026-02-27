import React, { useEffect, useMemo, useState } from 'react';
import {
  Animated,
  KeyboardAvoidingView,
  Modal,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { MessageCircle, X } from 'lucide-react-native';
import { useTheme } from '@/context/ThemeContext';

interface Message {
  id: string;
  text: string;
  sender: 'user' | 'assistant';
  timestamp: Date;
}

const QUICK_QUESTIONS = [
  'Comment payer une commande ?',
  'Ou suivre mes echeances ?',
  'Comment faire ma verification KYC ?',
  'Que faire en cas de retard ?',
];

function getAssistantReply(rawText: string): string {
  const text = rawText.toLowerCase();

  if (text.includes('kyc') || text.includes('verification') || text.includes('identite')) {
    return 'Pour le KYC, ouvre le formulaire KYC, ajoute tes infos, puis envoie les documents demandes. Le statut est visible dans ton profil.';
  }

  if (text.includes('echeance') || text.includes('mensualite') || text.includes('retard')) {
    return 'Tu peux suivre tes echeances dans Mes commandes. En cas de retard, paie au plus vite depuis le detail de commande pour eviter des frais.';
  }

  if (text.includes('payer') || text.includes('paiement') || text.includes('commande')) {
    return 'Pour payer, ouvre la commande puis appuie sur Payer. Verifie le montant, puis valide avec ton mode de paiement.';
  }

  if (text.includes('support') || text.includes('aide') || text.includes('probleme')) {
    return 'Pour une aide rapide, utilise la page Support dans Profil. Decris ton probleme avec ton numero de commande pour accelerer la resolution.';
  }

  return 'Je peux aider sur paiement, commandes, echeances, KYC et support. Pose une question courte et precise.';
}

export function VirtualAssistantFAB() {
  const { colors } = useTheme();
  const [showAssistant, setShowAssistant] = useState(false);
  const [messages, setMessages] = useState<Message[]>([
    {
      id: '1',
      text: 'Salut. Je peux t aider sur paiement, commandes, KYC et support.',
      sender: 'assistant',
      timestamp: new Date(),
    },
  ]);
  const [inputText, setInputText] = useState('');
  const [fabAnimation] = useState(new Animated.Value(0));

  useEffect(() => {
    Animated.loop(
      Animated.sequence([
        Animated.timing(fabAnimation, {
          toValue: 1,
          duration: 1000,
          useNativeDriver: true,
        }),
        Animated.timing(fabAnimation, {
          toValue: 0,
          duration: 1000,
          useNativeDriver: true,
        }),
      ])
    ).start();
  }, [fabAnimation]);

  const sendUserMessage = (text: string) => {
    const trimmed = text.trim();
    if (!trimmed) return;

    const userMessage: Message = {
      id: Date.now().toString(),
      text: trimmed,
      sender: 'user',
      timestamp: new Date(),
    };

    setMessages((prev) => [...prev, userMessage]);
    setInputText('');

    setTimeout(() => {
      const assistantMessage: Message = {
        id: `${Date.now()}-assistant`,
        text: getAssistantReply(trimmed),
        sender: 'assistant',
        timestamp: new Date(),
      };
      setMessages((prev) => [...prev, assistantMessage]);
    }, 250);
  };

  const chatCardColors = useMemo(
    () => ({
      panel: colors.card,
      border: colors.border,
      text: colors.text,
      fieldBg: colors.background,
      primary: colors.primary,
    }),
    [colors]
  );

  return (
    <>
      <Animated.View
        style={{
          position: 'absolute',
          bottom: 116,
          right: 24,
          zIndex: 999,
          opacity: fabAnimation.interpolate({
            inputRange: [0, 1],
            outputRange: [0.82, 1],
          }),
        }}
      >
        <TouchableOpacity
          style={[styles.fabButton, { backgroundColor: colors.primary }]}
          onPress={() => setShowAssistant(true)}
          activeOpacity={0.85}
        >
          <MessageCircle size={24} color="#ffffff" />
        </TouchableOpacity>
      </Animated.View>

      <Modal
        visible={showAssistant}
        transparent
        animationType="fade"
        onRequestClose={() => setShowAssistant(false)}
      >
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={styles.modalRoot}
        >
          <TouchableOpacity
            style={styles.overlay}
            activeOpacity={1}
            onPress={() => setShowAssistant(false)}
          />

          <View
            style={[
              styles.chatPanel,
              {
                backgroundColor: chatCardColors.panel,
                borderColor: chatCardColors.border,
              },
            ]}
          >
            <View style={styles.headerRow}>
              <Text style={[styles.headerTitle, { color: chatCardColors.text }]}>
                Assistant
              </Text>
              <TouchableOpacity
                onPress={() => setShowAssistant(false)}
                style={styles.closeBtn}
              >
                <X size={20} color={chatCardColors.text} />
              </TouchableOpacity>
            </View>

            <ScrollView
              horizontal
              showsHorizontalScrollIndicator={false}
              contentContainerStyle={styles.quickQuestionsRow}
            >
              {QUICK_QUESTIONS.map((question) => (
                <TouchableOpacity
                  key={question}
                  style={[styles.quickChip, { borderColor: chatCardColors.border }]}
                  onPress={() => sendUserMessage(question)}
                >
                  <Text style={[styles.quickChipText, { color: chatCardColors.text }]}>
                    {question}
                  </Text>
                </TouchableOpacity>
              ))}
            </ScrollView>

            <ScrollView
              style={styles.messagesArea}
              contentContainerStyle={styles.messagesContent}
              showsVerticalScrollIndicator={false}
            >
              {messages.map((message) => {
                const isUser = message.sender === 'user';
                return (
                  <View
                    key={message.id}
                    style={[
                      styles.messageRow,
                      { justifyContent: isUser ? 'flex-end' : 'flex-start' },
                    ]}
                  >
                    <View
                      style={[
                        styles.messageBubble,
                        isUser
                          ? { backgroundColor: chatCardColors.primary }
                          : { backgroundColor: chatCardColors.fieldBg, borderColor: chatCardColors.border, borderWidth: 1 },
                      ]}
                    >
                      <Text
                        style={[
                          styles.messageText,
                          { color: isUser ? '#ffffff' : chatCardColors.text },
                        ]}
                      >
                        {message.text}
                      </Text>
                    </View>
                  </View>
                );
              })}
            </ScrollView>

            <View style={styles.inputRow}>
              <TextInput
                style={[
                  styles.input,
                  {
                    backgroundColor: chatCardColors.fieldBg,
                    color: chatCardColors.text,
                    borderColor: chatCardColors.border,
                  },
                ]}
                placeholder="Pose ta question..."
                placeholderTextColor="#94a3b8"
                value={inputText}
                onChangeText={setInputText}
                onSubmitEditing={() => sendUserMessage(inputText)}
                returnKeyType="send"
              />
              <TouchableOpacity
                style={[styles.sendButton, { backgroundColor: chatCardColors.primary }]}
                onPress={() => sendUserMessage(inputText)}
              >
                <Text style={styles.sendText}>Envoyer</Text>
              </TouchableOpacity>
            </View>
          </View>
        </KeyboardAvoidingView>
      </Modal>
    </>
  );
}

const styles = StyleSheet.create({
  fabButton: {
    width: 56,
    height: 56,
    borderRadius: 28,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 8,
  },
  modalRoot: {
    flex: 1,
    justifyContent: 'flex-end',
    paddingHorizontal: 14,
    paddingBottom: 108,
  },
  overlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(15, 23, 42, 0.38)',
  },
  chatPanel: {
    width: '100%',
    maxHeight: 420,
    borderRadius: 18,
    borderWidth: 1,
    paddingTop: 10,
    paddingBottom: 12,
    paddingHorizontal: 10,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  headerTitle: {
    fontSize: 16,
    fontWeight: '700',
  },
  closeBtn: {
    width: 30,
    height: 30,
    borderRadius: 15,
    alignItems: 'center',
    justifyContent: 'center',
  },
  quickQuestionsRow: {
    paddingBottom: 8,
    gap: 8,
  },
  quickChip: {
    borderWidth: 1,
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 6,
  },
  quickChipText: {
    fontSize: 12,
    fontWeight: '600',
  },
  messagesArea: {
    maxHeight: 235,
  },
  messagesContent: {
    paddingVertical: 4,
    gap: 8,
  },
  messageRow: {
    flexDirection: 'row',
  },
  messageBubble: {
    maxWidth: '84%',
    borderRadius: 12,
    paddingHorizontal: 10,
    paddingVertical: 8,
  },
  messageText: {
    fontSize: 13,
    lineHeight: 18,
  },
  inputRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginTop: 8,
  },
  input: {
    flex: 1,
    borderWidth: 1,
    borderRadius: 12,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 13,
  },
  sendButton: {
    height: 40,
    borderRadius: 10,
    paddingHorizontal: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  sendText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: '700',
  },
});
