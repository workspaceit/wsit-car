<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title> @hasSection('title') @yield('title') -  @endif  {{cached('settings', 'title')}}</title>
    <link rel="shortcut icon" href="/backend_assets/global_assets/images/DriveGood-favicon.png">

    <!-- Global stylesheets -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="/backend_assets/global_assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/global_assets/css/icons/inventory/style.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/bootstrap_limitless.min.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/global_assets/css/icons/custom/style.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/layout.min.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/components.min.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/colors.min.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/custom.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"
          integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-avatar@1.0.3/dist/avatar.min.css" rel="stylesheet">

    <!-- /global stylesheets -->


    <!-- Core JS files -->
    <script src="/backend_assets/global_assets/js/main/jquery.min.js"></script>
    <script src="/backend_assets/global_assets/js/main/bootstrap.bundle.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/loaders/blockui.min.js"></script>
    <!-- /core JS files -->

    <!-- Theme JS files -->
    @stack('header')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/validate.js/0.13.1/validate.min.js"></script>
    <script src="/backend_assets/assets/js/validate.js?1000"></script>

    <script src="/backend_assets/global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/forms/selects/select2.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/forms/styling/uniform.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/notifications/pnotify.min.js"></script>
    <script src="/backend_assets/global_assets/js/demo_pages/form_layouts.js"></script>
    <script src="/backend_assets/global_assets/js/demo_pages/datatables_data_sources.js"></script>

    <script src="/backend_assets/global_assets/js/plugins/js-cookie/js.cookie.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/ui/moment/moment.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/pickers/daterangepicker.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/pickers/anytime.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/pickers/pickadate/picker.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/pickers/pickadate/picker.date.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/pickers/pickadate/picker.time.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/pickers/pickadate/legacy.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/notifications/jgrowl.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/uploaders/fileinput/plugins/piexif.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/uploaders/fileinput/plugins/sortable.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/uploaders/fileinput/fileinput.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/uploaders/fileinput/fileinputtheme.min.js"></script>
    @if(file_exists(public_path(sprintf("/backend_assets/global_assets/js/plugins/uploaders/fileinput/locales/%s.js",app()->getLocale()))))
        <script src="/backend_assets/global_assets/js/plugins/uploaders/fileinput/locales/{{ app()->getLocale() }}.js"></script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="/backend_assets/assets/js/app.js"></script>
    <!-- /theme JS files -->
    <script src="/backend_assets/global_assets/js/demo_pages/picker_date.js"></script>

    <script type="text/javascript">
        // Basic Datatable examples
        var agent = {!! json_encode($agent) !!};
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
    <script>
        $(() => {
            if (!Cookies.get('language')) {
                Cookies.set('language', '{{ encrypt(Session::get('setLang')) }}', { expires: 365 });
                window.location.reload()
            }
        })
    </script>
    @stack('bottom-header')
    <style>
        .navbar {
            background-color: #100e42;
        }
    </style>

</head>
<body class="sidebar-xs">
    @routes
@php
    $haveUnpublishedCar = App\Models\Car::whereIn('dealer_id', (array) Auth::user()->dealers)
            ->where('published', false)
            ->exists();
@endphp
<!-- Main navbar -->
    <div class="navbar navbar-expand-md navbar-dark pl-0">
        <div class="navbar-brand" style="padding-top: 0.50002rem;padding-bottom: 0.50002rem;">
            <a href="/" class="d-inline-block">
                <img src="/backend_assets/global_assets/images/DriveGood-logo.png" width="150" height="50" alt="DriveGood Logo">
            </a>
            <a href="/" class="d-inline-block">

                <span>DRIVE GOOD</span>
            </a>
        </div>

        <div class="d-md-none text-center">
            <button class="navbar-toggler pb-0" type="button" data-toggle="collapse" data-target="#navbar-mobile">
                <i class="icon-tree5"></i>
            </button>
            <button class="navbar-toggler sidebar-mobile-main-toggle pb-0" type="button">
                <i class="icon-paragraph-justify3"></i>
            </button>
            </br>
            @if(\Illuminate\Support\Facades\Session::has('impersonate'))
                <li class="list-unstyled">
                    <a href="{{route('impersonate',['id'=>session('impersonate')])}}" class="btn btn-primary mr-1"
                       style="margin-top: .3rem;"><i class="icon-redo2"></i> Return to Admin</a>
                </li>
            @endif
            @if(!auth()->user()->isSuperAdmin())
                <li class="list-unstyled">
                    <a href="{{ env('NEW_THEME_URL', 'https://drivegood.com') }}/auth-check?_token={{ encrypt(['email' => auth()->user()->email, 'password' => auth()->user()->password]) }}"
                        class="btn btn-primary mr-1" style="margin-top:.3rem; background-color:#ff5722;"><i class="icon-redo2"></i> Try New Theme </a>
                </li>
            @endif
        </div>

        <div class="collapse navbar-collapse" id="navbar-mobile">
            <ul class="navbar-nav">
                {{-- <li class="nav-item">
                    <a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block">
                        <i class="icon-paragraph-justify3"></i>
                    </a>
                </li> --}}
            </ul>

            <span class="navbar-text ml-md-3 mr-md-auto">
                {{-- <span class="badge bg-success">Online</span> --}}
            </span>

            <ul class="navbar-nav">
                {{-- <li class="dropdown mr-1 mt-1">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="langDropdown" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                        {{ strtoupper(app()->getLocale()) }}
                    </button>
                    <div class="dropdown-menu" aria-labelledby="langDropdown">
                        @if(app()->getLocale() !== 'en')
                            <a class="dropdown-item" href="javascript:" onclick="changeLang('{{ encrypt('en') }}')">EN</a>
                        @endif
                        @if(app()->getLocale() !== 'fr')
                            <a class="dropdown-item" href="javascript:" onclick="changeLang('{{ encrypt('fr') }}')">FR</a>
                        @endif
                    </div>
                </li> --}}

                <li>
                    @if(app()->getLocale() == 'en')
                        <a href="javascript:" class="btn btn-light mr-1" style="margin-top: .3rem;"
                            onclick="changeLang('{{ encrypt('fr') }}')"> FR </a>
                    @endif
                    @if(app()->getLocale() == 'fr')
                        <a href="javascript:" class="btn btn-light mr-1" style="margin-top: .3rem;"
                            onclick="changeLang('{{ encrypt('en') }}')"> EN </a>
                    @endif
                </li>

                @if(\Illuminate\Support\Facades\Session::has('impersonate'))
                    <li>
                        <a href="{{route('impersonate',['id'=>session('impersonate')])}}" class="btn btn-primary mr-1"
                           style="margin-top: .3rem;"><i class="icon-redo2"></i> Return to Super Admin </a>
                    </li>
                @endif

                @if(!auth()->user()->isSuperAdmin())
                    <li>
                        <a href="{{ env('NEW_THEME_URL', 'https://drivegood.com') }}/auth-check?_token={{ encrypt(['email' => auth()->user()->email, 'password' => auth()->user()->password]) }}"
                            class="btn btn-primary mr-1" style="margin-top:.3rem; background-color:#ff5722;"><i class="icon-redo2"></i> Try New Theme </a>
                    </li>
                @endif

                @if(!auth()->user()->isSuperAdmin() && auth()->user()->can('notifications.listing') && !auth()->user()->isIndividual())
                    <li class="nav-item dropdown mt-1" onclick="markAsRead()">
                        <a class="nav-link text-white" data-toggle="dropdown" href="#" aria-expanded="false">
                            <i class="far fa-bell"></i>
                            <span class="badge badge-warning navbar-badge" style="font-size: .6rem; font-weight: 300; padding: 2px 4px; position: absolute; right: 10px; top: 8px;">
                            {{getTotalNotifications()}}
                        </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="left: inherit; right: 0; max-width: 550px; min-width: 350px;">
                            <div class="text-center"
                                 style="display: block; width: 100%;  padding: .25rem 1rem; clear: both; font-weight: 400;  color: #212529; text-align: inherit; white-space: nowrap; background-color: transparent; border: 0;">{{getTotalNotifications()}} {{__('offer.notifications.title')}}</div>
                            <div class="dropdown-divider"></div>
                            @foreach($notifications = getNotifications() as $key => $notification)
                                <a class=" notification-item {{$notification->read === 0 ? 'font-weight-bold' : ''}}" href="{{$notification->link}}" onclick="markAsSeen({{$notification->id}})"
                                   style="display: block; width: 100%;  padding: .25rem 1rem; clear: both; font-weight: 400;  color: #212529; text-align: inherit; white-space: revert; background-color: transparent; border: 0;">
                                    <div class="text-capitalize ml-3">{{$notification->title}}</div>
                                    <p class="pb-0 mb-0"><i class="far fa-clock mr-2 pl-3"></i><span class="text-muted font-weight-normal">{{$notification->created_at->diffForHumans()}}</span></p>
                                </a>
                                <div class="dropdown-divider"></div>
                            @endforeach

                            @if($notifications->isEmpty())
                                <div
                                    style="display: block; width: 100%;  padding: .25rem 1rem; clear: both; font-weight: 400;  color: #212529; text-align: inherit; white-space: nowrap; background-color: transparent; border: 0;">
                                    <span>{{__('offer.notifications.not_found')}}</span>
                                </div>
                                <div class="dropdown-divider"></div>
                            @endif

                            <a href="{{route('notifications.manage')}}" class="text-center"
                               style="display: block; width: 100%;  padding: .25rem 1rem; clear: both; font-weight: 400;  color: #212529; text-align: inherit; white-space: nowrap; background-color: transparent; border: 0;">{{__('offer.notifications.sse_all_notification_button')}}</a>
                        </div>
                    </li>
                @endif

                <li {{ in_array(Auth::user()->type, ['super_admin', 'transporter', 'buyer', 'dealer', 'individual']) ? 'style=display:none;' : ''}}>
                    <a href="javascript:void(0)" class="navbar-nav-link">
                        <span>{{ Auth::user()->username }}</span>
                    </a>
                </li>

                <li class="nav-item dropdown dropdown-user" {{ !in_array(Auth::user()->type, ['super_admin', 'transporter', 'buyer', 'dealer', 'individual']) ? 'style=display:none;' : '' }} >

                    <a href="{{ auth()->user()->type === 'super_admin' ? route('users.edit', Auth::id()) : route('profile.edit') }}" title="Profile" class="navbar-nav-link">
                        <span>{{Auth::user()->username}}</span>
                    </a>

                    <form method="POST" action="{{ auth()->check() && auth()->user()->isSuperAdmin() ? route('admin.logout') : route('logout') }}" id="logout-form" style="display:none;">
                         @csrf
                    </form>

                    {{-- <a href="#" class="navbar-nav-link dropdown-toggle" data-toggle="dropdown">
                        <span>{{Auth::user()->username}}</span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="{{ auth()->user()->type === 'super_admin' ? route('users.edit', Auth::id()) : route('profile.edit') }}" class="dropdown-item"><i class="icon-cog5"></i>
                            Profile </a>
                        <form method="POST" action="{{ auth()->check() && auth()->user()->isSuperAdmin() ? route('admin.logout') : route('logout') }}" id="logout-form" style="display:none;">
                            @csrf
                        </form>
                    </div>--}}
                </li>
            </ul>
        </div>
    </div>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function markAsRead() {
            $.ajax({
                type: 'post',
                url: '{{ route('notifications.mark-as-read') }}',
                data: {},
                success: function (response) {
                },
                complete: function (data) {
                }
            });
        }
        function markAsSeen(notification_id) {
            $.ajax({
                type: 'post',
                url: '{{ route('notifications.mark-as-seen') }}',
                data: {
                    notification_id : notification_id
                },
                success: function (response) {
                },
                complete: function (data) {
                }
            });
        }
    </script>
