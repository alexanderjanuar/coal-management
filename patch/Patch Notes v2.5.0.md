# 🎉 Patch Notes v2.5.0 - Enhanced Client Management Interface

**Release Date**: November 24, 2025  
**Version**: 2.5.0  
**Feature**: Complete Client Management Interface Redesign

---

## 📋 Overview

Peningkatan besar pada sistem Manajemen Klien! Pembaruan ini menghadirkan antarmuka modern berbasis tab yang memberikan tampilan 360 derajat terhadap hubungan Anda dengan klien. Antarmuka baru ini menggabungkan seluruh informasi terkait klien ke dalam satu dashboard yang tertata rapi dan mudah dinavigasi.

---

## ✨ What's New

### 🔄 Complete Interface Redesign
- **Modern Tabbed Layout**: Navigasi lebih ringkas dengan 9 tab yang tertata rapi

### 📊 New Client Tabs

#### 1. **Identitas (Identity)**
Tab Identitas menyediakan informasi lengkap tentang klien dalam satu tampilan yang mudah dibaca:

**Informasi Perusahaan:**
  - Nama legal perusahaan dan nama brand/dagang
  - Bentuk usaha dan bidang usaha
  - Tanggal berdiri perusahaan
  - Tipe klien (Badan atau Pribadi)

**Informasi Kontak:**
- Email perusahaan utama
- Telepon kantor dan mobile
- Website perusahaan
- Alamat lengkap dengan kota, provinsi, dan kode pos

**Kontak Person:**
- Daftar lengkap kontak person dengan jabatan
- Informasi email dan telepon masing-masing kontak
- Penanda kontak utama
- Support untuk multiple kontak person

**Perusahaan Terkait/Afiliasi:**
- Daftar perusahaan afiliasi dengan hubungan yang jelas
- Persentase kepemilikan untuk setiap afiliasi
- NPWP perusahaan terkait
- Fitur untuk menambah/edit informasi afiliasi

#### 2. **Perpajakan (Taxation)**
Tab Perpajakan mengelola semua aspek perpajakan klien dengan interface yang user-friendly:

**Informasi NPWP:**
- Nomor NPWP dengan format yang mudah dibaca
- Tanggal terdaftar NPWP
- Kantor Pelayanan Pajak (KPP) yang menaungi
- Status EFIN dengan indikator visual (terdaftar/belum)

**Status PKP:**
- Status Pengusaha Kena Pajak dengan indikator visual yang jelas
- Informasi lengkap tentang kewajiban PPN
- Badge status PKP/Non-PKP yang mudah dipahami

**Account Representative:**
- Nama dan informasi kontak AR
- Nomor telepon AR yang dapat diklik langsung
- Email AR dengan link mailto
- Kantor KPP tempat AR bertugas

#### 3. **Projek (Projects)**
Tab Projek menyediakan sistem manajemen proyek yang terintegrasi untuk melacak semua layanan yang diberikan kepada klien:

**Dashboard Statistik:**
- Total proyek dengan breakdown status
- Monitor proyek yang sedang berjalan (in progress)
- Tracking proyek selesai vs ongoing
- Indikator proyek urgent yang memerlukan perhatian khusus

**Manajemen Proyek:**
- Informasi lengkap proyek (nama, deskripsi)
- Tipe proyek (Single, Monthly, Yearly)
- Priority flag untuk proyek urgent
- Deadline tracking dengan visual indicators
- Status tracking dengan color-coded badges

**Deadline Monitoring:**
- Visual countdown untuk setiap deadline
- Color coding: merah (terlambat), kuning (≤7 hari), abu-abu (normal)
- Animated pulse untuk proyek yang sedang aktif
- Warning indicators untuk proyek yang mendekati atau melewati deadline

**Status Management:**
- Draft, Analysis, In Progress, Review, Completed
- Special status untuk "Completed (Not Paid Yet)"
- Visual pulse animation untuk proyek aktif
- Direct navigation ke detail proyek dengan click

**PIC Assignment:**
- Display Person in Charge untuk setiap proyek
- Visual badge dengan icon user
- Tracking accountability per proyek

#### 4. **Dokumen (Documents)**
Tab Dokumen menyediakan sistem manajemen dokumen yang komprehensif dengan dua kategori utama:

**Dokumen Legal Wajib:**
- Checklist dokumen sesuai SOP berdasarkan tipe klien (Badan/Pribadi)
- Progress bar completion dengan animasi shimmer
- Status tracking untuk setiap dokumen (uploaded/pending)
- Informasi nomor dokumen dan tanggal kadaluarsa
- Tracking siapa yang upload dan kapan
- Preview dokumen (gambar, PDF) langsung dalam modal
- Download dan re-upload functionality

**Dokumen Tambahan:**
- Upload dokumen pendukung di luar SOP standar
- Kategorisasi dokumen dengan nomor dan tanggal expired
- Manajemen file dengan preview dan download
- History upload dengan timestamp

**Fitur Lengkap:**
- Kelengkapan dokumen dalam persentase dengan visual yang menarik
- Stats mini untuk dokumen valid dan expired
- Modal upload dengan form lengkap (nomor dokumen, kategori, tanggal expired)
- Preview modal yang mendukung berbagai format file
- Konfirmasi hapus untuk keamanan data

#### 5. **Komunikasi (Communications)**
Tab Komunikasi berfungsi sebagai hub dokumentasi lengkap untuk semua interaksi klien:

**Timeline Komunikasi:**
- Tampilan timeline interaktif dengan visual yang menarik
- Support untuk berbagai jenis komunikasi (meeting, email, telepon, video call)
- Rich text description dengan formatting
- File attachment management
- Quick actions (edit/delete) yang muncul saat hover

**Dashboard Statistik:**
- Cards statistik untuk total komunikasi per jenis
- Counter visual dengan ikon yang sesuai
- Breakdown komunikasi berdasarkan tipe (meeting, email, telepon)

**Detail Komunikasi:**
- Modal detail lengkap dengan informasi waktu dan tempat
- Manajemen attachment dengan preview dan download
- Tracking user yang membuat record
- Catatan tambahan untuk context yang lebih lengkap

**Fitur Interaktif:**
- Create/edit modal dengan form yang comprehensive
- Responsive design dengan hover effects
- Timeline visual dengan garis penghubung antar entry
- Search dan filtering capabilities

#### 6. **Kontrak (Contracts)**
Contract management system:
- Service agreement tracking
- Contract renewal alerts
- Terms and conditions management
- Document attachment support

#### 7. **Compliance**
Tab Compliance menyediakan dashboard monitoring kewajiban pajak dan deadline pelaporan yang komprehensif:

**Dashboard Kewajiban Pajak:**
- Overview total kewajiban pajak klien
- Tracking status completion (Selesai vs Pending)
- Priority flagging untuk kewajiban prioritas tinggi
- Visual indicators untuk deadline monitoring

**Deadline Management:**
- Real-time countdown untuk setiap kewajiban
- Color-coded deadline alerts:
  - Merah + pulse animation: Terlambat (overdue)
  - Orange + pulse: Hari ini!
  - Kuning: ≤7 hari tersisa (urgent)
  - Abu-abu: >7 hari (normal)
- Visual warning dengan animated ping untuk deadline terlewat

**Jenis Kewajiban:**
- PPN (Pajak Pertambahan Nilai) tracking
- PPh 21 (Pajak Penghasilan) monitoring
- Automated periode tracking (bulanan/tahunan)
- Icon-based visual identification

**Priority System:**
- Tinggi: Peredaran Bruto ≥ Rp 10 Miliar
- Sedang: Peredaran Bruto ≥ Rp 1 Miliar  
- Rendah: Peredaran Bruto < Rp 1 Miliar
- Tooltips informatif untuk setiap level prioritas
- Color-coded badges dengan ring indicators

**Status Tracking:**
- Selesai: Laporan telah disampaikan ke DJP
- Pending: Menunggu pelaporan dengan pulse indicator
- Tooltips dengan informasi detail status
- Visual feedback dengan hover effects

**Fitur Interaktif:**
- Direct link ke tax reports untuk action cepat
- Empty state informatif untuk first-time setup
- Responsive table dengan hover states
- Smart prioritization berdasarkan deadline dan nilai

#### 8. **Karyawan (Employees)**
Tab Karyawan menyediakan sistem manajemen karyawan yang komprehensif untuk klien korporat dengan integrasi perhitungan PPh 21:

**Dashboard Statistik:**
- Cards statistik interaktif menampilkan total karyawan
- Monitoring karyawan aktif vs tidak aktif
- Breakdown berdasarkan tipe (Karyawan Tetap vs Harian)
- Visual indicators dengan icon dan warna yang intuitif

**Manajemen Data Karyawan:**
- Informasi lengkap karyawan (nama, NPWP, jabatan)
- Tipe karyawan dengan badge visual (Tetap/Harian)
- Data gaji untuk kalkulasi PPh 21
- Status aktivitas karyawan dengan indikator warna

**Status PTKP (Penghasilan Tidak Kena Pajak):**
- Tracking status pernikahan (TK/K)
- Jumlah tanggungan (0-3)
- Visual badge dengan tooltip informasi
- Otomatis kalkulasi berdasarkan status

**Fitur Lengkap:**
- Modal create/edit dengan form validation
- Avatar generator untuk setiap karyawan
- Quick actions dengan hover effects
- Delete confirmation dengan warning
- Responsive table dengan hover states
- Empty state yang informatif untuk first-time users

#### 9. **Tim (Team)**
Internal team management:
- Assigned team members
- Role assignments
- Specializations tracking
- Workload distribution

---

## 📸 Screenshots

### Identitas Tab
![Identitas Tab]([Screenshot\Patchv2.5.0\IdentitasTab.png](https://github.com/alexanderjanuar/coal-management/blob/main/patch/Screenshot/Patchv2.5.0/ComplienceTab.png))
![Identitas Tab]((https://github.com/alexanderjanuar/coal-management/blob/main/patch/Screenshot/Patchv2.5.0/ComplienceTabDark.png))

### Perpajakan Tab
![Perpajakan Tab](Screenshot\Patchv2.5.0\PerpajakanTab.png)
![Perpajakan Tab](Screenshot\Patchv2.5.0\PerpajakanTabDark.png)

### Dokumen Tab
![Perpajakan Tab](Screenshot\Patchv2.5.0\DokumenTab.png)
![Perpajakan Tab](Screenshot\Patchv2.5.0\DokumenTab2.png)

### Komunikasi Tab
![Komunikasi Tab](Screenshot\Patchv2.5.0\KomunikasiTab.png)
![Komunikasi Tab](Screenshot\Patchv2.5.0\KomunikasiTabDark.png)

### Karyawan Tab
![Karyawan Tab](Screenshot\Patchv2.5.0\KaryawanTab.png)
![Karyawan Tab](Screenshot\Patchv2.5.0\KaryawanTabDark.png)

### Complience Tab
![Complience Tab](Screenshot\Patchv2.5.0\ComplienceTab.png)
![Complience Tab](Screenshot\Patchv2.5.0\ComplienceTabDark.png)

### Project Tab
![Project Tab](Screenshot\Patchv2.5.0\ProjectTab.png)
![Project Tab](Screenshot\Patchv2.5.0\ProjectTabDark.png)
---

## 🚀 Getting Started

### Accessing the New Interface

1. **Masuk ke Menu Klien**
   - Buka menu navigasi utama
   - Klik bagian "Clients"
   - Pilih salah satu klien dari daftar

2. **Explore the Tabs**
   - Gunakan navigasi bergaya pill di bagian atas
   - Klik tab apa pun untuk berpindah tampilan
   - Setiap tab akan memuat konten secara dinamis

3. **Tab Navigation**
   ```
   Identitas → Perpajakan → Projek → Kontrak → Dokumen → 
   Komunikasi → Compliance → Karyawan → Tim
   ```

### Tab Functions

#### **Tab Identitas (Identity)**
**Cara Menggunakan:**
1. **Melihat Informasi Dasar**
   - Informasi perusahaan ditampilkan dalam kotak terpisah untuk kemudahan membaca
   - Data seperti nama legal, bentuk usaha, dan tanggal berdiri tersedia langsung
   - Format display yang clean dan mudah dipahami

2. **Mengelola Kontak Person**
   - Multiple kontak person dapat ditambahkan untuk satu klien
   - Setiap kontak memiliki tipe (utama, sekunder, billing, teknis)
   - Kontak utama ditandai dengan badge khusus
   - Informasi lengkap meliputi nama, jabatan, email, dan telepon

3. **Perusahaan Afiliasi**
   - Klik "Tambah Perusahaan Terkait" untuk menambah afiliasi baru
   - Isi informasi seperti nama perusahaan, tipe hubungan, dan persentase kepemilikan
   - NPWP afiliasi dapat diisi untuk tracking yang lebih baik
   - Link ke klien lain jika afiliasi juga terdaftar sebagai klien

**Tips Penggunaan:**
- Pastikan informasi kontak selalu update untuk komunikasi yang efektif
- Gunakan catatan di bagian afiliasi untuk informasi tambahan yang penting
- Kontak utama sebaiknya adalah orang yang paling mudah dihubungi

#### **Tab Perpajakan (Taxation)**
**Cara Menggunakan:**
1. **Memantau Status NPWP**
   - NPWP ditampilkan dalam format yang mudah dibaca (XX.XXX.XXX.X-XXX.XXX)
   - Status EFIN ditandai dengan indikator visual hijau (terdaftar) atau merah (belum)
   - Informasi KPP menunjukkan kantor pajak yang menaungi klien

2. **Mengelola Status PKP**
   - Status PKP/Non-PKP ditampilkan dengan badge yang jelas
   - Informasi ini penting untuk menentukan kewajiban PPN klien
   - Status "Ya" dengan centang hijau untuk PKP, "Tidak" dengan X abu-abu untuk Non-PKP

3. **Informasi Account Representative**
   - Nama AR dan informasi kontak tersedia langsung
   - Nomor telepon AR dapat diklik untuk melakukan panggilan langsung
   - Email AR memiliki link mailto untuk kemudahan komunikasi

**Tips Penggunakan:**
- Selalu verifikasi status PKP klien sebelum memproses faktur PPN
- Simpan informasi kontak AR untuk komunikasi terkait perpajakan
- Update informasi EFIN jika ada perubahan status e-Filing klien

#### **Tab Dokumen (Documents)**
**Cara Menggunakan:**
1. **Memantau Kelengkapan Dokumen**
   - Progress bar menunjukkan persentase kelengkapan dokumen legal wajib
   - Stats mini menampilkan jumlah dokumen valid vs expired
   - Animasi shimmer pada progress bar memberikan feedback visual yang menarik

2. **Mengelola Dokumen Legal Wajib**
   - Checklist otomatis berdasarkan SOP sesuai tipe klien (Badan/Pribadi)
   - Status dokumen ditampilkan dengan badge berwarna (uploaded/pending)
   - Klik "Upload" untuk mengunggah dokumen baru atau "Re-upload" untuk mengganti
   - Informasi detail: nomor dokumen, tanggal expired, dan siapa yang upload

3. **Preview dan Download**
   - Klik ikon mata untuk preview dokumen (support gambar dan PDF)
   - Preview modal menampilkan dokumen dalam ukuran penuh
   - Download langsung dari tabel atau modal preview
   - Format tidak supported tetap bisa didownload

4. **Dokumen Tambahan**
   - Klik "Tambah Dokumen" untuk upload file di luar SOP
   - Input nomor dokumen dan tanggal expired untuk tracking yang lebih baik
   - Kategorisasi dokumen untuk organisasi yang lebih rapi

**Tips Penggunaan:**
- Selalu isi nomor dokumen dan tanggal expired untuk tracking yang optimal
- Gunakan preview sebelum download untuk memastikan dokumen yang benar
- Monitor tanggal expired secara berkala (ditampilkan dengan warna merah jika expired)

#### **Tab Komunikasi (Communications)**
**Cara Menggunakan:**
1. **Melihat Timeline Komunikasi**
   - Timeline menampilkan semua komunikasi dalam urutan kronologis terbaru
   - Hover pada card untuk melihat quick actions (edit/delete)
   - Klik card untuk membuka detail lengkap komunikasi
   - Garis timeline menghubungkan antar entry untuk kontinuitas visual

2. **Menambah Catatan Komunikasi**
   - Klik "Tambah Catatan" untuk membuat entry baru
   - Pilih jenis komunikasi (meeting, email, telepon, video call)
   - Input judul, deskripsi lengkap, tanggal dan waktu
   - Upload file attachment jika diperlukan

3. **Dashboard Statistik**
   - Cards di atas menampilkan total komunikasi per kategori
   - Visual icon yang sesuai dengan jenis komunikasi
   - Angka real-time berdasarkan data yang ada

4. **Detail dan Attachment Management**
   - Modal detail menampilkan informasi lengkap komunikasi
   - File attachment dapat di-preview dan download langsung
   - Tracking siapa yang membuat record dan kapan
   - Edit dan delete tersedia untuk modifikasi data

**Tips Penggunaan:**
- Dokumentasikan komunikasi sesegera mungkin setelah terjadi
- Gunakan attachment untuk menyimpan dokumen penting terkait komunikasi
- Manfaatkan rich text editor untuk deskripsi yang detail dan terformat
- Review timeline secara berkala untuk memantau frekuensi komunikasi klien

#### **Tab Karyawan (Employees)**
**Cara Menggunakan:**
1. **Melihat Dashboard Statistik**
   - Cards di atas tabel menampilkan ringkasan data karyawan
   - Total karyawan, jumlah aktif, tidak aktif, dan karyawan tetap
   - Visual icon dan warna memudahkan identifikasi status

2. **Mengelola Data Karyawan**
   - Klik "Tambah Karyawan" untuk menambah data baru
   - Tabel menampilkan informasi lengkap: nama, NPWP, jabatan, tipe, PTKP, gaji
   - Avatar otomatis generated untuk setiap karyawan
   - Hover pada baris untuk melihat action buttons

3. **Status PTKP (Penghasilan Tidak Kena Pajak)**
   - TK (Tidak Kawin) dengan angka tanggungan 0-3
   - K (Kawin) dengan angka tanggungan 0-3
   - Hover pada badge PTKP untuk melihat detail tooltip
   - Penting untuk perhitungan PPh 21

4. **Form Input Karyawan**
   - Modal form dengan validation
   - Field: Nama, NPWP, Jabatan, Tipe (Tetap/Harian)
   - Status pernikahan dan jumlah tanggungan
   - Input gaji untuk kalkulasi pajak
   - Status aktif/tidak aktif

5. **Edit dan Hapus**
   - Klik ikon pensil untuk edit data
   - Klik ikon trash untuk hapus dengan konfirmasi
   - Quick actions muncul saat hover pada baris

**Tips Penggunaan:**
- Pastikan data NPWP dan status PTKP benar untuk perhitungan PPh 21 yang akurat
- Update status karyawan (aktif/tidak aktif) secara berkala
- Gunakan tipe "Karyawan Tetap" untuk pegawai permanen dengan gaji bulanan
- Gunakan tipe "Harian" untuk pekerja lepas atau kontrak
- Data gaji bersifat opsional namun penting untuk kalkulasi pajak

#### **Tab Projek (Projects)**
**Cara Menggunakan:**
1. **Melihat Overview Proyek**
   - Dashboard cards menampilkan statistik real-time
   - Monitor total proyek, yang sedang berjalan, selesai, dan urgent
   - Visual indicators dengan icon dan warna yang intuitif

2. **Tracking Deadline**
   - Setiap proyek menampilkan deadline dengan countdown
   - Color coding otomatis: merah (terlambat), kuning (urgent), abu-abu (normal)
   - Hover untuk melihat detail hari tersisa atau keterlambatan

3. **Management Status**
   - Badge status dengan warna sesuai kondisi proyek
   - Pulse animation untuk proyek yang sedang aktif (in_progress)
   - Special indicator untuk proyek selesai tapi belum dibayar

4. **Navigasi ke Detail**
   - Klik pada baris proyek untuk membuka detail lengkap
   - Direct link ke project view untuk aksi lebih lanjut
   - PIC assignment visible untuk accountability

**Tips Penggunaan:**
- Prioritaskan proyek dengan badge "Urgent" dan deadline merah
- Monitor proyek "Completed (Not Paid Yet)" untuk follow up pembayaran
- Review proyek dengan status "In Progress" secara berkala
- Gunakan PIC assignment untuk koordinasi tim yang efektif

#### **Tab Compliance**
**Cara Menggunakan:**
1. **Monitoring Kewajiban Pajak**
   - Dashboard menampilkan total kewajiban dengan breakdown status
   - Cards statistik untuk tracking completion rate
   - Visual indicators untuk prioritas tinggi

2. **Deadline Management**
   - Real-time countdown untuk setiap kewajiban pajak
   - Color-coded alerts: merah (terlambat), orange (hari ini), kuning (urgent)
   - Animated pulse untuk deadline yang terlewat atau hari ini

3. **Priority System**
   - Hover pada badge prioritas untuk melihat tooltip informasi
   - Tinggi: Peredaran ≥10M, Sedang: ≥1M, Rendah: <1M
   - Ring indicators untuk visual emphasis

4. **Status Tracking**
   - Selesai: Green badge dengan check icon
   - Pending: Orange badge dengan pulse indicator
   - Tooltips menjelaskan detail status

5. **Action Items**
   - Link langsung ke tax reports untuk action
   - Empty state guide untuk first-time setup
   - Table responsif dengan hover effects

**Tips Penggunaan:**
- Prioritaskan kewajiban dengan deadline "HARI INI" atau terlambat
- Monitor kewajiban prioritas tinggi (peredaran ≥10 miliar) lebih ketat
- Set reminder untuk deadline yang mendekati (≤7 hari)
- Review status "Pending" secara berkala untuk ensure compliance
- Gunakan filter prioritas untuk fokus pada kewajiban kritikal
   - Total karyawan, jumlah aktif, tidak aktif, dan karyawan tetap
   - Visual icon dan warna memudahkan identifikasi status

2. **Mengelola Data Karyawan**
   - Klik "Tambah Karyawan" untuk menambah data baru
   - Tabel menampilkan informasi lengkap: nama, NPWP, jabatan, tipe, PTKP, gaji
   - Avatar otomatis generated untuk setiap karyawan
   - Hover pada baris untuk melihat action buttons

3. **Status PTKP (Penghasilan Tidak Kena Pajak)**
   - TK (Tidak Kawin) dengan angka tanggungan 0-3
   - K (Kawin) dengan angka tanggungan 0-3
   - Hover pada badge PTKP untuk melihat detail tooltip
   - Penting untuk perhitungan PPh 21

4. **Form Input Karyawan**
   - Modal form dengan validation
   - Field: Nama, NPWP, Jabatan, Tipe (Tetap/Harian)
   - Status pernikahan dan jumlah tanggungan
   - Input gaji untuk kalkulasi pajak
   - Status aktif/tidak aktif

5. **Edit dan Hapus**
   - Klik ikon pensil untuk edit data
   - Klik ikon trash untuk hapus dengan konfirmasi
   - Quick actions muncul saat hover pada baris

**Tips Penggunaan:**
- Pastikan data NPWP dan status PTKP benar untuk perhitungan PPh 21 yang akurat
- Update status karyawan (aktif/tidak aktif) secara berkala
- Gunakan tipe "Karyawan Tetap" untuk pegawai permanen dengan gaji bulanan
- Gunakan tipe "Harian" untuk pekerja lepas atau kontrak
- Data gaji bersifat opsional namun penting untuk kalkulasi pajak

#### **Tab Tim (Team)**
Internal team assignments:
- Assigned team members
- Role and responsibility tracking
- Specialization management
- Workload distribution

---
## 🐛 Known Issues

### Minor Issues
- Tab loading indicator may briefly appear on fast connections
---

## 🔮 Coming Soon

### Planned Features
- **Advanced Analytics**: Client insights dashboard
- **Bulk Export**: Excel/CSV export untuk data klien
- **Laporan Pajak**: Finalisasi Laporan Pajak

---

*For technical questions or support, please contact our support team.*

---

*Last Updated: November 24, 2025*  
*Next Update: November 25~, 2025*
