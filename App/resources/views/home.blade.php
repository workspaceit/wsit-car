@extends('layouts.app')
@section('title','Home Page')

@section('content')
    @can('dashboard.report')
        <div class="row">
            <div class="col-lg-3">

                <!-- Members online -->
                <div class="card bg-teal-400">
                    <div class="card-body">
                        <div class="d-flex">
                            <h3 class="font-weight-semibold mb-0"><i class="icon-users"></i></h3>
                            <span class="badge bg-teal-800 badge-pill align-self-center ml-auto">{{$count_dealers}}</span>
                        </div>
                        <div>
                            Dealers
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div id="members-online"></div>
                    </div>
                </div>
                <!-- /members online -->
            </div>

            <div class="col-lg-3">
                <div class="card bg-teal-400">
                    <div class="card-body">
                        <div class="d-flex">
                            <h3 class="font-weight-semibold mb-0"><i class="icon-car"></i></h3>
                            <span class="badge bg-teal-800 badge-pill align-self-center ml-auto">{{$count_cars}}</span>
                        </div>
                        <div>
                            Inventory
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div id="members-online"></div>
                    </div>
                </div>
                <!-- /members online -->
            </div>
        </div>
    @endcan
@endsection

@push('css')
    <style>
        .no-margin a {
            color: #fff
        }
    </style>
@endpush

@push('js')

@endpush
