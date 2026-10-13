<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'unit_name',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'role' => 'string',
        ];
    }

    /**
     * Cek apakah user adalah admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user adalah SPPG.
     */
    public function isSppg(): bool
    {
        return $this->role === 'sppg';
    }

    /**
     * Cek apakah user adalah sekolah.
     */
    public function isSekolah(): bool
    {
        return $this->role === 'sekolah';
    }

    /**
     * Relasi ke unit SPPG (jika user role sppg).
     */
    public function sppg(): HasOne
    {
        return $this->hasOne(Sppg::class);
    }

    /**
     * Relasi ke unit Sekolah (jika user role sekolah).
     */
    public function sekolah(): HasOne
    {
        return $this->hasOne(Sekolah::class);
    }

    /**
     * Relasi ke verifikasi yang dilakukan oleh user ini.
     */
    public function verifikasis(): HasMany
    {
        return $this->hasMany(Verifikasi::class, 'verified_by');
    }
}
