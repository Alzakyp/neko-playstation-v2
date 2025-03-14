@extends('layouts.app')

@section('title', 'Manage PlayStations')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All PlayStations</h5>
                        </div>
                        <a href="{{ route('admin.playstation.create') }}" class="btn bg-gradient-primary btn-sm mb-0">
                            <i class="fas fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;Add New PlayStation
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Search and Filters -->
                    <div class="px-4 pt-3">
                        <form action="{{ route('admin.playstation.index') }}" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" name="search" class="form-control" placeholder="Search by PS number" value="{{ request()->search }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <select name="ps_type" class="form-select">
                                        <option value="">All PlayStation Types</option>
                                        @foreach($ps_types as $type)
                                            <option value="{{ $type }}" {{ request()->ps_type == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <select name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ request()->status == $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn bg-gradient-info mb-0">Filter</button>
                                <a href="{{ route('admin.playstation.index') }}" class="btn bg-gradient-secondary mb-0">Reset</a>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">PlayStation</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Type</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hourly Rate</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($playstations as $playstation)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $playstation->ps_number }}</h6>
                                                <p class="text-xs text-secondary mb-0">
                                                    {{ Str::limit($playstation->description, 30) }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $playstation->ps_type }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ $playstation->formatted_rate }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        {!! $playstation->status_badge !!}
                                    </td>
                                    <td class="align-middle">
                                        <div class="ms-auto text-end">
                                            <a href="{{ route('admin.playstation.show', $playstation) }}" class="btn btn-link text-info text-gradient px-3 mb-0">
                                                <i class="far fa-eye me-2"></i>View
                                            </a>
                                            <a href="{{ route('admin.playstation.edit', $playstation) }}" class="btn btn-link text-dark px-3 mb-0">
                                                <i class="fas fa-pencil-alt text-dark me-2" aria-hidden="true"></i>Edit
                                            </a>
                                            <form action="{{ route('admin.playstation.destroy', $playstation) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger text-gradient px-3 mb-0" onclick="return confirm('Are you sure you want to delete this PlayStation?')">
                                                    <i class="far fa-trash-alt me-2"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No PlayStations found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-4 pt-4">
                        {{ $playstations->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
