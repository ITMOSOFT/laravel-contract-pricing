<?php

namespace ItmoSoft\ContractPricing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContractPriceList extends Model
{
    protected $guarded = [];

    protected $attributes = [
        'currency' => 'USD',
        'is_active' => true,
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function customer(): MorphTo
    {
        return $this->morphTo();
    }

    public function prices(): HasMany
    {
        return $this->hasMany(config('contract-pricing.models.price'));
    }

    public function isCurrentlyValid(?\DateTimeInterface $at = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $moment = $at ? \Illuminate\Support\Carbon::instance($at) : now();

        return (! $this->valid_from || $this->valid_from->lessThanOrEqualTo($moment))
            && (! $this->valid_until || $this->valid_until->greaterThanOrEqualTo($moment));
    }
}
