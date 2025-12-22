<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Engineering\Plant;

class PlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $locations =  [
            'Plant A',
            'Plant B',
            'Plant C',
            'Plant D',
            'Plant E',
            'Plant F',
            'SS',
            'SC',
            'Planning'
        ];

        foreach ($locations as $loc) {
            Plant::firstOrCreate(
                [
                    'name' => $loc
                ],
                ['name' => $loc]
            );
        }
    }
}
