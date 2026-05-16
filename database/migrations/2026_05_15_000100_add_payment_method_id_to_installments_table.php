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
        if (! Schema::hasTable('installments') || ! Schema::hasTable('payment_methods')) {
            return;
        }

        $addedPaymentMethodColumn = false;

        if (! Schema::hasColumn('installments', 'payment_method_id')) {
            Schema::table('installments', function (Blueprint $table) {
                $table->foreignId('payment_method_id')
                    ->nullable()
                    ->after('sale_id')
                    ->constrained()
                    ->restrictOnDelete();
            });

            $addedPaymentMethodColumn = true;
        }

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'payment_method_id')) {
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

        if ($addedPaymentMethodColumn) {
            Schema::table('installments', function (Blueprint $table) {
                $table->index(['payment_method_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('installments') || ! Schema::hasColumn('installments', 'payment_method_id')) {
            return;
        }

        Schema::table('installments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_method_id');
        });
    }
};
