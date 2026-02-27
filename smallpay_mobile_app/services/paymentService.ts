import { api } from '@/lib/api';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface PaymentInitiationResponse {
  success: boolean;
  reference: string;
  amount: number;
  currency: string;
  phone: string;
  message: string;
  redirect_to: string;
  redirect_url: string;
  error?: string;
}

interface PaymentStatusResponse {
  success: boolean;
  status: 'pending' | 'success' | 'failed';
  reference: string;
  message?: string;
  error_code?: string;
  error_reason?: string;
  is_insufficient_balance?: boolean;
  redirect_url?: string;
}

interface OrderPayment {
  id: number;
  order_id: number;
  reference: string;
  amount: number;
  status: 'pending' | 'success' | 'failed';
  created_at: string;
  payment_type: 'deposit' | 'installment';
}

interface PaymentSchedule {
  id: number;
  order_id: number;
  due_date: string;
  amount: number;
  installment_number: number;
  status: 'pending' | 'paid' | 'overdue';
  created_at: string;
}

class PaymentService {
  /**
   * Initier un paiement d'acompte pour une commande
   */
  async initiateDeposit(orderId: number): Promise<PaymentInitiationResponse> {
    try {
      const response = await api.post<PaymentInitiationResponse>('/payments/deposit', {
        order_id: orderId,
      });

      return response.data;
    } catch (error: any) {
      throw {
        success: false,
        error: error.response?.data?.message || 'Erreur lors de l\'initiation du paiement',
        reference: null,
      };
    }
  }

  /**
   * Initier un paiement mensuel pour une commande
   */
  async initiateMonthlyPayment(orderId: number): Promise<PaymentInitiationResponse> {
    try {
      const response = await api.post<PaymentInitiationResponse>('/payments/monthly', {
        order_id: orderId,
      });

      return response.data;
    } catch (error: any) {
      throw {
        success: false,
        error: error.response?.data?.message || 'Erreur lors de l\'initiation du paiement',
        reference: null,
      };
    }
  }

  /**
   * Vérifier le statut d'un paiement
   */
  async checkPaymentStatus(reference: string): Promise<PaymentStatusResponse> {
    try {
      const response = await api.get<PaymentStatusResponse>(`/payments/${reference}/status`);
      return response.data;
    } catch (error: any) {
      throw {
        success: false,
        status: 'failed' as const,
        reference,
        error_reason: error.response?.data?.message || 'Erreur lors de la vérification du statut',
      };
    }
  }

  /**
   * Récupérer l'historique des paiements d'une commande
   */
  async getOrderPayments(orderId: number): Promise<OrderPayment[]> {
    try {
      const response = await api.get<{ data: OrderPayment[] }>(`/orders/${orderId}/payments`);
      return response.data.data || [];
    } catch (error: any) {
      console.error('Erreur lors de la récupération des paiements:', error);
      return [];
    }
  }

  /**
   * Récupérer le calendrier de paiement d'une commande
   */
  async getPaymentSchedule(orderId: number): Promise<PaymentSchedule[]> {
    try {
      const response = await api.get<{ data: PaymentSchedule[] }>(`/orders/${orderId}/schedule`);
      return response.data.data || [];
    } catch (error: any) {
      console.error('Erreur lors de la récupération du calendrier:', error);
      return [];
    }
  }

  /**
   * Sauvegarder la dernière référence de paiement en cours
   */
  async saveLastPaymentReference(reference: string, orderId: number): Promise<void> {
    try {
      await AsyncStorage.setItem(
        `payment_reference_${orderId}`,
        JSON.stringify({ reference, timestamp: Date.now() })
      );
    } catch (error) {
      console.error('Erreur lors de la sauvegarde de la référence:', error);
    }
  }

  /**
   * Récupérer la dernière référence de paiement en cours
   */
  async getLastPaymentReference(orderId: number): Promise<string | null> {
    try {
      const data = await AsyncStorage.getItem(`payment_reference_${orderId}`);
      if (!data) return null;
      const parsed = JSON.parse(data);
      // Valide si moins de 24 heures
      if (Date.now() - parsed.timestamp < 24 * 60 * 60 * 1000) {
        return parsed.reference;
      }
      return null;
    } catch (error) {
      console.error('Erreur lors de la récupération de la référence:', error);
      return null;
    }
  }

  /**
   * Calculer les options de paiement (durées)
   */
  calculatePaymentOptions(totalAmount: number) {
    const durations = [3, 6, 12]; // mois
    const interestRate = 0.05; // 5% par an

    return durations.map(duration => {
      const depositAmount = Math.round(totalAmount * 0.60); // 60% d'acompte
      const remainingAmount = totalAmount - depositAmount;
      
      // Calcul simple des intérêts
      const monthlyRate = interestRate / 12;
      const totalWithInterest = remainingAmount * (1 + monthlyRate * duration);
      const monthlyPayment = Math.ceil(totalWithInterest / duration);

      return {
        duration,
        depositAmount,
        remainingAmount: totalWithInterest,
        monthlyPayment,
        totalInterest: totalWithInterest - remainingAmount,
      };
    });
  }

  /**
   * Formater un montant en devise locale
   */
  formatCurrency(amount: number, currency = 'FCFA'): string {
    return `${amount.toLocaleString('fr-CM')} ${currency}`;
  }

  /**
   * Valider si le paiement peut être initié
   */
  async canInitiatePayment(orderId: number, kycStatus: string): Promise<{ can: boolean; reason?: string }> {
    // Vérifier si le KYC est approuvé
    if (kycStatus !== 'approved') {
      return {
        can: false,
        reason: 'Votre KYC doit être approuvé pour effectuer le paiement',
      };
    }

    // Autres vérifications peuvent être ajoutées ici
    return { can: true };
  }
}

export default new PaymentService();
