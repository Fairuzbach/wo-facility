<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $departments = ['ENGINEERING', 'PRODUCTION', 'MAINTENANCE', 'QUALITY CONTROL', 'HRD', 'IT'];
        $positions = ['STAFF', 'OPERATOR', 'SPV', 'FOREMAN'];

        // Agar NIK tidak bentrok saat proses looping
        $usedNiks = [];

        $this->command->info('Sedang membuat 100 data karyawan...');

        for ($i = 1; $i <= 100; $i++) {
            do {
                // Membuat NIK acak 3 sampai 4 angka (100 - 9999)
                $nik = rand(100, 9999);
            } while (in_array($nik, $usedNiks));

            $usedNiks[] = $nik;

            Employee::create([
                'nik'        => (string)$nik,
                'name'       => 'Employee ' . $nik,
                'department' => $departments[array_rand($departments)],
                'position'   => $positions[array_rand($positions)]
            ]);
        }

        $this->command->info('✅ Berhasil menambahkan 100 data karyawan.');
    }
}
