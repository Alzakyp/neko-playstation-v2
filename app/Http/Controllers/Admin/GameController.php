<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameRequest;
use App\Models\Game;
use App\Models\Playstation;
use App\Traits\AlertMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class GameController extends Controller
{
    use AlertMessage;

    // Dimensi standar untuk gambar game
    protected $imageWidth = 300;  // Lebar standar dalam pixel
    protected $imageHeight = 400; // Tinggi standar dalam pixel

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $ps_type = $request->ps_type ?? '';

        $games = Game::query()
            ->when($search, function($query) use ($search) {
                return $query->where('title', 'like', "%{$search}%")
                    ->orWhere('genre', 'like', "%{$search}%");
            })
            ->when($ps_type, function($query) use ($ps_type) {
                return $query->where('ps_type', $ps_type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get unique PS types for filter dropdown
        $ps_types = Game::select('ps_type')->distinct()->pluck('ps_type');

        return view('admin.game.index', compact('games', 'ps_types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $playstations = Playstation::all();
        $ps_types = Playstation::select('ps_type')->distinct()->pluck('ps_type');

        return view('admin.game.create', compact('playstations', 'ps_types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GameRequest $request)
    {
        try {
            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image_url'] = $this->uploadImage($request->file('image'));
            }

            // Remove playstation_ids from data before creating game
            $playstationIds = $data['playstation_ids'] ?? [];
            unset($data['playstation_ids']);

            // Create game
            $game = Game::create($data);

            // Attach playstations
            if (!empty($playstationIds)) {
                $game->playstations()->attach($playstationIds);
            }

            $this->successMessage('Game berhasil ditambahkan');
            return redirect()->route('admin.game.index');
        } catch (\Exception $e) {
            $this->errorMessage('Error menambahkan Game: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game)
    {
        $game->load('playstations');
        return view('admin.game.show', compact('game'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Game $game)
    {
        $playstations = Playstation::all();
        $ps_types = Playstation::select('ps_type')->distinct()->pluck('ps_type');
        $selectedPlaystations = $game->playstations->pluck('id')->toArray();

        return view('admin.game.edit', compact('game', 'playstations', 'ps_types', 'selectedPlaystations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GameRequest $request, Game $game)
    {
        try {
            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($game->getRawOriginal('image_url')) {
                    Storage::delete('public/games/' . $game->getRawOriginal('image_url'));
                }
                $data['image_url'] = $this->uploadImage($request->file('image'));
            }

            // Remove playstation_ids from data before updating game
            $playstationIds = $data['playstation_ids'] ?? [];
            unset($data['playstation_ids']);

            // Update game
            $game->update($data);

            // Sync playstations
            $game->playstations()->sync($playstationIds);

            $this->successMessage('Game berhasil diperbarui');
            return redirect()->route('admin.game.index');
        } catch (\Exception $e) {
            $this->errorMessage('Error memperbarui Game: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        try {
            // Delete the image if exists
            if ($game->getRawOriginal('image_url')) {
                Storage::delete('public/games/' . $game->getRawOriginal('image_url'));
            }

            // Detach all playstations first (although cascade should handle this)
            $game->playstations()->detach();

            $game->delete();

            $this->successMessage('Game berhasil dihapus');
            return redirect()->route('admin.game.index');
        } catch (\Exception $e) {
            $this->errorMessage('Error menghapus Game: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Upload and resize image to standardized dimensions
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string The image filename
     */
    // private function uploadImage($image)
    // {
    //     // Generate unique filename
    //     $imageName = time() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
    //     $path = storage_path('app/public/games/' . $imageName);

    //     // Create the directory if it doesn't exist
    //     if (!file_exists(storage_path('app/public/games'))) {
    //         mkdir(storage_path('app/public/games'), 0755, true);
    //     }

    //     // Resize the image to standard dimensions
    //     $img = Image::make($image->getRealPath());

    //     // Resize maintaining aspect ratio (fit within the dimensions)
    //     $img->resize($this->imageWidth, $this->imageHeight, function ($constraint) {
    //         $constraint->aspectRatio();
    //         $constraint->upsize(); // Prevent upsizing if image is smaller
    //     });

    //     // If you want all images to be exactly the same dimensions (may crop):
    //     /*
    //     $img->fit($this->imageWidth, $this->imageHeight, function ($constraint) {
    //         $constraint->upsize(); // Prevent upsizing if image is smaller
    //     });
    //     */

    //     // Save the resized image
    //     $img->save($path);

    //     return $imageName;
    // }
}
