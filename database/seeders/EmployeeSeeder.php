<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // 1. Staff Biasa (Yang Lapor)
        Employee::create([
            'nik' => '12345',
            'name' => 'Budi Staff Engineer',
            'department' => 'ENGINEERING',
            'position' => 'STAFF'
        ]);

        // 2. SPV (Yang Approve)
        Employee::create([
            'nik' => '99999',
            'name' => 'Pak Bos Engineer',
            'department' => 'ENGINEERING',
            'position' => 'SPV'
        ]);
    }
}
