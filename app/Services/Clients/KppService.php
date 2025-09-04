<?php

namespace App\Services\Clients;

class KppService
{
    /**
     * Get all KPP options organized by region
     * 
     * @return array
     */
    public static function getAllKpp(): array
    {
        $kppByRegion = [
            // ACEH
            'ACEH' => [
                'KPP Pratama Banda Aceh',
                'KPP Pratama Lhokseumawe', 
                'KPP Pratama Meulaboh',
                'KPP Pratama Aceh Besar',
                'KPP Pratama Bireuen',
                'KPP Pratama Langsa',
                'KPP Pratama Subulussalam',
                'KPP Pratama Tapaktuan',
            ],

            // SUMATERA UTARA
            'SUMATERA UTARA' => [
                'KPP Madya Medan',
                'KPP Pratama Medan Barat',
                'KPP Pratama Medan Polonia', 
                'KPP Pratama Medan Belawan',
                'KPP Pratama Medan Timur',
                'KPP Pratama Medan Kota',
                'KPP Pratama Binjai',
                'KPP Pratama Tebing Tinggi',
                'KPP Pratama Kisaran',
                'KPP Pratama Rantau Prapat',
                'KPP Pratama Pematang Siantar',
                'KPP Pratama Padang Sidempuan',
                'KPP Pratama Lubuk Pakam',
            ],

            // SUMATERA BARAT & RIAU
            'SUMATERA BARAT' => [
                'KPP Pratama Padang Satu',
                'KPP Pratama Bukittinggi',
                'KPP Pratama Solok',
                'KPP Pratama Payakumbuh',
                'KPP Pratama Sijunjung',
            ],

            'RIAU & KEP. RIAU' => [
                'KPP Madya Pekanbaru',
                'KPP Pratama Pekanbaru Senapelan',
                'KPP Pratama Pekanbaru Tampan',
                'KPP Pratama Dumai',
                'KPP Pratama Rengat',
                'KPP Pratama Bengkalis',
                'KPP Pratama Bangkinang',
                'KPP Pratama Tanjung Pinang',
                'KPP Pratama Batam',
                'KPP Pratama Karimun',
            ],

            // JAMBI
            'JAMBI' => [
                'KPP Pratama Jambi',
                'KPP Pratama Muara Bungo',
                'KPP Pratama Bangko',
                'KPP Pratama Kuala Tungkal',
            ],

            // SUMATERA SELATAN & BABEL
            'SUMATERA SELATAN' => [
                'KPP Madya Palembang',
                'KPP Pratama Palembang Ilir Timur',
                'KPP Pratama Palembang Ilir Barat',
                'KPP Pratama Palembang Seberang Ulu',
                'KPP Pratama Lubuk Linggau',
                'KPP Pratama Baturaja',
                'KPP Pratama Kayu Agung',
                'KPP Pratama Prabumulih',
            ],

            'BANGKA BELITUNG' => [
                'KPP Pratama Pangkal Pinang',
                'KPP Pratama Tanjung Pandan',
            ],

            // BENGKULU & LAMPUNG
            'BENGKULU' => [
                'KPP Pratama Bengkulu',
                'KPP Pratama Curup',
            ],

            'LAMPUNG' => [
                'KPP Madya Bandar Lampung',
                'KPP Pratama Bandar Lampung',
                'KPP Pratama Metro',
                'KPP Pratama Tanjung Karang',
                'KPP Pratama Kotabumi',
            ],

            // DKI JAKARTA
            'DKI JAKARTA PUSAT' => [
                'KPP WP Besar Satu',
                'KPP WP Besar Dua', 
                'KPP WP Besar Tiga',
                'KPP WP Besar Empat',
                'KPP Madya Jakarta Pusat',
                'KPP Pratama Jakarta Gambir Satu',
                'KPP Pratama Jakarta Gambir Dua',
                'KPP Pratama Jakarta Gambir Tiga',
                'KPP Pratama Jakarta Sawah Besar',
                'KPP Pratama Jakarta Kemayoran',
                'KPP Pratama Jakarta Cempaka Putih',
                'KPP Pratama Jakarta Menteng Satu',
                'KPP Pratama Jakarta Menteng Dua',
                'KPP Pratama Jakarta Senen',
                'KPP Pratama Jakarta Tanah Abang Satu',
                'KPP Pratama Jakarta Tanah Abang Dua',
            ],

            'DKI JAKARTA UTARA' => [
                'KPP Madya Jakarta Utara',
                'KPP Pratama Jakarta Penjaringan',
                'KPP Pratama Jakarta Pademangan',
                'KPP Pratama Jakarta Tanjung Priok',
                'KPP Pratama Jakarta Koja',
                'KPP Pratama Jakarta Kelapa Gading',
            ],

            'DKI JAKARTA BARAT' => [
                'KPP Madya Jakarta Barat',
                'KPP Pratama Jakarta Palmerah',
                'KPP Pratama Jakarta Grogol Petamburan',
                'KPP Pratama Jakarta Tamansari Satu',
                'KPP Pratama Jakarta Tamansari Dua',
                'KPP Pratama Jakarta Tambora',
                'KPP Pratama Jakarta Cengkareng',
                'KPP Pratama Jakarta Kebon Jeruk',
            ],

            'DKI JAKARTA SELATAN' => [
                'KPP Madya Jakarta Selatan Satu',
                'KPP Madya Jakarta Selatan Dua',
                'KPP Pratama Jakarta Setiabudi Satu',
                'KPP Pratama Jakarta Setiabudi Dua',
                'KPP Pratama Jakarta Tebet',
                'KPP Pratama Jakarta Kebayoran Baru Satu',
                'KPP Pratama Jakarta Kebayoran Baru Dua',
                'KPP Pratama Jakarta Kebayoran Lama',
                'KPP Pratama Jakarta Mampang Prapatan',
                'KPP Pratama Jakarta Pancoran',
                'KPP Pratama Jakarta Cilandak',
                'KPP Pratama Jakarta Pasar Minggu',
                'KPP Pratama Jakarta Kebayoran Baru Tiga',
                'KPP Pratama Jakarta Pesanggrahan',
            ],

            'DKI JAKARTA TIMUR' => [
                'KPP Madya Jakarta Timur',
                'KPP Pratama Jakarta Matraman',
                'KPP Pratama Jakarta Jatinegara',
                'KPP Pratama Jakarta Pulogadung',
                'KPP Pratama Jakarta Cakung Satu',
                'KPP Pratama Jakarta Cakung Dua',
                'KPP Pratama Jakarta Kramat Jati',
                'KPP Pratama Jakarta Duren Sawit',
                'KPP Pratama Jakarta Cipayung',
            ],

            // BANTEN
            'BANTEN' => [
                'KPP Madya Tangerang',
                'KPP Pratama Serang',
                'KPP Pratama Serang Barat',
                'KPP Pratama Tangerang',
                'KPP Pratama Tangerang Barat',
                'KPP Pratama Serpong',
                'KPP Pratama Tigaraksa',
                'KPP Pratama Pandeglang',
                'KPP Pratama Lebak',
            ],

            // JAWA BARAT
            'JAWA BARAT' => [
                'KPP Madya Bandung',
                'KPP Madya Bogor',
                'KPP Madya Bekasi',
                'KPP Pratama Bandung Bojonagara',
                'KPP Pratama Bandung Cibeunying',
                'KPP Pratama Bandung Cicadas',
                'KPP Pratama Bandung Karees',
                'KPP Pratama Bandung Tegallega',
                'KPP Pratama Cimahi',
                'KPP Pratama Cianjur',
                'KPP Pratama Purwakarta',
                'KPP Pratama Tasikmalaya',
                'KPP Pratama Cirebon',
                'KPP Pratama Bogor',
                'KPP Pratama Cibinong',
                'KPP Pratama Sukabumi',
                'KPP Pratama Bekasi',
                'KPP Pratama Cikarang',
                'KPP Pratama Karawang',
                'KPP Pratama Depok',
                'KPP Pratama Subang',
                'KPP Pratama Indramayu',
                'KPP Pratama Garut',
                'KPP Pratama Ciamis',
                'KPP Pratama Kuningan',
            ],

            // JAWA TENGAH & DIY
            'JAWA TENGAH' => [
                'KPP Madya Semarang',
                'KPP Pratama Semarang Barat',
                'KPP Pratama Semarang Selatan',
                'KPP Pratama Semarang Timur',
                'KPP Pratama Semarang Tengah',
                'KPP Pratama Semarang Candisari',
                'KPP Pratama Tegal',
                'KPP Pratama Pekalongan',
                'KPP Pratama Salatiga',
                'KPP Pratama Kudus',
                'KPP Pratama Pati',
                'KPP Pratama Purwokerto',
                'KPP Pratama Cilacap',
                'KPP Pratama Kebumen',
                'KPP Pratama Magelang',
                'KPP Pratama Klaten',
                'KPP Pratama Surakarta',
                'KPP Pratama Karanganyar',
                'KPP Pratama Boyolali',
                'KPP Pratama Wonogiri',
                'KPP Pratama Sragen',
                'KPP Pratama Purbalingga',
                'KPP Pratama Banjarnegara',
                'KPP Pratama Wonosobo',
                'KPP Pratama Temanggung',
                'KPP Pratama Kendal',
                'KPP Pratama Demak',
                'KPP Pratama Jepara',
                'KPP Pratama Rembang',
                'KPP Pratama Blora',
                'KPP Pratama Grobogan',
            ],

            'D.I. YOGYAKARTA' => [
                'KPP Madya Yogyakarta',
                'KPP Pratama Yogyakarta',
                'KPP Pratama Wates',
                'KPP Pratama Wonosari',
            ],

            // JAWA TIMUR
            'JAWA TIMUR' => [
                'KPP Madya Surabaya',
                'KPP Madya Sidoarjo',
                'KPP Madya Malang',
                'KPP Madya Gresik',
                'KPP Pratama Surabaya Sukomanunggal',
                'KPP Pratama Surabaya Krembangan',
                'KPP Pratama Surabaya Pabean Cantikan',
                'KPP Pratama Surabaya Gubeng',
                'KPP Pratama Surabaya Tegalsari',
                'KPP Pratama Surabaya Sawahan',
                'KPP Pratama Surabaya Wonocolo',
                'KPP Pratama Surabaya Rungkut',
                'KPP Pratama Surabaya Genteng',
                'KPP Pratama Surabaya Simokerto',
                'KPP Pratama Surabaya Mulyorejo',
                'KPP Pratama Gresik',
                'KPP Pratama Sidoarjo Barat',
                'KPP Pratama Sidoarjo Timur',
                'KPP Pratama Mojokerto',
                'KPP Pratama Kediri',
                'KPP Pratama Tulungagung',
                'KPP Pratama Malang',
                'KPP Pratama Batu',
                'KPP Pratama Pasuruan',
                'KPP Pratama Probolinggo',
                'KPP Pratama Jember',
                'KPP Pratama Banyuwangi',
                'KPP Pratama Madiun',
                'KPP Pratama Bojonegoro',
                'KPP Pratama Pamekasan',
                'KPP Pratama Jombang',
                'KPP Pratama Ngawi',
                'KPP Pratama Magetan',
                'KPP Pratama Ponorogo',
                'KPP Pratama Pacitan',
                'KPP Pratama Trenggalek',
                'KPP Pratama Blitar',
                'KPP Pratama Lumajang',
                'KPP Pratama Bondowoso',
                'KPP Pratama Situbondo',
                'KPP Pratama Lamongan',
                'KPP Pratama Tuban',
                'KPP Pratama Sumenep',
                'KPP Pratama Bangkalan',
                'KPP Pratama Sampang',
            ],

            // KALIMANTAN
            'KALIMANTAN BARAT' => [
                'KPP Madya Pontianak',
                'KPP Pratama Pontianak',
                'KPP Pratama Singkawang',
                'KPP Pratama Ketapang',
                'KPP Pratama Sintang',
            ],

            'KALIMANTAN TENGAH' => [
                'KPP Pratama Palangkaraya',
                'KPP Pratama Sampit',
                'KPP Pratama Muara Teweh',
            ],

            'KALIMANTAN SELATAN' => [
                'KPP Madya Banjarmasin',
                'KPP Pratama Banjarmasin',
                'KPP Pratama Banjarbaru',
                'KPP Pratama Kandangan',
            ],

            'KALIMANTAN TIMUR' => [
                'KPP Madya Balikpapan',
                'KPP Madya Samarinda',
                'KPP Pratama Balikpapan',
                'KPP Pratama Samarinda Ulu',
                'KPP Pratama Samarinda Ilir',
                'KPP Pratama Bontang',
                'KPP Pratama Tarakan',
                'KPP Pratama Sangatta',
                'KPP Pratama Penajam',
            ],

            'KALIMANTAN UTARA' => [
                'KPP Pratama Tanjung Selor',
            ],

            // SULAWESI
            'SULAWESI SELATAN' => [
                'KPP Madya Makassar',
                'KPP Pratama Makassar Utara',
                'KPP Pratama Makassar Selatan',
                'KPP Pratama Pare-pare',
                'KPP Pratama Palopo',
                'KPP Pratama Watampone',
                'KPP Pratama Bulukumba',
                'KPP Pratama Sengkang',
            ],

            'SULAWESI TENGAH' => [
                'KPP Pratama Palu',
                'KPP Pratama Luwuk',
                'KPP Pratama Toli-toli',
            ],

            'SULAWESI UTARA' => [
                'KPP Madya Manado',
                'KPP Pratama Manado',
                'KPP Pratama Bitung',
                'KPP Pratama Tomohon',
            ],

            'SULAWESI TENGGARA' => [
                'KPP Pratama Kendari',
                'KPP Pratama Baubau',
                'KPP Pratama Kolaka',
            ],

            'GORONTALO' => [
                'KPP Pratama Gorontalo',
            ],

            'SULAWESI BARAT' => [
                'KPP Pratama Mamuju',
            ],

            // BALI & NUSA TENGGARA
            'BALI' => [
                'KPP Madya Denpasar',
                'KPP Pratama Denpasar Barat',
                'KPP Pratama Denpasar Timur',
                'KPP Pratama Singaraja',
                'KPP Pratama Tabanan',
                'KPP Pratama Gianyar',
                'KPP Pratama Badung Utara',
                'KPP Pratama Badung Selatan',
            ],

            'NUSA TENGGARA BARAT' => [
                'KPP Pratama Mataram',
                'KPP Pratama Praya',
                'KPP Pratama Sumbawa Besar',
                'KPP Pratama Raba Bima',
                'KPP Pratama Dompu',
            ],

            'NUSA TENGGARA TIMUR' => [
                'KPP Pratama Kupang',
                'KPP Pratama Maumere',
                'KPP Pratama Ende',
                'KPP Pratama Ruteng',
                'KPP Pratama Waingapu',
            ],

            // MALUKU & PAPUA
            'MALUKU' => [
                'KPP Pratama Ambon',
                'KPP Pratama Tual',
                'KPP Pratama Namlea',
            ],

            'MALUKU UTARA' => [
                'KPP Pratama Ternate',
                'KPP Pratama Tobelo',
            ],

            'PAPUA BARAT' => [
                'KPP Pratama Sorong',
                'KPP Pratama Manokwari',
                'KPP Pratama Fak-fak',
            ],

            'PAPUA' => [
                'KPP Madya Jayapura',
                'KPP Pratama Jayapura',
                'KPP Pratama Merauke',
                'KPP Pratama Timika',
                'KPP Pratama Biak',
            ],

            'PAPUA SELATAN' => [
                'KPP Pratama Tanah Merah',
            ],

            'PAPUA TENGAH' => [
                'KPP Pratama Nabire',
            ],

            'PAPUA PEGUNUNGAN' => [
                'KPP Pratama Wamena',
            ],

            'PAPUA BARAT DAYA' => [
                'KPP Pratama Fakfak',
            ],
        ];

        // Menggabungkan semua KPP menjadi satu array
        $allKpp = [];
        foreach ($kppByRegion as $region => $kppList) {
            $allKpp = array_merge($allKpp, $kppList);
        }

        // Mengurutkan secara alfabetis
        sort($allKpp);

        return $allKpp;
    }

    /**
     * Get flat array of all KPP options for select dropdown
     * 
     * @return array
     */
    public static function getKppOptions(): array
    {
        $grouped = self::getAllKppByRegion();
        $options = [];
        
        foreach ($grouped as $region => $kppList) {
            foreach ($kppList as $kpp) {
                $options[$kpp] = $kpp;
            }
        }
        
        // Sort alphabetically
        ksort($options);
        
        return $options;
    }

    /**
     * Get KPP options with region information for better context
     * 
     * @return array
     */
    public static function getKppOptionsWithRegion(): array
    {
        $grouped = self::getAllKppByRegion();
        $options = [];
        
        foreach ($grouped as $region => $kppList) {
            foreach ($kppList as $kpp) {
                $options[$kpp] = "{$kpp} - {$region}";
            }
        }
        
        // Sort alphabetically by KPP name
        ksort($options);
        
        return $options;
    }

    /**
     * Get KPP options grouped by region for optgroup select
     * 
     * @return array
     */
    public static function getKppOptionsGrouped(): array
    {
        $grouped = self::getAllKppByRegion();
        $options = [];
        
        foreach ($grouped as $region => $kppList) {
            $regionOptions = [];
            foreach ($kppList as $kpp) {
                $regionOptions[$kpp] = $kpp;
            }
            $options[$region] = $regionOptions;
        }
        
        return $options;
    }

    /**
     * Search KPP by keyword
     * 
     * @param string $keyword
     * @return array
     */
    public static function searchKpp(string $keyword): array
    {
        $allKpp = self::getKppOptions();
        $keyword = strtolower($keyword);
        
        return array_filter($allKpp, function($kpp) use ($keyword) {
            return strpos(strtolower($kpp), $keyword) !== false;
        });
    }

    /**
     * Get KPP by region
     * 
     * @param string $region
     * @return array
     */
    public static function getKppByRegion(string $region): array
    {
        $allKpp = self::getAllKppByRegion();
        
        return $allKpp[$region] ?? [];
    }

    /**
     * Check if KPP exists
     * 
     * @param string $kpp
     * @return bool
     */
    public static function kppExists(string $kpp): bool
    {
        $allKpp = self::getKppOptions();
        
        return array_key_exists($kpp, $allKpp);
    }

    /**
     * Get region of a specific KPP
     * 
     * @param string $kpp
     * @return string|null
     */
    public static function getKppRegion(string $kpp): ?string
    {
        $grouped = self::getAllKppByRegion();
        
        foreach ($grouped as $region => $kppList) {
            if (in_array($kpp, $kppList)) {
                return $region;
            }
        }
        
        return null;
    }

    /**
     * Get total count of all KPP
     * 
     * @return int
     */
    public static function getTotalKppCount(): int
    {
        return count(self::getKppOptions());
    }

    /**
     * Get count of KPP by type (Pratama, Madya, WP Besar)
     * 
     * @return array
     */
    public static function getKppCountByType(): array
    {
        $allKpp = self::getKppOptions();
        $counts = [
            'Pratama' => 0,
            'Madya' => 0,
            'WP Besar' => 0,
            'Khusus' => 0,
        ];
        
        foreach ($allKpp as $kpp) {
            if (strpos($kpp, 'Pratama') !== false) {
                $counts['Pratama']++;
            } elseif (strpos($kpp, 'Madya') !== false) {
                $counts['Madya']++;
            } elseif (strpos($kpp, 'WP Besar') !== false) {
                $counts['WP Besar']++;
            } else {
                $counts['Khusus']++;
            }
        }
        
        return $counts;
    }
}