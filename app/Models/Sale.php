<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'customer_id',
        'payment_method_id',
        'sale_date',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class)->orderBy('name');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    public function getPaymentMethodNamesAttribute(): string
    {
        $names = $this->relationLoaded('installments')
            ? $this->installments
                ->loadMissing('paymentMethod')
                ->pluck('paymentMethod.name')
                ->filter()
                ->unique()
                ->values()
            : $this->installments()
                ->with('paymentMethod:id,name')
                ->get()
                ->pluck('paymentMethod.name')
                ->filter()
                ->unique()
                ->values();

        if ($names->isNotEmpty()) {
            return $names->join(', ');
        }

        return $this->paymentMethod?->name ?? 'N/A';
    }
}
