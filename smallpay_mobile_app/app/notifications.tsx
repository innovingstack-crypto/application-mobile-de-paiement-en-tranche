import React, { useState, useEffect } from 'react';
import {
    View,
    Text,
    TouchableOpacity,
    FlatList,
    SafeAreaView,
    StatusBar,
    ActivityIndicator,
} from 'react-native';
import { Trash2, CheckCircle, AlertCircle, Info } from 'lucide-react-native';
import { BackButton } from '@/components/ui/BackButton';
import { notificationsStyles as styles } from '@/constants/notifications.styles';
import NotificationService, { NotificationData } from '@/services/NotificationService';

interface Notification {
  id: string;
  title: string;
  description: string;
  type: 'success' | 'warning' | 'info' | 'kyc_approved' | 'kyc_rejected' | 'order_created' | 'payment_success' | 'payment_failed' | 'order_status_changed' | 'payment_overdue' | 'payment_reminder' | 'refund' | 'account_blocked';
  timestamp: string;
  read: boolean;
  read_at?: string;
  created_at?: string;
  related_data?: any;
}

export default function NotificationsScreen() {
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [refreshing, setRefreshing] = useState(false);

    // Charger les notifications au montage du composant
    useEffect(() => {
        fetchNotifications();
    }, []);

    // Charger les notifications depuis le backend
    const fetchNotifications = async () => {
        try {
            setLoading(true);
            setError(null);
            const result = await NotificationService.fetchNotifications(1, 50);

            if (result.success && result.data && result.data.length > 0) {
                const formattedNotifications: Notification[] = result.data.map((notif: NotificationData) => ({
                    id: notif.id,
                    title: notif.title,
                    description: notif.description || notif.message || '',
                    type: notif.type,
                    timestamp: notif.timestamp || '',
                    read: notif.read,
                    read_at: notif.read_at,
                    created_at: notif.created_at,
                }));
                setNotifications(formattedNotifications);
            } else {
                setNotifications([]);
            }
        } catch (err: any) {
            setError(err.message || 'Erreur lors du chargement des notifications');
            setNotifications([]);
        } finally {
            setLoading(false);
        }
    };

    // Rafraîchir les notifications
    const handleRefresh = async () => {
        setRefreshing(true);
        await fetchNotifications();
        setRefreshing(false);
    };

    const getIconComponent = (type: string) => {
      switch (type) {
        case 'success':
        case 'payment_success':
        case 'kyc_approved':
        case 'order_created':
        case 'refund':
          return <CheckCircle size={24} color="#10b981" />;
        case 'warning':
        case 'payment_failed':
        case 'kyc_rejected':
        case 'payment_overdue':
        case 'account_blocked':
          return <AlertCircle size={24} color="#f59e0b" />;
        case 'info':
        case 'order_status_changed':
        case 'payment_reminder':
        default:
          return <Info size={24} color="#3b82f6" />;
      }
    };

    const getTypeColor = (type: string) => {
      switch (type) {
        case 'success':
        case 'payment_success':
        case 'kyc_approved':
        case 'refund':
          return '#d1fae5';
        case 'warning':
        case 'payment_failed':
        case 'kyc_rejected':
        case 'payment_overdue':
        case 'account_blocked':
          return '#fee2e2';
        case 'info':
        case 'order_created':
        case 'order_status_changed':
        case 'payment_reminder':
        default:
          return '#dbeafe';
      }
    };

    const handleDeleteNotification = async (id: string) => {
        // Supprimer localement d'abord pour UX rapide
        setNotifications(notifications.filter((notif) => notif.id !== id));

        // Marquer comme lue sur le backend (optionnel, ou implémenter delete si disponible)
        try {
            await NotificationService.markAsRead(id);
        } catch (err) {
            console.log('Erreur lors de la suppression:', err);
        }
    };

    const handleClearAll = async () => {
        // Marquer toutes les notifications comme lues
        setNotifications([]);

        // Optionnel: appeler le backend pour marquer toutes comme lues
        try {
            for (const notif of notifications) {
                if (!notif.read) {
                    await NotificationService.markAsRead(notif.id);
                }
            }
        } catch (err) {
            console.log('Erreur lors du marquage:', err);
        }
    };

    const renderNotification = ({ item }: { item: Notification }) => (
        <View
            style={[
                styles.notificationCard,
                !item.read && styles.notificationCardUnread,
            ]}
        >
            <View
                style={[styles.iconContainer, { backgroundColor: getTypeColor(item.type) }]}
            >
                {getIconComponent(item.type)}
            </View>

            <View style={styles.notificationContent}>
                <Text style={styles.notificationTitle}>{item.title}</Text>
                <Text style={styles.notificationDescription}>{item.description}</Text>
                <Text style={styles.notificationTime}>{item.timestamp}</Text>
            </View>

            <TouchableOpacity
                style={styles.deleteButton}
                onPress={() => handleDeleteNotification(item.id)}
            >
                <Trash2 size={18} color="#6b7280" />
            </TouchableOpacity>
        </View>
    );

    const unreadCount = notifications.filter((n) => !n.read).length;

    return (
        <SafeAreaView style={styles.container}>
            <StatusBar barStyle="dark-content" translucent backgroundColor="transparent" />

            {/* Header */}
            <View style={styles.header}>
                <BackButton />
                <Text style={styles.title}>Notifications</Text>
                <View style={styles.spacer} />
            </View>

            {/* Loading State */}
            {loading && !refreshing && (
                <View style={styles.emptyStateContainer}>
                    <ActivityIndicator size="large" color="#3b82f6" />
                    <Text style={styles.emptyTitle}>Chargement...</Text>
                </View>
            )}

            {/* Error State */}
            {error && (
                <View style={styles.emptyStateContainer}>
                    <View style={styles.emptyIconBackground}>
                        <AlertCircle size={40} color="#ef4444" />
                    </View>
                    <Text style={styles.emptyTitle}>Erreur</Text>
                    <Text style={styles.emptyMessage}>{error}</Text>
                    <TouchableOpacity
                        style={{
                            marginTop: 20,
                            paddingHorizontal: 20,
                            paddingVertical: 10,
                            backgroundColor: '#3b82f6',
                            borderRadius: 8,
                        }}
                        onPress={handleRefresh}
                    >
                        <Text style={{ color: '#fff', fontWeight: '600' }}>Réessayer</Text>
                    </TouchableOpacity>
                </View>
            )}

            {/* Notification Stats */}
            {!loading && !error && notifications.length > 0 && (
                <View style={styles.statsContainer}>
                    <Text style={styles.statsText}>
                        {unreadCount} {unreadCount <= 1 ? 'nouveau' : 'nouveaux'}
                    </Text>
                    {notifications.length > 0 && (
                        <TouchableOpacity onPress={handleClearAll}>
                            <Text style={styles.clearAllButton}>Effacer tout</Text>
                        </TouchableOpacity>
                    )}
                </View>
            )}

            {/* Notifications List */}
            {!loading && !error && notifications.length > 0 ? (
                <FlatList
                    data={notifications}
                    renderItem={renderNotification}
                    keyExtractor={(item) => item.id}
                    contentContainerStyle={styles.listContainer}
                    scrollEnabled={true}
                    refreshing={refreshing}
                    onRefresh={handleRefresh}
                />
            ) : !loading && !error ? (
                <View style={styles.emptyStateContainer}>
                    <View style={styles.emptyIconBackground}>
                        <Info size={40} color="#9ca3af" />
                    </View>
                    <Text style={styles.emptyTitle}>Aucune notification</Text>
                    <Text style={styles.emptyMessage}>
                        Vous n'avez pas de notification pour le moment
                    </Text>
                </View>
            ) : null}
        </SafeAreaView>
    );
}
