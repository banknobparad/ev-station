<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('connectors')->orderBy('id')->get();

        $grouped = $rows->groupBy(fn ($row) => $row->station_id . '|' . $row->type);

        foreach ($grouped as $group) {
            if ($group->count() <= 1) {
                continue;
            }

            $keep = $group->first();
            $total = $group->sum('total');

            DB::table('connectors')->where('id', $keep->id)->update(['total' => $total]);
            DB::table('connectors')->whereIn('id', $group->skip(1)->pluck('id'))->delete();
        }

        Schema::table('connectors', function (Blueprint $table) {
            $table->unique(['station_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('connectors', function (Blueprint $table) {
            $table->dropUnique(['station_id', 'type']);
        });
    }
};
