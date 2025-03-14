@extends('layouts.app')

@section('title', 'Create New Game')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Add New Game</h5>
                        </div>
                        <a href="{{ route('admin.game.index') }}" class="btn bg-gradient-secondary btn-sm mb-0">
                            <i class="fas fa-arrow-left" aria-hidden="true"></i>&nbsp;&nbsp;Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.game.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="form-control-label">Title</label>
                                    <input class="form-control @error('title') is-invalid @enderror" type="text"
                                        id="title" name="title" value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="genre" class="form-control-label">Genre</label>
                                    <input class="form-control @error('genre') is-invalid @enderror" type="text"
                                        id="genre" name="genre" value="{{ old('genre') }}" required>
                                    @error('genre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ps_type" class="form-control-label">PlayStation Type</label>
                                    <select class="form-control @error('ps_type') is-invalid @enderror"
                                        id="ps_type" name="ps_type" required>
                                        <option value="">Select PlayStation Type</option>
                                        @foreach($ps_types as $type)
                                            <option value="{{ $type }}" {{ old('ps_type') == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('ps_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image" class="form-control-label">Cover Image</label>
                                    <input class="form-control @error('image') is-invalid @enderror" type="file"
                                        id="image" name="image" accept="image/*" required>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description" class="form-control-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="5">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- PlayStation Selection -->
                            <div class="col-md-12 mt-3">
                                <label class="form-control-label">Assign to PlayStations</label>
                                <div class="form-group">
                                    @foreach($playstations->chunk(4) as $chunk)
                                        <div class="row mb-3">
                                            @foreach($chunk as $playstation)
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="ps-{{ $playstation->id }}"
                                                            name="playstation_ids[]"
                                                            value="{{ $playstation->id }}"
                                                            {{ in_array($playstation->id, old('playstation_ids', [])) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="ps-{{ $playstation->id }}">
                                                            {{ $playstation->ps_number }} ({{ $playstation->ps_type }})
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn bg-gradient-primary mt-3 btn-lg w-100">
                                    <i class="fas fa-save mr-1"></i> Create Game
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
