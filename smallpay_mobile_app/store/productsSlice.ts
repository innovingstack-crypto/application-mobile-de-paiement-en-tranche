import { createAsyncThunk, createSlice, PayloadAction } from '@reduxjs/toolkit';
import { Product } from '@/types';
import { ProductService, Category } from '@/lib/productService';

interface ProductsState {
  categories: Category[];
  products: Product[];
  filteredProducts: Product[];
  selectedProduct: Product | null;
  selectedCategory: string | null;
  loading: boolean;
  error: string | null;
}

const initialState: ProductsState = {
  categories: [],
  products: [],
  filteredProducts: [],
  selectedProduct: null,
  selectedCategory: null,
  loading: false,
  error: null,
};

// Thunks
export const fetchCategories = createAsyncThunk(
  'products/fetchCategories',
  async (_, { rejectWithValue }) => {
    try {
      const result = await ProductService.getCategories();
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      return result.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const fetchProducts = createAsyncThunk(
  'products/fetchProducts',
  async (_, { rejectWithValue }) => {
    try {
      const result = await ProductService.getAllProducts();
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      return result.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const fetchProductsByCategory = createAsyncThunk(
  'products/fetchByCategory',
  async (category: string, { rejectWithValue }) => {
    try {
      const result = await ProductService.getProductsByCategory(category);
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      return result.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

export const fetchProductById = createAsyncThunk(
  'products/fetchProductById',
  async (productId: string, { rejectWithValue }) => {
    try {
      const result = await ProductService.getProductDetails(productId);
      if (!result.success) {
        return rejectWithValue(result.error);
      }
      return result.data;
    } catch (error: any) {
      return rejectWithValue(error.message);
    }
  }
);

const productsSlice = createSlice({
  name: 'products',
  initialState,
  reducers: {
    setSelectedProduct: (state, action: PayloadAction<Product | null>) => {
      state.selectedProduct = action.payload;
    },
    selectCategory: (state, action: PayloadAction<string | null>) => {
      state.selectedCategory = action.payload;
    },
    clearError: (state) => {
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch Categories
      .addCase(fetchCategories.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchCategories.fulfilled, (state, action: PayloadAction<any>) => {
        state.loading = false;
        const categories = action.payload || [];
        // Ajouter une catégorie "Tout" au début
        const allCategory = { id: 'all', name: 'Tout', label: 'Tout' };
        state.categories = [allCategory, ...categories];
        // Sélectionner "Tout" par défaut si aucune catégorie n'est sélectionnée
        if (!state.selectedCategory) {
          state.selectedCategory = 'all';
        }
      })
      .addCase(fetchCategories.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })

      // Fetch All Products
      .addCase(fetchProducts.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchProducts.fulfilled, (state, action: PayloadAction<any>) => {
        state.loading = false;
        state.products = action.payload || [];
        state.filteredProducts = action.payload || [];
      })
      .addCase(fetchProducts.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })

      // Fetch Products by Category
      .addCase(fetchProductsByCategory.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchProductsByCategory.fulfilled, (state, action: PayloadAction<any>) => {
        state.loading = false;
        state.filteredProducts = action.payload || [];
      })
      .addCase(fetchProductsByCategory.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      })

      // Fetch Product by ID
      .addCase(fetchProductById.fulfilled, (state, action: PayloadAction<any>) => {
        state.selectedProduct = action.payload;
      });
  },
});

export const { setSelectedProduct, selectCategory, clearError } = productsSlice.actions;
export default productsSlice.reducer;
