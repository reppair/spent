<?php

namespace App\Models;

use App\Currency;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'currency' => Currency::class,
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function formattedAmount(): Attribute
    {
        return Attribute::get(fn () => $this->currency->sign().number_format($this->amount, 2));
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => $value / 100,
            set: fn (string $value) => $value * 100,
        );
    }
}
