<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'sale_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0.01'],
            'installments' => ['required', 'array', 'min:1'],
            'installments.*.payment_method_id' => ['required', 'exists:payment_methods,id'],
            'installments.*.due_date' => ['required', 'date'],
            'installments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'installments.*.status' => ['required', 'in:pending,paid'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = collect($this->input('items', []))
                ->filter(fn ($item) => filled($item['product_id'] ?? null));

            $installments = collect($this->input('installments', []))
                ->filter(fn ($installment) => filled($installment['due_date'] ?? null));

            if ($items->isEmpty()) {
                $validator->errors()->add('items', 'Informe ao menos um item para a venda.');
            }

            if ($installments->isEmpty()) {
                $validator->errors()->add('installments', 'Informe ao menos uma parcela para a venda.');
            }

            $itemsTotal = $items->sum(
                fn (array $item) => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)
            );

            $installmentsTotal = $installments->sum(
                fn (array $installment) => (float) ($installment['amount'] ?? 0)
            );

            if ($itemsTotal > 0 && abs($itemsTotal - $installmentsTotal) > 0.05) {
                $validator->errors()->add(
                    'installments',
                    'A soma das parcelas precisa ser igual ao total da venda.'
                );
            }
        });
    }
}
