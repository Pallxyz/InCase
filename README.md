# InCase — Laravel 12 + Blade + Tailwind

Ini bukan project Laravel penuh (composer install butuh akses ke packagist.org yang
gak bisa saya jangkau dari sandbox ini). Yang saya kasih: semua file Blade + config
frontend, tinggal drop ke project Laravel 12 kosong.

## Cara pasang

1. Bikin project Laravel 12 baru (kalau belum ada):
   ```bash
   composer create-project laravel/laravel incase
   cd incase
   ```

2. Copy semua isi folder ini ke root project, timpa yang sudah ada:
   - `resources/views/` → replace/merge
   - `resources/css/app.css` → replace
   - `resources/js/app.js` dan `resources/js/bootstrap.js` → replace
   - `routes/web.php` → replace
   - `tailwind.config.js` → taruh di root
   - `vite.config.js` → replace punya bawaan Laravel
   - `package.json` → merge `dependencies`/`devDependencies`-nya ke package.json yang ada

3. Install dependency:
   ```bash
   npm install
   composer require laravel/breeze --dev # skip kalau gak butuh auth
   ```

4. Jalankan:
   ```bash
   npm run dev
   php artisan serve
   ```

## Struktur

```
resources/views/
  layouts/
    app.blade.php         → shell HTML (head, font, @vite)
  components/
    navbar.blade.php       (pakai Alpine.js buat toggle menu mobile)
    hero.blade.php
    stats.blade.php
    features.blade.php
    how-it-works.blade.php
    dashboard-preview.blade.php
    technology.blade.php
    faq.blade.php          (Alpine.js buat accordion)
    footer.blade.php
    ui/button.blade.php    (reusable button, variant & size props)
    icon/*.blade.php       (29 icon Lucide dalam bentuk SVG asli, dipakai via <x-icon.nama />)
  pages/
    home.blade.php          → assembly semua komponen di atas
routes/web.php               → GET / render pages.home
```

## Catatan teknis

- **Alpine.js** dipakai buat interaktivitas (toggle menu navbar, accordion FAQ) —
  bukan Vue/React/Livewire, cuma library JS ringan (~15kb) yang lazim dipasangkan
  sama Blade + Tailwind. Kalau mau dihapus dan diganti vanilla JS murni, tinggal
  ganti `x-data`/`x-show`/`@click` di `navbar.blade.php` dan `faq.blade.php` sama
  `addEventListener` biasa.
- **Icon**: saya ambil SVG asli dari package `lucide-static` (sama persis dengan
  `lucide-react` yang dipakai di versi React), jadi bentuknya identik, bukan
  reka-reka.
- **Warna, spacing, radius, font, animasi scan** — semua CSS variable & keyframe
  di `app.css` saya salin apa adanya dari `globals.css` React, jadi tampilan
  harusnya pixel-identical.
- Data statis (stats, features, steps, faq, dst) saya taruh sebagai array `@php`
  di masing-masing komponen — kalau nanti mau dari database, tinggal ganti jadi
  Eloquent query / Controller yang pass ke view.
