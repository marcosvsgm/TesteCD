<?php

namespace App\Http\Requests;

use App\Models\Installment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class InstallmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'due_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'status' => ['required', 'in:pending,paid'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Installment|null $installment */
            $installment = $this->route('installment');

            if (! $installment) {
                return;
            }

            $sale = $installment->sale;
            $newAmount = round((float) $this->input('amount', 0), 2);
            $otherInstallmentsTotal = round(
                (float) $sale->installments()->whereKeyNot($installment->id)->sum('amount'),
                2
            );

            if (abs(($otherInstallmentsTotal + $newAmount) - (float) $sale->total) > 0.05) {
                $validator->errors()->add(
                    'amount',
                    'A soma das parcelas precisa continuar igual ao total da venda.'
                );
            }
        });
    }
}
