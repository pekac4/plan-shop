<?php

namespace Database\Seeders;

use App\Models\CustomShoppingItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomShoppingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Cigarettes (pack)', 'price' => 350],
            ['name' => 'Toilet paper (4-pack)', 'price' => 300],
            ['name' => 'Laundry detergent (1.5–2kg)', 'price' => 1000],
            ['name' => 'Dish soap (500ml)', 'price' => 200],
            ['name' => 'Kitchen trash bags (20 pcs)', 'price' => 300],
            ['name' => 'Toilet cleaner (1L)', 'price' => 350],
            ['name' => 'Shampoo (400ml)', 'price' => 500],
            ['name' => 'Shower gel (400ml)', 'price' => 450],
            ['name' => 'Toothpaste', 'price' => 250],
            ['name' => 'Toothbrush', 'price' => 200],
            ['name' => 'Deodorant', 'price' => 400],
            ['name' => 'Hand soap (liquid)', 'price' => 250],
            ['name' => 'Paper towels (2-roll pack)', 'price' => 250],
            ['name' => 'Wet wipes', 'price' => 250],
            ['name' => 'Sponge pack (kitchen)', 'price' => 150],
            ['name' => 'Aluminum foil', 'price' => 250],
            ['name' => 'Plastic wrap (stretch film)', 'price' => 200],
            ['name' => 'Batteries (AA 4-pack)', 'price' => 450],
            ['name' => 'Light bulb (LED)', 'price' => 300],
            ['name' => 'Bleach (1L)', 'price' => 200],
        ];

        User::query()
            ->select('id')
            ->get()
            ->each(function (User $user) use ($items): void {
                foreach ($items as $item) {
                    CustomShoppingItem::query()->firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'name' => $item['name'],
                        ],
                        ['price' => $item['price']],
                    );
                }
            });
    }
}
