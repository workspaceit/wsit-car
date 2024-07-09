<?php

namespace App\Http\View\Composers;

use App\Models\Customer;
use App\Models\Dealer;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class SidebarComposer
{
    public function compose(View $view)
    {
        $isDealerExchangesEnabled = false;
        $isDealerTransportRequestEnabled = false;
        $isSalesContractEnabled = Auth::check() && Auth::user()->can('view', Customer::class);
        $isuserCanSeeInvitationsSection = false;

        if (auth()->check() && auth()->user()->isDealer()){
            $isDealerExchangesEnabled = Dealer::whereIn('id', (array) auth()->user()->dealers ?? [])
                ->where('active', true)->where('exchanges', true)->exists();

            $isDealerTransportRequestEnabled = Dealer::whereIn('id', (array) auth()->user()->dealers ?? [])
                ->where('active', true)->where('transport_request', true)->exists();

            $isuserCanSeeInvitationsSection = Dealer::whereIn('id', (array) auth()->user()->dealers ?? [])
                ->where('active', true)->where('invitations', 1)->exists();
        }

        $view->with(compact('isSalesContractEnabled', 'isDealerExchangesEnabled', 'isDealerTransportRequestEnabled', 'isuserCanSeeInvitationsSection'));
    }
}
