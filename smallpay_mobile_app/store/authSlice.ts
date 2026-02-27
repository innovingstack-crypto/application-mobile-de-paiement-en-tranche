import { AuthService } from '@/lib/authService';
import { User } from '@/types';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { createAsyncThunk, createSlice, PayloadAction } from '@reduxjs/toolkit';

interface AuthState {
  user: User | null;
  token: string | null;
  loading: boolean;
  error: string | null;
  verificationMethod: 'email' | 'sms' | null;
  otpData: {
    identifier: string;
    method: 'email' | 'sms';
    expires_at?: string;
    code?: string; // Le code OTP pour reset password
  } | null;
}

const initialState: AuthState = {
  user: null,
  token: null,
  loading: false,
  error: null,
  verificationMethod: null,
  otpData: null,
};

export const checkAvailability = createAsyncThunk(
  'auth/checkAvailability',
  async (
    data: { email?: string; phone?: string },
    { rejectWithValue }
  ) => {
    try {
      const result = await AuthService.checkAvailability(data);
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      return result.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const register = createAsyncThunk(
  'auth/register',
  async (
    data: {
      name: string;
      email: string;
      phone?: string;
      password: string;
      confirmPassword: string;
      verification_method: 'email' | 'sms';
      
    },
    { rejectWithValue }
  ) => {
    try {
      const result = await AuthService.register(data);
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      return result.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const requestOTP = createAsyncThunk(
  'auth/requestOTP',
  async (
    data: { identifier: string; method: 'email' | 'sms' },
    { rejectWithValue }
  ) => {
    try {
      const result = await AuthService.requestOTP(data);
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      return result.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const requestPasswordReset = createAsyncThunk(
  'auth/requestPasswordReset',
  async (
    data: { identifier: string; method: 'email' | 'sms' },
    { rejectWithValue }
  ) => {
    try {
      const result = await AuthService.requestPasswordReset(data);
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      return result.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const verifyOTP = createAsyncThunk(
  'auth/verifyOTP',
  async (
    data: { identifier: string; code: string; method: 'email' | 'sms' },
    { rejectWithValue }
  ) => {
    try {
      const result = await AuthService.verifyOTP(data);
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      
      // Stocker le token
      if (result.data?.token) {
        await AsyncStorage.setItem('authToken', result.data.token);
      }
      
      return result.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const verifyPasswordResetOTP = createAsyncThunk(
  'auth/verifyPasswordResetOTP',
  async (
    data: { identifier: string; code: string; method: 'email' | 'sms' },
    { rejectWithValue }
  ) => {
    try {
      const result = await AuthService.verifyPasswordResetOTP(data);
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      
      return result.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const checkOTPStatus = createAsyncThunk(
  'auth/checkOTPStatus',
  async (
    data: { identifier: string; method: 'email' | 'sms' },
    { rejectWithValue }
  ) => {
    try {
      const result = await AuthService.checkOTPStatus(data);
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      return result.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const resetPassword = createAsyncThunk(
  'auth/resetPassword',
  async (
    data: { identifier: string; password: string; method: 'email' | 'sms' },
    { rejectWithValue }
  ) => {
    try {
      const result = await AuthService.resetPassword(data);
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      return result;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const login = createAsyncThunk(
  'auth/login',
  async (
    data: { email: string; password: string },
    { rejectWithValue }
  ) => {
    try {
      const result = await AuthService.login(data);
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      
      // Stocker le token et l'utilisateur
      if (result.data?.token) {
        await AsyncStorage.setItem('authToken', result.data.token);
      }
      if (result.data?.user) {
        await AsyncStorage.setItem('user', JSON.stringify(result.data.user));
      }
      
      return result.data;
    } catch (error: any) {
      // Capturer les données d'erreur axios
      if (error?.response?.data) {
        const errorData = error.response.data;
        if (errorData.unverified) {
          return rejectWithValue({
            message: errorData.message,
            unverified: true,
            identifier: errorData.identifier,
            method: errorData.method
          });
        }
      }
      return rejectWithValue(error.message);
    }
  }
);

export const loadUser = createAsyncThunk(
  'auth/loadUser',
  async (_, { rejectWithValue }) => {
    try {
      const userJson = await AsyncStorage.getItem('user');
      if (!userJson) {
        return rejectWithValue('No user found');
      }
      return JSON.parse(userJson);
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const logout = createAsyncThunk('auth/logout', async () => {
  await AsyncStorage.removeItem('user');
});

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    setVerificationMethod: (state, action: PayloadAction<'email' | 'sms'>) => {
      state.verificationMethod = action.payload;
    },
    clearOTPData: (state) => {
      state.otpData = null;
    },
    setOTPCode: (state, action: PayloadAction<string>) => {
      if (state.otpData) {
        state.otpData.code = action.payload;
      }
    },
  },
  extraReducers: (builder) => {
    builder
      // Check availability
      .addCase(checkAvailability.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(checkAvailability.fulfilled, (state) => {
        state.loading = false;
      })
      .addCase(checkAvailability.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Register
      .addCase(register.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(register.fulfilled, (state, action: PayloadAction<any>) => {
        state.loading = false;
        // Stocker les données de l'OTP pour l'écran suivant
        if (action.payload?.otp_info) {
          state.otpData = {
            identifier: action.payload.otp_info.identifier,
            method: action.payload.otp_info.identifier.includes('@') ? 'email' : 'sms',
            expires_at: action.payload.otp_info.expires_at,
          };
        }
      })
      .addCase(register.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Request OTP
      .addCase(requestOTP.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(requestOTP.fulfilled, (state, action: PayloadAction<any>) => {
        state.loading = false;
        state.otpData = {
          identifier: action.payload.identifier,
          method: action.payload.method,
          expires_at: action.payload.expires_at,
        };
        state.verificationMethod = action.payload.method;
      })
      .addCase(requestOTP.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })

      // Request Password Reset
      .addCase(requestPasswordReset.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(requestPasswordReset.fulfilled, (state, action: PayloadAction<any>) => {
        state.loading = false;
        state.otpData = {
          identifier: action.payload.identifier,
          method: action.payload.method,
          expires_at: action.payload.expires_at,
        };
      })
      .addCase(requestPasswordReset.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Verify OTP
      .addCase(verifyOTP.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(verifyOTP.fulfilled, (state, action: PayloadAction<any>) => {
        state.loading = false;
        state.user = action.payload.user;
        state.token = action.payload.token;
        state.otpData = null;
        state.verificationMethod = null;
      })
      .addCase(verifyOTP.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })

      // Verify Password Reset OTP
      .addCase(verifyPasswordResetOTP.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(verifyPasswordResetOTP.fulfilled, (state, action: PayloadAction<any>) => {
        state.loading = false;
        // Ne pas vider otpData - on en aura besoin pour reset-password-new
      })
      .addCase(verifyPasswordResetOTP.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Check OTP Status
      .addCase(checkOTPStatus.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(checkOTPStatus.fulfilled, (state) => {
        state.loading = false;
      })
      .addCase(checkOTPStatus.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Reset Password
      .addCase(resetPassword.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(resetPassword.fulfilled, (state) => {
        state.loading = false;
      })
      .addCase(resetPassword.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })
      
      // Login classique
      .addCase(login.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(login.fulfilled, (state, action: PayloadAction<any>) => {
        state.loading = false;
        state.user = action.payload.user;
        state.token = action.payload.token;
      })
      .addCase(login.rejected, (state, action: PayloadAction<any>) => {
        state.loading = false;
        const payload = action.payload;
        
        // Si c'est un compte non vérifié, stocker les données pour l'OTP
        if (typeof payload === 'object' && payload?.unverified) {
          state.otpData = {
            identifier: payload.identifier,
            method: payload.method,
          };
          state.verificationMethod = payload.method;
          state.error = payload.message || 'Votre compte n\'est pas encore vérifié';
        } else {
          // Erreur normale
          state.error = typeof payload === 'string' ? payload : (payload?.message || 'Erreur de connexion');
        }
      })
      
      // Load user
      .addCase(loadUser.fulfilled, (state, action: PayloadAction<User>) => {
        state.user = action.payload;
      })
      
      // Logout
      .addCase(logout.fulfilled, (state) => {
        state.user = null;
        state.token = null;
        state.otpData = null;
        state.verificationMethod = null;
      });
  },
});

export const { clearError, setVerificationMethod, clearOTPData, setOTPCode } = authSlice.actions;
export default authSlice.reducer;
