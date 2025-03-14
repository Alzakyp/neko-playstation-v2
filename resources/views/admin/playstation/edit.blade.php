@extends('layouts.app')

@section('title', 'Edit PlayStation')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Edit PlayStation</h5>
                            <p class="text-sm mb-0">Update PlayStation information</p>
                        </div>
                        <a href="{{ route('admin.playstation.index') }}" class="btn bg-gradient-secondary btn-sm mb-0">
                            <i class="fas fa-arrow-left" aria-hidden="true"></i>&nbsp;&nbsp;Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.playstation.update', $playstation) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ps_number" class="form-control-label">PlayStation Number</label>
                                    <input class="form-control @error('ps_number') is-invalid @enderror" type="text"
                                        id="ps_number" name="ps_number" value="{{ old('ps_number', $playstation->ps_number) }}" required>
                                    @error('ps_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ps_type" class="form-control-label">PlayStation Type</label>
                                    <input class="form-control @error('ps_type') is-invalid @enderror" type="text"
                                        id="ps_type" name="ps_type" value="{{ old('ps_type', $playstation->ps_type) }}" list="ps_type_list" required>
                                    <datalist id="ps_type_list">
                                        <option value="PS3">
                                        <option value="PS4">
                                        <option value="PS5">
                                    </datalist>
                                    @error('ps_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hourly_rate" class="form-control-label">Hourly Rate (Rp)</label>
                                    <input class="form-control @error('hourly_rate') is-invalid @enderror" type="number"
                                        id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate', $playstation->hourly_rate) }}" min="0" step="1000" required>
                                    @error('hourly_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-control-label">Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                        <option value="available" {{ old('status', $playstation->status) == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="in_use" {{ old('status', $playstation->status) == 'in_use' ? 'selected' : '' }}>In Use</option>
                                        <option value="maintenance" {{ old('status', $playstation->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description" class="form-control-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="5">{{ old('description', $playstation->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Game Selection -->
                            <div class="col-md-12 mt-3">
                                <label class="form-control-label">Assign Games</label>
                                <div class="form-group">
                                    <div class="row">
                                        @foreach($games->chunk(3) as $chunk)
                                            <div class="col-md-4">
                                                @foreach($chunk as $game)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="game-{{ $game->id }}"
                                                            name="game_ids[]"
                                                            value="{{ $game->id }}"
                                                            {{ in_array($game->id, old('game_ids', $selectedGames)) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="game-{{ $game->id }}">
                                                            {{ $game->title }} ({{ $game->ps_type }})
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn bg-gradient-success mt-3 btn-lg w-100">
                                    <i class="fas fa-save mr-1"></i> Update PlayStation
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
