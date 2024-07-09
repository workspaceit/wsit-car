@extends('layouts.app')
@section('title','Dashboard')
@section('breadcrumbs', Breadcrumbs::render('tm_tasks.dashboard'))
@section('content')
        <div class="row task-dashboad mt-3">
            <div class="col-lg-2">
                <a href="{{route("task-manager.tasks.index")}}">
                    <div class="card bg-teal-400">
                        <div class="card-body">
                            <div class="d-flex">
                                <h3 class="font-weight-semibold mb-0"><i class="icon-task"></i></h3>
                                <span class="align-middle align-self-center pl-2 mb-0"> All Tasks </span>
                                <span class="badge bg-teal-800 badge-pill align-self-center ml-auto">{{$all_tasks_count ?? 0}}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-2">
                <a href="{{route("task-manager.tasks.index")}}">
                    <div class="card bg-teal-400">
                        <div class="card-body">
                            <div class="d-flex">
                                <h3 class="font-weight-semibold mb-0"><i class="icon-task"></i></h3>
                                <span class="align-middle align-self-center pl-2 mb-0"> To-Do </span>
                                <span class="badge bg-teal-800 badge-pill align-self-center ml-auto">{{$todo_count ?? 0}}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-2">
                <a href="{{route("task-manager.tasks.index")}}">
                    <div class="card bg-teal-400">
                        <div class="card-body">
                            <div class="d-flex">
                                <h3 class="font-weight-semibold mb-0"><i class="icon-task"></i></h3>
                                <span class="align-middle align-self-center pl-2 mb-0"> In Progress </span>
                                <span class="badge bg-teal-800 badge-pill align-self-center ml-auto">{{$in_Progress_count ?? 0}}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-2">
                <a href="{{route("task-manager.tasks.index")}}">
                    <div class="card bg-teal-400">
                        <div class="card-body">
                            <div class="d-flex">
                                <h3 class="font-weight-semibold mb-0"><i class="icon-task"></i></h3>
                                <span class="align-middle align-self-center pl-2 mb-0"> In Review </span>
                                <span class="badge bg-teal-800 badge-pill align-self-center ml-auto">{{$in_Review_count ?? 0}}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-2">
                <a href="{{route("task-manager.tasks.index")}}">
                    <div class="card bg-teal-400">
                        <div class="card-body">
                            <div class="d-flex">
                                <h3 class="font-weight-semibold mb-0"><i class="icon-task"></i></h3>
                                <span class="align-middle align-self-center pl-2 mb-0"> Done </span>
                                <span class="badge bg-teal-800 badge-pill align-self-center ml-auto">{{$done_count ?? 0}}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-2">
                <a href="{{route("task-manager.tasks.index")}}">
                    <div class="card bg-teal-400">
                        <div class="card-body">
                            <div class="d-flex">
                                <h3 class="font-weight-semibold mb-0"><i class="icon-task"></i></h3>
                                <span class="align-middle align-self-center pl-2 mb-0"> Urgent </span>
                                <span class="badge bg-teal-800 badge-pill align-self-center ml-auto">{{$urgent_count ?? 0}}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body" id="app">
                <div class="row">
                    <div class="col-md-4">
                        <div class="">
                            <h3>Team Members</h3>
                            @foreach ($users as $user)
                                <div tasks_container="task_of_{{ $user->id }}" class="p-1 m-1 d-flex border team-member">
                                    <span class="col-md-4 align-self-center"><h4>{{ $user->username }}</h4></span>
                                    <div class="assigned-task text-right col-md-8">
                                        <h6>Assigned Tasks:</h6>
                                        <h5>{{ count($user->assigned_task) }}</h5>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="task-card-right">
                            @foreach ($users as $user)
                                <div id="task_of_{{ $user->id }}" class="tasks_container">
                                    <h3>Active Tasks:</h3>
                                    @foreach ($user->assigned_task as $task)
                                        @if( $task->status->name != 'done')
                                            <a href="{{ route('task-manager.tasks.show',['id'=>$task->id ]) }}" class="p-1 m-1 d-flex border">
                                                <span class="col-md-4 align-self-center"><h5>{{ $task->title }}</h5></span>
                                                <div class="assigned-task text-right col-md-8">
                                                    <span class="text-capitalize">Status:   {{ $task->status->name }}</span>
                                                    <span class="text-capitalize">Priority: {{ $priorities[$task->priority] }}</span>
                                                </div>
                                            </a>
                                        @endif
                                    @endforeach
                                    <h3>Done:</h3>
                                    @foreach ($user->assigned_task as $task)
                                        @if( $task->status->name == 'done')
                                            <a href="{{ route('task-manager.tasks.show',['id'=>$task->id ]) }}" class="p-1 m-1 d-flex border">
                                                <span class="col-md-4 align-self-center"><h5>{{ $task->title }}</h5></span>
                                                <div class="assigned-task text-right col-md-8">
                                                    <span class="text-capitalize">Status:   {{ $task->status->name }}</span>
                                                    <span class="text-capitalize">Priority: {{ $priorities[$task->priority] }}</span>
                                                </div>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>

            </div>
            <!-- /basic layout -->
        </div>
        <script>
            $(document).ready(function() {
                $(".tasks_container").hide();
                $('.team-member').click(function(){
                $(".tasks_container").hide();
                $("#"+$(this).attr('tasks_container')+"").show();
            });
            });

        </script>
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
