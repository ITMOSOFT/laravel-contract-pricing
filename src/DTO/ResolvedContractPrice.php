<?php

namespace ItmoSoft\ContractPricing\DTO;

final readonly class ResolvedContractPrice
{
    public function __construct(
        public float $unitPrice,
        public string $currency,
        public int $priceListId,
        public int $priceId,
        public float $minimumQuantity,
    ) {
    }
}
