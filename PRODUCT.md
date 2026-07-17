# Product

## Register

product

## Users

Manajer dan supervisor internal Kisantra, konsultan pajak yang menangani banyak klien sekaligus.

Konteks pemakaian: dibuka beberapa kali dalam seminggu dari monitor desktop di kantor, pada jam kerja, di ruangan bercahaya terang. Ini bukan surface yang ditunggui sepanjang hari, melainkan tempat orang mampir untuk mengecek keadaan lalu pergi mengerjakan sesuatu.

Pekerjaan yang ingin diselesaikan, berurutan menurut prioritas:

1. **Triase:** klien mana yang statusnya masih Belum Lapor dan deadline-nya sudah mepet.
2. **Kelengkapan:** berapa progres pelaporan PPN, PPh, dan Bupot untuk periode berjalan.
3. **Nilai:** berapa total pajak yang ditangani dan bagaimana trennya antar bulan.

Karena pemakaiannya berkala, bukan kontinu, surface ini harus menjawab "apa yang berubah dan apa yang genting" tanpa mengharuskan pembacanya mengingat kondisi terakhir kali dia mampir.

## Product Purpose

Memberi manajer gambaran keadaan pelaporan pajak seluruh klien dalam satu layar, dan menunjukkan dengan tepat di mana ada risiko keterlambatan.

Sukses berarti: seorang manajer membuka dashboard, dalam lima detik tahu klien mana yang perlu didorong, lalu mengklik langsung menuju laporan tersebut. Gagal berarti: manajer melihat sekumpulan angka, tidak tahu harus berbuat apa, lalu menutup tab dan bertanya ke stafnya lewat chat.

Konsekuensi keterlambatan pelaporan pajak di Indonesia bersifat nyata (sanksi administratif dan denda), sehingga surface ini menanggung tanggung jawab operasional, bukan sekadar menampilkan ringkasan.

## Brand Personality

Terpercaya, rapi, tenang.

Nada acuan: dashboard perkakas keuangan kelas atas. Angka diperlakukan sebagai subjek utama dan disajikan dengan hierarki yang tegas. Kesan yang dituju: "ini uang dan kewajiban hukum sungguhan, dan sistem ini menanganinya dengan serius."

Suara UI: Bahasa Indonesia, lugas, tanpa basa-basi. Tidak menyapa, tidak merayakan, tidak memakai tanda seru. Sebuah label berbunyi "3 klien lewat jatuh tempo", bukan "Ups! Ada 3 klien yang perlu perhatian Anda 😊".

## Anti-references

- **Gradient dekoratif dan warna tanpa makna.** Kondisi saat ini penuh gradient purple-pink, blue-purple, green-teal, amber-orange, pink-rose, ditambah badge `animate-pulse`. Tidak satu pun menyampaikan informasi. Warna di surface ini hanya boleh dipakai ketika ia berarti sesuatu.
- **Angka yang tidak bisa ditindaklanjuti.** Kartu statistik yang menyebutkan jumlah tanpa jalan menuju pekerjaan berikutnya. Setiap metrik harus punya tujuan klik.
- **Template dashboard generik.** Empat kartu KPI seragam di atas, chart di bawah, tanpa karakter. Bisa jadi dashboard apa pun di industri apa pun.
- **Kloning Stripe yang literal.** Referensi Stripe menunjuk pada perlakuan serius terhadap angka dan hierarki yang jelas, bukan pada palet ungu-birunya. Meniru tampilannya persis akan jatuh ke perangkap generik yang sama.
- **Refleks kategori "fintech".** Navy dan emas, biru korporat, atau neon di atas hitam. Semua adalah jawaban pertama yang muncul dari kategori pajak/keuangan, dan karena itu justru harus dihindari.

## Design Principles

1. **Urgensi mendahului agregat.** Total tanpa keterangan "siapa" dan "kapan" tidak menolong siapa pun. Yang genting muncul lebih dulu dan lebih besar daripada yang sekadar besar angkanya.
2. **Setiap angka punya tujuan.** Bila sebuah metrik tidak bisa diklik menuju pekerjaan nyata, ia hanya hiasan dan sebaiknya dihapus.
3. **Warna adalah informasi.** Satu-satunya alasan sah memakai warna di sini adalah membedakan status atau menandai risiko. Selebihnya netral.
4. **Kalender pajak adalah struktur, bukan pelengkap.** Kewajiban pelaporan di Indonesia terikat tanggal resmi yang berulang setiap bulan. Tenggat itu membentuk kerangka dashboard, bukan menempel sebagai widget di pojok.
5. **Kepercayaan lahir dari kejelasan.** Kesan profesional datang dari hierarki yang benar dan angka yang jujur, bukan dari bayangan, gradient, atau animasi.

## Accessibility & Inclusion

- **WCAG AA untuk kontras teks** (rasio minimal 4.5:1 untuk teks normal, 3:1 untuk teks besar). Dashboard dipakai di monitor kantor dengan cahaya terang dan kualitas layar yang beragam.
- Status pelaporan sebaiknya tetap terbaca tanpa mengandalkan persepsi warna, meski ini tidak diminta secara eksplisit. Prinsip 3 sudah mengarah ke sana: bila warna dipakai hemat, label dan bentuk mau tidak mau harus menanggung maknanya.
- Target klik memadai untuk pemakaian mouse di desktop, yang merupakan konteks utama surface ini.
