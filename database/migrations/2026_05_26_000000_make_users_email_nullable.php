<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ทำให้ users.email เป็นค่าว่างได้สำหรับ driver (driver จะเพิ่ม email ทีหลัง)
        // ใช้ DB::statement เพื่อเลี่ยงการพึ่ง doctrine/dbal
        DB::statement("ALTER TABLE users MODIFY email VARCHAR(255) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL");
    }
};

