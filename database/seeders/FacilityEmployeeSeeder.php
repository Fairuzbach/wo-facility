<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee; // Pastikan Model Employee sudah di-import

class FacilityEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            ['nik' => '2693', 'name' => 'AGUS DWI PRIYANTO'],
            ['nik' => '2958', 'name' => 'IRAWAN'],
            ['nik' => '3628', 'name' => 'MARIO CHANDRA WIJAYA'],
            ['nik' => '1222', 'name' => 'MULYONO'],
            ['nik' => '3664', 'name' => 'RUDI'],
            ['nik' => '174',  'name' => 'SARJANA'], // Pastikan tipe data NIK di DB support 3 digit, atau tambahkan '0174' jika perlu
            ['nik' => '1307', 'name' => 'SARTANA'],
            ['nik' => '2850', 'name' => 'SUHARYANTO'],
            ['nik' => '3629', 'name' => 'TEGAR ANDI PRATAMA'],
            ['nik' => '3614', 'name' => 'WAHYU AJI MARHABAN'], // NIK 3614 sesuai request
        ];

        foreach ($employees as $data) {
            Employee::updateOrCreate(
                ['nik' => $data['nik']], // Cek berdasarkan NIK
                [
                    'name' => $data['name'],
                    'department' => 'Facility', // Sesuaikan nama kolom divisi di database Anda (misal: 'department')
                    'position' => 'TEKNISI KONSTRUKSI', // Opsional: Tambahkan jabatan jika ada
                ]
            );
        }
    }
}
