<?php

namespace App\Libraries\Support\World;

use phpDocumentor\Reflection\Types\Self_;

class Cities
{
    /**
     * Postman country cities & states api
     * https://countriesnow.space/api/v0.1/countries/states
     *
     * @return mixed
     */
    public static function getResponseBodies()
    {
        return json_decode(file_get_contents(storage_path(sprintf('app%sworld%scities.json', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR))), true);
    }

    /**
     * List the all states of world
     *
     * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection
     */
    public static function getCitiesResponse()
    {

        $cities = collect();
        $results = self::getResponseBodies();

        foreach ($results['data'] as $data){
            foreach ($data["cities"] as $city) {
                $cities->push(collect(['id' => $city, 'text' => $city, 'type' => 'city', 'country' => $data['country']]));
            }
        }

        return $cities;
    }
}
