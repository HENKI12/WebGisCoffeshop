<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoffeeShop extends Model
{
    protected $fillable = [
        'source_key', 'license_number', 'name', 'address', 'ward', 'business_code',
        'business_type', 'latitude', 'longitude', 'location_accuracy', 'opening_hours', 'source_year', 'source',
    ];

    protected function casts(): array
    {
        return ['latitude' => 'float', 'longitude' => 'float', 'source_year' => 'integer'];
    }
}
