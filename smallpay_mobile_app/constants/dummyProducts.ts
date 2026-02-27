import { Product } from '@/types';

export const DUMMY_PRODUCTS: Product[] = [
  {
    id: '1',
    name: 'iPhone 15 Pro Max',
    image_url: 'https://images.unsplash.com/photo-1679896949191-dc62950076ba?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxzbWFydHBob25lJTIwZWxlY3Ryb25pY3N8ZW58MXx8fHwxNzY2MTkwNTA5fDA&ixlib=rb-4.1.0&q=80&w=1080',
    price: 850000,
    category: 'Smartphone',
    description: 'Découvrez notre iPhone 15 Pro Max, un smartphone haut de gamme avec des fonctionnalités exceptionnelles.',
    stock: 10,
    is_active: true,
    created_at: new Date().toISOString()
  },
  {
    id: '2',
    name: 'MacBook Pro 14\"' , // Escaped double quote
    image_url: 'https://images.unsplash.com/photo-1511385348-a52b4a160dc2?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHxsYXB0b3AlMjBjb21wdXRlcnxlbnwxfHx8fDE3NjYxOTM4NDJ8MA&ixlib=rb-4.1.0&q=80&w=1080',
    price: 1200000,
    category: 'Ordinateur',
    description: 'Découvrez notre MacBook Pro 14 pouces, un ordinateur portable puissant et élégant.',
    stock: 10,
    is_active: true,
    created_at: new Date().toISOString()
  },
  {
    id: '3',
    name: 'AirPods Pro 2',
    image_url: 'https://images.unsplash.com/photo-1572119244337-bcb4aae995af?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHxoZWFkcGhvbmVzJTIwYXVkaW98ZW58MXx8fHwxNzY2MTI2MjYxfDA&ixlib=rb-4.1.0&q=80&w=1080',
    price: 150000,
    category: 'Audio',
    description: 'Découvrez nos AirPods Pro 2, des écouteurs sans fil avec une qualité sonore exceptionnelle.',
    stock: 10,
    is_active: true,
    created_at: new Date().toISOString()
  },
  {
    id: '4',
    name: 'Apple Watch Series 9',
    image_url: 'https://images.unsplash.com/photo-1762513461072-5008c7f6511d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHx3YXRjaCUyMGFjY2Vzc29yaWVzfGVufDF8fHx8MTc2NjIwNTY4M3ww&ixlib=rb-4.1.0&q=80&w=1080',
    price: 250000,
    category: 'Montre',
    description: 'Découvrez notre Apple Watch Series 9, une montre connectée élégante et performante.',
    stock: 10,
    is_active: true,
    created_at: new Date().toISOString()
  },
  {
    id: '5',
    name: 'Canon EOS R6',
    image_url: 'https://images.unsplash.com/photo-1579535984712-92fffbbaa266?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHxjYW1lcmElMjBwaG90b2dyYXBoeXxlbnwxfHx8fDE3NjYxNTkzNTl8MA&ixlib=rb-4.1.0&q=80&w=1080',
    price: 980000,
    category: 'Caméra',
    description: 'Découvrez notre Canon EOS R6, un appareil photo professionnel avec des fonctionnalités avancées.',
    stock: 10,
    is_active: true,
    created_at: new Date().toISOString()
  },
  {
    id: '6',
    name: 'iPad Pro 12.9\"' , // Escaped double quote
    image_url: 'https://images.unsplash.com/photo-1671087709820-274fdd9ac67a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHx0YWJsZXQlMjBkZXZpY2V8ZW58MXx8fHwxNzY2MTMwMTMwfDA&ixlib=rb-4.1.0&q=80&w=1080',
    price: 750000,
    category: 'Tablette',
    description: 'Découvrez notre iPad Pro 12.9 pouces, une tablette puissante et polyvalente.',
    stock: 10,
    is_active: true,
    created_at: new Date().toISOString()
  },
];
