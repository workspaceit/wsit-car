<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Drive Good DMS</title>
    <link rel="shortcut icon" href="/backend_assets/global_assets/images/DriveGood-favicon.png">


    <!-- Global stylesheets -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="/backend_assets/global_assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/bootstrap_limitless.min.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/layout.min.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/components.min.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/colors.min.css" rel="stylesheet" type="text/css">
    <link href="/backend_assets/assets/css/custom.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <!-- /global stylesheets -->

    <!-- Core JS files -->
    <script src="/backend_assets/global_assets/js/main/jquery.min.js"></script>
    <script src="/backend_assets/global_assets/js/main/bootstrap.bundle.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/loaders/blockui.min.js"></script>
    <script src="/backend_assets/global_assets/js/plugins/js-cookie/js.cookie.min.js"></script>
    <!-- /core JS files -->

    <!-- Theme JS files -->
    <script src="/backend_assets/assets/js/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/validate.js/0.13.1/validate.min.js"></script>
    <script src="/backend_assets/assets/js/validate.js?1000"></script>
    @if (in_array(request()->route()->getName(), ['login', 'admin.login', 'passwords.showConfirmForm']))
        <script src="/backend_assets/global_assets/js/plugins/forms/validation/validate.min.js"></script>
        <script src="/backend_assets/global_assets/js/plugins/forms/styling/uniform.min.js"></script>
        <script src="/backend_assets/global_assets/js/demo_pages/login_validation.js"></script>
    @endif
    <!-- /theme JS files -->

 </head>

 <body class="bg-slate-800 {{!in_array(request()->route()->getName(), ['admin.login', 'passwords.showConfirmForm']) ? 'page-auth' : ''}}">


    <!-- Page content -->
    <div class="page-content">

        <!-- Main content -->
        <div class="content-wrapper">

            @yield('content')

            <!-- Footer -->
            <!-- <div class="navbar navbar-expand-lg navbar-light">
                <div class="text-center d-lg-none w-100">
                    <button type="button" class="navbar-toggler dropdown-toggle" data-toggle="collapse" data-target="#navbar-footer">
                        <i class="icon-unfold mr-2"></i>
                        Footer
                    </button>
                </div>

                <div class="navbar-collapse collapse" id="navbar-footer">
                    <span class="navbar-text">
                    &copy; {--{{date('Y')}}--}. <a href="/">  {--{{ config('app.name', 'Thorba Admin') }}--}</a>
                    </span>

                    <ul class="navbar-nav ml-lg-auto">
                    </ul>
                </div>
            </div> -->
            <!-- /footer -->

        </div>
        <!-- /main content -->

    </div>
    <!-- /page content -->

</body>
</html>
