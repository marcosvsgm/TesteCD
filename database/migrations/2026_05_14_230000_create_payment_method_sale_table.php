<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_method_sale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['sale_id', 'payment_method_id']);
        });

        $sales = DB::table('sales')
            ->select('id', 'payment_method_id')
            ->whereNotNull('payment_method_id')
            ->get();

        if ($sales->isEmpty()) {
            return;
        }

        $now = now();

        DB::table('payment_method_sale')->insert(
            $sales->map(fn ($sale) => [
                'sale_id' => $sale->id,
                'payment_method_id' => $sale->payment_method_id,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_sale');
    }
};
