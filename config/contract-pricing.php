<?php

return [
    'models' => [
        'price_list' => ItmoSoft\ContractPricing\Models\ContractPriceList::class,
        'price' => ItmoSoft\ContractPricing\Models\ContractPrice::class,
    ],

    'tables' => [
        'price_lists' => 'contract_price_lists',
        'prices' => 'contract_prices',
    ],

    'default_currency' => 'USD',
];