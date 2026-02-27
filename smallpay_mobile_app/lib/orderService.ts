import { api, handleApiResponse, handleApiError } from './api';
import { Order, Payment, PaymentSchedule } from '@/types';

export const OrderService = {
  /**
   * Récupérer toutes les commandes de l'utilisateur
   */
  getUserOrders: async (userId: string) => {
    try {
      const response = await api.get(`/orders`);
      const result = handleApiResponse(response);
      
      if (result.success && Array.isArray(result.data)) {
        // Le backend retourne déjà les commandes avec les produits
        return { ...result, data: result.data as Order[] };
      }
      
      return result;
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Récupérer les détails d'une commande
   */
  getOrderDetails: async (orderId: string) => {
    try {
      const response = await api.get(`/orders/${orderId}`);
      const result = handleApiResponse(response);
      
      if (result.success && result.data) {
        return { ...result, data: result.data as Order };
      }
      
      return result;
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Créer une nouvelle commande
   */
  createOrder: async (orderData: {
    product_id: string;
    total_amount: number;
    deposit_amount: number;
    remaining_amount: number;
    payment_duration: number;
    majoration_rate: number;
  }) => {
    try {
      const response = await api.post(`/orders`, orderData);
      const result = handleApiResponse(response);
      
      if (result.success && result.data) {
        return { ...result, data: result.data as Order };
      }
      
      return result;
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Récupérer l'historique des paiements d'une commande
   */
  getPaymentHistory: async (orderId: string) => {
    try {
      const response = await api.get(`/orders/${orderId}/history`);
      const result = handleApiResponse(response);
      
      if (result.success && Array.isArray(result.data)) {
        return { ...result, data: result.data as PaymentSchedule[] };
      }
      
      return result;
    } catch (error) {
      return handleApiError(error);
    }
  },

  /**
   * Initier un paiement
   */
  initiatePayment: async (paymentData: {
    order_id: string;
    schedule_id?: string;
    method: 'MTN' | 'ORANGE';
    amount: number;
    phone: string;
  }) => {
    try {
      const response = await api.post(`/payments/initiate`, paymentData);
      const result = handleApiResponse<Payment>(response);
      
      return result;
    } catch (error) {
      return handleApiError(error);
    }
  },
};
