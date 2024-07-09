<style>
    .sidebar {
        background-color: #100e42;
    }
</style>
<div class="sidebar sidebar-dark sidebar-main sidebar-expand-md">
    <div class="sidebar-mobile-toggler text-center">
        <a href="#" class="sidebar-mobile-main-toggle">
            <i class="icon-arrow-right8"></i>
        </a>
        Navigation
        <a href="#" class="sidebar-mobile-expand">
            <i class="icon-screen-full"></i>
            <i class="icon-screen-normal"></i>
        </a>
    </div>

    <div class="sidebar-content">
        <div class="card card-sidebar-mobile">
            <ul class="nav nav-sidebar" data-nav-type="accordion">
                <li class="nav-item">
                    <a href="#" class="nav-link sidebar-control sidebar-main-toggle d-none d-md-block">
                        {{-- <i class="icon-paragraph-justify3"></i> --}}
                        <div id="nav-icon4">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </a>
                </li>
                @can('dealers.listing')
                    <li class="nav-item">
                        <a href="{{route('dealers')}}" class="nav-link" id="dealers">
                            <i class="icon-users"></i> <span> Dealers </span>
                        </a>
                    </li>
                @endcan


                @if(auth()->user()->type != 'super_admin' && auth()->user()->canManageDashboard())
                <li class="nav-item">
                    <a href="{{route('dealers.dashboard')}}" class="nav-link" id="dealers-dashboard">
                        <i class="icon-home4"></i> <span> {{ __('dashboard.dashboard') }} </span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a data-toggle="collapse" href="#inventory" role="button" aria-expanded="false" aria-controls="inventory" class="nav-link">
                            <i class="icon-inventory pl-3" style="font-size: 17px;"><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span><span class="path10"></span><span class="path11"></span><span class="path12"></span><span class="path13"></span><span class="path14"></span><span class="path15"></span><span class="path16"></span></i><span> {{ trans_choice('ui.vehicle', 2) }} </span>
                            <i class="ml-1 mt-1 fas fa-chevron-down" style="font-size: 10px"></i>
                        </a>
                    </li>
                    <ul class="nav nav-sidebar collapse" id="inventory" style="background: #383838;">
                        @can('vehicles.listing')
                            @if(auth()->user()->isSuperAdmin())
                            <li class="nav-item">
                                <a href="{{route(auth()->user()->isSuperAdmin() ? 'cars.index' : 'cars.for-user')}}" class="nav-link" id="cars-index">
                                    <i class="icon-car"></i> <span>  {{__('ui.vehicles')}} </span>
                                </a>
                            </li>
                            @else
                                @if(auth()->user()->isAdmin() && auth()->user()->canManageVehicles())
                                <li class="nav-item">
                                    <a href="{{route( 'cars.for-user')}}" class="nav-link" id="cars-index">
                                        <i class="icon-car"></i> <span>  {{__('ui.vehicles')}} </span>
                                    </a>
                                </li>
                                @endif
                            @endif
                        @endcan
                        @can('products.listing')
                            @if(auth()->user()->isSuperAdmin())
                                <li class="nav-item">
                                    <a href="{{route('inventory.others.manage')}}" class="nav-link" id="inventory-others.manage">
                                        <i class="icon-item" style="font-size: 20px;"></i> <span>{{ __('ui.items') }}</span>
                                    </a>
                                </li>
                            @else
                                @if(auth()->user()->isAdmin() && auth()->user()->canManageProducts())
                                    <li class="nav-item">
                                        <a href="{{route('inventory.others.manage')}}" class="nav-link" id="inventory-others.manage">
                                            <i class="icon-item" style="font-size: 20px;"></i> <span>{{ __('ui.items') }}</span>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endcan
                        @can('histories.listing')
                            <li class="nav-item">
                                <a href="{{route('cars.histories.manage')}}" class="nav-link" id="cars-index">
                                    <i class="icon-history"></i> <span>  @lang('ui.car_history')  </span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('inventory.items.logs.manage')}}" class="nav-link" id="inventory-items.logs.manage">
                                    <i class="fa fa-recycle"></i> <span>@lang('other.item_logs')</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                @else
                    @can('vehicles.listing')
                        @if(!auth()->user()->isBuyer() && (auth()->user()->canManageProducts() || auth()->user()->canManageVehicles()))
                            <li class="nav-item">
                                <a data-toggle="collapse" href="#inventory" role="button" aria-expanded="false" aria-controls="facebook" class="nav-link">
                                    <i class="icon-inventory pl-3" style="font-size: 17px;"><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span><span class="path10"></span><span class="path11"></span><span class="path12"></span><span class="path13"></span><span class="path14"></span><span class="path15"></span><span class="path16"></span></i><span> {{ trans_choice('ui.vehicle', 2) }} </span>
                                    <i class="ml-1 mt-1 fas fa-chevron-down" style="font-size: 10px"></i>
                                </a>
                            </li>
                            <ul class="nav nav-sidebar collapse" id="inventory" style="background: #383838;">
                                @if(auth()->user()->canManageVehicles())
                                <li class="nav-item">
                                    <a href="{{route('cars.for-user')}}" class="nav-link" id="cars-for-user">
                                        <i class="icon-car"></i> <span>{{ __('ui.vehicles') }}</span>
                                    </a>
                                </li>
                                @endif
                                @if(auth()->user()->canManageProducts())
                                    <li class="nav-item">
                                        <a href="{{route('inventory.others.manage')}}" class="nav-link" id="inventory-others.manage">
                                            <i class="icon-item" style="font-size: 20px;"></i> <span>{{ __('ui.items') }}</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                        @if(((auth()->user()->isDealer() || auth()->user()->isIndividual()) && $isDealerExchangesEnabled) || auth()->user()->isBuyer())
                            <li class="nav-item">
                                <a href="{{route('advance-search.manage')}}" class="nav-link" aria-expanded="false" id="advance-search-manage">
                                    <i class="icon-search4"></i>
                                    <span>
                                        {{ __('ui.menu.advance_search') }}
                                        <span class="text-dark">
                                            <sup style="font-size: 85%;"><span class="badge rounded-pill bg-danger" style="font-size: 85%;">Beta</span></sup>
                                        </span>
                                    </span>
                                </a>
                            </li>
                        @endif
                    @endcan
                    @can('locations.listing')
                        @if (is_array(auth()->user()->dealers) && count(auth()->user()->dealers) > 1)
                            <li class="nav-item">
                                <a href="{{route('dealers.for-user')}}" class="nav-link" id="dealers-for-user">
                                    <i class="icon-map4"></i><span> {{ trans_choice('ui.location', 2) }}</span>
                                </a>
                            </li>
                        @endif
                    @endcan
                @endif

                @if (auth()->user()->isSuperAdmin() || auth()->user()->canManageTask() || auth()->user()->isTmMember())
                    <li class="nav-item">
                        <a data-toggle="collapse" href="#taskManager" role="button" aria-expanded="false" aria-controls="taskManager" class="nav-link">
                            <i class="icon-task"></i> <span> Task Manager </span>
                            <i class="ml-1 mt-1 fas fa-chevron-down" style="font-size: 10px"></i>
                        </a>
                    </li>
                    <ul class="nav nav-sidebar collapse" id="taskManager" style="background: #383838;">
                        @can("members.listing")
                        <li class="nav-item">
                            <a href="{{route('task-manager.members.index')}}" class="nav-link" id="task-manager-members.index">
                                <i class="icon-users"></i> <span> Members </span>
                            </a>
                        </li>
                        @endcan
                        @can("tasks.listing")
                        <li class="nav-item">
                            <a href="{{route('task-manager.tasks.index')}}" class="nav-link" id="task-manager-tasks.index">
                                <i class="icon-task"></i> <span> Tasks </span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                @endif


                @if (auth()->user()->isSuperAdmin())
                    <li class="nav-item">
                        <a data-toggle="collapse" href="#facebook" role="button" aria-expanded="false" aria-controls="facebook" class="nav-link">
                            <i class="icon-facebook"></i> <span> FB </span>
                            <i class="ml-1 mt-1 fas fa-chevron-down" style="font-size: 10px"></i>
                        </a>
                    </li>
                    <ul class="nav nav-sidebar collapse" id="facebook" style="background: #383838;">
                        <li class="nav-item">
                            <a href="{{ route('dealers.fbPosts') }}" class="nav-link" id="dealers-fbPosts">
                                <i class="icon-circle-small"></i> <span> FB Posts </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dealers.fbMkError') }}" class="nav-link" id="dealers-fbMkError">
                                <i class="icon-circle-small"></i> <span> FB MK Error </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dealers.fbPostsItem') }}" class="nav-link" id="dealers-fbPostsItem">
                                <i class="icon-circle-small"></i> <span> FB Item Posts </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dealers.fbItemMkError') }}" class="nav-link" id="dealers-fbItemMkError">
                                <i class="icon-circle-small"></i> <span> FB Item Errors </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dealers.fbAppLog') }}" class="nav-link" id="dealers-fbAppLog">
                                <i class="icon-circle-small"></i> <span> FB App Log </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dealers.missedFBModel') }}" class="nav-link" id="dealers-missedFBModel">
                                <i class="icon-circle-small"></i> <span>MissedFBModel </span>
                            </a>
                        </li>
                    </ul>
                @endif

                @can('users.listing')
                    <li class="nav-item">
                        <a href="{{route('users')}}" class="nav-link" id="users">
                            <i class="icon-man"></i> <span> Users  </span>
                        </a>
                    </li>
                @endcan
                    @can('settings.view')
                        <li class="nav-item">
                            <a class="nav-link" href="#settings" data-toggle="collapse" role="button"
                                aria-expanded="false" aria-controls="collapseExample">
                                <i class="icon-cog3"></i> <span> Settings   </span> <i
                                    class="ml-1 mt-1 fas fa-chevron-down" style="font-size: 10px"></i>
                            </a>
                        </li>
                    @endcan
                    @if(auth()->user()->type === 'super_admin')
                        <ul class="nav nav-sidebar collapse" id="settings" style="background: #383838;">
                            <li class="nav-item" style="padding-bottom: 0;">
                                <a href="{{route('settings.manage')}}" class="nav-link" id="monezftp-settings">
                                    <i class="icon-folder"></i> <span> @lang("ui.monezftp_settings.title") </span>
                                </a>
                            </li>

                            <li class="nav-item" style="padding-bottom: 0;">
                                <a href="{{route('system-notifications.index')}}" class="nav-link"
                                   id="system-notifications">
                                    <i class="icon-bell3"></i> <span> @lang('ui.menu.system_notifications') </span>
                                </a>
                            </li>
                            @canany(['watermarks.listing', 'expenses.listings', 'provinces.listing', 'makes.listing'])
                                @can('watermarks.listing')
                                    <li class="nav-item" style="padding-bottom: 0">
                                        <a href="{{route('watermarks.index')}}" class="nav-link" id="watermarks-index">
                                            <i class="icon-image2"></i> <span>@lang('ui.watermark')</span>
                                        </a>
                                    </li>
                                    {{--@elseif(userCanSeeWatermarkSettings())
                                    <li class="nav-item" style="padding-bottom: 0">
                                        <a href="{{route('watermarks.index')}}" class="nav-link" id="watermarks-index">
                                            <i class="icon-image2"></i> <span>@lang('ui.watermark')</span>
                                        </a>
                                    </li>--}}
                                @endcan

                                @can('expenses.listing')
                                    <li class="nav-item" style="padding-bottom: 0">
                                        <a href="{{route('accounts.expenses.manage')}}" class="nav-link" id="expenses-manage">
                                            <i class="icon-coin-dollar"></i> <span>@lang('ui.expenses')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('makes.listing')
                                    <li class="nav-item">
                                        <a href="{{ route('vehicle-makes.index') }}" class="nav-link" id="vehicle-makes-index">
                                            <i class="icon-car"></i> <span> Vehicle Makes </span>
                                        </a>
                                    </li>
                                @endcan
                                @can('categories.listing')
                                    <li class="nav-item">
                                        <a href="{{ route('categories.manage') }}" class="nav-link" id="categories-manage">
                                            <i class="icon-list-numbered"></i> <span> @lang('category.categories') </span>
                                        </a>
                                    </li>
                                @endcan
                                @can('provinces.listing')
                                    <li class="nav-item">
                                        <a href="{{route('provinces')}}" class="nav-link" id="provinces">
                                            <i class="icon-city"></i> <span> Provinces   </span>
                                        </a>
                                    </li>
                                @endcan
                            @endcanany
                            @if(auth()->user()->type === 'super_admin')
                                <li class="nav-item"><a href="{{route('getApiOutput')}}" class="nav-link" id="api-output">
                                        <i class="icon-profile"></i> <span> @lang('ui.menu.vin_decoder')  </span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    @endif
                 @if(userCanSeeSalesMenu() && auth()->user()->can('accounting.listing'))
                  <li class="nav-item">
                      <a data-toggle="collapse" href="#account-sales" role="button" aria-expanded="false" aria-controls="collapseExample" class="nav-link" id="sales-contracts">
                         <i class="icon-clippy"></i> <span> @lang('ui.menu.accounting') </span> <i class="ml-1 mt-1 fas fa-chevron-down" style="font-size: 10px"></i>
                      </a>
                  </li>
                   <ul class="nav nav-sidebar collapse" id="account-sales" style="background: #383838;">
                       <li class="nav-item" style="padding-bottom: 0">
                           <a href="{{route('accountings.manage')}}" class="nav-link" id="sales-contracts">
                               <i class="ml-1 icon-credit-card"></i> <span>@lang('ui.menu.accountings')</span>
                           </a>
                       </li>
                       @can('payment_histories.listing')
                       <li class="nav-item" style="padding-bottom: 0">
                           <a href="{{route('payments.histories.manage')}}" class="nav-link" id="deposit-receipts">
                               <i class="ml-1 icon-paypal"></i> <span>@lang('ui.menu.payment_histories')</span>
                           </a>
                       </li>
                       @endcan
                       @can('expense_histories.listing')
                       <li class="nav-item" style="padding-bottom: 0">
                           <a href="{{route('expenses.histories.manage')}}" class="nav-link" id="deposit-receipts">
                               <i class="ml-1 icon-coin-dollar"></i> <span>@lang('ui.menu.expenses_histories')</span>
                           </a>
                       </li>
                       @endcan
                   </ul>
                @endif

                @if(auth()->user()->can('payments.listing') && !auth()->user()->isSuperAdmin())
                    @if($isSalesContractEnabled && auth()->user()->isActivePayments())
                        <li class="nav-item">
                            <a data-toggle="collapse" href="#sales-payments" role="button" aria-expanded="false" aria-controls="collapseExample" class="nav-link" id="plugins-payments-manage">
                            <i class="icon-credit-card"></i> <span> @lang('payment.sales_payment') </span> <i class="ml-1 mt-1 fas fa-chevron-down" style="font-size: 10px"></i>
                            </a>
                        </li>
                         <ul class="nav nav-sidebar collapse" id="sales-payments" style="background: #383838;">
                             <li class="nav-item" style="padding-bottom: 0">
                                 <a href="{{route('plugins::payments.manage')}}" class="nav-link" id="plugins-payments-manage">
                                     <i class="ml-1 icon-credit-card2"></i> <span>@lang('payment.payments')</span>
                                 </a>
                             </li>
                             <li class="nav-item" style="padding-bottom: 0">
                                 <a href="{{route('plugins::payments.histories.manage')}}" class="nav-link" id="plugins-payments-histories-manage">
                                     <i class="ml-1 icon-wallet"></i> <span> @lang('payment.histories')</span>
                                 </a>
                             </li>
                         </ul>
                    @endif
                @endif

                @if(auth()->user()->can('documents.listing') && !auth()->user()->isSuperAdmin() && !auth()->user()->hasFinanceCompanyDealer())
                    @if($isSalesContractEnabled)
                        <li class="nav-item">
                            <a href="{{ route('documents-list.index') }}" class="nav-link" id="documentations">
                                <i class="icon-files-empty2"></i> <span> @lang('ui.menu.documentations_full')  </span>
                            </a>
                        </li>
                    @endif
                @endif

                @if(auth()->user()->can('leads.listing') && !auth()->user()->isSuperAdmin())
                    @if((userCanSeeLeadSection() && $isSalesContractEnabled) || auth()->user()->isSuperAdmin())
                        <li class="nav-item">
                            <a data-toggle="collapse" href="#leadsExamples" role="button" aria-expanded="false" aria-controls="leadsExamples" class="nav-link" id="leads">
                                <i class="icon-file-stats2"></i> <span> @lang('ui.menu.leads') <!-- <span class="badge badge-primary beta-badge">@lang('ui.menu.beta')</span> --> </span>
                                <i class="ml-1 mt-1 fas fa-chevron-down" tyle="font-size: 10px"></i>
                            </a>
                        </li>

                        <ul class="nav nav-sidebar collapse" id="leadsExamples" style="background: #383838;">
                            <li class="nav-item" style="padding-bottom: 0">
                                <a href="{{ route('leads.index') }}" class="nav-link" id="leads">
                                    <i class="icon-shutter"></i> <span>@lang('ui.menu.general') <!-- <span class="badge badge-primary beta-badge">@lang('ui.menu.beta')</span> --> </span>
                                </a>
                            </li>
                            <li class="nav-item" style="padding-bottom: 0">
                                <a href="{{ route('leads.contact_request') }}" class="nav-link" id="leads">
                                    <i class="fas fa-address-book"></i> <span>@lang('ui.menu.contact_request') <!-- <span class="badge badge-primary beta-badge">@lang('ui.menu.beta')</span> --> </span>
                                </a>
                            </li>
                            <li class="nav-item" style="padding-bottom: 0">
                                <a href="{{ route('finance-request') }}" class="nav-link" id="leads">
                                    <i class="icon-coins"></i> <span>@lang('ui.menu.finance')</span>
                                </a>
                            </li>
                            <li class="nav-item" style="padding-bottom: 0">
                                <a href="{{ route('finance') }}" class="nav-link" id="leads">
                                    <i class="fas fa-chart-line"></i> <span>@lang('ui.menu.prime_finance')</span>
                                </a>
                            </li>
                        </ul>
                    @endif
                @endif

                @if (userCanSeeCarFinderSection())
                <li class="nav-item">
                    <a data-toggle="collapse" href="#carfinder" role="button" aria-expanded="false" aria-controls="carfinder" class="nav-link" id="carfinderlist">
                        <i class="fa fa-car" aria-hidden="true" style="font-size: 16px;"></i> <span> @lang('ui.menu.car_finder')</span>
                        <i class="ml-1 mt-1 fas fa-chevron-down" tyle="font-size: 10px"></i>
                    </a>
                    <ul class="nav nav-sidebar collapse" id="carfinder" style="background: #383838;">
                        <li class="nav-item" style="padding-bottom: 0">
                            <a href="{{ route('car-finder-scrapper.index') }}" class="nav-link" id="car-finder-scrapper">
                                <i class="icon-circle-small" style="font-size: 16px;"></i> <span> @lang('ui.menu.car_finder_scrapper')</span>
                            </a>
                        </li>
                        <li class="nav-item" style="padding-bottom: 0">
                            <a href="{{ route('car-finder.index') }}" class="nav-link" id="car-finder">
                                <i class="icon-circle-small" style="font-size: 16px;"></i> <span> @lang('ui.menu.car_finder_result') </span>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                @if(auth()->user()->can('contacts.listing') && !auth()->user()->isSuperAdmin())
                    <li class="nav-item">
                        <a href="{{ route('contacts.index') }}" class="nav-link" id="contacts-index">
                            <i class="icon-user-tie"></i> <span> @lang('ui.menu.contacts') </span>
                        </a>
                    </li>
                @endif

                @if (!auth()->user()->isSuperAdmin() && auth()->user()->canViewPackage())
                    <li class="nav-item">
                        <a href="{{ route('user.package') }}" class="nav-link" id="contacts-index">
                            <i class="icon-box"></i> <span> @lang('ui.menu.package') </span>
                        </a>
                    </li>
                @endif

                {{-- @can('invitations.listing') --}}
                @if (auth()->user()->can('invitations.listing') && ($isuserCanSeeInvitationsSection))
                    <li class="nav-item">
                        <a href="{{route('invitations.manage')}}" class="nav-link" id="invitations-manage">
                            <i class="icon-user-plus"></i><span>{{__('invitations.menu.invitations')}}</span>
                        </a>
                    </li>
                @endif
                {{-- @endcan --}}

                @if(auth()->user()->can('offers.listing') && ($isDealerExchangesEnabled || !auth()->user()->isDealer()))
                    <li class="nav-item">
                        <a href="{{route('offers.manage')}}" class="nav-link" id="offers-manage">
                            <i class="fas fa-handshake" style="font-size: 16px;"></i><span>{{__('ui.menu.offers')}}</span>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->can('transporter_requests.listing') && ($isDealerTransportRequestEnabled || !auth()->user()->isDealer()))
                    <li class="nav-item">
                        <a href="{{route('transporters.requests.manage')}}" class="nav-link" id="transporters-requests.manage">
                            <i class="icon-truck"></i><span>{{__('ui.menu.transporter_request')}}</span>
                        </a>
                    </li>
                @endif

                @can('logs.listing')
                    <li class="nav-item">
                        <a data-toggle="collapse" href="#logs" role="button" aria-expanded="false" aria-controls="logs" class="nav-link">
                            <i class="icon-list-unordered"></i> <span> @lang('ui.menu.logs') </span>
                            <i class="ml-1 mt-1 fas fa-chevron-down" style="font-size: 10px"></i>
                        </a>
                    </li>

                    <ul class="nav nav-sidebar collapse" id="logs" style="background: #383838;">
                        <li class="nav-item">
                            <a href="{{route('user-logs')}}" class="nav-link" id="user-logs">
                                <i class="icon-profile"></i> <span> User Logs   </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('cars.mk-logs.manage')}}" class="nav-link" id="cars-mk-logs.manage">
                                <i class="fa fa-recycle" style="font-size: 16px;"></i> <span> {{__('ui.mk_logs')}}   </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('dealers.mk-boost-log')}}" class="nav-link" id="dealers-mk-boost-log">
                                <i class="icon-profile"></i> <span> MK Boost Logs </span>
                            </a>
                        </li>
                        @if(auth()->user()->type === 'super_admin')
                        <li class="nav-item">
                            <a href="{{route('leads.status-history')}}" class="nav-link" id="leads-status-history">
                                <i class="icon-profile"></i> <span> @lang('ui.deal.status_history') </span>
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->type === 'super_admin')
                        <li class="nav-item">
                            <a href="{{route('dealers.impact-cielocom-log')}}" class="nav-link" id="impact-cielocom-log">
                                <i class="icon-profile"></i> <span> @lang('ui.deal.impact_cielocom_log') </span>
                            </a>
                        </li>
                        @endif
                    </ul>
                @endcan



                @if(auth()->user()->type === 'admin')
                    <li class="nav-item">
                        <a href="{{route('leads.status-history')}}" class="nav-link" id="leads-status-history">
                            <i class="icon-profile"></i> <span> @lang('ui.deal.status_history') </span>
                        </a>
                    </li>
                @endif

                @can('supports.listing')
                    @if(auth()->user()->type !== 'super_admin' && !auth()->user()->isIndividual() && !auth()->user()->hasFinanceCompanyDealer())

                        <li class="nav-item">
                            <a href="{{route('supports')}}" class="nav-link" id="supports">
                                <i class="icon-headset"></i> <span> @lang('ui.menu.support')  </span>
                            </a>
                        </li>
                    @endif
                @endcan

                <li class="nav-item">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="nav-link">
                        <i class="icon-switch2"></i><span>@lang('ui.auth.logout')</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    $('.nav-link').on('click', function () {
        let menu = $(this).attr('aria-expanded') === 'true'
        const icon = $(this).find('.fas')
        if (menu) {
            icon.removeClass('fa-chevron-up')
            icon.addClass('fa-chevron-down')
        } else {
            icon.removeClass('fa-chevron-down')
            icon.addClass('fa-chevron-up')
        }
    })
    $('.nav-link.sidebar-control').on('click', function () {
		$('#nav-icon4').toggleClass('open');
    })
</script>

<style>
    .sidebar-dark .nav-sidebar.show > .nav-item > .nav-link {
        padding-left: 50px !important;
    }

    @media (min-width: 768px) {
        .sidebar-xs .sidebar-main .nav-sidebar > .nav-item > .nav-link {
            -ms-flex-pack: center;
            justify-content: left;
            padding-left: 13px !important;
            padding-right: 0;
        }

        .sidebar-xs .sidebar-main .nav-sidebar.show > .nav-item > .nav-link {
            padding-left: 20px !important;
        }
    }

    .responsive .datatable-ajax th {
        width: 1% !important;
    }

    .responsive .datatable-ajax th.check-all {
        width: 0% !important;
    }
    .nav-link {
        position: relative;
        display: inline-block;
        border-bottom: 1px dotted black;
    }
    .tooltiptext {
        visibility: hidden;
        position: absolute;
        width: 120px;
        background-color: #555;
        color: #fff;
        text-align: center;
        padding: 5px 0;
        border-radius: 6px;
        z-index: 1;
        opacity: 0;
        transition: opacity 0.3s;
        display: block !important;
    }
    .tooltiptext::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 100%;
        margin-top: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: transparent #555 transparent transparent;
    }
    .sidebar-xs .nav-link:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }
    .tooltip-right {
        top: 10px;
        left: 110%;
    }
</style>
