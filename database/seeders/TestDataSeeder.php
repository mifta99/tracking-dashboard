<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Puskesmas;
use App\Models\Role;
use App\Models\User;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create roles first if they don't exist
        if (Role::count() == 0) {
            Role::create(['id' => 1, 'name' => 'Puskesmas']);
            Role::create(['id' => 2, 'name' => 'Kemenkes']);
            Role::create(['id' => 3, 'name' => 'Endo']);
            Role::create(['id' => 4, 'name' => 'Logistik']);
        }

        // Create sample provinces
        $provinces = [
            ['id' => '31', 'name' => 'DKI Jakarta'],
            ['id' => '32', 'name' => 'Jawa Barat'],
            ['id' => '33', 'name' => 'Jawa Tengah'],
            ['id' => '34', 'name' => 'DI Yogyakarta'],
            ['id' => '35', 'name' => 'Jawa Timur']
        ];

        foreach ($provinces as $province) {
            Province::updateOrCreate(['id' => $province['id']], $province);
        }

        // Create sample regencies
        $regencies = [
            ['id' => '3171', 'province_id' => '31', 'name' => 'Jakarta Pusat'],
            ['id' => '3172', 'province_id' => '31', 'name' => 'Jakarta Utara'],
            ['id' => '3173', 'province_id' => '31', 'name' => 'Jakarta Barat'],
            ['id' => '3201', 'province_id' => '32', 'name' => 'Bogor'],
            ['id' => '3202', 'province_id' => '32', 'name' => 'Sukabumi'],
            ['id' => '3301', 'province_id' => '33', 'name' => 'Cilacap'],
            ['id' => '3302', 'province_id' => '33', 'name' => 'Banyumas'],
            ['id' => '3401', 'province_id' => '34', 'name' => 'Kulon Progo'],
            ['id' => '3402', 'province_id' => '34', 'name' => 'Bantul'],
            ['id' => '3501', 'province_id' => '35', 'name' => 'Pacitan'],
            ['id' => '3502', 'province_id' => '35', 'name' => 'Ponorogo']
        ];

        foreach ($regencies as $regency) {
            Regency::updateOrCreate(['id' => $regency['id']], $regency);
        }

        // Create sample districts
        $districts = [
            ['id' => '3171010', 'regency_id' => '3171', 'name' => 'Gambir'],
            ['id' => '3171020', 'regency_id' => '3171', 'name' => 'Sawah Besar'],
            ['id' => '3171030', 'regency_id' => '3171', 'name' => 'Kemayoran'],
            ['id' => '3172010', 'regency_id' => '3172', 'name' => 'Penjaringan'],
            ['id' => '3172020', 'regency_id' => '3172', 'name' => 'Pademangan'],
            ['id' => '3173010', 'regency_id' => '3173', 'name' => 'Cengkareng'],
            ['id' => '3173020', 'regency_id' => '3173', 'name' => 'Grogol Petamburan'],
            ['id' => '3201010', 'regency_id' => '3201', 'name' => 'Nanggung'],
            ['id' => '3201020', 'regency_id' => '3201', 'name' => 'Leuwiliang'],
            ['id' => '3202010', 'regency_id' => '3202', 'name' => 'Palabuhanratu']
        ];

        foreach ($districts as $district) {
            District::updateOrCreate(['id' => $district['id']], $district);
        }

        // Create sample puskesmas
        $puskesmas = [
            [
                'id' => '3171010p1',
                'district_id' => '3171010',
                'name' => 'Puskesmas Gambir',
                'pic' => 'Dr. Ahmad Sutanto',
                'kepala' => 'Dr. Siti Nurhaliza',
                'pic_dinkes_prov' => 'Dr. Budi Santoso',
                'pic_dinkes_kab' => 'Dr. Retno Wulandari'
            ],
            [
                'id' => '3171020p1',
                'district_id' => '3171020',
                'name' => 'Puskesmas Sawah Besar',
                'pic' => 'Dr. Rina Kusumawati',
                'kepala' => 'Dr. Imam Wahyudi',
                'pic_dinkes_prov' => 'Dr. Budi Santoso',
                'pic_dinkes_kab' => 'Dr. Retno Wulandari'
            ],
            [
                'id' => '3171030p1',
                'district_id' => '3171030',
                'name' => 'Puskesmas Kemayoran',
                'pic' => 'Dr. Fitri Handayani',
                'kepala' => 'Dr. Agus Setiawan',
                'pic_dinkes_prov' => 'Dr. Budi Santoso',
                'pic_dinkes_kab' => 'Dr. Retno Wulandari'
            ],
            [
                'id' => '3172010p1',
                'district_id' => '3172010',
                'name' => 'Puskesmas Penjaringan',
                'pic' => 'Dr. Maya Sari',
                'kepala' => 'Dr. Dedi Kurniawan',
                'pic_dinkes_prov' => 'Dr. Budi Santoso',
                'pic_dinkes_kab' => 'Dr. Retno Wulandari'
            ],
            [
                'id' => '3172020p1',
                'district_id' => '3172020',
                'name' => 'Puskesmas Pademangan',
                'pic' => 'Dr. Indra Permana',
                'kepala' => 'Dr. Lina Marlina',
                'pic_dinkes_prov' => 'Dr. Budi Santoso',
                'pic_dinkes_kab' => 'Dr. Retno Wulandari'
            ],
            [
                'id' => '3173010p1',
                'district_id' => '3173010',
                'name' => 'Puskesmas Cengkareng',
                'pic' => 'Dr. Hendra Wijaya',
                'kepala' => 'Dr. Sri Mulyani',
                'pic_dinkes_prov' => 'Dr. Budi Santoso',
                'pic_dinkes_kab' => 'Dr. Retno Wulandari'
            ],
            [
                'id' => '3173020p1',
                'district_id' => '3173020',
                'name' => 'Puskesmas Grogol Petamburan',
                'pic' => 'Dr. Tuti Melani',
                'kepala' => 'Dr. Wahyu Pratama',
                'pic_dinkes_prov' => 'Dr. Budi Santoso',
                'pic_dinkes_kab' => 'Dr. Retno Wulandari'
            ],
            [
                'id' => '3201010p1',
                'district_id' => '3201010',
                'name' => 'Puskesmas Nanggung',
                'pic' => 'Dr. Eka Susanti',
                'kepala' => 'Dr. Bambang Irawan',
                'pic_dinkes_prov' => 'Dr. Joko Susilo',
                'pic_dinkes_kab' => 'Dr. Ani Sutrisno'
            ],
            [
                'id' => '3201020p1',
                'district_id' => '3201020',
                'name' => 'Puskesmas Leuwiliang',
                'pic' => 'Dr. Ratna Sari',
                'kepala' => 'Dr. Andi Pratama',
                'pic_dinkes_prov' => 'Dr. Joko Susilo',
                'pic_dinkes_kab' => 'Dr. Ani Sutrisno'
            ],
            [
                'id' => '3202010p1',
                'district_id' => '3202010',
                'name' => 'Puskesmas Palabuhanratu',
                'pic' => 'Dr. Dewi Lestari',
                'kepala' => 'Dr. Rudi Hartono',
                'pic_dinkes_prov' => 'Dr. Joko Susilo',
                'pic_dinkes_kab' => 'Dr. Ani Sutrisno'
            ]
        ];

        foreach ($puskesmas as $data) {
            Puskesmas::updateOrCreate(['id' => $data['id']], $data);
        }

        // Create a test user if it doesn't exist
        if (User::where('email', 'admin@test.com')->count() == 0) {
            User::create([
                'role_id' => 2,
                'name' => 'Admin Test',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'jabatan' => 'Administrator',
                'instansi' => 'Kementerian Kesehatan'
            ]);
        }

        $this->command->info('Test data seeded successfully!');
        $this->command->info('Provinces: ' . Province::count());
        $this->command->info('Regencies: ' . Regency::count());
        $this->command->info('Districts: ' . District::count());
        $this->command->info('Puskesmas: ' . Puskesmas::count());
    }
}