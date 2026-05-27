<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('connectors', function (Blueprint $table) {
            // ถ้าคอลัมน์ status มีอยู่จริง ให้ลบออก
            if (Schema::hasColumn('connectors', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('connectors', function (Blueprint $table) {
            if (!Schema::hasColumn('connectors', 'status')) {
                $table->enum('status', ['available', 'busy', 'maintenance'])->default('available');
            }
        });
    }
};

