<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property User $user
 * @property Merchant $merchant
 * @property float $commission_rate
 */
class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'merchant_id',
        'commission_rate',
        'discount_code'
    ];
    private mixed $user_id;
    private mixed $merchant_id;
    private mixed $discount_code;


    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
