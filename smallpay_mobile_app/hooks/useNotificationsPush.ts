import { useEffect, useState, useRef } from 'react';
import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Platform, Alert } from 'react-native';
import { usePermissions } from './usePermissions';

/**
 * Hook pour gérer les notifications push
 * 
 * Utilisation:
 * const { 
 *   initializeNotifications, 
 *   isInitialized, 
 *   pushToken, 
 *   sendLocalNotification 
 * } = useNotificationsPush();
 */
export const useNotificationsPush = () => {
  const [isInitialized, setIsInitialized] = useState(false);
  const [pushToken, setPushToken] = useState<string | undefined>();
  const [notification, setNotification] = useState<any>(undefined);
  const notificationListener = useRef<any>();
  const responseListener = useRef<any>();
  const { requestPermission, checkPermission } = usePermissions();

  /**
   * Configurer le comportement des notifications
   */
  const setupNotificationChannels = async () => {
    if (Platform.OS === 'android') {
      // Créer un channel de notification pour Android
      await Notifications.setNotificationChannelAsync('default', {
        name: 'SmallPay Notifications',
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#FF231F7C',
        sound: 'default',
        enableVibrate: true,
      });

      // Channel pour les transactions
      await Notifications.setNotificationChannelAsync('transactions', {
        name: 'Notifications de transactions',
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#00FF00',
        sound: 'default',
        enableVibrate: true,
      });

      // Channel pour les paiements
      await Notifications.setNotificationChannelAsync('payments', {
        name: 'Notifications de paiements',
        importance: Notifications.AndroidImportance.HIGH,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#3B82F6',
        sound: 'default',
        enableVibrate: true,
      });

      // Channel pour les mises à jour KYC
      await Notifications.setNotificationChannelAsync('kyc', {
        name: 'Mises à jour KYC',
        importance: Notifications.AndroidImportance.HIGH,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#F59E0B',
        sound: 'default',
        enableVibrate: true,
      });
    }
  };

  /**
   * Demander la permission et obtenir le push token
   */
  const requestNotificationPermission = async () => {
    try {
      // Vérifier si c'est un vrai appareil
      if (!Device.isDevice) {
        console.log('Notifications push non disponibles sur les émulateurs');
        return null;
      }

      // Demander la permission sur Android 13+
      if (Platform.OS === 'android') {
        const permissionStatus = await requestPermission('notifications');
        if (!permissionStatus.granted) {
          Alert.alert(
            'Permission refusée',
            'Les notifications ont été refusées. Vous pouvez les activer dans les paramètres.'
          );
          return null;
        }
      }

      // Sur iOS, Expo gère automatiquement la permission
      if (Platform.OS === 'ios') {
        const currentSettings = await Notifications.getPermissionsAsync();
        if (currentSettings.granted === false) {
          const finalSettings = await Notifications.requestPermissionsAsync();
          if (finalSettings.granted === false) {
            Alert.alert(
              'Permission refusée',
              'Les notifications ont été refusées. Vous pouvez les activer dans les paramètres.'
            );
            return null;
          }
        }
      }

      return true;
    } catch (error) {
      console.error('Erreur lors de la demande de permission notifications:', error);
      return null;
    }
  };

  /**
   * Obtenir le push token pour l'envoyer au backend
   */
  const getExponentPushTokenAsync = async () => {
    try {
      const projectId = process.env.EXPO_PROJECT_ID;
      if (!projectId) {
        console.warn('EXPO_PROJECT_ID non configuré');
        return null;
      }

      const token = await Notifications.getExpoPushTokenAsync({
        projectId,
      });

      console.log('Push Token:', token.data);
      return token.data;
    } catch (error) {
      console.error('Erreur lors de la récupération du push token:', error);
      return null;
    }
  };

  /**
   * Initialiser les notifications push
   */
  const initializeNotifications = async () => {
    try {
      // Setup des channels Android
      await setupNotificationChannels();

      // Demander la permission
      const permissionGranted = await requestNotificationPermission();
      if (!permissionGranted) {
        setIsInitialized(false);
        return;
      }

      // Obtenir le push token
      const token = await getExponentPushTokenAsync();
      if (token) {
        setPushToken(token);
        console.log('Notifications push initialisées avec le token:', token);
      }

      // Écouter les notifications reçues en foreground
      notificationListener.current = Notifications.addNotificationReceivedListener(
        (notification) => {
          setNotification(notification);
          console.log('Notification reçue:', notification);
        }
      );

      // Écouter les réponses de l'utilisateur aux notifications
      responseListener.current = Notifications.addNotificationResponseReceivedListener(
        (response) => {
          console.log('Réponse notification:', response);
          // Gérer la réponse (redirection, action, etc.)
          handleNotificationResponse(response);
        }
      );

      setIsInitialized(true);
    } catch (error) {
      console.error('Erreur lors de l\'initialisation des notifications:', error);
      setIsInitialized(false);
    }
  };

  /**
   * Gérer la réponse quand l'utilisateur appuie sur une notification
   */
  const handleNotificationResponse = (response: any) => {
    const { notification } = response;
    const data = notification.request.content.data;

    console.log('Données notification reçues:', data);

    // Rediriger selon le type de notification
    if (data.type === 'payment_success' || data.type === 'payment_failed') {
      // Rediriger vers l'écran des transactions
      console.log('Redirection vers transactions');
    } else if (data.type === 'kyc_approved' || data.type === 'kyc_rejected') {
      // Rediriger vers le profil
      console.log('Redirection vers profil');
    } else if (data.type === 'order_created' || data.type === 'order_status_changed') {
      // Rediriger vers les commandes
      console.log('Redirection vers commandes');
    }
  };

  /**
   * Envoyer une notification locale (pour test)
   */
  const sendLocalNotification = async (
    title: string,
    message: string,
    data?: Record<string, any>,
    channelId: string = 'default'
  ) => {
    try {
      await Notifications.scheduleNotificationAsync({
        content: {
          title,
          body: message,
          sound: 'default',
          vibrate: [0, 250, 250, 250],
          data: data || {},
        },
        trigger: {
          seconds: 1,
        },
        identifier: channelId,
      });
      console.log('Notification locale envoyée');
    } catch (error) {
      console.error('Erreur lors de l\'envoi de la notification:', error);
    }
  };

  /**
   * Vérifier si les notifications sont activées
   */
  const checkNotificationsEnabled = async () => {
    try {
      const settings = await Notifications.getPermissionsAsync();
      return settings.granted || settings.ios?.status === Notifications.IosAuthorizationStatus.PROVISIONAL;
    } catch (error) {
      console.error('Erreur lors de la vérification des notifications:', error);
      return false;
    }
  };

  /**
   * Ouvrir les paramètres des notifications
   */
  const openNotificationSettings = async () => {
    try {
      const settings = await Notifications.getPermissionsAsync();
      if (settings.ios?.status === Notifications.IosAuthorizationStatus.DENIED) {
        Alert.alert(
          'Notifications désactivées',
          'Allez dans Paramètres > SmallPay > Notifications pour les activer.'
        );
      }
    } catch (error) {
      console.error('Erreur:', error);
    }
  };

  /**
   * Nettoyer les listeners au démontage du composant
   */
  useEffect(() => {
    return () => {
      if (notificationListener.current) {
        Notifications.removeNotificationSubscription(notificationListener.current);
      }
      if (responseListener.current) {
        Notifications.removeNotificationSubscription(responseListener.current);
      }
    };
  }, []);

  return {
    initializeNotifications,
    isInitialized,
    pushToken,
    notification,
    sendLocalNotification,
    checkNotificationsEnabled,
    openNotificationSettings,
  };
};
