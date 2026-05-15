<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::updateOrCreate(
            ['email' => 'vendedor@teste.com'],
            [
                'name' => 'Marcos Souza',
                'password' => Hash::make('12345678'),
            ]
        );

        $customers = collect([
            'João Silva',
            'Maria Oliveira',
            'Empresa Alpha LTDA',
            'Cliente Avulso',
        ])->mapWithKeys(
            fn (string $name) => [$name => Customer::updateOrCreate(['name' => $name])]
        );

        $products = collect([
            ['name' => 'Notebook Dell', 'unit_price' => 3500.00],
            ['name' => 'Mouse Sem Fio', 'unit_price' => 80.00],
            ['name' => 'Teclado Mecânico', 'unit_price' => 250.00],
            ['name' => 'Monitor 24 Polegadas', 'unit_price' => 900.00],
            ['name' => 'Impressora Epson', 'unit_price' => 1200.00],
        ])->mapWithKeys(function (array $product) {
            $model = Product::updateOrCreate(
                ['name' => $product['name']],
                ['unit_price' => $product['unit_price'], 'is_active' => true]
            );

            return [$product['name'] => $model];
        });

        $paymentMethods = collect([
            'Dinheiro',
            'Pix',
            'Cartão de Crédito',
            'Cartão de Débito',
            'Boleto',
        ])->mapWithKeys(function (string $name) {
            $model = PaymentMethod::updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );

            return [$name => $model];
        });

        if (Sale::exists()) {
            return;
        }

        $sales = [
            [
                'customer' => 'João Silva',
                'payment_method' => 'Cartão de Crédito',
                'sale_date' => now()->subDays(6)->toDateString(),
                'items' => [
                    ['product' => 'Notebook Dell', 'quantity' => 1],
                    ['product' => 'Mouse Sem Fio', 'quantity' => 1],
                ],
                'installments' => [
                    ['days' => 30, 'status' => 'paid'],
                    ['days' => 60, 'status' => 'pending'],
                    ['days' => 90, 'status' => 'pending'],
                ],
            ],
            [
                'customer' => 'Empresa Alpha LTDA',
                'payment_method' => 'Boleto',
                'sale_date' => now()->subDays(3)->toDateString(),
                'items' => [
                    ['product' => 'Monitor 24 Polegadas', 'quantity' => 2],
                    ['product' => 'Teclado Mecânico', 'quantity' => 2],
                ],
                'installments' => [
                    ['days' => 15, 'status' => 'pending'],
                    ['days' => 45, 'status' => 'pending'],
                ],
            ],
            [
                'customer' => 'Cliente Avulso',
                'payment_method' => 'Pix',
                'sale_date' => now()->subDay()->toDateString(),
                'items' => [
                    ['product' => 'Impressora Epson', 'quantity' => 1],
                ],
                'installments' => [
                    ['days' => 0, 'status' => 'paid'],
                ],
            ],
        ];

        foreach ($sales as $saleData) {
            $items = collect($saleData['items'])->map(function (array $item) use ($products): array {
                $product = $products[$item['product']];
                $quantity = $item['quantity'];
                $unitPrice = (float) $product->unit_price;

                return [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => round($quantity * $unitPrice, 2),
                ];
            });

            $total = round($items->sum('total'), 2);
            $installments = collect($saleData['installments']);
            $baseInstallmentValue = round($total / $installments->count(), 2);

            $sale = Sale::create([
                'user_id' => $seller->id,
                'customer_id' => $customers[$saleData['customer']]->id,
                'payment_method_id' => $paymentMethods[$saleData['payment_method']]->id,
                'sale_date' => $saleData['sale_date'],
                'total' => $total,
            ]);

            $sale->paymentMethods()->sync([$paymentMethods[$saleData['payment_method']]->id]);

            $sale->items()->createMany($items->all());

            $remaining = $total;

            foreach ($installments as $index => $installment) {
                $amount = $index === $installments->count() - 1 ? $remaining : $baseInstallmentValue;
                $remaining = round($remaining - $amount, 2);

                $sale->installments()->create([
                    'payment_method_id' => $paymentMethods[$saleData['payment_method']]->id,
                    'due_date' => Carbon::parse($saleData['sale_date'])->addDays($installment['days'])->toDateString(),
                    'amount' => $amount,
                    'status' => $installment['status'],
                ]);
            }
        }
    }
}
