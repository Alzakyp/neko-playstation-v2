<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Playstation;
use App\Models\Game;

class PlaystationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create PS4 consoles
        $this->createPS4Consoles();

        // Create PS5 consoles
        $this->createPS5Consoles();

        // Assign games to PlayStations
        $this->assignGames();
    }

    /**
     * Create PS4 consoles
     */
    private function createPS4Consoles(): void
    {
        $consoles = [
            [
                'ps_number' => 'PS4-001',
                'ps_type' => 'PS4',
                'status' => 'available',
                'hourly_rate' => 25000,
                'description' => 'Standard PS4 console with 500GB storage, includes 2 controllers.'
            ],
            [
                'ps_number' => 'PS4-002',
                'ps_type' => 'PS4',
                'status' => 'in_use',
                'hourly_rate' => 25000,
                'description' => 'Standard PS4 console with 500GB storage, includes 2 controllers.'
            ],
            [
                'ps_number' => 'PS4-003',
                'ps_type' => 'PS4 Pro',
                'status' => 'available',
                'hourly_rate' => 30000,
                'description' => 'PS4 Pro with 1TB storage and enhanced graphics. Includes 2 controllers and headset.'
            ],
            [
                'ps_number' => 'PS4-004',
                'ps_type' => 'PS4 Pro',
                'status' => 'maintenance',
                'hourly_rate' => 30000,
                'description' => 'PS4 Pro with 1TB storage and enhanced graphics. Includes 2 controllers and headset.'
            ],
            [
                'ps_number' => 'PS4-005',
                'ps_type' => 'PS4 Slim',
                'status' => 'available',
                'hourly_rate' => 25000,
                'description' => 'PS4 Slim with 1TB storage, more energy-efficient. Includes 2 controllers.'
            ],
        ];

        foreach ($consoles as $console) {
            Playstation::create($console);
        }
    }

    /**
     * Create PS5 consoles
     */
    private function createPS5Consoles(): void
    {
        $consoles = [
            [
                'ps_number' => 'PS5-001',
                'ps_type' => 'PS5',
                'status' => 'available',
                'hourly_rate' => 50000,
                'description' => 'Next-gen PS5 console with ultra-high-speed SSD and 3D audio. Includes DualSense controller.'
            ],
            [
                'ps_number' => 'PS5-002',
                'ps_type' => 'PS5',
                'status' => 'in_use',
                'hourly_rate' => 50000,
                'description' => 'Next-gen PS5 console with ultra-high-speed SSD and 3D audio. Includes DualSense controller.'
            ],
            [
                'ps_number' => 'PS5-003',
                'ps_type' => 'PS5 Digital',
                'status' => 'available',
                'hourly_rate' => 45000,
                'description' => 'Digital Edition PS5 without disc drive. Includes DualSense controller and 825GB SSD.'
            ],
            [
                'ps_number' => 'PS5-004',
                'ps_type' => 'PS5',
                'status' => 'maintenance',
                'hourly_rate' => 50000,
                'description' => 'Next-gen PS5 console with ultra-high-speed SSD and 3D audio. Includes DualSense controller.'
            ],
            [
                'ps_number' => 'PS5-005',
                'ps_type' => 'PS5',
                'status' => 'available',
                'hourly_rate' => 50000,
                'description' => 'Next-gen PS5 console with ultra-high-speed SSD and 3D audio. Includes DualSense controller and additional storage.'
            ],
        ];

        foreach ($consoles as $console) {
            Playstation::create($console);
        }
    }

    /**
     * Assign games to PlayStations
     */
    private function assignGames(): void
    {
        // Get all games
        $ps4Games = Game::where('ps_type', 'PS4')->pluck('id')->toArray();
        $ps5Games = Game::where('ps_type', 'PS5')->pluck('id')->toArray();
        $allGames = Game::pluck('id')->toArray();

        // If there are no games, skip this step
        if (empty($allGames)) {
            return;
        }

        // Assign games to PS4 consoles
        $ps4Consoles = Playstation::where('ps_type', 'like', 'PS4%')->get();
        foreach ($ps4Consoles as $console) {
            // Assign 5-10 random PS4 games to each PS4 console
            $gameCount = rand(5, min(10, count($ps4Games)));
            $selectedGames = array_rand(array_flip($ps4Games), $gameCount);
            $console->games()->attach($selectedGames);
        }

        // Assign games to PS5 consoles
        $ps5Consoles = Playstation::where('ps_type', 'like', 'PS5%')->get();
        foreach ($ps5Consoles as $console) {
            // PS5 consoles can play both PS4 and PS5 games
            // Assign 5-10 random games to each PS5 console
            $gameCount = rand(5, min(10, count($allGames)));
            $selectedGames = array_rand(array_flip($allGames), $gameCount);
            $console->games()->attach($selectedGames);
        }
    }
}
