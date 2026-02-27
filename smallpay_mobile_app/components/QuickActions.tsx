import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { ShoppingBag, CreditCard, TrendingUp, Gift } from 'lucide-react-native';
import { useRouter } from 'expo-router';

interface QuickAction {
  icon: any;
  label: string;
  color: string;
  route: string;
}

const quickActions: QuickAction[] = [
  { icon: ShoppingBag, label: 'Mes achats', color: '#3b82f6', route: '/home/my-purchases' },
  { icon: CreditCard, label: 'Mes paiements', color: '#a855f7', route: '/(tabs)/orders' },
  { icon: TrendingUp, label: 'Historique', color: '#10b981', route: '/home/history' },
  { icon: Gift, label: 'Offres', color: '#f97316', route: '/home/offers' },
];

export const QuickActions: React.FC = () => {
  const router = useRouter();

  const handleActionPress = (route: string) => {
    router.push(route as any);
  };

  return (
    <View style={styles.quickActionsContainer}>
      <View style={styles.quickActionsContent}>
        <View style={styles.quickActionsGrid}>
          {quickActions.map((action, index) => (
            <TouchableOpacity 
              key={index} 
              style={styles.actionButton}
              onPress={() => handleActionPress(action.route)}
            >
              <View style={[styles.actionIcon, { backgroundColor: action.color }]}>
                <action.icon size={20} color="#fff" />
              </View>
              <Text style={styles.actionLabel}>{action.label}</Text>
            </TouchableOpacity>
          ))}
        </View>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  quickActionsContainer: {
    marginHorizontal: -24,
    marginTop: -12,
    paddingHorizontal: 24,
    paddingVertical: 16,
    paddingBottom: 20,
  },
  quickActionsContent: {
    backgroundColor: '#fff',
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  quickActionsGrid: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  actionButton: {
    flex: 1,
    alignItems: 'center',
    paddingVertical: 8,
  },
  actionIcon: {
    width: 48,
    height: 48,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  actionLabel: {
    fontSize: 12,
    fontWeight: '500',
    color: '#4b5563',
    textAlign: 'center',
  },
});
