<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create categories
        $categories = [
            'Electronics',
            'Clothing',
            'Home & Kitchen',
            'Books',
            'Sports & Outdoors',
        ];
        
        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
        
        // Create sample products
        $products = [
            [
                'name' => 'Wireless Headphones',
                'price' => 99.99,
                'description' => 'High-quality wireless headphones with noise cancellation.',
                'category_id' => 1,
                'stock' => 50,
                'status' => 'Active',
            ],
            [
                'name' => 'Smart Watch',
                'price' => 199.99,
                'description' => 'Track your fitness and stay connected with this smart watch.',
                'category_id' => 1,
                'stock' => 30,
                'status' => 'Active',
            ],
            [
                'name' => 'Men\'s T-Shirt',
                'price' => 24.99,
                'description' => 'Comfortable cotton t-shirt for everyday wear.',
                'category_id' => 2,
                'stock' => 100,
                'status' => 'Active',
            ],
            [
                'name' => 'Coffee Maker',
                'price' => 79.99,
                'description' => 'Brew delicious coffee at home with this easy-to-use coffee maker.',
                'category_id' => 3,
                'stock' => 20,
                'status' => 'Active',
            ],
            [
                'name' => 'Novel: The Great Adventure',
                'price' => 14.99,
                'description' => 'Bestselling novel about an epic adventure.',
                'category_id' => 4,
                'stock' => 200,
                'status' => 'Active',
            ],
            [
                'name' => 'Yoga Mat',
                'price' => 29.99,
                'description' => 'Non-slip yoga mat for your workout sessions.',
                'category_id' => 5,
                'stock' => 40,
                'status' => 'Active',
            ],
        ];
        
        foreach ($products as $product) {
            Product::create($product);
        }
    }
}