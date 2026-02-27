import { PaymentOption } from '@/types';

export const MAJORATION_RATES = { 1: 0.05, 3: 0.10, 6: 0.20, 12: 0.30 };
export const DEPOSIT_RATE = 0.6;

export function calculatePaymentOptions(basePrice: number): PaymentOption[] {
  const options: PaymentOption[] = [];
  const deposit = basePrice * DEPOSIT_RATE; // Acompte fixe à 60% du prix de base
  [1, 3, 6, 12].forEach((duration) => {
    const majorationRate = MAJORATION_RATES[duration as keyof typeof MAJORATION_RATES];
    const totalPrice = basePrice * (1 + majorationRate);
    const monthlyPayment = (totalPrice - deposit) / duration;
    options.push({ duration, majorationRate: majorationRate * 100, totalPrice, deposit, monthlyPayment, remainingAmount: totalPrice - deposit });
  });
  return options;
}

export function validatePhone(phone: string): boolean {
  return /^(237)?6[0-9]{8}$/.test(phone.replace(/\s/g, ''));
}

export function validateEmail(email: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
