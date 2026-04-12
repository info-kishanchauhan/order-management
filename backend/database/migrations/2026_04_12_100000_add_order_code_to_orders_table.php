<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_code', 32)->nullable()->unique()->after('id');
        });

        foreach (DB::table('orders')->orderBy('id')->cursor() as $row) {
            DB::table('orders')->where('id', $row->id)->update([
                'order_code' => 'ORD-'.str_pad((string) $row->id, 4, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_code']);
            $table->dropColumn('order_code');
        });
    }
};
