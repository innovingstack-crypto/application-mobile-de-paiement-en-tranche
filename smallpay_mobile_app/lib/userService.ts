import { api, handleApiResponse, handleApiError } from './api';
import { OrderService } from './orderService';

export interface UserStats {
  totalOrders: number;
  paidOrders: number;
  activeOrders: number;
  pendingOrders: number;
  totalSpent?: number;
}

export const UserService = {
  /**
   * Récupérer le profil utilisateur actuel
   */
  getCurrentUser: async () => {
    try {
      const response = await api.get(`/auth/profile`);
      const result = handleApiResponse(response);
      
      if (result.success && result.data) {
        return { ...result, data: result.data };
      }
      
      return result;
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Récupérer les statistiques de l'utilisateur
   * Calcule les stats à partir des commandes de l'utilisateur
   */
  getUserStats: async () => {
    try {
      // Récupérer les commandes de l'utilisateur
      const ordersResult = await OrderService.getUserOrders('');
      
      if (ordersResult.success && Array.isArray(ordersResult.data)) {
        const orders = ordersResult.data;
        
        const stats: UserStats = {
          totalOrders: orders.length,
          paidOrders: orders.filter(o => o.status === 'completed').length,
          activeOrders: orders.filter(o => o.status === 'active').length,
          pendingOrders: orders.filter(o => o.status === 'pending').length,
          totalSpent: orders.reduce((sum, order) => sum + (order.total_amount || 0), 0),
        };
        
        return { success: true, data: stats };
      }
      
      return ordersResult as any;
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Mettre à jour le profil utilisateur
   */
  updateProfile: async (userData: {
    name?: string;
    email?: string;
    phone?: string;
    avatar?: string;
  }) => {
    try {
      const response = await api.put(`/user/profile`, userData);
      const result = handleApiResponse(response);
      
      if (result.success && result.data) {
        return { ...result, data: result.data };
      }
      
      return result;
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Changer le mot de passe
   */
  changePassword: async (passwordData: {
    current_password: string;
    new_password: string;
    new_password_confirmation: string;
  }) => {
    try {
      const response = await api.post(`/user/change-password`, passwordData);
      const result = handleApiResponse(response);
      
      return result;
    } catch (error) {
      return handleApiError(error);
    }
  },
};
