<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $products = [
            ['name' => 'A4 Copy Paper Ream', 'code' => 'STN-A4-001', 'price' => '249.00', 'tax_percentage' => '18.00', 'stock_on_hand' => 40],
            ['name' => 'Blue Ballpoint Pen', 'code' => 'STN-PEN-002', 'price' => '12.50', 'tax_percentage' => '18.00', 'stock_on_hand' => 200],
            ['name' => 'Stapler', 'code' => 'STN-STP-003', 'price' => '185.00', 'tax_percentage' => '18.00', 'stock_on_hand' => 25],
            ['name' => 'USB-C Cable 1m', 'code' => 'ELC-USB-004', 'price' => '199.00', 'tax_percentage' => '18.00', 'stock_on_hand' => 8],
            ['name' => 'Wireless Mouse', 'code' => 'ELC-MOU-005', 'price' => '799.00', 'tax_percentage' => '18.00', 'stock_on_hand' => 12],
            ['name' => 'Notebook (200 pages)', 'code' => 'STN-NBK-006', 'price' => '65.00', 'tax_percentage' => '12.00', 'stock_on_hand' => 60],
            ['name' => 'Desk Organizer', 'code' => 'STN-ORG-007', 'price' => '349.00', 'tax_percentage' => '18.00', 'stock_on_hand' => 4],
            ['name' => 'Bottled Water 1L', 'code' => 'GRO-WTR-008', 'price' => '20.00', 'tax_percentage' => '5.00', 'stock_on_hand' => 3],
        ];

        foreach ($products as $product) {
            Product::query()->create($product);
        }

        $customers = [
            ['name' => 'Anita Sharma', 'email' => 'anita.sharma@example.com'],
            ['name' => 'Rahul Menon', 'email' => 'rahul.menon@example.com'],
            ['name' => 'Priya Nair', 'email' => 'priya.nair@example.com'],
            ['name' => 'Walk-in Counter', 'email' => 'walkin.counter@example.com'],
        ];

        foreach ($customers as $customer) {
            Customer::query()->create($customer);
        }
    }
}
