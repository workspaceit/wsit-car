            <div class="navbar navbar-expand-lg navbar-light">
                <div class="text-center d-lg-none w-100">
                    <button type="button" class="navbar-toggler dropdown-toggle" data-toggle="collapse" data-target="#navbar-footer">
                        <i class="icon-unfold mr-2"></i>
                        {!! cached('settings','title') !!}
                    </button>
                </div>

                <div class="navbar-collapse collapse" id="navbar-footer">
                    <span class="navbar-text text-white">
                        &copy; {{date('Y')}}. <a href="https://drivegood.com/" target="_blank">DriveGood</a>
                        <!--  If the footer needs to be dynamic, the table entry must be set accordingly  -->
                        {{-- <a href="/">  {!! cached('settings','title') !!}</a>--}}
                    </span>
                </div>
            </div>


        </div>
        <!-- /main content -->

    </div>
    <!-- /page content -->

    <div class="modal fade loading-modal-lg" id="loading-modal" data-backdrop="static" data-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="ajax-loader"></div>
            <div class="ajax-loader-text">@lang('ui.loading')...</div>
        </div>
    </div>

    <!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

<script type="text/javascript">
    $(function ($q) {
        $('body').on('click', '.remove-item', function (e) {

            if (!confirm("{{ __('ui.form.are_you_sure_you_want_to_delete_this_item') }}"))
                return false;

            $(this).next('form').submit();

        });

        $('body').on('click', '.remove-item-link', function (e) {
            if (!confirm("{{ __('ui.form.are_you_sure_you_want_to_delete_this_item') }}"))
                return false;
        });

        $('body').on('click', '.add-item', function (e) {
            if (!confirm('هل أنت متأكد من  هذه  العملية  ؟'))
                return false;

            $(this).next('form').submit();
        });

        function setCurrentPage(page) {
            page = page.replace('.', '-');
            page = page.replace('::', '-');

            var ul = document.querySelector('.nav-sidebar');
            var items = ul.getElementsByTagName("a");
            for (var i = 0; i < items.length; ++i) {
                items[i].classList.remove("active");
            }

            var currentPage = document.getElementById(page);
            currentPage && currentPage.classList.add("active");
            parent = $('#' + page).parents('.nav-group-sub');
            if (parent.hasClass('nav-group-sub'))
                parent.toggle();

            parent.parents('.nav-item-submenu').toggleClass('nav-item-open');

        }
        // End Helpers

        // Set Active nav for current page
        // ------------------------------
        setCurrentPage(agent.current_route);

        @if(session('success'))
            // Success notification
            new PNotify({
                title: '{{ __('ui.note') }}',
                text: '{{ session('success') }}',
                icon: 'icon-checkmark3',
                class: 'stack-custom-bottom bg-success border-success',
                type: 'success',
                hide: true,
                delay: 800
            });
        @endif

        @if(session('error'))
            // Success notification
            new PNotify({
                title: '{{ __('ui.note') }}',
                text: '{{ session('error') }}',
                icon: 'icon-cancel-circle2',
                class: 'stack-custom-bottom bg-danger border-danger',
                type: 'error',
                hide: true,
                delay: 800
            });
        @endif

        @if(session('warning'))
            // Warning notification
            new PNotify({
                title: '{{ __('ui.note') }}',
                text: '{{ session('warning') }}',
                icon: 'icon-warning2',
                class: 'stack-custom-bottom bg-warning border-warning',
                type: 'warning',
                hide: true,
                delay: 1800
            });
        @endif

        //Add div for tooltip
        let menuSpans = $(".nav-sidebar > .nav-item > a.nav-link > span");
        $.each(menuSpans, function(key,val) {
            let text = $(this).contents().filter(function(){
                return this.nodeType == 3;
            })[0].nodeValue;
            $(this).parent().append(`<span class='tooltiptext tooltip-right'>${text}</span>`);
        });
    });

    /*Change Language*/
    function changeLang(value) {
        jQuery.ajax({
            type: "POST",
            url: route('user.api.update_lang'),
            dataType: 'json',
            data: {lang: value},

            success: function (obj, textstatus) {
                Cookies.set('language', value, { expires: 365 });
                location.reload();
            }
        });
    }
    /*Change Language END*/

    function capitalize(s){
        return s.toLowerCase().replace( /\b./g, function(a){ return a.toUpperCase(); } );
    };

     // Hook up the form so we can prevent it from being posted
     validate.validators.presence.options = {
        message: @json(__('validation.required'))
    };

    validate.validators.email.options = {
        message: @json(__('validation.email', ['attribute' => 'email']))
    };
</script>

@stack('footer')

</body>
</html>
