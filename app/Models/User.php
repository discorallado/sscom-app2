<?php

namespace App\Models;

use App\Models\Manager\Cotization as ModelsCotization;
use App\Models\Manager\Customer as ModelsCustomer;
use App\Models\Manager\Outdate as ModelsOutdate;
use App\Models\Manager\Payment as ModelsPayment;
use App\Models\Manager\Work as ModelsWork;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function canAccessFilament(): bool
    {
        return true;
    }

    public function note(): HasMany
    {
        return $this->hasMany(Note::class, 'user_id');
    }

    public function cotization(): HasMany
    {
        return $this->hasMany(ModelsCotization::class, 'user_id');
    }

    public function customer(): HasMany
    {
        return $this->hasMany(ModelsCustomer::class, 'user_id');
    }

    public function outdate(): HasMany
    {
        return $this->hasMany(ModelsOutdate::class, 'user_id');
    }

    public function payents(): HasMany
    {
        return $this->hasMany(ModelsPayment::class, 'user_id');
    }

    public function work(): HasMany
    {
        return $this->hasMany(ModelsWork::class, 'user_id');
    }


}
