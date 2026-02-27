import { api, handleApiResponse, handleApiError } from '@/lib/api';

/**
 * Service pour gérer les push tokens et les notifications push
 * Communique avec le backend pour enregistrer et gérer les tokens
 */
class PushNotificationService {
  /**
   * Envoyer le push token au backend pour le stocker
   */
  async savePushToken(token: string) {
    try {
      const response = await api.post('/notifications/push-token', {
        push_token: token,
      });

      const result = handleApiResponse(response);

      if (result.success) {
        console.log('Push token sauvegardé avec succès');
        return {
          success: true,
          message: 'Push token enregistré',
        };
      } else {
        return {
          success: false,
          error: result.error || 'Erreur lors de l\'enregistrement du token',
        };
      }
    } catch (error: any) {
      const errorResult = handleApiError(error);
      console.error('Erreur lors de la sauvegarde du token:', errorResult);
      return {
        success: false,
        error: errorResult.error,
      };
    }
  }

  /**
   * Mettre à jour le push token (en cas de renouvellement)
   */
  async updatePushToken(oldToken: string, newToken: string) {
    try {
      const response = await api.put('/notifications/push-token', {
        old_token: oldToken,
        new_token: newToken,
      });

      const result = handleApiResponse(response);

      if (result.success) {
        console.log('Push token mis à jour');
        return {
          success: true,
          message: 'Push token mis à jour',
        };
      } else {
        return {
          success: false,
          error: result.error || 'Erreur lors de la mise à jour du token',
        };
      }
    } catch (error: any) {
      const errorResult = handleApiError(error);
      return {
        success: false,
        error: errorResult.error,
      };
    }
  }

  /**
   * Supprimer le push token (à la déconnexion)
   */
  async deletePushToken(token: string) {
    try {
      const response = await api.delete('/notifications/push-token', {
        data: {
          push_token: token,
        },
      });

      const result = handleApiResponse(response);

      if (result.success) {
        console.log('Push token supprimé');
        return {
          success: true,
          message: 'Push token supprimé',
        };
      } else {
        return {
          success: false,
          error: result.error || 'Erreur lors de la suppression du token',
        };
      }
    } catch (error: any) {
      const errorResult = handleApiError(error);
      return {
        success: false,
        error: errorResult.error,
      };
    }
  }

  /**
   * Activer/désactiver les notifications push
   */
  async setNotificationPreference(enabled: boolean) {
    try {
      const response = await api.put('/notifications/preferences', {
        push_notifications_enabled: enabled,
      });

      const result = handleApiResponse(response);

      if (result.success) {
        console.log(`Notifications push ${enabled ? 'activées' : 'désactivées'}`);
        return {
          success: true,
          message: `Notifications ${enabled ? 'activées' : 'désactivées'}`,
        };
      } else {
        return {
          success: false,
          error: result.error || 'Erreur lors de la mise à jour des préférences',
        };
      }
    } catch (error: any) {
      const errorResult = handleApiError(error);
      return {
        success: false,
        error: errorResult.error,
      };
    }
  }

  /**
   * Récupérer les préférences de notifications
   */
  async getNotificationPreferences() {
    try {
      const response = await api.get('/notifications/preferences');

      const result = handleApiResponse<{
        push_notifications_enabled?: boolean;
        email_notifications_enabled?: boolean;
        notify_payments?: boolean;
        notify_kyc?: boolean;
        notify_orders?: boolean;
        notify_reminders?: boolean;
      }>(response);

      if (result.success && result.data) {
        const data = result.data as any;
        return {
          success: true,
          data: {
            pushNotificationsEnabled: data.push_notifications_enabled || false,
            emailNotificationsEnabled: data.email_notifications_enabled || false,
            notifyPayments: data.notify_payments || true,
            notifyKyc: data.notify_kyc || true,
            notifyOrders: data.notify_orders || true,
            notifyReminders: data.notify_reminders || true,
          },
        };
      } else {
        return {
          success: false,
          error: result.error || 'Erreur lors de la récupération des préférences',
          data: null,
        };
      }
    } catch (error: any) {
      const errorResult = handleApiError(error);
      return {
        success: false,
        error: errorResult.error,
        data: null,
      };
    }
  }

  /**
   * Mettre à jour les préférences détaillées de notifications
   */
  async updateNotificationPreferences(preferences: {
    pushNotificationsEnabled?: boolean;
    emailNotificationsEnabled?: boolean;
    notifyPayments?: boolean;
    notifyKyc?: boolean;
    notifyOrders?: boolean;
    notifyReminders?: boolean;
  }) {
    try {
      const payload: Record<string, any> = {};

      if (preferences.pushNotificationsEnabled !== undefined) {
        payload.push_notifications_enabled = preferences.pushNotificationsEnabled;
      }
      if (preferences.emailNotificationsEnabled !== undefined) {
        payload.email_notifications_enabled = preferences.emailNotificationsEnabled;
      }
      if (preferences.notifyPayments !== undefined) {
        payload.notify_payments = preferences.notifyPayments;
      }
      if (preferences.notifyKyc !== undefined) {
        payload.notify_kyc = preferences.notifyKyc;
      }
      if (preferences.notifyOrders !== undefined) {
        payload.notify_orders = preferences.notifyOrders;
      }
      if (preferences.notifyReminders !== undefined) {
        payload.notify_reminders = preferences.notifyReminders;
      }

      const response = await api.put('/notifications/preferences', payload);

      const result = handleApiResponse(response);

      if (result.success) {
        console.log('Préférences de notifications mises à jour');
        return {
          success: true,
          message: 'Préférences mises à jour',
        };
      } else {
        return {
          success: false,
          error: result.error || 'Erreur lors de la mise à jour',
        };
      }
    } catch (error: any) {
      const errorResult = handleApiError(error);
      return {
        success: false,
        error: errorResult.error,
      };
    }
  }

  /**
   * Envoyer une notification de test
   */
  async sendTestNotification(title: string, message: string) {
    try {
      const response = await api.post('/notifications/test', {
        title,
        message,
      });

      const result = handleApiResponse(response);

      if (result.success) {
        return {
          success: true,
          message: 'Notification de test envoyée',
        };
      } else {
        return {
          success: false,
          error: result.error || 'Erreur lors de l\'envoi du test',
        };
      }
    } catch (error: any) {
      const errorResult = handleApiError(error);
      return {
        success: false,
        error: errorResult.error,
      };
    }
  }
}

export default new PushNotificationService();
