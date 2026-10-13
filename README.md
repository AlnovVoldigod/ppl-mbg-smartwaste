# 🥗 MBG SmartWaste (Sistem Monitoring Sisa Pangan & Sampah Kemasan MBG)

> **Proyek Pasangan Pembelajaran Lapangan (PPL)**  
> **Tema**: GovTech & Sustainable Nutrition (SDG 12: *Responsible Consumption and Production*)  
> **Status**: Laporan Progres Sprint 1 — Fondasi Sistem & Antarmuka UI/UX Dasar

---

## 📌 Deskripsi Proyek
**MBG SmartWaste** adalah sistem informasi berbasis web presisi tinggi untuk memantau sirkularitas dan meminimalisasi pemborosan pangan (*food waste*) serta sampah kemasan anorganik pada program nasional **Makan Bergizi Gratis (MBG)**. Platform ini menghubungkan tiga pemangku kepentingan utama:
1. **Satuan Pelayanan Pemenuhan Gizi (SPPG)**: Dapur produksi dan distribusi menu MBG.
2. **Sekolah Penerima**: Titik konsumsi dan pencatatan sisa porsi makanan (*plate waste*).
3. **Admin & Monitoring Pusat**: Pengawas kebijakan gizi, audit kepatuhan, dan analitik sirkularitas.

---

## 🛠️ Tech Stack
- **Framework Backend**: Laravel 12 (PHP 8.2+)
- **UI / Frontend**: Blade Views, Tailwind CSS v4, Heroicons
- **Micro-Reactivity**: Alpine.js (State management reaktif tanpa full-page reload)
- **Data Visualization**: Chart.js (Grafik tren, komposisi, dan peringkat)
- **Database**: SQLite (Development) / MySQL / PostgreSQL (Production)

---

## 🎯 Status Progres Sprint 1: Fondasi & UI/UX Dasar

| Kode PBI | Modul / Fitur | Status Sprint 1 | Keterangan |
|:---|:---|:---:|:---|
| **PBI-01** | Master Data SPPG & Wilayah Kerja |  Selesai | CRUD dasar & visualisasi card |
| **PBI-02** | Master Data Sekolah Penerima MBG |  Selesai | Registrasi unit & integrasi kuota siswa |
| **PBI-03** | Master Data Resep & Menu Pangan Bergizi |  Selesai | Komposisi gizi & estimasi kalori |
| **PBI-04** | Form Input Produksi Harian SPPG |  Selesai | Form menu, jam kemas, uji suhu layak |
| **PBI-13** | Dashboard Monitoring Pusat (UI & Metrik) |  Selesai | 4 KPI Cards, Waste Rate Badge, Chart.js |
| **PBI-LAYOUT**| Master Layout Shell & Role-Aware Navigation |  Selesai | Sidebar gradasi emerald, role switcher, datepicker |
| **PBI-MICRO** | Interaktivitas Mikro Alpine.js |  Selesai | Dynamic multi-row form, state toggles |

---

## 🚀 Panduan Menjalankan Proyek Lokal

### 1. Prasyarat
- PHP >= 8.2 & Composer
- Node.js >= 18.x & NPM
- Ekstensi SQLite / MySQL aktif

### 2. Instalasi & Menjalankan
```bash
# Salin konfigurasi lingkungan
cp .env.example .env

# Pasang dependensi PHP dan Node.js
composer install
npm install

# Generate Application Key
php artisan key:generate

# Migrasi skema database & pasang seeder simulasi
php artisan migrate --seed

# Kompilasi aset antarmuka (Tailwind CSS & Alpine.js)
npm run build

# Jalankan server lokal
php artisan serve
```

Akses browser pada alamat: **`http://127.0.0.1:8000`**

---

## 🎨 Palet Warna & Desain Sistem
- **Brand Primary**: Deep Emerald (`#065F46`) & Mint Eco (`#10B981`)
- **Indikator Waste Rate**:
  -  **Aman** (`< 5%`): Hijau Emerald
  -  **Perlu Evaluasi** (`5% - 10%`): Kuning Amber
  -  **Kritis** (`> 10%`): Merah Crimson

---
*Tim Pengembang MBG SmartWaste — Sprint 1 Milestone Review (2026).*
