<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name', 100);
            $table->string('item_name', 150);
            $table->unsignedInteger('price_cents');
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('price_cents');
        });

        // CHECK constraints: MySQL 8.0.16+ / PostgreSQL. SQLite dev DB relies on app validation.
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending', 'paid', 'cancelled'))");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending', 'paid', 'cancelled'))");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            try {
                DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_status_check');
            } catch (\Throwable) {
                //
            }
        }

        Schema::dropIfExists('orders');
    }
};
