/**
 * Exemple: Afficher les infos de session dans le profil utilisateur
 * 
 * Cet exemple montre comment afficher:
 * - Les infos utilisateur
 * - Le temps restant avant expiration de la session
 * - Un bouton pour rafraîchir la session
 * - Un bouton pour se déconnecter
 */

import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { useAuth } from '../context/AuthContext';

const ProfileScreen = () => {
  const {
    user,
    isAuthenticated,
    sessionExpiry,
    getTimeRemaining,
    refreshToken,
    logout,
  } = useAuth();

  const [daysRemaining, setDaysRemaining] = useState(0);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  // Charger le temps restant
  useEffect(() => {
    const updateSessionTime = async () => {
      const days = await getTimeRemaining();
      setDaysRemaining(days);
    };

    updateSessionTime();

    // Vérifier chaque heure
    const interval = setInterval(updateSessionTime, 60 * 60 * 1000);

    return () => clearInterval(interval);
  }, [getTimeRemaining]);

  // Obtenir l'état de la session
  const getSessionStatus = () => {
    if (daysRemaining > 14) {
      return { status: 'Valide', color: '#4CAF50', icon: '✅' };
    } else if (daysRemaining > 7) {
      return { status: 'À expirer bientôt', color: '#FFC107', icon: '⚠️' };
    } else if (daysRemaining > 0) {
      return { status: 'À expirer très bientôt', color: '#FF9800', icon: '⚠️' };
    } else {
      return { status: 'Expirée', color: '#F44336', icon: '❌' };
    }
  };

  const handleRefreshSession = async () => {
    try {
      setIsRefreshing(true);
      const result = await refreshToken();

      if (result.success) {
        const newDays = await getTimeRemaining();
        setDaysRemaining(newDays);
        Alert.alert(
          'Session rafraîchie',
          `Votre session a été prolongée jusqu'à ${newDays} jours.`
        );
      } else {
        Alert.alert('Erreur', 'Impossible de rafraîchir la session.');
      }
    } catch (error) {
      console.error('Erreur lors du rafraîchissement:', error);
      Alert.alert('Erreur', 'Une erreur est survenue lors du rafraîchissement.');
    } finally {
      setIsRefreshing(false);
    }
  };

  const handleLogout = async () => {
    Alert.alert('Déconnexion', 'Êtes-vous sûr de vouloir vous déconnecter ?', [
      { text: 'Annuler', onPress: () => {}, style: 'cancel' },
      {
        text: 'Déconnexion',
        onPress: async () => {
          try {
            setIsLoggingOut(true);
            await logout();
            // La navigation est gérée par le contexte d'authentification
          } catch (error) {
            console.error('Erreur lors de la déconnexion:', error);
            Alert.alert('Erreur', 'Erreur lors de la déconnexion.');
          } finally {
            setIsLoggingOut(false);
          }
        },
        style: 'destructive',
      },
    ]);
  };

  if (!isAuthenticated || !user) {
    return (
      <View style={styles.container}>
        <Text style={styles.centerText}>Non connecté</Text>
      </View>
    );
  }

  const sessionStatus = getSessionStatus();

  return (
    <ScrollView style={styles.container}>
      {/* En-tête avec infos utilisateur */}
      <View style={styles.header}>
        <Text style={styles.userName}>{user.name || 'Utilisateur'}</Text>
        <Text style={styles.userEmail}>{user.email || user.phone}</Text>
      </View>

      {/* Section Session */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Infos de Session</Text>

        {/* État de la session */}
        <View style={[styles.statusCard, { borderLeftColor: sessionStatus.color }]}>
          <View style={styles.statusHeader}>
            <Text style={styles.statusIcon}>{sessionStatus.icon}</Text>
            <Text style={[styles.statusText, { color: sessionStatus.color }]}>
              {sessionStatus.status}
            </Text>
          </View>

          <View style={styles.statusDetails}>
            <Text style={styles.statusDetail}>
              📅 Temps restant: <Text style={styles.bold}>{daysRemaining} jours</Text>
            </Text>

            {sessionExpiry && (
              <Text style={styles.statusDetail}>
                🕐 Expire le:{' '}
                <Text style={styles.bold}>
                  {new Date(sessionExpiry).toLocaleDateString('fr-FR', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                  })}
                </Text>
              </Text>
            )}

            <Text style={styles.statusDetail}>
              🔐 Type: <Text style={styles.bold}>JWT Token</Text>
            </Text>
          </View>
        </View>

        {/* Avertissement si expiration proche */}
        {daysRemaining <= 7 && daysRemaining > 0 && (
          <View style={styles.warningBox}>
            <Text style={styles.warningText}>
              ⚠️ Votre session expire bientôt. Rafraîchissez-la pour rester connecté.
            </Text>
          </View>
        )}

        {daysRemaining === 0 && (
          <View style={[styles.warningBox, { backgroundColor: '#FFEBEE' }]}>
            <Text style={[styles.warningText, { color: '#C62828' }]}>
              ❌ Votre session a expiré. Veuillez vous reconnecter.
            </Text>
          </View>
        )}
      </View>

      {/* Section Sécurité */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Sécurité</Text>

        <View style={styles.infoBox}>
          <Text style={styles.infoLabel}>🔒 Chiffrement</Text>
          <Text style={styles.infoValue}>End-to-end (HTTPS)</Text>
        </View>

        <View style={styles.infoBox}>
          <Text style={styles.infoLabel}>🛡️ Token</Text>
          <Text style={styles.infoValue}>JWT signé et chiffré</Text>
        </View>

        <View style={styles.infoBox}>
          <Text style={styles.infoLabel}>📱 Stockage</Text>
          <Text style={styles.infoValue}>AsyncStorage sécurisé</Text>
        </View>

        <View style={styles.infoBox}>
          <Text style={styles.infoLabel}>🔄 Rafraîchissement</Text>
          <Text style={styles.infoValue}>1 fois par 24h maximum</Text>
        </View>
      </View>

      {/* Section Compte */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Compte</Text>

        {user.kyc_verified && (
          <View style={styles.infoBox}>
            <Text style={styles.infoLabel}>✅ Vérification KYC</Text>
            <Text style={styles.infoValue}>Complète</Text>
          </View>
        )}

        {user.created_at && (
          <View style={styles.infoBox}>
            <Text style={styles.infoLabel}>📅 Inscrit le</Text>
            <Text style={styles.infoValue}>
              {new Date(user.created_at).toLocaleDateString('fr-FR')}
            </Text>
          </View>
        )}
      </View>

      {/* Boutons d'action */}
      <View style={styles.actionContainer}>
        <TouchableOpacity
          style={[styles.button, styles.refreshButton]}
          onPress={handleRefreshSession}
          disabled={isRefreshing}
        >
          {isRefreshing ? (
            <ActivityIndicator color="#007AFF" />
          ) : (
            <>
              <Text style={styles.refreshButtonText}>🔄 Rafraîchir la session</Text>
              <Text style={styles.refreshButtonSubtext}>
                Prolonger de 30 jours supplémentaires
              </Text>
            </>
          )}
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.button, styles.logoutButton]}
          onPress={handleLogout}
          disabled={isLoggingOut}
        >
          {isLoggingOut ? (
            <ActivityIndicator color="#F44336" />
          ) : (
            <Text style={styles.logoutButtonText}>🚪 Déconnexion</Text>
          )}
        </TouchableOpacity>
      </View>

      {/* Footer avec infos légales */}
      <View style={styles.footer}>
        <Text style={styles.footerText}>
          Pour plus d'informations sur la gestion de votre session, consultez notre{' '}
          <Text style={styles.linkText}>Politique de Confidentialité</Text> (Section 9.1)
        </Text>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  centerText: {
    textAlign: 'center',
    marginTop: 20,
    fontSize: 16,
    color: '#666',
  },
  header: {
    backgroundColor: '#007AFF',
    paddingVertical: 24,
    paddingHorizontal: 16,
    alignItems: 'center',
  },
  userName: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginBottom: 8,
  },
  userEmail: {
    fontSize: 14,
    color: '#E0E0E0',
  },
  section: {
    backgroundColor: '#FFFFFF',
    marginTop: 12,
    paddingHorizontal: 16,
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#E0E0E0',
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 16,
  },
  statusCard: {
    backgroundColor: '#F9F9F9',
    borderRadius: 8,
    borderLeftWidth: 4,
    padding: 16,
    marginBottom: 12,
  },
  statusHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  statusIcon: {
    fontSize: 24,
    marginRight: 12,
  },
  statusText: {
    fontSize: 16,
    fontWeight: '600',
  },
  statusDetails: {
    backgroundColor: '#FFFFFF',
    borderRadius: 4,
    padding: 12,
  },
  statusDetail: {
    fontSize: 14,
    color: '#555',
    marginBottom: 8,
    lineHeight: 20,
  },
  bold: {
    fontWeight: '700',
    color: '#000',
  },
  warningBox: {
    backgroundColor: '#FFF9C4',
    borderRadius: 8,
    padding: 12,
    marginBottom: 12,
    borderLeftWidth: 4,
    borderLeftColor: '#FBC02D',
  },
  warningText: {
    fontSize: 13,
    color: '#F57F17',
    lineHeight: 18,
  },
  infoBox: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  infoLabel: {
    fontSize: 14,
    color: '#666',
    fontWeight: '500',
  },
  infoValue: {
    fontSize: 14,
    color: '#333',
    fontWeight: '600',
  },
  actionContainer: {
    paddingHorizontal: 16,
    paddingVertical: 20,
    gap: 12,
  },
  button: {
    borderRadius: 8,
    paddingVertical: 14,
    paddingHorizontal: 16,
    alignItems: 'center',
  },
  refreshButton: {
    backgroundColor: '#E3F2FD',
    borderWidth: 1,
    borderColor: '#007AFF',
  },
  refreshButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#007AFF',
  },
  refreshButtonSubtext: {
    fontSize: 12,
    color: '#0059B3',
    marginTop: 4,
  },
  logoutButton: {
    backgroundColor: '#FFEBEE',
    borderWidth: 1,
    borderColor: '#F44336',
  },
  logoutButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#F44336',
  },
  footer: {
    backgroundColor: '#FAFAFA',
    paddingHorizontal: 16,
    paddingVertical: 16,
    borderTopWidth: 1,
    borderTopColor: '#E0E0E0',
  },
  footerText: {
    fontSize: 12,
    color: '#999',
    lineHeight: 18,
  },
  linkText: {
    color: '#007AFF',
    fontWeight: '600',
  },
});

export default ProfileScreen;
