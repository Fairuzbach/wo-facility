<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class GaEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Departemen dikunci khusus GENERAL AFFAIR
        $department = 'GENERAL AFFAIR';

        // Jabatan-jabatan yang umum di General Affair
        $positions = [
            'General Affair',
            'GENERAL AFFAIR',
            'General Affairs',
            'GENERAL AFFAIRS',
            'ga',
            'GA'
        ];

        // Ambil NIK yang sudah ada di database agar tidak error Duplicate Entry
        $usedNiks = Employee::pluck('nik')->toArray();

        $jumlahData = 50; // Kita buat 50 karyawan GA
        $this->command->info("Sedang membuat $jumlahData data karyawan General Affair...");

        for ($i = 1; $i <= $jumlahData; $i++) {
            do {
                // Membuat NIK acak 3 sampai 4 angka (100 - 9999)
                $nik = rand(100, 9999);
            } while (in_array((string)$nik, $usedNiks));

            // Simpan NIK ke array pengecekan
            $usedNiks[] = (string)$nik;

            Employee::create([
                'nik'        => (string)$nik,
                'name'       => 'GA Team ' . $nik, // Nama diset unik
                'department' => $department,       // Pasti GENERAL AFFAIR
                'position'   => $positions[array_rand($positions)]
            ]);
        }

        $this->command->info("✅ Berhasil menambahkan $jumlahData data karyawan General Affair.");
    }
}
