<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'base_price',
        'is_active',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'is_active' => 'boolean',
        ];
    }


}
