<?php

namespace App\Providers;

use App\Models\Penerimaan;
use App\Models\SampahKemasan;
use App\Models\SisaPangan;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'penerimaan' => Penerimaan::class,
            'sisa_pangan' => SisaPangan::class,
            'sampah_kemasan' => SampahKemasan::class,
        ]);
    }
}
