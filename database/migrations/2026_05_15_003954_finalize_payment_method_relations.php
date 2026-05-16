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
        if (Schema::hasTable('sales') && Schema::hasTable('payment_methods') && ! Schema::hasTable('payment_method_sale')) {
            Schema::create('payment_method_sale', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
                $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
                $table->timestamps();

                $table->unique(['sale_id', 'payment_method_id']);
            });
        }

        if (
            Schema::hasTable('installments') &&
            Schema::hasTable('payment_methods') &&
            ! Schema::hasColumn('installments', 'payment_method_id')
        ) {
            Schema::table('installments', function (Blueprint $table) {
                $table->foreignId('payment_method_id')
                    ->nullable()
                    ->after('sale_id')
                    ->constrained()
                    ->restrictOnDelete();

                $table->index(['payment_method_id', 'status']);
            });
        }

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'payment_method_id')) {
            if (Schema::hasTable('payment_method_sale')) {
                $sales = DB::table('sales')
                    ->select('id', 'payment_method_id')
                    ->whereNotNull('payment_method_id')
                    ->get();

                if ($sales->isNotEmpty()) {
                    $now = now();

                    DB::table('payment_method_sale')->upsert(
                        $sales->map(fn ($sale) => [
                            'sale_id' => $sale->id,
                            'payment_method_id' => $sale->payment_method_id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])->all(),
                        ['sale_id', 'payment_method_id'],
                        ['updated_at']
                    );
                }
            }

            if (Schema::hasTable('installments') && Schema::hasColumn('installments', 'payment_method_id')) {
                DB::table('installments')
                    ->select('id', 'sale_id')
                    ->whereNull('payment_method_id')
                    ->orderBy('id')
                    ->chunkById(100, function ($installments): void {
                        $salePaymentMethods = DB::table('sales')
                            ->whereIn('id', $installments->pluck('sale_id')->filter()->unique())
                            ->pluck('payment_method_id', 'id');

                        $installments
                            ->filter(fn (\stdClass $installment): bool => $salePaymentMethods->has($installment->sale_id))
                            ->each(function (\stdClass $installment) use ($salePaymentMethods): void {
                                DB::table('installments')
                                    ->where('id', $installment->id)
                                    ->update([
                                        'payment_method_id' => $salePaymentMethods[$installment->sale_id],
                                    ]);
                            });
                    });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('installments') && Schema::hasColumn('installments', 'payment_method_id')) {
            Schema::table('installments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('payment_method_id');
            });
        }

        if (Schema::hasTable('payment_method_sale')) {
            Schema::dropIfExists('payment_method_sale');
        }
    }
};
