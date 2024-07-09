<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Dealer;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        if (auth()->user()->isBuyer()){
            return redirect()->route('advance-search.manage');
        }
        if (auth()->user()->isTransporter()){
            return redirect()->route('transporters.requests.manage');
        }

        if (in_array(auth()->user()->type, [User::TYPE_VIEWER, User::TYPE_INDIVIDUAL, User::TYPE_DEALER, User::TYPE_ADMIN, User::TYPE_TEAM_MEMBER])) {
            return redirect()->route(auth()->user()->canManageDashboard() ? 'dealers.dashboard' : ( auth()->user()->canManageVehicles() ? 'cars.for-user' : (auth()->user()->canManageProducts() ? 'inventory.others.manage' : 'supports')));
        }

        $count_dealers = Dealer::count();
        $count_cars = Car::count();

        if (Auth::user()->type != 'super_admin') {
            $dealers = Auth::user()->dealers;
            $dealers[] = Dealer::whereIn('id', $dealers)->pluck('dealers');
            $dealers = array_flatten($dealers);
            $count_cars = Car::where('user_id', Auth::id())->orWhereIn('dealer_id', $dealers)->count();
        }

        aclUser('dashboard.index');
        return view('home', compact('count_dealers', 'count_cars'));
    }

    public function userPackage()
    {
        return view('package.manage');
    }

    public function onetimeOperation(Request $req)
    {
        /**
         * This is etup to manually convert a dealer's cars images to webp
         */
        if ($req->dealer_id && $req->token === md5("reputation")) {

            echo '--- Started ---<br>';
            $cars = Car::withTrashed()->where('dealer_id', $req->dealer_id)->get();

            foreach ($cars as $car) {

                if ($car->photos) {

                    dispatch(new \App\Jobs\ConvertCarImagesToWebpJob( $req->dealer_id, $car->ID))->onQueue('cars_to_db');
                    Log::info('ConvertCarImagesToWebpJob dispatched for ID: '.$car->ID.' dealer_id: '.$car->dealer_id);

                    echo 'job created for ID: '.$car->ID.' dealer_id: '.$car->dealer_id.'<br>';
                }
            }

            echo '--- End ---';
        } else {

            abort(Response::HTTP_FORBIDDEN, 'You don\'t have access on this page.');
        }

    }
}
