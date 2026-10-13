<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Sekolah extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'sekolahs';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'alamat',
        'kelurahan',
        'kecamatan',
        'jumlah_siswa',
        'sppg_id',
        'user_id',
    ];

    /**
     * Konversi tipe data atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah_siswa' => 'integer',
        ];
    }

    /**
     * SPPG penyedia makanan untuk sekolah ini.
     */
    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class);
    }

    /**
     * Akun user perwakilan sekolah.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Riwayat distribusi makanan ke sekolah ini.
     */
    public function distribusis(): HasMany
    {
        return $this->hasMany(Distribusi::class);
    }

    /**
     * Riwayat penerimaan makanan di sekolah melalui tabel distribusi.
     */
    public function penerimaans(): HasManyThrough
    {
        return $this->hasManyThrough(
            Penerimaan::class,
            Distribusi::class,
            'sekolah_id',     // Foreign key on distribusis table
            'distribusi_id',  // Foreign key on penerimaans table
            'id',             // Local key on sekolahs table
            'id'              // Local key on distribusis table
        );
    }

    /**
     * Alias distribusi -> distribusis.
     */
    public function distribusi(): HasMany
    {
        return $this->hasMany(Distribusi::class);
    }

    /**
     * Accessor alias nama_sekolah -> nama.
     */
    public function getNamaSekolahAttribute(): string
    {
        return $this->attributes['nama'] ?? '';
    }
}
