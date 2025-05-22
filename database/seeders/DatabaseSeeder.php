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
            'Traditional Clothing',
            'Coffee & Beverages',
            'Home & Kitchen',
            'Books & Literature',
            'Sport & Culture',
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }

        // Create sample products
        $products = [
            [
                'name' => 'Habesha Kemis',
                'price' => 1200.00,
                'description' => 'Beautiful handwoven traditional Ethiopian dress for women.',
                'category_id' => 1,
                'stock' => 20,
                'status' => 'Active',
            ],
            [
                'name' => 'Men’s Ethiopian Shirt',
                'price' => 850.00,
                'description' => 'Cotton shirt with traditional Ethiopian embroidery patterns.',
                'category_id' => 1,
                'stock' => 35,
                'status' => 'Active',
            ],
            [
                'name' => 'Yirgacheffe Coffee (1kg)',
                'price' => 450.00,
                'description' => 'Premium Ethiopian Yirgacheffe beans for a rich, aromatic cup.',
                'category_id' => 2,
                'stock' => 100,
                'status' => 'Active',
            ],
            [
                'name' => 'Jebena (Traditional Coffee Pot)',
                'price' => 180.00,
                'description' => 'Authentic handmade clay coffee pot used in Ethiopian coffee ceremonies.',
                'category_id' => 3,
                'stock' => 50,
                'status' => 'Active',
            ],
            [
                'name' => '“Fikir Eske Mekabir” by Haddis Alemayehu',
                'price' => 250.00,
                'description' => 'Classic Ethiopian novel exploring love and social issues.',
                'category_id' => 4,
                'stock' => 75,
                'status' => 'Active',
            ],
            [
                'name' => 'Genna Set (Ethiopian Traditional Hockey)',
                'price' => 300.00,
                'description' => 'Traditional Genna game set for cultural sports activities.',
                'category_id' => 5,
                'stock' => 15,
                'status' => 'Active',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
