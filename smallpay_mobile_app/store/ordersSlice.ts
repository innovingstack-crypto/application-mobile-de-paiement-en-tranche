import { Order, Payment, PaymentSchedule } from '@/types';
import { createAsyncThunk, createSlice, PayloadAction } from '@reduxjs/toolkit';
import { OrderService } from '@/lib/orderService';

interface OrdersState {
  orders: Order[];
  currentOrder: Order | null;
  paymentSchedules: PaymentSchedule[];
  payments: Payment[];
  loading: boolean;
  error: string | null;
}

const initialState: OrdersState = {
  orders: [],
  currentOrder: null,
  paymentSchedules: [],
  payments: [],
  loading: false,
  error: null,
};

export const createOrder = createAsyncThunk(
  'orders/createOrder',
  async (
    orderData: {
      product_id: string;
      total_amount: number;
      deposit_amount: number;
      remaining_amount: number;
      payment_duration: number;
      majoration_rate: number;
    },
    { rejectWithValue }
  ) => {
    try {
      const result = await OrderService.createOrder(orderData);
      
      if (result.success && result.data) {
        return result.data;
      }
      
      return rejectWithValue(result.error || 'Erreur lors de la création de la commande');
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const fetchOrders = createAsyncThunk(
  'orders/fetchOrders',
  async (userId: string, { rejectWithValue }) => {
    try {
      const result = await OrderService.getUserOrders(userId);
      
      if (result.success && Array.isArray(result.data)) {
        return result.data;
      }
      
      return rejectWithValue(result.error || 'Erreur lors de la récupération des commandes');
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const fetchPaymentSchedules = createAsyncThunk(
  'orders/fetchPaymentSchedules',
  async (orderId: string, { rejectWithValue }) => {
    try {
      const result = await OrderService.getPaymentHistory(orderId);
      
      if (result.success && Array.isArray(result.data)) {
        return result.data;
      }
      
      return rejectWithValue(result.error || 'Erreur lors de la récupération des tranches');
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const makePayment = createAsyncThunk(
  'orders/makePayment',
  async (
    paymentData: {
      order_id: string;
      schedule_id?: string;
      method: 'MTN' | 'ORANGE';
      amount: number;
      phone: string;
    },
    { rejectWithValue }
  ) => {
    try {
      const result = await OrderService.initiatePayment(paymentData);
      
      if (result.success) {
        return result.data;
      }
      
      return rejectWithValue(result.error || 'Erreur lors de l\'initiation du paiement');
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

const ordersSlice = createSlice({
  name: 'orders',
  initialState,
  reducers: {
    setCurrentOrder: (state, action: PayloadAction<Order | null>) => {
      state.currentOrder = action.payload;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(createOrder.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(createOrder.fulfilled, (state, action) => {
         state.loading = false;
         state.currentOrder = action.payload as Order;
       })
      .addCase(createOrder.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      .addCase(fetchOrders.fulfilled, (state, action) => {
        state.orders = action.payload;
      })
      .addCase(fetchPaymentSchedules.fulfilled, (state, action) => {
        state.paymentSchedules = action.payload;
      })
      .addCase(makePayment.pending, (state) => {
        state.loading = true;
      })
      .addCase(makePayment.fulfilled, (state, action) => {
        state.loading = false;
        state.payments.push(action.payload as Payment);
      })
      .addCase(makePayment.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });
  },
});

export const { setCurrentOrder } = ordersSlice.actions;
export default ordersSlice.reducer;
