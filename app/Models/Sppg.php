<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sppg extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'sppgs';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'wilayah',
        'alamat',
        'kapasitas_harian',
        'kontak_penanggung_jawab',
        'telepon',
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
            'kapasitas_harian' => 'integer',
        ];
    }

    /**
     * Akun user penanggung jawab SPPG.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Daftar sekolah yang dilayani oleh SPPG ini.
     */
    public function sekolahs(): HasMany
    {
        return $this->hasMany(Sekolah::class);
    }

    /**
     * Riwayat produksi makanan oleh SPPG ini.
     */
    public function produksis(): HasMany
    {
        return $this->hasMany(Produksi::class);
    }

    /**
     * Catatan limbah dapur dari SPPG ini.
     */
    public function limbahDapurs(): HasMany
    {
        return $this->hasMany(LimbahDapur::class);
    }

    /**
     * Alias sekolah -> sekolahs.
     */
    public function sekolah(): HasMany
    {
        return $this->hasMany(Sekolah::class);
    }

    /**
     * Alias produksi -> produksis.
     */
    public function produksi(): HasMany
    {
        return $this->hasMany(Produksi::class);
    }

    /**
     * Alias limbahDapur -> limbahDapurs.
     */
    public function limbahDapur(): HasMany
    {
        return $this->hasMany(LimbahDapur::class);
    }

    /**
     * Accessor alias nama_sppg -> nama.
     */
    public function getNamaSppgAttribute(): string
    {
        return $this->attributes['nama'] ?? '';
    }
}
