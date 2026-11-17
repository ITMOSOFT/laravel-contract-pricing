<?php

namespace ItmoSoft\ContractPricing\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'test_customers';
    protected $guarded = [];
}
