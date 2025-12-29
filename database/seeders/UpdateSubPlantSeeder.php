<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateSubPlantSeeder extends Seeder
{
    public function run(): void
    {
        // Data Mapping Khusus Plant C (Sesuai request sebelumnya)
        $plantC_Data = [
            // Sub Plant C (Tubuler)
            'C' => [
                'MD-2C',
                'MD-3C',
                'TW-MD',
                'FD-9',
                'FD-10',
                'FD-11',
                'FD-12',
                'FD-20',
                'FD-21',
                'TIN-1',
                'TIN-2',
                'TIN-3',
                'ELECTRO PLATING',
                'BC-1C',
                'BC-2C',
                'BC-3C',
                'BC-4C',
                'BC-5C',
                'BC-7',
                'BC-8',
                'BC-9',
                'BC-10',
                'TV-5',
                'TV-6',
                'TV-7',
                'TV-8',
                'TV-9',
                'TV-10',
                'TV-11',
                'TV-12',
                'EX-65 1C',
                'EX-65 2C',
                'EX-80+50 A',
                'EX-80+50 B',
                'EX-65 3C',
                'EX-65 4C',
                'TW-630 A',
                'TW-630 B',
                'TW-800A',
                'TW-800B',
                'TW-125A',
                'TW-125B',
                'CB-16A',
                'CB-16C',
                'CB-20A',
                'EX-80J',
                'EX-90E',
                'EX-120F',
                'EX-150C',
                'EX-120A',
                'EX-120/100D',
                'ST-12A',
                'REW-TWB-16',
                'TWB-16A',
                'TWB-16B',
                'REW-TWB-24A',
                'REW-TWB-24B',
                'TWB-24A',
                'TWB-24B',
                'TWB-24C',
                'TWB-24D',
                'TWB-24E',
                'REW-SWB-24A',
                'REW-SWB-24B',
                'SWB-24A',
                'SWB-24B',
                'SWB-24C',
                'SWB-24D',
                'SWB-24E',
                'REW-SWB-32A',
                'REW-SWB-32B',
                'SWB-32A',
                'SWB-32B',
                'REW-SWB-48A',
                'SWB-48',
                'TP-10'
            ]
        ];

        // 1. Ambil ID Plant C
        $plant = DB::table('plants')->where('name', 'Plant C')->first();

        if ($plant) {
            $this->command->info('Memproses Update Sub Plant untuk: ' . $plant->name);

            foreach ($plantC_Data as $subPlantCode => $machines) {
                // Update sekaligus menggunakan WhereIn agar cepat
                $updated = DB::table('machines')
                    ->where('plant_id', $plant->id)
                    ->whereIn('name', $machines)
                    ->update(['sub_plant' => $subPlantCode]);

                $this->command->info("Updated Sub Plant '$subPlantCode': $updated mesin.");
            }
        } else {
            $this->command->error('Plant C tidak ditemukan di database!');
        }
    }
}
