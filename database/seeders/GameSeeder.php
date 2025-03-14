<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class GameSeeder extends Seeder
{
    // Dimensi standar untuk gambar game (sama dengan di GameController)
    protected $imageWidth = 300;
    protected $imageHeight = 400;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan direktori storage terlebih dahulu
        if (!Storage::exists('public/games')) {
            Storage::makeDirectory('public/games');
        }

        // Create PS4 games
        $this->command->info('Seeding PS4 games...');
        $this->createPS4Games();

        // Create PS5 games
        $this->command->info('Seeding PS5 games...');
        $this->createPS5Games();
    }

    /**
     * Create PS4 games
     */
    private function createPS4Games(): void
    {
        $games = [
            [
                'title' => 'God of War',
                'description' => 'His vengeance against the Gods of Olympus years behind him, Kratos now lives as a man in the realm of Norse Gods and monsters.',
                'genre' => 'Action-Adventure',
                'ps_type' => 'PS4',
                'image_url' => 'https://cdn1.epicgames.com/offer/3ddd6a590da64e3686042d108968a6b2/EGS_GodofWar_SantaMonicaStudio_S2_1200x1600-fbdf3cbc2980749091d52751ffabb7b7_1200x1600-fbdf3cbc2980749091d52751ffabb7b7',
            ],
            [
                'title' => 'The Last of Us Part II',
                'description' => 'Five years after their dangerous journey across the post-pandemic United States, Ellie and Joel have settled down in Jackson, Wyoming.',
                'genre' => 'Action-Adventure, Survival Horror',
                'ps_type' => 'PS4',
                'image_url' => 'https://assetsio.gnwcdn.com/ar4s6.jpg?width=1200&height=600&fit=crop&enable=upscale&auto=webp',
            ],
            [
                'title' => 'Red Dead Redemption 2',
                'description' => 'America, 1899. The end of the Wild West era has begun. After a robbery goes badly wrong, Arthur Morgan and the Van der Linde gang are forced to flee.',
                'genre' => 'Action-Adventure, Western',
                'ps_type' => 'PS4',
                'image_url' => 'https://cdn1.epicgames.com/b30b6d1b4dfd4dcc93b5490be5e094e5/offer/RDR2476298253_Epic_Games_Wishlist_RDR2_2560x1440_V01-2560x1440-2a9ebe1f7ee202102555be202d5632ec.jpg',
            ],
            [
                'title' => 'Ghost of Tsushima',
                'description' => 'In the late 13th century, the Mongol empire has laid waste to entire nations. Tsushima Island is all that stands between mainland Japan and a massive Mongol invasion.',
                'genre' => 'Action-Adventure, Stealth',
                'ps_type' => 'PS4',
                'image_url' => 'https://upload.wikimedia.org/wikipedia/en/thumb/b/b6/Ghost_of_Tsushima.jpg/220px-Ghost_of_Tsushima.jpg',
            ],
            [
                'title' => 'Horizon Zero Dawn',
                'description' => 'In a lush, post-apocalyptic world where nature has reclaimed the ruins of a forgotten civilization, pockets of humanity live on in primitive hunter-gatherer tribes.',
                'genre' => 'Action RPG',
                'ps_type' => 'PS4',
                'image_url' => 'https://upload.wikimedia.org/wikipedia/id/9/93/Horizon_Zero_Dawn.jpg',
            ],
            [
                'title' => 'Uncharted 4: A Thief\'s End',
                'description' => 'Several years after his last adventure, retired fortune hunter Nathan Drake is forced back into the world of thieves.',
                'genre' => 'Action-Adventure',
                'ps_type' => 'PS4',
                'image_url' => 'https://imgcdn.espos.id/@espos/images/2016/03/Uncharted-4-A-Thiefs-End-Forbes.jpg?quality=60',
            ],
            [
                'title' => 'FIFA 22',
                'description' => 'Powered by Football™, EA SPORTS™ FIFA 22 brings the game even closer to the real thing with fundamental gameplay advances and a new season of innovation across every mode.',
                'genre' => 'Sports',
                'ps_type' => 'PS4',
                'image_url' => 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1506830/capsule_616x353.jpg?t=1712678728',
            ],
            [
                'title' => 'Call of Duty: Modern Warfare',
                'description' => 'The stakes have never been higher as players take on the role of lethal Tier One operators in a heart-racing saga that will affect the global balance of power.',
                'genre' => 'First-person Shooter',
                'ps_type' => 'PS4',
                'image_url' => 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/2000950/capsule_616x353.jpg?t=1678294805',
            ],
            [
                'title' => 'Grand Theft Auto V',
                'description' => 'When a young street hustler, a retired bank robber, and a terrifying psychopath find themselves entangled with some of the most frightening and deranged elements of the criminal underworld.',
                'genre' => 'Action-Adventure',
                'ps_type' => 'PS4',
                'image_url' => 'https://www.godisageek.com/wp-content/uploads/GTA-V-Background1.jpg',
            ],
            [
                'title' => 'Bloodborne',
                'description' => 'Face your fears as you search for answers in the ancient city of Yharnam, now cursed with a strange endemic illness spreading through the streets like wildfire.',
                'genre' => 'Action RPG',
                'ps_type' => 'PS4',
                'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQk62h2PTD0r1rB9MFnK_eWPArp8FriPJp9cw&s',
            ],
        ];

        foreach ($games as $gameData) {
            // Download dan resize gambar jika URL valid
            if (filter_var($gameData['image_url'], FILTER_VALIDATE_URL)) {
                $localFileName = $this->downloadAndResizeImage($gameData['image_url'], 'ps4_' . Str::slug($gameData['title']));
                if ($localFileName) {
                    $gameData['image_url'] = $localFileName;
                }
            }

            Game::create($gameData);
            $this->command->info("Created game: {$gameData['title']}");
        }
    }

    /**
     * Create PS5 games
     */
    private function createPS5Games(): void
    {
        $games = [
            [
                'title' => 'Demon\'s Souls',
                'description' => 'From PlayStation Studios and Bluepoint Games comes a remake of the PlayStation classic, Demon\'s Souls. Entirely rebuilt from the ground up and masterfully enhanced.',
                'genre' => 'Action RPG',
                'ps_type' => 'PS5',
                'image_url' => 'https://image.api.playstation.com/vulcan/img/rnd/202011/1717/GemRaOZaCMhGxQ9dRhnQQyT5.png',
            ],
            [
                'title' => 'Ratchet & Clank: Rift Apart',
                'description' => 'Go dimension-hopping with Ratchet and Clank as they take on an evil emperor from another reality.',
                'genre' => 'Action-Adventure, Platformer',
                'ps_type' => 'PS5',
                'image_url' => 'https://image.api.playstation.com/vulcan/ap/rnd/202101/2921/CrGbGyUFNdkZKbg9DM2qPTE1.jpg',
            ],
            [
                'title' => 'Spider-Man: Miles Morales',
                'description' => 'In the latest adventure in the Marvel\'s Spider-Man universe, teenager Miles Morales is adjusting to his new home while following in the footsteps of his mentor, Peter Parker, as a new Spider-Man.',
                'genre' => 'Action-Adventure',
                'ps_type' => 'PS5',
                'image_url' => 'https://image.api.playstation.com/vulcan/ap/rnd/202008/1020/PRfYtTZQsuj3ALrBXGL8MjAH.jpg',
            ],
            [
                'title' => 'Returnal',
                'description' => 'Break the cycle of chaos on an always changing alien planet. After crash-landing on this shape-shifting world, Selene must search through the barren landscape of an ancient civilization for her escape.',
                'genre' => 'Third-person Shooter, Roguelike',
                'ps_type' => 'PS5',
                'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT9QrPV9pQILDww9W5hK3aBGe8FtnP2XRGwfA&s',
            ],
            [
                'title' => 'Horizon Forbidden West',
                'description' => 'Join Aloy as she braves the Forbidden West – a majestic but dangerous frontier that conceals mysterious new threats.',
                'genre' => 'Action RPG',
                'ps_type' => 'PS5',
                'image_url' => 'https://image.api.playstation.com/vulcan/ap/rnd/202107/3100/yIa8STLMmCyhj48fGDpaAuRM.jpg',
            ],
            [
                'title' => 'Gran Turismo 7',
                'description' => 'The best-selling PlayStation racing franchise returns. Gran Turismo 7 brings together the very best features of the Real Driving Simulator.',
                'genre' => 'Racing, Simulation',
                'ps_type' => 'PS5',
                'image_url' => 'https://image.api.playstation.com/vulcan/ap/rnd/202109/1321/3mjMyRiJaq8lw1EFWiTCUJRV.png',
            ],
            [
                'title' => 'Final Fantasy XVI',
                'description' => 'The latest standalone single-player entry in the series. Experience an epic dark fantasy world where the fate of the land is decided by the mighty Eikons and the Dominants who wield them.',
                'genre' => 'Action RPG',
                'ps_type' => 'PS5',
                'image_url' => 'https://image.api.playstation.com/vulcan/ap/rnd/202211/1009/oehAww2ytCqKnxk12h74Hx7y.jpg',
            ],
            [
                'title' => 'Hogwarts Legacy',
                'description' => 'Hogwarts Legacy is an immersive, open-world action RPG. Now you can take control of the action and be at the center of your own adventure in the wizarding world.',
                'genre' => 'Action RPG',
                'ps_type' => 'PS5',
                'image_url' => 'https://image.api.playstation.com/vulcan/ap/rnd/202208/0921/Ah7Ar9MU0r1BBlzAUflmhyQP.png',
            ],
            [
                'title' => 'Elden Ring',
                'description' => 'The Golden Order has been broken. Rise, Tarnished, and be guided by grace to brandish the power of the Elden Ring and become an Elden Lord in the Lands Between.',
                'genre' => 'Action RPG',
                'ps_type' => 'PS5',
                'image_url' => 'https://image.api.playstation.com/vulcan/ap/rnd/202108/0410/D8mYIXWja8knuqYlwqcqVpi1.jpg',
            ],
            [
                'title' => 'Deathloop',
                'description' => 'Take on the role of Colt as you hunt down targets across the island of Blackreef in an endless time loop.',
                'genre' => 'First-person Shooter',
                'ps_type' => 'PS5',
                'image_url' => 'https://image.api.playstation.com/vulcan/ap/rnd/202007/1617/Fv4asO4zbdqL83hiL9COTlWZ.png',
            ],
        ];

        foreach ($games as $gameData) {
            // Download dan resize gambar jika URL valid
            if (filter_var($gameData['image_url'], FILTER_VALIDATE_URL)) {
                $localFileName = $this->downloadAndResizeImage($gameData['image_url'], 'ps5_' . Str::slug($gameData['title']));
                if ($localFileName) {
                    $gameData['image_url'] = $localFileName;
                }
            }

            Game::create($gameData);
            $this->command->info("Created game: {$gameData['title']}");
        }
    }

    /**
     * Download gambar dari URL dan simpan ke storage (tanpa resize)
     *
     * @param string $imageUrl URL gambar yang akan diunduh
     * @param string $baseFileName Nama dasar file (tanpa ekstensi)
     * @return string|null Nama file jika berhasil, null jika gagal
     */
    private function downloadAndResizeImage($imageUrl, $baseFileName)
    {
        try {
            // Coba unduh gambar
            $tempFile = tempnam(sys_get_temp_dir(), 'game_img');
            $ch = curl_init($imageUrl);
            $fp = fopen($tempFile, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Untuk menangani https
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            // Periksa jika download berhasil
            if ($httpCode !== 200) {
                $this->command->error("Failed to download image from: $imageUrl (HTTP Code: $httpCode)");
                unlink($tempFile);
                return null;
            }

            // Buat nama file unik dengan timestamp
            $fileName = Str::slug($baseFileName) . '_' . time() . '.jpg';

            // Langsung copy file yang diunduh ke storage tanpa resize
            copy($tempFile, storage_path('app/public/games/' . $fileName));

            // Hapus file temp
            unlink($tempFile);

            $this->command->info("Image downloaded and saved: $fileName");
            return $fileName;
        } catch (\Exception $e) {
            $this->command->error("Error processing image: " . $e->getMessage());
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            return null;
        }
    }
}
