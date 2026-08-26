<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $plan
 * @property string|null $role
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'plan', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Define quem é Administrador do Sistema
     */
    public function isAdministrator(): bool
    {
        // Verifica se o email é o teu admin ou se tem a role explicitamente no banco
        return in_array($this->email, ['admin@admin.pt'])
               || $this->role === 'administrador';
    }

    /**
     * Verifica se o utilizador tem plano Premium
     */
    public function hasFinderPremium(): bool
    {
        $normalizedPlan = strtolower($this->plan ?? '');

        return in_array($normalizedPlan, [
            'finder_premium',
            'premium',
            'pro'
        ]);
    }

    /**
     * Define se pode criar sites (Admins podem sempre)
     */
    public function canCreateSites(): bool
    {
        return $this->isAdministrator() || $this->hasFinderPremium();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function ownedSites(): HasMany
    {
        return $this->hasMany(Site::class, 'owner_id');
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class)->withPivot('role')->withTimestamps();
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
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
