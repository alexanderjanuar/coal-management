# 📝 Patch Notes - Fitur Catatan Proyek (Project Notes)

**Tanggal Rilis**: 2 Maret 2026  
**Fitur**: Project Notes dengan Riwayat Catatan

---

## 📋 Overview

Pembaruan ini menghadirkan fitur **Catatan Proyek** yang memungkinkan tim untuk menambahkan, melihat, dan mengelola catatan langsung dari daftar proyek. Setiap catatan tersimpan dengan riwayat lengkap beserta nama penulis, waktu, dan jenis catatan — sehingga komunikasi dan dokumentasi proyek menjadi lebih tertata.

---

## ✨ Yang Baru

### 💬 Fitur Catatan Proyek (Project Notes)

#### Tombol Aksi "Notes" di Tabel Proyek
- Tombol **Notes** kini tersedia langsung di setiap baris proyek
- Badge angka menunjukkan jumlah catatan yang sudah ada pada proyek tersebut
- Satu tombol yang menggabungkan dua fungsi: lihat riwayat & tambah catatan baru

#### Panel Catatan (Slide-Over)
Saat tombol diklik, panel sarung geser (slide-over) terbuka dengan dua bagian:

**Bagian Atas — Riwayat Catatan:**
- Menampilkan semua catatan sebelumnya dari yang terbaru ke terlama
- Setiap catatan dilengkapi:
  - Inisial avatar penulis
  - Nama lengkap penulis
  - Waktu relatif & absolut (contoh: "2 jam lalu · 2 Mar 2026, 10:30")
  - Badge jenis catatan berwarna
  - Isi catatan

**Bagian Bawah — Form Tambah Catatan:**
- Pilihan **Jenis Catatan**:
  - 💬 **Umum** — Informasi atau pembaruan rutin
  - ⚠️ **Penting** — Hal yang perlu mendapat perhatian
  - 🚫 **Penghambat** — Kendala yang menghambat kemajuan proyek
- Kolom **Catatan Baru** (textarea, maksimal 2.000 karakter)
- Tombol **"Add Note"** untuk menyimpan

#### Indikator Catatan di Tabel
- Ikon gelembung chat muncul di kolom khusus pada baris yang sudah memiliki catatan
- **Warna ikon** mencerminkan jenis catatan terbaru:
  - 🔵 Biru — Umum
  - 🟡 Kuning — Penting
  - 🔴 Merah — Penghambat
- **Tooltip** saat hover menampilkan jumlah catatan dan waktu catatan terakhir

---

## 🚀 Cara Menggunakan

1. **Buka menu Proyek** di panel admin
2. **Cari baris proyek** yang ingin diberi catatan
3. **Klik tombol "Notes"** (ikon gelembung chat) di kolom aksi
4. Panel akan terbuka dari sisi kanan layar
5. **Lihat riwayat** catatan di bagian atas
6. **Isi form** di bagian bawah dan klik **"Add Note"** untuk menyimpan

---

## 💡 Tips Penggunaan

- Gunakan jenis **Penghambat** 🚫 untuk segera menginformasikan kendala ke tim
- Gunakan jenis **Penting** ⚠️ untuk hal-hal yang memerlukan tindak lanjut
- Gunakan jenis **Umum** 💬 untuk update progres rutin
- Pantau ikon di kolom tabel — warna merah menandakan ada catatan penghambat yang perlu diperhatikan

---

*Terakhir Diperbarui: 2 Maret 2026*
