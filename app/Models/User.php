<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    public const LOGIN_FIELD = 'whatsapp_number';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'whatsapp_number',
        'email',
        'password',
        'branch_id',
        'otp_code',
        'otp_expires_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super_admin', 'developer', 'demo_super_admin', 'branch_admin']);
    }

    public function isDemo(): bool
    {
        return $this->hasRole('demo_super_admin');
    }

    public static function loginField(): string
    {
        return self::LOGIN_FIELD;
    }

    public static function findForOtpLogin(string $whatsappNumber): ?self
    {
        return self::query()
            ->where(self::LOGIN_FIELD, $whatsappNumber)
            ->first();
    }

    public function setOtp(string $code, int $ttlMinutes = 5): void
    {
        $this->forceFill([
            'otp_code' => $code,
            'otp_expires_at' => now()->addMinutes($ttlMinutes),
        ])->save();
    }

    public function hasValidOtp(string $code): bool
    {
        return filled($this->otp_code)
            && hash_equals($this->otp_code, $code)
            && $this->otp_expires_at?->isFuture();
    }

    public function clearOtp(): void
    {
        $this->forceFill([
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime',
        ];
    }
}
