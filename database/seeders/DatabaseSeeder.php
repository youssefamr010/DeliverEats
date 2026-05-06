<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\ItemVariant;
use App\Models\Rider;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStateLog;
use App\Models\Rating;
use App\Models\Review;
use App\Models\Payout;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== ADMIN =====
        $admin = User::create([
            'name' => 'Admin User', 'email' => 'admin@delivereats.com',
            'password' => Hash::make('admin'), 'role' => 'admin', 'phone' => '+201234567890',
        ]);

        // ===== RESTAURANT OWNERS & RESTAURANTS (Cairo, Egypt) =====
        $restaurantData = [
            ['owner' => ['name' => 'Chef Ahmad', 'email' => 'chef@burgerpalace.com'], 'restaurant' => ['name' => 'Burger Palace', 'cuisine_type' => 'American', 'address' => '15 Tahrir Square, Downtown Cairo', 'lat' => 30.0444, 'lng' => 31.2357, 'delivery_fee' => 25.00, 'avg_prep_time' => 25, 'commission_rate' => 15]],
            ['owner' => ['name' => 'Maria Rossi', 'email' => 'maria@pizzaroma.com'], 'restaurant' => ['name' => 'Pizza Roma', 'cuisine_type' => 'Italian', 'address' => '22 Zamalek, Gezira Island', 'lat' => 30.0600, 'lng' => 31.2200, 'delivery_fee' => 30.00, 'avg_prep_time' => 30, 'commission_rate' => 12]],
            ['owner' => ['name' => 'Li Wei', 'email' => 'li@dragonwok.com'], 'restaurant' => ['name' => 'Dragon Wok', 'cuisine_type' => 'Chinese', 'address' => '8 Mohandessin, Giza', 'lat' => 30.0560, 'lng' => 31.2020, 'delivery_fee' => 20.00, 'avg_prep_time' => 20, 'commission_rate' => 18]],
            ['owner' => ['name' => 'Yuki Tanaka', 'email' => 'yuki@sushizen.com'], 'restaurant' => ['name' => 'Sushi Zen', 'cuisine_type' => 'Japanese', 'address' => '45 Maadi, Cairo', 'lat' => 29.9602, 'lng' => 31.2569, 'delivery_fee' => 40.00, 'avg_prep_time' => 35, 'commission_rate' => 15]],
            ['owner' => ['name' => 'Omar Hassan', 'email' => 'omar@falafelking.com'], 'restaurant' => ['name' => 'Falafel King', 'cuisine_type' => 'Middle Eastern', 'address' => '3 Khan El-Khalili, Old Cairo', 'lat' => 30.0478, 'lng' => 31.2625, 'delivery_fee' => 15.00, 'avg_prep_time' => 15, 'commission_rate' => 10]],
            ['owner' => ['name' => 'Priya Patel', 'email' => 'priya@spicegarden.com'], 'restaurant' => ['name' => 'Spice Garden', 'cuisine_type' => 'Indian', 'address' => '12 Heliopolis, Cairo', 'lat' => 30.0870, 'lng' => 31.3220, 'delivery_fee' => 27.50, 'avg_prep_time' => 30, 'commission_rate' => 14]],
            ['owner' => ['name' => 'Khaled Mostafa', 'email' => 'khaled@kosharicorner.com'], 'restaurant' => ['name' => 'Koshari Corner', 'cuisine_type' => 'Egyptian', 'address' => '33 Abbassia, Cairo', 'lat' => 30.0710, 'lng' => 31.2830, 'delivery_fee' => 10.00, 'avg_prep_time' => 10, 'commission_rate' => 10]],
            ['owner' => ['name' => 'Fatma El-Said', 'email' => 'fatma@nilebistroo.com'], 'restaurant' => ['name' => 'Nile Bistro', 'cuisine_type' => 'Mediterranean', 'address' => '7 Corniche El Nil, Garden City', 'lat' => 30.0390, 'lng' => 31.2320, 'delivery_fee' => 35.00, 'avg_prep_time' => 35, 'commission_rate' => 16]],
            ['owner' => ['name' => 'Tarek Nour', 'email' => 'tarek@grillmaster.com'], 'restaurant' => ['name' => 'Grill Master', 'cuisine_type' => 'BBQ', 'address' => '55 Nasr City, Cairo', 'lat' => 30.0500, 'lng' => 31.3400, 'delivery_fee' => 22.50, 'avg_prep_time' => 25, 'commission_rate' => 13]],
            ['owner' => ['name' => 'Layla Abdel-Nour', 'email' => 'layla@sweetbites.com'], 'restaurant' => ['name' => 'Sweet Bites', 'cuisine_type' => 'Desserts', 'address' => '18 New Cairo', 'lat' => 30.0300, 'lng' => 31.4700, 'delivery_fee' => 30.00, 'avg_prep_time' => 20, 'commission_rate' => 15]],
        ];

        $restaurants = [];
        foreach ($restaurantData as $rd) {
            $owner = User::create(array_merge($rd['owner'], ['password' => Hash::make('password'), 'role' => 'restaurant_owner']));
            $restaurants[] = Restaurant::create(array_merge($rd['restaurant'], [
                'owner_id' => $owner->id, 'is_active' => true, 'is_open' => true,
                'opens_at' => '09:00', 'closes_at' => '23:00', 'rating_avg' => rand(35, 50) / 10,
            ]));
        }

        // ===== MENUS =====
        $menus = [
            0 => [ // Burger Palace
                'Burgers' => [
                    ['name' => 'Classic Burger', 'base_price' => 380, 'description' => 'Juicy beef patty with lettuce, tomato, and special sauce', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=400&auto=format&fit=crop', 'variants' => [['name' => 'Double', 'price_modifier' => 150], ['name' => 'With Cheese', 'price_modifier' => 80]]],
                    ['name' => 'Chicken Burger', 'base_price' => 340, 'description' => 'Crispy chicken fillet with mayo and pickles', 'image' => 'https://images.unsplash.com/photo-1615557960916-5f4791effe82?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Veggie Burger', 'base_price' => 310, 'description' => 'Plant-based patty with fresh vegetables', 'image' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'BBQ Burger', 'base_price' => 420, 'description' => 'Smoky BBQ sauce with caramelized onions', 'image' => 'https://images.unsplash.com/photo-1594212699903-ec8a3eca50f5?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Mushroom Swiss', 'base_price' => 450, 'description' => 'Sautéed mushrooms and melted Swiss cheese', 'image' => 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?q=80&w=400&auto=format&fit=crop'],
                ],
                'Sides' => [
                    ['name' => 'French Fries', 'base_price' => 150, 'image' => 'https://images.unsplash.com/photo-1630384066252-4272428d19ad?q=80&w=400&auto=format&fit=crop', 'variants' => [['name' => 'Large', 'price_modifier' => 70]]],
                    ['name' => 'Onion Rings', 'base_price' => 190, 'image' => 'https://images.unsplash.com/photo-1639024471283-035188835118?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Coleslaw', 'base_price' => 130, 'image' => 'https://images.unsplash.com/photo-1599021419847-d8a7a6aba5b4?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Mozzarella Sticks', 'base_price' => 220, 'image' => 'https://images.unsplash.com/photo-1531749956467-0b2954844930?q=80&w=400&auto=format&fit=crop'],
                ],
                'Drinks' => [
                    ['name' => 'Coca Cola', 'base_price' => 90, 'image' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Milkshake', 'base_price' => 240, 'image' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?q=80&w=400&auto=format&fit=crop', 'variants' => [['name' => 'Chocolate', 'price_modifier' => 0], ['name' => 'Strawberry', 'price_modifier' => 30]]],
                ],
            ],
            1 => [ // Pizza Roma
                'Pizzas' => [
                    ['name' => 'Margherita', 'base_price' => 480, 'description' => 'Fresh mozzarella, tomato sauce, basil', 'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbad80ad38?q=80&w=400&auto=format&fit=crop', 'variants' => [['name' => 'Large', 'price_modifier' => 180], ['name' => 'Family', 'price_modifier' => 350]]],
                    ['name' => 'Pepperoni', 'base_price' => 560, 'description' => 'Loaded with pepperoni and cheese', 'image' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Four Cheese', 'base_price' => 600, 'description' => 'Mozzarella, gorgonzola, parmesan, fontina', 'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Diavola', 'base_price' => 620, 'description' => 'Spicy salami with chili flakes', 'image' => 'https://images.unsplash.com/photo-1534308983496-4fabb1a015ee?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Vegetarian', 'base_price' => 500, 'description' => 'Bell peppers, olives, mushrooms, and onions', 'image' => 'https://images.unsplash.com/photo-1571407970349-bc81e7e96d47?q=80&w=400&auto=format&fit=crop'],
                ],
                'Pasta' => [
                    ['name' => 'Spaghetti Bolognese', 'base_price' => 440, 'image' => 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Fettuccine Alfredo', 'base_price' => 480, 'image' => 'https://images.unsplash.com/photo-1645112481338-35613ef83790?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Penne Arrabbiata', 'base_price' => 400, 'image' => 'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?q=80&w=400&auto=format&fit=crop'],
                ],
            ],
            2 => [ // Dragon Wok
                'Noodles' => [
                    ['name' => 'Fried Noodles', 'base_price' => 350, 'image' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Lo Mein', 'base_price' => 390, 'image' => 'https://images.unsplash.com/photo-1617093727343-374698b1b08d?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Pad Thai', 'base_price' => 420, 'image' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?q=80&w=400&auto=format&fit=crop'],
                ],
                'Rice Dishes' => [
                    ['name' => 'Fried Rice', 'base_price' => 300, 'image' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?q=80&w=400&auto=format&fit=crop', 'variants' => [['name' => 'With Chicken', 'price_modifier' => 100], ['name' => 'With Shrimp', 'price_modifier' => 150]]],
                    ['name' => 'Sweet & Sour Chicken', 'base_price' => 440, 'image' => 'https://images.unsplash.com/photo-1525755662778-989d0524087e?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Kung Pao Chicken', 'base_price' => 480, 'image' => 'https://images.unsplash.com/photo-1525755662778-989d0524087e?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Beef with Broccoli', 'base_price' => 520, 'image' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?q=80&w=400&auto=format&fit=crop'],
                ],
            ],
            3 => [ // Sushi Zen
                'Sushi Rolls' => [
                    ['name' => 'California Roll', 'base_price' => 400, 'image' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Spicy Tuna Roll', 'base_price' => 480, 'image' => 'https://images.unsplash.com/photo-1559466273-d95e72debaf8?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Dragon Roll', 'base_price' => 650, 'image' => 'https://images.unsplash.com/photo-1611143669185-af224c5e3252?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Philadelphia Roll', 'base_price' => 500, 'image' => 'https://images.unsplash.com/photo-1617196034183-421b4917c92d?q=80&w=400&auto=format&fit=crop'],
                ],
                'Sashimi' => [
                    ['name' => 'Salmon Sashimi', 'base_price' => 580, 'image' => 'https://images.unsplash.com/photo-1534422298391-e4f8c170db06?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Tuna Sashimi', 'base_price' => 620, 'image' => 'https://images.unsplash.com/photo-1625938146369-adc833682940?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Ebi Sashimi', 'base_price' => 550, 'image' => 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?q=80&w=400&auto=format&fit=crop'],
                ],
            ],
            4 => [ // Falafel King
                'Sandwiches' => [
                    ['name' => 'Falafel Wrap', 'base_price' => 260, 'image' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Shawarma', 'base_price' => 320, 'image' => 'https://images.unsplash.com/photo-1561651823-34feb02250e4?q=80&w=400&auto=format&fit=crop', 'variants' => [['name' => 'Chicken', 'price_modifier' => 0], ['name' => 'Beef', 'price_modifier' => 50]]],
                    ['name' => 'Kofta Sandwich', 'base_price' => 340, 'image' => 'https://images.unsplash.com/photo-1603360946369-dc9bb6258143?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Halloumi Sandwich', 'base_price' => 280, 'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=400&auto=format&fit=crop'],
                ],
                'Platters' => [
                    ['name' => 'Mixed Grill Platter', 'base_price' => 650, 'image' => 'https://images.unsplash.com/photo-1544124499-58ec529dd233?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Hummus & Pita', 'base_price' => 220, 'image' => 'https://images.unsplash.com/photo-1577906030558-6c61f2f01f1f?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Tabbouleh Salad', 'base_price' => 180, 'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?q=80&w=400&auto=format&fit=crop'],
                ],
            ],
            5 => [ // Spice Garden
                'Curries' => [
                    ['name' => 'Butter Chicken', 'base_price' => 520, 'image' => 'https://images.unsplash.com/photo-1603894584202-77296955fc72?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Lamb Vindaloo', 'base_price' => 600, 'image' => 'https://images.unsplash.com/photo-1545243191-34802445e810?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Palak Paneer', 'base_price' => 440, 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Chicken Tikka Masala', 'base_price' => 540, 'image' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?q=80&w=400&auto=format&fit=crop'],
                ],
                'Breads' => [
                    ['name' => 'Naan', 'base_price' => 120, 'image' => 'https://images.unsplash.com/photo-1533777324565-a040eb52facd?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Garlic Naan', 'base_price' => 160, 'image' => 'https://images.unsplash.com/photo-1533777324565-a040eb52facd?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Cheese Naan', 'base_price' => 180, 'image' => 'https://images.unsplash.com/photo-1533777324565-a040eb52facd?q=80&w=400&auto=format&fit=crop'],
                ],
            ],
            6 => [ // Koshari Corner
                'Main Dishes' => [
                    ['name' => 'Koshari Classic', 'base_price' => 180, 'description' => 'Egypt\'s national dish — rice, pasta, lentils, crispy onions, tomato sauce', 'image' => 'https://images.unsplash.com/photo-1533777324565-a040eb52facd?q=80&w=400&auto=format&fit=crop', 'variants' => [['name' => 'Large', 'price_modifier' => 70], ['name' => 'Extra Sauce', 'price_modifier' => 30]]],
                    ['name' => 'Koshari with Chicken', 'base_price' => 280, 'image' => 'https://images.unsplash.com/photo-1533777324565-a040eb52facd?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Foul Medames', 'base_price' => 140, 'description' => 'Traditional Egyptian fava beans', 'image' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Taameya Plate', 'base_price' => 160, 'description' => 'Egyptian-style falafel made from fava beans', 'image' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Mousakka', 'base_price' => 220, 'description' => 'Layered eggplant and spiced minced beef', 'image' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?q=80&w=400&auto=format&fit=crop'],
                ],
                'Drinks' => [
                    ['name' => 'Karkade', 'base_price' => 80, 'image' => 'https://images.unsplash.com/photo-1544383181-423521094002?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Sahlab', 'base_price' => 120, 'image' => 'https://images.unsplash.com/photo-1544383181-423521094002?q=80&w=400&auto=format&fit=crop'],
                ],
            ],
            7 => [ // Nile Bistro
                'Starters' => [
                    ['name' => 'Mezze Platter', 'base_price' => 450, 'image' => 'https://images.unsplash.com/photo-1544148103-0773bf10d330?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Grilled Halloumi', 'base_price' => 360, 'image' => 'https://images.unsplash.com/photo-1544148103-0773bf10d330?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Baba Ganoush', 'base_price' => 280, 'image' => 'https://images.unsplash.com/photo-1544148103-0773bf10d330?q=80&w=400&auto=format&fit=crop'],
                ],
                'Main Courses' => [
                    ['name' => 'Grilled Sea Bass', 'base_price' => 850, 'description' => 'Fresh Nile sea bass with herbs', 'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Lamb Tagine', 'base_price' => 750, 'image' => 'https://images.unsplash.com/photo-1541518763669-27f7045d4685?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Chicken Musakhan', 'base_price' => 650, 'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Beef Shawarma Platter', 'base_price' => 600, 'image' => 'https://images.unsplash.com/photo-1561651823-34feb02250e4?q=80&w=400&auto=format&fit=crop'],
                ],
            ],
            8 => [ // Grill Master
                'Grills' => [
                    ['name' => 'Mixed Grill', 'base_price' => 750, 'image' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?q=80&w=400&auto=format&fit=crop', 'variants' => [['name' => 'For Two', 'price_modifier' => 450]]], 
                    ['name' => 'Kofta Kebab', 'base_price' => 520, 'image' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Chicken Tikka', 'base_price' => 480, 'image' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Lamb Chops', 'base_price' => 880, 'image' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?q=80&w=400&auto=format&fit=crop'],
                ],
                'Sides' => [
                    ['name' => 'Tahini Salad', 'base_price' => 180, 'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Grilled Vegetables', 'base_price' => 220, 'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=400&auto=format&fit=crop'],
                ],
            ],
            9 => [ // Sweet Bites
                'Cakes' => [
                    ['name' => 'Chocolate Lava Cake', 'base_price' => 360, 'image' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Kunafa', 'base_price' => 320, 'description' => 'Traditional Egyptian cheese dessert', 'image' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Basbousa', 'base_price' => 240, 'image' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?q=80&w=400&auto=format&fit=crop'],
                    ['name' => 'Red Velvet Cake', 'base_price' => 380, 'image' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?q=80&w=400&auto=format&fit=crop'],
                ],
                'Ice Cream' => [
                    ['name' => 'Mango Sorbet', 'base_price' => 200, 'image' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?q=80&w=400&auto=format&fit=crop'], 
                    ['name' => 'Pistachio Gelato', 'base_price' => 250, 'image' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?q=80&w=400&auto=format&fit=crop'],
                ],
            ],
        ];

        foreach ($menus as $rIdx => $categories) {
            foreach ($categories as $catName => $items) {
                $cat = Category::create(['restaurant_id' => $restaurants[$rIdx]->id, 'name' => $catName, 'is_active' => true]);
                foreach ($items as $itemData) {
                    $variants = $itemData['variants'] ?? [];
                    unset($itemData['variants']);
                    $item = MenuItem::create(array_merge($itemData, ['category_id' => $cat->id, 'is_available' => true]));
                    foreach ($variants as $v) {
                        ItemVariant::create(array_merge($v, ['menu_item_id' => $item->id, 'is_available' => true]));
                    }
                }
            }
        }

        // ===== RIDERS (scattered across Cairo) =====
        $ridersData = [
            ['name' => 'Rider Ahmed', 'email' => 'rider1@delivereats.com', 'lat' => 30.0450, 'lng' => 31.2360, 'vehicle' => 'motorcycle'],
            ['name' => 'Rider Sara', 'email' => 'rider2@delivereats.com', 'lat' => 30.0580, 'lng' => 31.2190, 'vehicle' => 'motorcycle'],
            ['name' => 'Rider Mike', 'email' => 'rider3@delivereats.com', 'lat' => 30.0550, 'lng' => 31.2030, 'vehicle' => 'car'],
            ['name' => 'Rider Fatima', 'email' => 'rider4@delivereats.com', 'lat' => 30.0870, 'lng' => 31.3230, 'vehicle' => 'motorcycle'],
            ['name' => 'Rider Carlos', 'email' => 'rider5@delivereats.com', 'lat' => 30.0490, 'lng' => 31.2620, 'vehicle' => 'bicycle'],
            ['name' => 'Rider Hassan', 'email' => 'rider6@delivereats.com', 'lat' => 30.0710, 'lng' => 31.2840, 'vehicle' => 'motorcycle'],
            ['name' => 'Rider Nour', 'email' => 'rider7@delivereats.com', 'lat' => 30.0400, 'lng' => 31.2340, 'vehicle' => 'motorcycle'],
            ['name' => 'Rider Youssef', 'email' => 'rider8@delivereats.com', 'lat' => 30.0510, 'lng' => 31.3410, 'vehicle' => 'car'],
        ];

        $riders = [];
        foreach ($ridersData as $i => $rd) {
            $assignedRestaurant = $restaurants[$i % count($restaurants)] ?? $restaurants[0];
            $user = User::create(['name' => $rd['name'], 'email' => $rd['email'], 'password' => Hash::make('password'), 'role' => 'rider', 'restaurant_id' => $assignedRestaurant->id]);
            $riders[] = Rider::create([
                'user_id' => $user->id, 'current_lat' => $rd['lat'], 'current_lng' => $rd['lng'],
                'is_online' => true, 'is_available' => true,
                'vehicle_type' => $rd['vehicle'],
                'rating_avg' => rand(38, 50) / 10, 'total_deliveries' => rand(50, 300),
            ]);
        }

        // ===== CHEFS (2 per restaurant) =====
        $chefNames = ['Chef Amira', 'Chef Mahmoud', 'Chef Salma', 'Chef Karim', 'Chef Dina', 'Chef Yousef', 'Chef Rana', 'Chef Tarek', 'Chef Mona', 'Chef Adel', 'Chef Nadia', 'Chef Samir', 'Chef Heba', 'Chef Walid', 'Chef Jasmine', 'Chef Fares', 'Chef Laila', 'Chef Ziad', 'Chef Noura', 'Chef Bassem'];
        $chefIdx = 0;
        foreach ($restaurants as $restaurant) {
            for ($c = 0; $c < 2; $c++) {
                $chefName = $chefNames[$chefIdx] ?? 'Chef ' . ($chefIdx + 1);
                User::create([
                    'name' => $chefName,
                    'email' => 'chef' . ($chefIdx + 1) . '@delivereats.com',
                    'password' => Hash::make('password'),
                    'role' => 'chef',
                    'restaurant_id' => $restaurant->id,
                ]);
                $chefIdx++;
            }
        }

        // ===== CUSTOMERS =====
        $customers = [];
        $customerData = [
            ['name' => 'John Smith', 'address' => '42 Zamalek, Cairo'],
            ['name' => 'Emma Wilson', 'address' => '15 Maadi, Cairo'],
            ['name' => 'Liam Johnson', 'address' => '8 Heliopolis, Cairo'],
            ['name' => 'Sophia Brown', 'address' => '31 Nasr City, Cairo'],
            ['name' => 'Noah Davis', 'address' => '22 Downtown, Cairo'],
            ['name' => 'Olivia Martinez', 'address' => '55 New Cairo'],
            ['name' => 'Mohamed Ali', 'address' => '10 Dokki, Giza'],
            ['name' => 'Hana Ibrahim', 'address' => '19 Garden City, Cairo'],
        ];
        foreach ($customerData as $i => $cd) {
            $customers[] = User::create([
                'name' => $cd['name'], 'email' => strtolower(explode(' ', $cd['name'])[0]) . '@customer.com',
                'password' => Hash::make('password'), 'role' => 'customer',
                'address' => $cd['address'],
            ]);
        }

        // ===== SAMPLE ORDERS =====
        $statuses = ['placed', 'confirmed', 'preparing', 'ready_for_pickup', 'on_the_way', 'delivered', 'delivered', 'delivered'];
        $paymentService = new PaymentService();

        for ($i = 0; $i < 40; $i++) {
            $customer = $customers[array_rand($customers)];
            $restaurant = $restaurants[array_rand($restaurants)];
            $rider = $riders[array_rand($riders)];
            $status = $statuses[array_rand($statuses)];
            $menuItems = $restaurant->menuItems()->inRandomOrder()->take(rand(1, 4))->get();

            if ($menuItems->isEmpty()) continue;

            $subtotal = 0;
            $orderItemsData = [];
            foreach ($menuItems as $mi) {
                $qty = rand(1, 3);
                $price = (float)$mi->base_price;
                $sub = $price * $qty;
                $subtotal += $sub;
                $orderItemsData[] = ['menu_item_id' => $mi->id, 'quantity' => $qty, 'unit_price' => $price, 'subtotal' => $sub];
            }

            $deliveryFee = (float)$restaurant->delivery_fee;
            $tax = round($subtotal * 0.05, 2);
            $tip = rand(0, 50);
            $total = round($subtotal + $deliveryFee + $tax + $tip, 2);

            $order = Order::create([
                'customer_id' => $customer->id, 'restaurant_id' => $restaurant->id,
                'rider_id' => $status !== 'placed' ? $rider->id : null,
                'status' => $status, 'subtotal' => $subtotal, 'delivery_fee' => $deliveryFee,
                'surge_multiplier' => 1.0, 'surge_fee' => 0, 'tax' => $tax, 'tip' => $tip, 'total' => $total,
                'delivery_address' => $customer->address ?? '123 Cairo Street',
                'payment_method' => 'cash', 'payment_status' => $status === 'delivered' ? 'completed' : 'pending',
                'delivered_at' => $status === 'delivered' ? now()->subHours(rand(1, 48)) : null,
                'created_at' => now()->subHours(rand(1, 72)),
            ]);

            foreach ($orderItemsData as $oi) {
                OrderItem::create(array_merge($oi, ['order_id' => $order->id]));
            }

            OrderStateLog::create(['order_id' => $order->id, 'from_state' => null, 'to_state' => 'placed', 'actor_type' => 'customer', 'actor_id' => $customer->id, 'transitioned_at' => $order->created_at]);

            if ($status === 'delivered') {
                $paymentService->processPayment($order);
                $score = rand(3, 5);
                $rating = Rating::create(['user_id' => $customer->id, 'order_id' => $order->id, 'rateable_type' => Restaurant::class, 'rateable_id' => $restaurant->id, 'score' => $score]);
                $comments = ['Great food!', 'Fast delivery!', 'Delicious!', 'Would order again.', 'Amazing quality.', 'Best in Cairo!', 'Excellent Egyptian cuisine!', 'Perfectly prepared!'];
                Review::create(['rating_id' => $rating->id, 'comment' => $comments[array_rand($comments)]]);
            }
        }

        foreach ($restaurants as $r) { $r->updateRatingAverage(); }

        echo "✅ Seeded: 1 admin, 10 restaurants (Cairo), 20 chefs, 8 riders, 8 customers, 40 orders\n";
    }
}
