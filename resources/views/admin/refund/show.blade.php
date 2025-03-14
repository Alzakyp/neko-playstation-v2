@extends('layouts.app')

@section('title', 'Game Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Game Details</h5>
                        </div>
                        <div>
                            <a href="{{ route('admin.game.edit', $game) }}" class="btn bg-gradient-info btn-sm mb-0">
                                <i class="fas fa-edit"></i>&nbsp;&nbsp;Edit
                            </a>
                            <a href="{{ route('admin.game.index') }}" class="btn bg-gradient-secondary btn-sm mb-0">
                                <i class="fas fa-arrow-left"></i>&nbsp;&nbsp;Back to List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="{{ $game->image_url }}" alt="{{ $game->title }}" class="img-fluid rounded mb-3" style="max-height: 200px;">
                            <div class="d-grid gap-2">
                                <span class="badge badge-lg bg-gradient-{{ $game->ps_type == 'PS5' ? 'primary' : 'info' }}">{{ $game->ps_type }}</span>
                                <span class="badge badge-lg bg-gradient-dark">{{ $game->genre }}</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h3 class="font-weight-bold mb-3">{{ $game->title }}</h3>

                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mt-4">Description:</h6>
                            <p class="mb-4">{{ $game->description ?? 'No description available.' }}</p>

                            <div class="row">
                                <div class="col-6">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder">Created At:</h6>
                                    <p class="text-sm mb-2">{{ $game->created_at->format('F d, Y') }}</p>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder">Last Updated:</h6>
                                    <p class="text-sm mb-2">{{ $game->updated_at->format('F d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Assigned PlayStations</h5>
                </div>
                <div class="card-body">
                    @if($game->playstations->count() > 0)
                        <ul class="list-group">
                            @foreach($game->playstations as $ps)
                                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape icon-sm me-3 bg-gradient-{{ $ps->status == 'available' ? 'success' : ($ps->status == 'in_use' ? 'warning' : 'danger') }} shadow text-center">
                                            <i class="ni ni-controller text-white opacity-10"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 text-sm">{{ $ps->ps_number }}</h6>
                                            <span class="text-xs">{{ $ps->ps_type }} • {{ ucfirst($ps->status) }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <a href="{{ route('admin.playstation.show', $ps) }}" class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                            <i class="ni ni-bold-right text-xs"></i>
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-secondary text-white text-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            No PlayStations assigned to this game yet
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.game.edit', $game) }}" class="btn btn-outline-primary btn-sm">
                                Assign PlayStations
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-dark font-weight-bold mb-1">Delete Game</h6>
                            <p class="text-sm mb-0">
                                Once you delete this game, there is no going back. This action cannot be undone.
                            </p>
                        </div>
                        <div class="ms-auto">
                            <form action="{{ route('admin.game.destroy', $game) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger mb-0"
                                    onclick="return confirm('Are you sure you want to delete {{ $game->title }}? This action cannot be undone.')">
                                    <i class="fas fa-trash me-2"></i> Delete Game
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
