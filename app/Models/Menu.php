<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'deskripsi',
        'kategori',
        'komponen_menu',
        'kalori_estimasi',
    ];

    /**
     * Konversi tipe data atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kalori_estimasi' => 'integer',
        ];
    }

    /**
     * Riwayat produksi yang menggunakan menu ini.
     */
    public function produksis(): HasMany
    {
        return $this->hasMany(Produksi::class);
    }

    /**
     * Alias relasi produksi.
     */
    public function produksi(): HasMany
    {
        return $this->hasMany(Produksi::class);
    }

    /**
     * Accessor alias nama_menu -> nama.
     */
    public function getNamaMenuAttribute(): string
    {
        return $this->attributes['nama'] ?? '';
    }
}
