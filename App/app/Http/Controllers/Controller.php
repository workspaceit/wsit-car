<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $request;

    public function __construct()
    {

        $this->request = request();

        $agent = new \stdClass();
        if(!is_null($this->request->route()))
        $agent->current_route = $this->request->route()->getName();

        view()->share('agent', $agent);

       // $this->request['Auth-Role'] = $this->getRole();
    }


}
