# Panduan Penggunaan Daily Task Management

**Versi**: 1.0
**Terakhir diperbarui**: 17 Februari 2026

---

## Daftar Isi

1. [Membuka Halaman Daily Task](#1-membuka-halaman-daily-task)
2. [Mengenal Tampilan Kanban Board](#2-mengenal-tampilan-kanban-board)
3. [Membuat Tugas Baru (Quick Create)](#3-membuat-tugas-baru-quick-create)
4. [Membuat Tugas Baru (Wizard/Lengkap)](#4-membuat-tugas-baru-wizardlengkap)
5. [Memindahkan Tugas antar Kolom (Drag & Drop)](#5-memindahkan-tugas-antar-kolom-drag--drop)
6. [Melihat Detail Tugas](#6-melihat-detail-tugas)
7. [Mengedit Tugas](#7-mengedit-tugas)
8. [Mengelola Subtask](#8-mengelola-subtask)
9. [Menambah Komentar](#9-menambah-komentar)
10. [Melihat Riwayat Aktivitas](#10-melihat-riwayat-aktivitas)
11. [Menggunakan Filter & Pencarian](#11-menggunakan-filter--pencarian)
12. [Mengelompokkan Tugas (Grouped View)](#12-mengelompokkan-tugas-grouped-view)
13. [Menggunakan Dashboard](#13-menggunakan-dashboard)
14. [Menghapus Tugas](#14-menghapus-tugas)
15. [Tips & Catatan Penting](#15-tips--catatan-penting)

---

## 1. Membuka Halaman Daily Task

1. Login ke aplikasi
2. Pada menu navigasi utama, klik **"Daily Task"**
3. Kamu akan diarahkan ke halaman **Daily Task List** yang menampilkan Kanban Board sebagai tampilan utama

---

## 2. Mengenal Tampilan Kanban Board

Kanban Board terdiri dari **3 kolom** yang mewakili status tugas:

| Kolom | Status | Keterangan |
|-------|--------|------------|
| **To Do** | Pending | Tugas yang belum dikerjakan |
| **In Progress** | In Progress | Tugas yang sedang dikerjakan (maks. 5 tugas) |
| **Done** | Completed | Tugas yang sudah selesai |

**Yang ditampilkan pada setiap kartu tugas:**
- Judul tugas
- Label kategori (berdasarkan klien/proyek)
- Nama proyek (jika ada)
- Avatar user yang ditugaskan
- Tanggal deadline
- Jumlah subtask yang sudah selesai

**Catatan:** Kolom **In Progress** memiliki batas maksimal 5 tugas (WIP Limit). Jika sudah penuh, kamu tidak bisa menambah atau memindahkan tugas ke kolom tersebut sampai ada tugas yang dipindahkan keluar.

---

## 3. Membuat Tugas Baru (Quick Create)

Quick Create memungkinkan kamu membuat tugas langsung dari kolom Kanban tanpa meninggalkan halaman.

1. Di bawah salah satu kolom (To Do / In Progress / Done), klik tombol **"+ Tambah Tugas Baru"**
2. Form inline akan muncul di dalam kolom tersebut
3. Isi data berikut:
   - **Judul Tugas** (wajib) — ketik nama tugas yang ingin dibuat
   - **Deskripsi** (opsional) — tambahkan keterangan singkat
   - **Prioritas** — pilih: Rendah, Normal, Tinggi, atau Mendesak
   - **Tanggal** — pilih tanggal deadline tugas
4. Klik tombol **"Simpan"** untuk membuat tugas
5. Tugas baru akan langsung muncul di kolom yang dipilih

> Jika ingin membatalkan, klik tombol **"Batal"**

---

## 4. Membuat Tugas Baru (Wizard/Lengkap)

Untuk membuat tugas dengan informasi yang lebih lengkap, gunakan Wizard Create:

1. Klik tombol **"Buat Tugas"** di bagian header halaman
2. Ikuti langkah-langkah wizard berikut:

**Langkah 1 — Informasi Tugas:**
- Masukkan judul tugas
- Tambahkan deskripsi (mendukung rich text: bold, italic, list, dll.)
- Pilih tanggal mulai dan deadline
- Tentukan prioritas (Rendah / Normal / Tinggi / Mendesak)

**Langkah 2 — Klien & Proyek:**
- Pilih klien terlebih dahulu
- Daftar proyek akan otomatis menyesuaikan berdasarkan klien yang dipilih
- Pilih proyek yang relevan

**Langkah 3 — Assign User:**
- Pilih satu atau lebih user yang akan ditugaskan
- Secara default, kamu sendiri akan otomatis ditambahkan

**Langkah 4 — Subtask (Opsional):**
- Tambahkan subtask/checklist jika tugas memiliki langkah-langkah kecil
- Klik "Tambah" untuk menambah baris subtask baru
- Kamu bisa mengatur urutan subtask dengan drag & drop

3. Klik **"Simpan"** untuk membuat tugas

---

## 5. Memindahkan Tugas antar Kolom (Drag & Drop)

1. Arahkan kursor ke kartu tugas yang ingin dipindahkan
2. **Klik dan tahan** kartu tersebut
3. **Seret (drag)** kartu ke kolom tujuan (To Do, In Progress, atau Done)
4. **Lepaskan (drop)** kartu di kolom tujuan
5. Status tugas akan otomatis berubah sesuai kolom baru

**Yang terjadi secara otomatis:**
- Jika tugas dipindah ke **In Progress**, tanggal mulai akan otomatis diisi dengan hari ini (jika belum ada)
- Jika kolom **In Progress** sudah penuh (5 tugas), kamu akan mendapat notifikasi peringatan dan tugas akan dikembalikan ke kolom asal

---

## 6. Melihat Detail Tugas

1. Klik pada **kartu tugas** di Kanban Board
2. Modal detail tugas akan terbuka, menampilkan:
   - Judul tugas
   - Deskripsi lengkap
   - Status saat ini
   - Prioritas
   - Tanggal deadline dan tanggal mulai
   - User yang ditugaskan (beserta departemen & posisi)
   - Proyek dan klien terkait
   - Informasi siapa yang membuat dan kapan
3. Di bagian bawah modal terdapat **3 tab**: Komentar, Subtask, dan Aktivitas

---

## 7. Mengedit Tugas

1. Buka detail tugas dengan klik kartu di Kanban
2. Pada modal detail, kamu bisa mengubah:
   - **Judul** — klik pada judul untuk mengedit secara inline
   - **Deskripsi** — klik pada deskripsi untuk mengedit (mendukung rich text)
   - **Status** — gunakan dropdown untuk mengubah status
   - **Prioritas** — gunakan dropdown untuk mengubah prioritas
   - **Tanggal Deadline** — klik date picker untuk mengubah tanggal
   - **Tanggal Mulai** — klik date picker untuk mengubah tanggal
   - **Assign User** — tambah atau hapus user yang ditugaskan
   - **Proyek** — ubah proyek terkait (dropdown klien akan memfilter proyek)
3. Perubahan akan tersimpan otomatis

---

## 8. Mengelola Subtask

Subtask adalah langkah-langkah kecil di dalam sebuah tugas utama.

### Menambah Subtask
1. Buka detail tugas
2. Klik tab **"Subtask"**
3. Klik tombol **"Tambah Subtask"**
4. Ketik judul subtask
5. Subtask baru akan muncul di daftar

### Menandai Subtask Selesai
1. Pada tab Subtask, klik **checkbox** di samping subtask
2. Subtask akan berubah status menjadi **Completed**

### Mengedit Subtask
1. Klik pada judul subtask untuk mengedit secara inline
2. Ubah teks sesuai kebutuhan
3. Simpan perubahan

### Menghapus Subtask
1. Klik tombol **hapus** (ikon tempat sampah) di samping subtask

### Auto-Complete
- Jika **semua subtask** sudah berstatus selesai atau dibatalkan, tugas utama akan **otomatis berubah menjadi Completed**
- Jika ada subtask yang dimulai, tugas utama akan **otomatis berubah menjadi In Progress**

> Progress subtask ditampilkan pada kartu tugas di Kanban dalam format jumlah (contoh: 3/5)

---

## 9. Menambah Komentar

1. Buka detail tugas
2. Klik tab **"Komentar"** (tab default saat modal dibuka)
3. Pada area input di bagian bawah, ketik komentar kamu
4. Klik tombol **Kirim**
5. Komentar akan muncul di daftar lengkap dengan nama, avatar, dan waktu

---

## 10. Melihat Riwayat Aktivitas

1. Buka detail tugas
2. Klik tab **"Aktivitas"**
3. Kamu bisa melihat semua riwayat perubahan, termasuk:
   - Siapa yang membuat tugas
   - Perubahan status (contoh: dari Pending ke In Progress)
   - Perubahan prioritas
   - Perubahan judul atau deskripsi
   - Perubahan tanggal
   - Siapa yang melakukan perubahan dan kapan

---

## 11. Menggunakan Filter & Pencarian

Filter membantu kamu menemukan tugas tertentu dengan cepat.

### Pencarian Teks
1. Pada bagian filter di atas Kanban Board, ketik kata kunci di kolom **pencarian**
2. Hasil akan langsung difilter berdasarkan judul dan deskripsi tugas

### Filter Berdasarkan Kriteria
Kamu bisa mengkombinasikan beberapa filter sekaligus:

| Filter | Fungsi |
|--------|--------|
| **Tanggal** | Filter berdasarkan satu tanggal atau rentang tanggal |
| **Preset Cepat** | Hari Ini, Besok, Minggu Ini, Minggu Depan, Bulan Ini, Overdue |
| **Status** | Pilih satu atau beberapa status sekaligus |
| **Prioritas** | Pilih satu atau beberapa level prioritas |
| **Proyek** | Filter berdasarkan proyek tertentu |
| **Assignee** | Filter berdasarkan user yang ditugaskan |
| **Departemen** | Filter berdasarkan departemen |
| **Posisi** | Filter berdasarkan jabatan |

### Menghapus Filter
- Klik **tanda X** pada badge filter aktif untuk menghapus filter satu per satu
- Klik **"Reset Semua Filter"** untuk menghapus semua filter sekaligus

### Sorting (Pengurutan)
- Pilih urutan berdasarkan: Tanggal Tugas, Tanggal Dibuat, Judul, atau Prioritas
- Klik toggle **Ascending/Descending** untuk mengubah arah pengurutan

---

## 12. Mengelompokkan Tugas (Grouped View)

Selain Kanban Board, kamu bisa melihat tugas dalam bentuk kelompok:

1. Pada bagian pengaturan tampilan, pilih **"Group By"**
2. Pilih pengelompokan yang diinginkan:
   - **Status** — kelompokkan berdasarkan Pending, In Progress, Completed, Cancelled
   - **Prioritas** — kelompokkan berdasarkan Urgent, High, Normal, Low
   - **Proyek** — kelompokkan berdasarkan proyek
   - **Assignee** — kelompokkan berdasarkan user
   - **Tanggal** — kelompokkan berdasarkan Overdue, Hari Ini, Akan Datang, dll.
   - **Tidak Ada** — tampilkan sebagai daftar biasa

---

## 13. Menggunakan Dashboard

Dashboard memberikan ringkasan visual dari seluruh tugas.

### Mengakses Dashboard
1. Klik menu **"Daily Task Dashboard"** pada navigasi utama

### Kartu Statistik
Di bagian atas dashboard terdapat 4 kartu ringkasan:
- **Total Tugas** — jumlah total tugas pada periode yang dipilih
- **Tugas Saya** — jumlah tugas yang ditugaskan kepada kamu
- **Tugas Terlambat** — jumlah tugas yang melewati deadline (ditandai merah)
- **Tingkat Penyelesaian** — persentase tugas yang sudah selesai

### Grafik
- **Distribusi Status** — grafik donut yang menampilkan proporsi tugas berdasarkan status

### Daftar Tugas per Status
Di bawah grafik terdapat tab yang mengelompokkan tugas:
- **Semua Tugas** — menampilkan seluruh tugas
- **Pending** — tugas yang belum dimulai
- **In Progress** — tugas yang sedang dikerjakan
- **Completed** — tugas yang sudah selesai
- **Cancelled** — tugas yang dibatalkan

Pada setiap kartu tugas di dashboard, kamu bisa:
- Klik untuk membuka detail tugas
- Mengubah status langsung melalui dropdown

### Filter Dashboard
Dashboard memiliki filter sendiri yang bisa diatur:
- Rentang tanggal (dengan preset: Hari Ini, Minggu Ini, Bulan Ini)
- Proyek
- Assignee
- Departemen
- Posisi

---

## 14. Menghapus Tugas

1. Buka detail tugas dengan klik kartu di Kanban
2. Klik tombol **Hapus** (ikon tempat sampah) di bagian header modal
3. Dialog konfirmasi akan muncul
4. Klik **"Ya, Hapus"** untuk mengkonfirmasi
5. Tugas beserta seluruh subtask akan dihapus secara permanen

> **Peringatan:** Penghapusan tugas bersifat permanen dan tidak bisa dibatalkan.

---

## 15. Tips & Catatan Penting

- **WIP Limit:** Kolom In Progress dibatasi maksimal 5 tugas. Selesaikan tugas yang ada sebelum menambah tugas baru di kolom tersebut.
- **Auto-Complete:** Manfaatkan subtask agar tugas utama otomatis selesai saat semua subtask sudah selesai.
- **Filter Default:** Saat pertama kali membuka halaman, filter akan menampilkan tugas yang ditugaskan kepada kamu.
- **Lazy Loading:** Jika dalam satu kolom ada banyak tugas, hanya 10 tugas pertama yang dimuat. Klik **"Muat Lagi"** di bawah kolom untuk melihat tugas selanjutnya.
- **Dark Mode:** Semua tampilan mendukung dark mode. Sesuaikan melalui pengaturan tema aplikasi.
- **Keyboard & Aksesibilitas:** Kartu tugas bisa diakses menggunakan keyboard untuk navigasi yang lebih cepat.

---

*Jika ada pertanyaan atau kendala, silakan hubungi tim support.*
