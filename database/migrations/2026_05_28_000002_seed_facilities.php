<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('facilities')->insert([
            ['name' => 'ที่กินข้าว', 'icon' => 'utensils', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ที่จอดรถ', 'icon' => 'parking', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ที่ชอปปิ้ง', 'icon' => 'shopping-cart', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ห้องน้ำ', 'icon' => 'restroom', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ร้านขายของชำ', 'icon' => 'convenience-store', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ที่นั่งพัก', 'icon' => 'chair', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'WiFi', 'icon' => 'wifi', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'สถานีอัดอากาศ', 'icon' => 'wind', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('facilities')->truncate();
    }
};
