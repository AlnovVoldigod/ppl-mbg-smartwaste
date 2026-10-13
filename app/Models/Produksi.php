<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produksi extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'produksis';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sppg_id',
        'menu_id',
        'tanggal',
        'target_porsi',
        'realisasi_porsi',
        'jam_mulai_masak',
        'jam_selesai_masak',
        'jam_selesai_kemas',
        'suhu_layak',
        'catatan',
    ];

    /**
     * Konversi tipe data atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'target_porsi' => 'integer',
            'realisasi_porsi' => 'integer',
            'suhu_layak' => 'boolean',
        ];
    }

    /**
     * Atribut tambahan yang otomatis disertakan dalam array / JSON.
     *
     * @var list<string>
     */
    protected $appends = [
        'total_distribusi',
    ];

    /**
     * SPPG yang memproduksi menu makanan.
     */
    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class);
    }

    /**
     * Menu makanan yang dimasak.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Riwayat distribusi dari produksi ini ke berbagai sekolah.
     */
    public function distribusis(): HasMany
    {
        return $this->hasMany(Distribusi::class);
    }

    /**
     * Alias relasi distribusi.
     */
    public function distribusi(): HasMany
    {
        return $this->hasMany(Distribusi::class);
    }

    /**
     * Accessor untuk total porsi yang telah didistribusikan.
     */
    public function getTotalDistribusiAttribute(): int
    {
        if ($this->relationLoaded('distribusis')) {
            return (int) $this->distribusis->sum('jumlah_porsi');
        }

        return (int) $this->distribusis()->sum('jumlah_porsi');
    }
}
