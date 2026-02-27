export interface User {
  id: string;
  phone: string;
  email?: string;
  name: string;
  is_verified: boolean;
  credit_score: number;
  created_at: string;
  kyc_status?: 'pending' | 'approved' | 'under_review' | 'rejected';
}

export interface Product {
  id: string;
  name: string;
  description: string;
  price: number;
  stock: number;
  image_url?: string;
  images?: string[]; // Array of image URLs (main + secondary)
  category: string;
  is_active: boolean;
  created_at: string;
}

export interface Order {
  id: string;
  user_id: string;
  product_id: string;
  total_amount: number;
  deposit_amount: number;
  remaining_amount: number;
  payment_duration: number;
  majoration_rate: number;
  status: 'pending' | 'active' | 'completed' | 'cancelled';
  created_at: string;
  next_due_date?: string;
  product?: Product;
}

export interface PaymentSchedule {
  id: string;
  order_id: string;
  due_date: string;
  amount: number;
  status: 'pending' | 'paid' | 'overdue';
  installment_number: number;
  created_at: string;
}

export interface Payment {
  id: string;
  order_id: string;
  schedule_id?: string;
  method: 'MTN' | 'ORANGE' | 'CARD';
  transaction_id?: string;
  amount: number;
  status: 'pending' | 'completed' | 'failed';
  payment_date: string;
  created_at: string;
}

export interface PaymentOption {
  duration: number;
  majorationRate: number;
  totalPrice: number;
  deposit: number;
  monthlyPayment: number;
  remainingAmount: number;
}

export type FilterStatus = 'all' | 'en_cours' | 'paye' | 'retard';

export interface MenuSection {
  title: string;
  items: Array<{
    icon: any;
    label: string;
    value?: string;
    action: () => void;
  }>;
}