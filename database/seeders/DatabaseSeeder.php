<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CoffeeShop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        }

        User::firstOrCreate(
            ['email' => 'admin@ruangtanah.test'],
            ['name' => 'Administrator', 'password' => 'Sekadau123!'],
        );

        $dataset = json_decode(file_get_contents(database_path('seeders/data/pontianak-coffee-shops.json')), true, flags: JSON_THROW_ON_ERROR);
        $sourceKeys = [];
        foreach ($dataset['shops'] as $shop) {
            $sourceKey = 'ptk-oss-2021-'.$shop['source_row'];
            $sourceKeys[] = $sourceKey;
            CoffeeShop::updateOrCreate(['source_key' => $sourceKey], [
                'license_number' => $shop['license_number'], 'name' => $shop['name'],
                'address' => $shop['address'], 'ward' => $shop['ward'],
                'business_code' => $shop['business_code'], 'business_type' => $shop['business_type'],
                'latitude' => $shop['latitude'], 'longitude' => $shop['longitude'],
                'location_accuracy' => $shop['location_accuracy'], 'source_year' => $dataset['source_year'],
                'source' => $dataset['attribution'],
            ]);
        }
        CoffeeShop::where('source', $dataset['attribution'])->whereNotIn('source_key', $sourceKeys)->delete();
    }
}
