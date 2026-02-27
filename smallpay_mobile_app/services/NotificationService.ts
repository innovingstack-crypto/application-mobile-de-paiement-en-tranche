import { api, handleApiResponse, handleApiError } from '@/lib/api';

export interface NotificationData {
  id: string;
  type: 'success' | 'warning' | 'info' | 'kyc_approved' | 'kyc_rejected' | 'order_created' | 'payment_success' | 'payment_failed' | 'order_status_changed' | 'payment_overdue' | 'payment_reminder' | 'refund' | 'account_blocked';
  title: string;
  message: string;
  description?: string;
  timestamp?: string;
  read: boolean;
  read_at?: string;
  created_at?: string;
  related_data?: any;
}

export interface PaginationData {
  current_page?: number;
  per_page?: number;
  total?: number;
  last_page?: number;
}

class NotificationService {
  /**
    * Récupérer la liste des notifications de l'utilisateur
    */
   async fetchNotifications(page: number = 1, perPage: number = 20): Promise<{
     success: boolean;
     data: NotificationData[];
     pagination?: PaginationData;
     error?: string;
   }> {
     try {
       const response = await api.get('/notifications', {
         params: {
           page,
           per_page: perPage,
         },
       });

       const result = handleApiResponse<{ data?: any[]; pagination?: PaginationData }>(response);

       if (result.success && result.data) {
         // Formater les notifications reçues du backend
         const notificationList = Array.isArray(result.data) ? result.data : (result.data as any).data || [];
         const formattedData = notificationList.map((notification: any) =>
           this.formatNotification(notification)
         );

         return {
           success: true,
           data: formattedData,
           pagination: (result.data as any)?.pagination as PaginationData | undefined,
         };
       } else {
         return {
           success: false,
           error: result.error || 'Erreur lors du chargement des notifications',
           data: [],
         };
       }
     } catch (error: any) {
       const errorResult = handleApiError(error);
       return {
         success: false,
         error: errorResult.error,
         data: [],
       };
     }
   }

  /**
   * Marquer une notification comme lue
   */
  async markAsRead(notificationId: string) {
    try {
      const response = await api.put(`/notifications/${notificationId}/read`);
      const result = handleApiResponse(response);

      return {
        success: result.success,
        message: result.message || 'Notification marquée comme lue',
      };
    } catch (error: any) {
      const errorResult = handleApiError(error);
      return {
        success: false,
        message: errorResult.error,
      };
    }
  }

  /**
   * Obtenir le nombre de notifications non lues
   */
  async getUnreadCount() {
    try {
      const response = await api.get('/notifications/unread-count');
      const result = handleApiResponse<{ unread_count?: number }>(response);

      if (result.success && result.data) {
        const count = (result.data as any)?.unread_count || (typeof result.data === 'number' ? result.data : 0);
        return {
          success: true,
          count,
        };
      } else {
        return {
          success: false,
          count: 0,
        };
      }
    } catch (error: any) {
      console.error('Error fetching unread count:', error);
      return {
        success: false,
        count: 0,
      };
    }
  }

  /**
   * Convertir une notification de la BD au format UI
   * Supporte le format du backend Laravel (is_read, created_at, etc.)
   */
  formatNotification(dbNotification: any): NotificationData {
    // Le backend retourne 'is_read', on le mappe à 'read'
    const isRead = dbNotification.is_read !== undefined 
      ? dbNotification.is_read 
      : dbNotification.read || false;

    const baseNotification: NotificationData = {
      id: dbNotification.id?.toString() || '',
      type: this.mapNotificationType(dbNotification.type),
      title: dbNotification.title || '',
      message: dbNotification.message || '',
      description: dbNotification.message || '',
      timestamp: this.formatTimestamp(dbNotification.created_at),
      read: isRead,
      read_at: dbNotification.read_at,
      created_at: dbNotification.created_at,
      related_data: {
        related_order_id: dbNotification.related_order_id,
        related_schedule_id: dbNotification.related_schedule_id,
        ...dbNotification.related_data
      },
    };

    return baseNotification;
  }

  /**
   * Mapper les types de notifications de la BD aux types UI
   * Supporte tous les types du backend Laravel
   */
  private mapNotificationType(dbType: string): NotificationData['type'] {
    const typeMap: Record<string, NotificationData['type']> = {
      // KYC types
      kyc_approved: 'kyc_approved',
      kyc_rejected: 'kyc_rejected',
      kyc_pending: 'info',
      
      // Order types
      order_created: 'order_created',
      order_status_changed: 'order_status_changed',
      order_shipped: 'info',
      order_delivered: 'success',
      order_cancelled: 'warning',
      
      // Payment types
      payment_success: 'payment_success',
      payment_failed: 'payment_failed',
      payment_overdue: 'payment_overdue',
      payment_reminder: 'payment_reminder',
      
      // Other types
      refund: 'refund',
      account_blocked: 'account_blocked',
    };

    return typeMap[dbType] || 'info';
  }

  /**
   * Formater le timestamp en format lisible
   */
  private formatTimestamp(dateString: string): string {
    if (!dateString) return '';

    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'À l\'instant';
    if (diffMins < 60) return `${diffMins} min`;
    if (diffHours < 24) return `${diffHours} h`;
    if (diffDays < 7) return `${diffDays} j`;

    return date.toLocaleDateString('fr-FR', {
      day: 'numeric',
      month: 'short',
      year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
    });
  }

  /**
   * Obtenir le titre et description basés sur le type de notification
   */
  getNotificationDetails(notification: NotificationData) {
    const details: Record<string, { icon: string; bgColor: string; textColor: string }> = {
      // KYC types
      kyc_approved: {
        icon: '✓',
        bgColor: '#d1fae5',
        textColor: '#065f46',
      },
      kyc_rejected: {
        icon: '✕',
        bgColor: '#fee2e2',
        textColor: '#991b1b',
      },
      
      // Order types
      order_created: {
        icon: '📦',
        bgColor: '#dbeafe',
        textColor: '#1e40af',
      },
      order_status_changed: {
        icon: '📦',
        bgColor: '#dbeafe',
        textColor: '#1e40af',
      },
      
      // Payment types
      payment_success: {
        icon: '✓',
        bgColor: '#d1fae5',
        textColor: '#065f46',
      },
      payment_failed: {
        icon: '✕',
        bgColor: '#fee2e2',
        textColor: '#991b1b',
      },
      payment_reminder: {
        icon: '⚠',
        bgColor: '#fef3c7',
        textColor: '#92400e',
      },
      payment_overdue: {
        icon: '⚠',
        bgColor: '#fee2e2',
        textColor: '#991b1b',
      },
      
      // Other types
      refund: {
        icon: '💰',
        bgColor: '#d1fae5',
        textColor: '#065f46',
      },
      account_blocked: {
        icon: '🔒',
        bgColor: '#fee2e2',
        textColor: '#991b1b',
      },
      
      // Generic types
      success: {
        icon: '✓',
        bgColor: '#d1fae5',
        textColor: '#065f46',
      },
      warning: {
        icon: '⚠',
        bgColor: '#fef3c7',
        textColor: '#92400e',
      },
      info: {
        icon: 'ℹ',
        bgColor: '#dbeafe',
        textColor: '#1e40af',
      },
    };

    return details[notification.type] || details.info;
  }
}

export default new NotificationService();
