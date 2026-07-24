#!/usr/bin/env bash
#
# Skrip deploy produksi. Dijalankan DI SERVER, dipanggil oleh GitHub Actions
# lewat SSH setiap kali ada push ke main.
#
# Bisa juga dijalankan manual dari server:
#   cd /home/kisantra-dev/htdocs/coal-management && ./deploy.sh
#
# CATATAN PENTING
#
# 1. Migrasi TIDAK dijalankan di sini. Skema database diubah manual supaya
#    tidak ada perubahan struktur yang mengenai data produksi tanpa ditinjau:
#      php artisan migrate --force
#
# 2. `php artisan view:cache` sengaja TIDAK dipakai. Paket
#    moataz-01/filament-notification-sound tidak menyertakan direktori
#    resources/views, sehingga perintah itu selalu gagal dan akan menggagalkan
#    seluruh deploy. `php artisan optimize` di Laravel 10 hanya meng-cache
#    config dan route, jadi aman.

set -euo pipefail

APP_DIR="${APP_DIR:-/home/kisantra-dev/htdocs/coal-management}"
BRANCH="${BRANCH:-main}"

log() { printf '\n\033[1;36m==> %s\033[0m\n' "$*"; }

cd "$APP_DIR"

log "Menarik perubahan dari origin/$BRANCH"
git fetch origin "$BRANCH"
# --ff-only sengaja dipilih ketimbang `reset --hard`: kalau ada perubahan lokal
# di server, deploy berhenti dengan pesan jelas alih-alih menghapusnya diam-diam.
git merge --ff-only "origin/$BRANCH"

log "Membersihkan cache lama"
# Dilakukan sebelum composer install, karena config cache yang basi bisa
# membuat package:discover gagal saat autoload di-dump ulang.
php artisan optimize:clear
php artisan filament:optimize-clear

log "Memasang dependensi PHP"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

log "Membangun aset frontend"
# public/build ada di .gitignore, jadi aset WAJIB dibangun di server.
# Tanpa langkah ini, seluruh token CSS dashboard hilang dan tampilannya rusak.
npm ci
npm run build

log "Menyiapkan cache produksi"
php artisan optimize          # config + route saja, view:cache dihindari
php artisan filament:optimize # komponen Filament + Blade icons

log "Merestart worker antrean"
# Worker menahan kode lama di memori sampai direstart.
php artisan queue:restart

log "Selesai: $(git rev-parse --short HEAD) $(git log -1 --format=%s)"
