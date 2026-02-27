<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'total_amount',
        'status',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Order_item::class, 'order_id');
    }
}
