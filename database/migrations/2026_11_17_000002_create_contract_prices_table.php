<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('contract-pricing.tables.prices', 'contract_prices'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contract_price_list_id')
                ->constrained(config('contract-pricing.tables.price_lists', 'contract_price_lists'))
                ->cascadeOnDelete();
            $table->morphs('product');
            $table->decimal('min_quantity', 14, 3)->default(1);
            $table->decimal('unit_price', 14, 4);
            $table->string('sku')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['contract_price_list_id', 'product_type', 'product_id', 'min_quantity'], 'contract_prices_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('contract-pricing.tables.prices', 'contract_prices'));
    }
};
