<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $dbName = DB::connection()->getDatabaseName();

        $phoneExists = DB::table('information_schema.columns')
            ->where('table_schema', $dbName)
            ->where('table_name', 'users')
            ->where('column_name', 'phone')
            ->exists();

        if (! $phoneExists) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone')->nullable()->unique();
            });
        }

        $emailIsNullable = DB::table('information_schema.columns')
            ->where('table_schema', $dbName)
            ->where('table_name', 'users')
            ->where('column_name', 'email')
            ->value('IS_NULLABLE') === 'YES';

        if (! $emailIsNullable) {
            // ชื่อคอลัมน์/ขนาด email ในโปรเจคนี้ถูกกำหนดเป็น VARCHAR(255)
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        // ทำย้อนกลับไม่ได้อย่างปลอดภัยในทุกกรณี (เพราะอาจกระทบข้อมูล)
        // ปล่อยว่างไว้เพื่อให้ migration สามารถรันได้ในสภาพจริง
    }
};

