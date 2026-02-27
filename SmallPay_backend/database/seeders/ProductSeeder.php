<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'sku' => 'PHONE-001',
                'name' => 'iPhone 15 Pro',
                'description' => 'Dernier modèle Apple avec puce A17 Pro',
                'price' => 1299.99,
                'stock' => 50,
                'category' => 'Téléphones',
                'main_image' => 'https://via.placeholder.com/600x600?text=iPhone+15+Pro+Main',
                'secondary_images' => [
                    'https://via.placeholder.com/300x300?text=iPhone+15+Side',
                    'https://via.placeholder.com/300x300?text=iPhone+15+Back',
                    'https://via.placeholder.com/300x300?text=iPhone+15+Detail',
                ],
                'is_active' => true,
            ],
            [
                'sku' => 'PHONE-002',
                'name' => 'Samsung Galaxy S24',
                'description' => 'Smartphone flagship Samsung avec écran AMOLED',
                'price' => 999.99,
                'stock' => 45,
                'category' => 'Téléphones',
                'main_image' => 'https://via.placeholder.com/600x600?text=Galaxy+S24+Main',
                'secondary_images' => [
                    'https://via.placeholder.com/300x300?text=Galaxy+S24+Side',
                    'https://via.placeholder.com/300x300?text=Galaxy+S24+Back',
                ],
                'is_active' => true,
            ],
            [
                'sku' => 'LAPTOP-001',
                'name' => 'MacBook Pro 16"',
                'description' => 'Ordinateur portable professionnel Apple',
                'price' => 2499.99,
                'stock' => 20,
                'category' => 'Ordinateurs',
                'main_image' => 'https://via.placeholder.com/600x600?text=MacBook+Pro+Main',
                'secondary_images' => [
                    'https://via.placeholder.com/300x300?text=MacBook+Open',
                    'https://via.placeholder.com/300x300?text=MacBook+Side',
                    'https://via.placeholder.com/300x300?text=MacBook+Keyboard',
                    'https://via.placeholder.com/300x300?text=MacBook+Ports',
                ],
                'is_active' => true,
            ],
            [
                'sku' => 'TABLET-001',
                'name' => 'iPad Air',
                'description' => 'Tablette légère et puissante',
                'price' => 799.99,
                'stock' => 30,
                'category' => 'Tablettes',
                'main_image' => 'https://via.placeholder.com/600x600?text=iPad+Air+Main',
                'secondary_images' => [
                    'https://via.placeholder.com/300x300?text=iPad+Front',
                    'https://via.placeholder.com/300x300?text=iPad+Back',
                ],
                'is_active' => true,
            ],
            [
                'sku' => 'WATCH-001',
                'name' => 'Apple Watch Series 9',
                'description' => 'Montre intelligente avec santé et fitness',
                'price' => 399.99,
                'stock' => 60,
                'category' => 'Montres',
                'main_image' => 'https://via.placeholder.com/600x600?text=Apple+Watch+Main',
                'secondary_images' => [
                    'https://via.placeholder.com/300x300?text=Watch+Side',
                    'https://via.placeholder.com/300x300?text=Watch+Back',
                    'https://via.placeholder.com/300x300?text=Watch+Display',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
