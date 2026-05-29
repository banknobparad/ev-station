<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_station_audit_logs', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('action');
        });

        // รายการเก่าที่ driver แก้/ลบไปแล้วถือว่าดำเนินการเสร็จ
        \DB::table('driver_station_audit_logs')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('driver_station_audit_logs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
