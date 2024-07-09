

    <!-- main navbar -->

    @include('layouts.partials.header')

    <!-- /main navbar -->


    <!-- Page content -->
    <div class="page-content">
    <?php
            $headerMessages = \App\Models\HeaderMessage::whereHas('status', function($q){
                $q->where("name", "active");
            })->orderby('updated_at', 'asc')->get();
        ?>

        <!-- Main sidebar -->
        @include('layouts.partials.sidebar')
        <!-- /main sidebar -->


        <!-- Main content -->
        <div class="content-wrapper">
        @foreach ($headerMessages as $headerMessage)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    @if(app()->getLocale() == 'en')
                    <strong>{{$headerMessage->message_en}}</strong>
                @endif
                @if(app()->getLocale() == 'fr')
                    <strong>{{$headerMessage->message_fr}}</strong>
                @endif
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
            @endforeach


             @yield('breadcrumbs')

            <!-- Content area -->
            <div class="content" style="padding-top: 0.25rem;">

             @yield('content')

            </div>
            <!-- /content area -->


            <!-- Footer -->
        @include('layouts.partials.footer')
            <!-- /footer -->

