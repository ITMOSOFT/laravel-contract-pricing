<?php

namespace ItmoSoft\ContractPricing\Tests;

use Illuminate\Support\Carbon;
use ItmoSoft\ContractPricing\ContractPriceResolver;
use ItmoSoft\ContractPricing\Models\ContractPriceList;
use ItmoSoft\ContractPricing\Tests\Fixtures\Customer;
use ItmoSoft\ContractPricing\Tests\Fixtures\Product;

class ContractPriceResolverTest extends TestCase
{
    protected function entities(): array
    {
        return [
            Customer::create(['name' => 'Wholesale Buyer']),
            Product::create(['name' => 'Desk']),
        ];
    }

    protected function listFor(Customer $customer, array $attributes = []): ContractPriceList
    {
        return ContractPriceList::create($attributes + [
            'name' => 'Wholesale prices',
            'customer_type' => $customer->getMorphClass(),
            'customer_id' => $customer->getKey(),
            'currency' => 'USD',
            'is_active' => true,
        ]);
    }

    protected function addPrice(ContractPriceList $list, Product $product, float $min, float $price): void
    {
        $list->prices()->create([
            'product_type' => $product->getMorphClass(),
            'product_id' => $product->getKey(),
            'min_quantity' => $min,
            'unit_price' => $price,
        ]);
    }

    public function test_resolves_customer_product_contract_price(): void
    {
        [$customer, $product] = $this->entities();
        $list = $this->listFor($customer);
        $this->addPrice($list, $product, 1, 82.5);

        $resolved = app(ContractPriceResolver::class)->resolve($customer, $product);

        $this->assertNotNull($resolved);
        $this->assertSame(82.5, $resolved->unitPrice);
        $this->assertSame('USD', $resolved->currency);
    }

    public function test_selects_highest_applicable_quantity_break(): void
    {
        [$customer, $product] = $this->entities();
        $list = $this->listFor($customer);
        $this->addPrice($list, $product, 1, 100);
        $this->addPrice($list, $product, 10, 90);
        $this->addPrice($list, $product, 50, 75);

        $resolved = app(ContractPriceResolver::class)->resolve($customer, $product, 20);

        $this->assertSame(90.0, $resolved->unitPrice);
        $this->assertSame(10.0, $resolved->minimumQuantity);
    }

    public function test_returns_null_when_no_matching_contract_price_exists(): void
    {
        [$customer, $product] = $this->entities();
        $this->listFor($customer);

        $this->assertNull(app(ContractPriceResolver::class)->resolve($customer, $product));
    }

    public function test_ignores_inactive_price_lists(): void
    {
        [$customer, $product] = $this->entities();
        $list = $this->listFor($customer, ['is_active' => false]);
        $this->addPrice($list, $product, 1, 20);

        $this->assertNull(app(ContractPriceResolver::class)->resolve($customer, $product));
    }

    public function test_ignores_price_lists_outside_validity_window(): void
    {
        [$customer, $product] = $this->entities();
        $list = $this->listFor($customer, [
            'valid_from' => now()->addDay(),
        ]);
        $this->addPrice($list, $product, 1, 20);

        $this->assertNull(app(ContractPriceResolver::class)->resolve($customer, $product));
    }

    public function test_uses_higher_priority_price_list_first(): void
    {
        [$customer, $product] = $this->entities();

        $lower = $this->listFor($customer, ['priority' => 1]);
        $this->addPrice($lower, $product, 1, 90);

        $higher = $this->listFor($customer, ['priority' => 10]);
        $this->addPrice($higher, $product, 1, 80);

        $resolved = app(ContractPriceResolver::class)->resolve($customer, $product);

        $this->assertSame(80.0, $resolved->unitPrice);
        $this->assertSame($higher->id, $resolved->priceListId);
    }

    public function test_filters_by_requested_currency(): void
    {
        [$customer, $product] = $this->entities();
        $usd = $this->listFor($customer, ['currency' => 'USD']);
        $this->addPrice($usd, $product, 1, 100);
        $eur = $this->listFor($customer, ['currency' => 'EUR']);
        $this->addPrice($eur, $product, 1, 85);

        $resolved = app(ContractPriceResolver::class)->resolve($customer, $product, 1, 'eur');

        $this->assertSame(85.0, $resolved->unitPrice);
        $this->assertSame('EUR', $resolved->currency);
    }

    public function test_rejects_zero_or_negative_quantity(): void
    {
        [$customer, $product] = $this->entities();

        $this->expectException(\InvalidArgumentException::class);
        app(ContractPriceResolver::class)->resolve($customer, $product, 0);
    }

    public function test_supports_resolving_at_a_specific_time(): void
    {
        [$customer, $product] = $this->entities();
        $list = $this->listFor($customer, [
            'valid_from' => Carbon::parse('2026-01-01 00:00:00'),
            'valid_until' => Carbon::parse('2026-12-31 23:59:59'),
        ]);
        $this->addPrice($list, $product, 1, 60);

        $resolved = app(ContractPriceResolver::class)->resolve(
            $customer,
            $product,
            1,
            null,
            Carbon::parse('2026-06-01 12:00:00')
        );

        $this->assertSame(60.0, $resolved->unitPrice);
    }
}
