// Script pour initialiser des données de test dans AsyncStorage
// À exécuter une seule fois au démarrage de l'app

import AsyncStorage from '@react-native-async-storage/async-storage';

const MOCK_PRODUCTS = [
  {
    id: 'prod-1',
    name: 'iPhone 15 Pro',
    description: 'Dernier modèle Apple avec A17 Pro chip',
    price: 1200000,
    stock: 5,
    image_url: 'https://images.unsplash.com/photo-1592286927505-1def25115558?w=400',
    category: 'Téléphones',
    is_active: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'prod-2',
    name: 'Samsung Galaxy S24',
    description: 'Smartphone haute performance avec AI intégrée',
    price: 900000,
    stock: 8,
    image_url: 'https://images.unsplash.com/photo-1511707267537-b85faf00021e?w=400',
    category: 'Téléphones',
    is_active: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'prod-3',
    name: 'MacBook Pro 14"',
    description: 'Ordinateur portable professionnel avec M3 Max',
    price: 2500000,
    stock: 3,
    image_url: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400',
    category: 'Ordinateurs',
    is_active: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'prod-4',
    name: 'Dell XPS 15',
    description: 'Laptop Windows haute performance',
    price: 1800000,
    stock: 4,
    image_url: 'https://images.unsplash.com/photo-1588872657840-90a53d2b5534?w=400',
    category: 'Ordinateurs',
    is_active: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'prod-5',
    name: 'Sony WH-1000XM5',
    description: 'Casque Bluetooth avec réduction de bruit',
    price: 380000,
    stock: 12,
    image_url: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400',
    category: 'Audio',
    is_active: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'prod-6',
    name: 'PlayStation 5',
    description: 'Console de jeu nouvelle génération',
    price: 500000,
    stock: 6,
    image_url: 'https://images.unsplash.com/photo-1607674814121-fa91dbc16e75?w=400',
    category: 'Gaming',
    is_active: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'prod-7',
    name: 'Xbox Series X',
    description: 'Console Microsoft puissante pour le gaming',
    price: 550000,
    stock: 5,
    image_url: 'https://images.unsplash.com/photo-1538481143235-5d630a6a7b47?w=400',
    category: 'Gaming',
    is_active: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'prod-8',
    name: 'Canon EOS R6',
    description: 'Appareil photo reflex numérique professionnel',
    price: 3500000,
    stock: 2,
    image_url: 'https://images.unsplash.com/photo-1606986628025-35d57e735ae0?w=400',
    category: 'Caméras',
    is_active: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'prod-9',
    name: 'iPad Pro 12.9"',
    description: 'Tablette Apple avec M2 chip',
    price: 1400000,
    stock: 7,
    image_url: 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=400',
    category: 'Tablettes',
    is_active: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'prod-10',
    name: 'Samsung Galaxy Tab S9',
    description: 'Tablette Android haute performance',
    price: 800000,
    stock: 9,
    image_url: 'https://images.unsplash.com/photo-1432821596592-e2c18853d3dd?w=400',
    category: 'Tablettes',
    is_active: true,
    created_at: new Date().toISOString(),
  },
];

const MOCK_USERS = [
  {
    id: 'user-1',
    phone: '237670000001',
    email: 'test@example.com',
    name: 'John Doe',
    is_verified: true,
    credit_score: 750,
    password_hash: 'password123',
    created_at: new Date().toISOString(),
  },
  {
    id: 'user-2',
    phone: '237670000002',
    email: 'jane@example.com',
    name: 'Jane Smith',
    is_verified: true,
    credit_score: 820,
    password_hash: 'password123',
    created_at: new Date().toISOString(),
  },
];

export const initializeTestData = async () => {
  try {
    const existingData = await AsyncStorage.getItem('supabase_data');

    if (!existingData) {
      const testData = {
        products: MOCK_PRODUCTS,
        users: MOCK_USERS,
        orders: [],
        payments: [],
        payment_schedules: [],
      };

      await AsyncStorage.setItem('supabase_data', JSON.stringify(testData));
      console.log('✅ Données de test initialisées avec succès');
    } else {
      console.log('📦 Données existantes détectées, initialisation ignorée');
    }
  } catch (error) {
    console.error('❌ Erreur lors de l\'initialisation:', error);
  }
};

export const clearTestData = async () => {
  try {
    await AsyncStorage.removeItem('supabase_data');
    console.log('✅ Toutes les données supprimées');
  } catch (error) {
    console.error('❌ Erreur lors de la suppression:', error);
  }
};
