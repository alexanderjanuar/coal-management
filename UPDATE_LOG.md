# 📋 Update Log — Kisantra

Catatan ringkas perubahan, dikelompokkan per tanggal. Tiap entri: **modul** + inti perubahannya. Bukan dokumentasi formal — sekadar biar gampang dilacak apa yang berubah dan kapan.

---

## 23 Juni 2026

**👤 Tambah Client — PIC untuk Klien Pribadi**
- Tipe **Pribadi/Perorangan** kini bisa ditautkan ke **PIC**: dropdown **"Individu ini terdaftar sebagai PIC?"** → pilih **Ya** untuk memunculkan field PIC.
- Bisa **pilih PIC yang sudah ada atau buat PIC baru**; begitu dipilih, **Nama klien, NIK & password PIC** terisi otomatis (NIK & password tampil read-only).
- Layout form **Client Profile** dirapikan.

---

## 19 Juni 2026

**📊 Laporan Pajak**
- Kartu dikeluarkan dari container tabel Filament → sekarang tampil **flat & bersih**, tidak lagi seperti tabel.
- Toolbar dirapikan: filter dipindah ke **panel "Filter"** (modal); tombol "Group by" & toggle kolom dibuang.
- Bar **"Active filters"** dan baris **"Sort by" / select-all** di atas kartu dihilangkan.
- Pagination dibuat minimal (tanpa footer tabel).
- _Catatan: desain kartu & navigasi tahun/bulan tidak diubah; semua filter/aksi tetap berfungsi._

**🔔 Notifikasi Error (Discord)**
- Tambah perintah `php artisan discord:test-error` untuk kirim notifikasi contoh (uji format & koneksi), melewati guard production.

---

## 18 Juni 2026

**🔔 Notifikasi Error (Discord)**
- Error hanya dikirim saat **production** (bukan lagi di local/dev).
- Pesan lebih mudah dipahami: menampilkan **halaman**, **komponen/aksi**, dan **user** yang mengalami error.

**📄 Kontrak Klien**
- Tab **Kontrak** di detail klien jadi fungsional: **preview dokumen** (PDF/gambar) dalam frame + **upload / ganti / hapus**.

**🏢 Grup Client**
- Halaman daftar Grup Client: **pencarian**, **filter status**, dan **tambah client ke grup**.

---

## 15 Juni 2026

**📈 Dashboard Proyek**
- "Beban Kerja PIC" diubah jadi **bar chart kolom vertikal**.
- Widget "Aktivitas Terbaru" dihapus.

---

## 14 Juni 2026

**👤 Detail Klien — Identitas**
- **Redesign** dashboard identitas klien + indikator kelengkapan data.
- Tambah logika **prefix badan usaha** (PT, CV, dll).

---

## 29 Mei 2026

**🔑 Kredensial Klien**
- Komponen **CredentialManager** baru; modal lama diganti komponen modular.
- Redesign tampilan field & status kredensial.

---

## 25–26 Mei 2026

**📊 Dashboard Proyek**
- Dashboard proyek baru: distribusi status, analisis beban PIC, filter rentang waktu.
- Dukungan **dark mode** untuk komponen UI.
