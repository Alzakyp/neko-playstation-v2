@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Games</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $data['totalGames'] }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="ni ni-controller text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">PlayStations</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $data['totalPlaystations'] }}
                                <small class="text-success text-sm font-weight-bolder">
                                    <i class="fa fa-check-circle"></i> {{ $data['availablePlaystationCount'] ?? 0 }} available
                                </small>
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                            <i class="ni ni-tv-2 text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Reservations</p>
                            <h5 class="font-weight-bolder mb-0">
                                {{ $data['totalReservations'] }}
                                <small class="text-warning text-sm font-weight-bolder">
                                    <i class="fa fa-clock"></i> {{ $data['pendingReservations'] }} pending
                                </small>
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                            <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Revenue</p>
                            <h5 class="font-weight-bolder mb-0">
                                Rp {{ number_format($data['totalRevenue'], 0, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                            <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-lg-7 mb-lg-0 mb-4">
        <div class="card">
            <div class="card-header pb-0">
                <h6>Recent Reservations</h6>
            </div>
            <div class="card-body p-3">
                @if(count($data['recentReservations']) > 0)
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Customer</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">PlayStation</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['recentReservations'] as $reservation)
                            <tr>
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $reservation->user->name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $reservation->playstation->ps_number }}</p>
                                    <p class="text-xs text-secondary mb-0">{{ $reservation->playstation->ps_type }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <span class="badge badge-sm bg-gradient-{{
                                        $reservation->status == 'confirmed' ? 'info' :
                                        ($reservation->status == 'completed' ? 'success' :
                                        ($reservation->status == 'cancelled' ? 'danger' : 'warning'))
                                    }}">{{ ucfirst($reservation->status) }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ $reservation->start_time->format('d/m/Y H:i') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center">No recent reservations found</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100 p-3">
            <div class="overflow-hidden position-relative border-radius-lg bg-cover h-100" style="background-image: url('{{ asset('assets/img/ivancik.jpg') }}');">
                <span class="mask bg-gradient-dark"></span>
                <div class="card-body position-relative z-index-1 d-flex flex-column h-100 p-3">
                    <h5 class="text-white font-weight-bolder mb-4 pt-2">Popular PlayStations</h5>
                    @if(count($data['popularPlaystations']) > 0)
                        <ul class="list-group mb-3">
                            @foreach($data['popularPlaystations'] as $ps)
                                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape icon-sm me-3 bg-gradient-{{ $ps->status == 'available' ? 'success' : ($ps->status == 'in_use' ? 'warning' : 'danger') }} shadow text-center">
                                            <i class="ni ni-controller text-white opacity-10"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 text-sm text-white">{{ $ps->ps_number }}</h6>
                                            <span class="text-xs text-white">{{ $ps->ps_type }} • {{ $ps->reservations_count }} reservations</span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-white">No data available</p>
                    @endif
                    <a class="text-white text-sm font-weight-bold mb-0 icon-move-right mt-auto" href="{{ route('admin.playstation.index') }}">
                        View All PlayStations
                        <i class="fas fa-arrow-right text-sm ms-1" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
