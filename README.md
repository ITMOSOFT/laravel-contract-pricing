# ITMOSOFT Laravel Contract Pricing

Customer-specific price lists and negotiated product prices for Laravel 12 and 13.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

```bash
composer require itmosoft/laravel-contract-pricing
php artisan vendor:publish --tag=contract-pricing-config
php artisan migrate
```

Laravel package discovery registers the service provider automatically.

## Create a customer-specific price list

Customer and product references are polymorphic, so this package does not
require a specific company, customer, or product model.

```php
use ItmoSoft\ContractPricing\Models\ContractPriceList;

$list = ContractPriceList::create([
    'name' => 'Acme annual contract',
    'customer_type' => App\Models\Company::class,
    'customer_id' => $company->id,
    'currency' => 'USD',
    'priority' => 50,
    'is_active' => true,
    'valid_from' => now(),
    'valid_until' => now()->addYear(),
]);

$list->prices()->create([
    'product_type' => App\Models\Product::class,
    'product_id' => $product->id,
    'min_quantity' => 1,
    'unit_price' => 89.50,
]);

$list->prices()->create([
    'product_type' => App\Models\Product::class,
    'product_id' => $product->id,
    'min_quantity' => 25,
    'unit_price' => 82.00,
]);
```

## Resolve a contract price

```php
use ItmoSoft\ContractPricing\ContractPriceResolver;

$price = app(ContractPriceResolver::class)->resolve(
    customer: $company,
    product: $product,
    quantity: 30,
    currency: 'USD',
);

if ($price) {
    $unitPrice = $price->unitPrice;
    $currency = $price->currency;
}
```

Resolution behavior:

1. Match price lists assigned to the customer.
2. Ignore inactive or out-of-date lists.
3. Apply the requested currency when supplied.
4. Check lists by descending priority.
5. Within the first list with a matching product, select the highest
   `min_quantity` that does not exceed the requested quantity.

A missing match returns `null`. The caller can then fall back to standard
catalog pricing.

## Important pricing rules

- `priority` determines which matching price list wins.
- Quantity breaks use `min_quantity` thresholds.
- Currency belongs to the price list, not individual price rows.
- `unit_price` is stored to four decimal places.
- The resolver returns a value object and does not mutate cart or order prices.
- Applications should snapshot the resolved unit price and currency onto
  checkout/order line items.

## Scope boundaries

This package resolves negotiated prices. It does not implement customer
authentication, contract signatures, quote negotiation, tax calculation,
discount stacking, cart integration, price approval workflows, or order price
snapshots. Those concerns belong in the relevant application or commerce
packages.

## Testing

```bash
composer install
composer test
```

## License

MIT.
