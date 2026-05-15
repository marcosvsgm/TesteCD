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
        Schema::table('installments', function (Blueprint $table) {
            $table->foreignId('payment_method_id')
                ->nullable()
                ->after('sale_id')
                ->constrained()
                ->restrictOnDelete();
        });

        DB::table('installments')
            ->join('sales', 'sales.id', '=', 'installments.sale_id')
            ->whereNull('installments.payment_method_id')
            ->update([
                'installments.payment_method_id' => DB::raw('sales.payment_method_id'),
            ]);

        Schema::table('installments', function (Blueprint $table) {
            $table->index(['payment_method_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_method_id');
        });
    }
};
