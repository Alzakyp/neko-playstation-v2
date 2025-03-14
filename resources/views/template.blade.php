@extends('layouts.app')

@section('title', 'Template Preview')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <h6>Neko PlayStation v2 Template Test</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6 col-md-12 col-sm-12 mt-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="d-flex">
                                        <div class="icon icon-shape icon-lg bg-gradient-primary shadow text-center">
                                            <i class="ni ni-controller opacity-10"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="mb-0">PlayStation Games</h5>
                                            <p class="mb-0">Explore new games available</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12 mt-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="d-flex">
                                        <div class="icon icon-shape icon-lg bg-gradient-info shadow text-center">
                                            <i class="ni ni-world-2 opacity-10"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="mb-0">Online Features</h5>
                                            <p class="mb-0">Connect with other players</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
