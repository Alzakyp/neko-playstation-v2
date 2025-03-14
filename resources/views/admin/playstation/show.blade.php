@extends('layouts.app')

@section('title', 'PlayStation Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">PlayStation Details</h5>
                        </div>
                        <div>
                            <a href="{{ route('admin.playstation.edit', $playstation) }}" class="btn bg-gradient-info btn-sm mb-0">
                                <i class="fas fa-edit"></i>&nbsp;&nbsp;Edit
                            </a>
                            <a href="{{ route('admin.playstation.index') }}" class="btn bg-gradient-secondary btn-sm mb-0">
                                <i class="fas fa-arrow-left"></i>&nbsp;&nbsp;Back to List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="icon icon-shape icon-xxl shadow border-radius-xl bg-gradient-primary text-center mb-3">
                                <i class="ni ni-controller text-white opacity-10" style="font-size: 4rem;"></i>
                            </div>
                            <div>
                                {!! $playstation->status_badge !!}
                                <h4 class="mt-3">{{ $playstation->ps_type }}</h4>
                                <h6 class="text-muted mb-0">{{ $playstation->formatted_rate }} / hour</h6>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h3 class="font-weight-bold mb-3">{{ $playstation->ps_number }}</h3>

                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mt-4">Description:</h6>
                            <p class="mb-4">{{ $playstation->description ?? 'No description available.' }}</p>

                            <div class="row">
                                <div class="col-6">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder">Created At:</h6>
                                    <p class="text-sm mb-2">{{ $playstation->created_at->format('F d, Y') }}</p>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder">Last Updated:</h6>
                                    <p class="text-sm mb-2">{{ $playstation->updated_at->format('F d, Y') }}</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder">Quick Actions:</h6>
                                <div class="d-flex mt-2">
                                    <form action="{{ route('admin.playstation.update-status', $playstation) }}" method="POST" class="me-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="available">
                                        <button type="submit" class="btn btn-sm bg-gradient-success mb-0">
                                            Set Available
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.playstation.update-status', $playstation) }}" method="POST" class="me-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="in_use">
                                        <button type="submit" class="btn btn-sm bg-gradient-warning mb-0">
                                            Set In Use
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.playstation.update-status', $playstation) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="maintenance">
                                        <button type="submit" class="btn btn-sm bg-gradient-danger mb-0">
                                            Set Maintenance
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reservations -->
            <div class="card mt-4">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Recent Reservations</h5>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        @if($playstation->reservations->count() > 0)
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date & Time</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($playstation->reservations as $reservation)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $reservation->user->name ?? 'N/A' }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $reservation->user->email ?? '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $reservation->start_time->format('d M Y') }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-{{
                                            $reservation->status == 'confirmed' ? 'info' :
                                            ($reservation->status == 'completed' ? 'success' :
                                            ($reservation->status == 'cancelled' ? 'danger' :
                                            ($reservation->status == 'refunded' ? 'secondary' : 'warning')))
                                        }}">
                                            {{ ucfirst($reservation->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('admin.reservation.show', $reservation) }}" class="btn btn-link text-info text-gradient px-3 mb-0">
                                            <i class="far fa-eye me-2"></i>View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="text-center py-4">
                            <p class="text-sm mb-0">No reservations found for this PlayStation.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Games List -->
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Assigned Games</h5>
                </div>
                <div class="card-body">
                    @if($playstation->games->count() > 0)
                        <ul class="list-group">
                            @foreach($playstation->games as $game)
                                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                                            <i class="ni ni-app text-white opacity-10"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 text-sm">{{ $game->title }}</h6>
                                            <span class="text-xs">{{ $game->genre }} • {{ $game->ps_type }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <a href="{{ route('admin.game.show', $game) }}" class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                            <i class="ni ni-bold-right text-xs"></i>
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-secondary text-white text-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            No games assigned to this PlayStation yet
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.playstation.edit', $playstation) }}" class="btn btn-outline-primary btn-sm">
                                Assign Games
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card mt-4">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-dark font-weight-bold mb-1">Delete PlayStation</h6>
                            <p class="text-sm mb-0">
                                Once deleted, all associated data will be permanently removed.
                            </p>
                        </div>
                        <div class="ms-auto">
                            <form action="{{ route('admin.playstation.destroy', $playstation) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger mb-0"
                                    onclick="return confirm('Are you sure you want to delete this PlayStation? This action cannot be undone.')">
                                    <i class="fas fa-trash me-2"></i> Delete
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
