<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Engineering\Plant;
use App\Models\Engineering\Machine;

class UpdateMachineSubPlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting to update machines sub_plant...');

        // Cari ID untuk plant PE dan MT
        $pePlant = Plant::where('name', 'LIKE', '%PE%')->first();
        $mtPlant = Plant::where('name', 'LIKE', '%MT%')->first();

        if (!$pePlant) {
            $this->command->warn('⚠️ Plant PE tidak ditemukan!');
            $this->command->info('💡 Mencoba mencari dengan nama alternatif...');
            // Coba cari dengan variasi nama
            $pePlant = Plant::whereIn('name', ['PE', 'Production Engineering', 'Prod. Engineering'])->first();
        }

        if (!$mtPlant) {
            $this->command->warn('⚠️ Plant MT tidak ditemukan!');
            $this->command->info('💡 Mencoba mencari dengan nama alternatif...');
            $mtPlant = Plant::whereIn('name', ['MT', 'Maintenance', 'Mechanical'])->first();
        }

        // Array sub plants
        $subPlants = ['A', 'B', 'C', 'D', 'E', 'F'];

        // ========================================
        // UPDATE PE MACHINES
        // ========================================
        if ($pePlant) {
            $this->command->info("✅ Plant PE ditemukan (ID: {$pePlant->id})");

            $peMachines = Machine::where('plant_id', $pePlant->id)->get();
            $totalPE = $peMachines->count();

            if ($totalPE === 0) {
                $this->command->warn('⚠️ Tidak ada mesin di plant PE');
            } else {
                $this->command->info("📊 Total mesin di PE: {$totalPE}");

                // Bagi mesin secara merata ke sub plant A-F
                $machinesPerSubPlant = ceil($totalPE / 6);
                $this->command->info("📦 Distribusi: ~{$machinesPerSubPlant} mesin per sub-plant");

                $index = 0;
                foreach ($peMachines as $machine) {
                    $subPlantIndex = floor($index / $machinesPerSubPlant);
                    $subPlant = $subPlants[$subPlantIndex] ?? 'F'; // Default ke F jika overflow

                    $machine->sub_plant = $subPlant;
                    $machine->save();

                    $this->command->line("  ➜ {$machine->name} → Plant {$subPlant}");
                    $index++;
                }

                $this->command->info("✅ PE: {$totalPE} mesin berhasil diupdate!");
            }
        } else {
            $this->command->error('❌ Plant PE tidak ditemukan di database!');
            $this->command->info('💡 Jalankan: php artisan tinker');
            $this->command->info('   Lalu: Plant::all()->pluck("name", "id")');
        }

        $this->command->newLine();

        // ========================================
        // UPDATE MT MACHINES
        // ========================================
        if ($mtPlant) {
            $this->command->info("✅ Plant MT ditemukan (ID: {$mtPlant->id})");

            $mtMachines = Machine::where('plant_id', $mtPlant->id)->get();
            $totalMT = $mtMachines->count();

            if ($totalMT === 0) {
                $this->command->warn('⚠️ Tidak ada mesin di plant MT');
            } else {
                $this->command->info("📊 Total mesin di MT: {$totalMT}");

                // Bagi mesin secara merata ke sub plant A-F
                $machinesPerSubPlant = ceil($totalMT / 6);
                $this->command->info("📦 Distribusi: ~{$machinesPerSubPlant} mesin per sub-plant");

                $index = 0;
                foreach ($mtMachines as $machine) {
                    $subPlantIndex = floor($index / $machinesPerSubPlant);
                    $subPlant = $subPlants[$subPlantIndex] ?? 'F';

                    $machine->sub_plant = $subPlant;
                    $machine->save();

                    $this->command->line("  ➜ {$machine->name} → Plant {$subPlant}");
                    $index++;
                }

                $this->command->info("✅ MT: {$totalMT} mesin berhasil diupdate!");
            }
        } else {
            $this->command->error('❌ Plant MT tidak ditemukan di database!');
            $this->command->info('💡 Jalankan: php artisan tinker');
            $this->command->info('   Lalu: Plant::all()->pluck("name", "id")');
        }

        $this->command->newLine();
        $this->command->info('🎉 Seeder selesai dijalankan!');

        // Summary
        $this->showSummary();
    }

    /**
     * Show summary of sub_plant distribution
     */
    private function showSummary(): void
    {
        $this->command->info('📊 SUMMARY - Distribusi Sub Plant:');
        $this->command->newLine();

        $pePlant = Plant::where('name', 'LIKE', '%PE%')->first();
        $mtPlant = Plant::where('name', 'LIKE', '%MT%')->first();

        if ($pePlant) {
            $this->command->info("🏭 Plant PE (ID: {$pePlant->id}):");
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $subPlant) {
                $count = Machine::where('plant_id', $pePlant->id)
                    ->where('sub_plant', $subPlant)
                    ->count();
                $this->command->line("   Plant {$subPlant}: {$count} mesin");
            }
            $this->command->newLine();
        }

        if ($mtPlant) {
            $this->command->info("🏭 Plant MT (ID: {$mtPlant->id}):");
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $subPlant) {
                $count = Machine::where('plant_id', $mtPlant->id)
                    ->where('sub_plant', $subPlant)
                    ->count();
                $this->command->line("   Plant {$subPlant}: {$count} mesin");
            }
        }
    }
}
