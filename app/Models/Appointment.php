<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const LOCATION_CLINIC = 'clinic';
    public const LOCATION_HOMECARE = 'homecare';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'patient_id',
        'service_id',
        'complaint_summary',
        'ai_urgency',
        'ai_recommendation',
        'ai_notes',
        'status',
        'service_location_type',
        'address_at_time',
        'lat',
        'lng',
        'final_price',
        'scheduled_at',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function soapNote(): HasOne
    {
        return $this->hasOne(SoapNote::class);
    }

    protected function casts(): array
    {
        return [
            'final_price' => 'integer',
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
            'scheduled_at' => 'datetime',
        ];
    }
}
