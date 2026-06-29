<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\GeocodeService;

class Branch extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama_cabang',
        'alamat',
        'whatsapp_number',
        'is_active',
        'lat',
        'lng',
    ];

    protected static function booted()
    {
        static::saving(function (Branch $branch) {
            // Geocode otomatis via OSM jika alamat diubah/baru dan lat/lng belum diisi
            if (
                $branch->alamat &&
                (!$branch->lat || $branch->isDirty('alamat'))
            ) {
                $geocode = app(GeocodeService::class);
                $coords = $geocode->geocode($branch->alamat);
                if ($coords['lat'] && $coords['lng']) {
                    $branch->lat = $coords['lat'];
                    $branch->lng = $coords['lng'];
                }
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }


}
