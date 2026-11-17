<?php

namespace ItmoSoft\ContractPricing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use ItmoSoft\ContractPricing\DTO\ResolvedContractPrice;
use ItmoSoft\ContractPricing\Models\ContractPrice;
use ItmoSoft\ContractPricing\Models\ContractPriceList;

class ContractPriceResolver
{
    public function resolve(
        Model $customer,
        Model $product,
        float $quantity = 1,
        ?string $currency = null,
        ?\DateTimeInterface $at = null
    ): ?ResolvedContractPrice {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        $moment = $at ? Carbon::instance($at) : now();
        $priceListClass = config('contract-pricing.models.price_list', ContractPriceList::class);
        $priceClass = config('contract-pricing.models.price', ContractPrice::class);

        $lists = $priceListClass::query()
            ->where('customer_type', $customer->getMorphClass())
            ->where('customer_id', $customer->getKey())
            ->where('is_active', true)
            ->where(function ($query) use ($moment) {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', $moment);
            })
            ->where(function ($query) use ($moment) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', $moment);
            })
            ->when($currency, fn ($query) => $query->where('currency', strtoupper($currency)))
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->get();

        foreach ($lists as $list) {
            $price = $priceClass::query()
                ->where('contract_price_list_id', $list->getKey())
                ->where('product_type', $product->getMorphClass())
                ->where('product_id', $product->getKey())
                ->where('min_quantity', '<=', $quantity)
                ->orderByDesc('min_quantity')
                ->first();

            if ($price) {
                return new ResolvedContractPrice(
                    unitPrice: (float) $price->unit_price,
                    currency: (string) $list->currency,
                    priceListId: (int) $list->getKey(),
                    priceId: (int) $price->getKey(),
                    minimumQuantity: (float) $price->min_quantity,
                );
            }
        }

        return null;
    }
}
