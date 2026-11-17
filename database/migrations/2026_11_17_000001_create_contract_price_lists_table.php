<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('contract-pricing.tables.price_lists', 'contract_price_lists'), function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->nullableMorphs('customer');
            $table->char('currency', 3)->default('USD');
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['customer_type', 'customer_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('contract-pricing.tables.price_lists', 'contract_price_lists'));
    }
};
