<?php

namespace ItmoSoft\ContractPricing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContractPrice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'min_quantity' => 'decimal:3',
        'unit_price' => 'decimal:4',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(config('contract-pricing.models.price_list'));
    }

    public function product(): MorphTo
    {
        return $this->morphTo();
    }
}
