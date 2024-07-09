<?php


namespace App\Libraries;


class Provinces
{
    public static function canada() {
        /*****DO NOT CHANGE THE ORDER****/
        return [
            'code' => 'CA',
            'country' => 'Canada',
            'provinces' => [
                "ON" => "Ontario",
                "QC" => "Quebec",
                "AB" => "Alberta",
                "BC" => "British Columbia",
                "MB" => "Manitoba",
                "NB" => "New Brunswick",
                "NL" => "Newfoundland and Labrador",
                "NT" => "Northwest Territories",
                "NS" => "Nova Scotia",
                "NU" => "Nunavut",
                "PE" => "Prince Edward Island",
                "SK" => "Saskatchewan",
                "YT" => "Yukon"
            ]
        ];
    }

    public static function us() {
        /*****DO NOT CHANGE THE ORDER****/
        return [
            'code' => 'US',
            'country' => 'USA',
            'provinces' => [
                'AL' => 'Alabama',
                'AK' => 'Alaska',
                'AZ' => 'Arizona',
                'AR' => 'Arkansas',
                'CA' => 'California',
                'CO' => 'Colorado',
                'CT' => 'Connecticut',
                'DE' => 'Delaware',
                'DC' => 'Washington DC',
                'FL' => 'Florida',
                'GA' => 'Georgia',
                'HI' => 'Hawaii',
                'ID' => 'Idaho',
                'IL' => 'Illinois',
                'IN' => 'Indiana',
                'IA' => 'Iowa',
                'KS' => 'Kansas',
                'KY' => 'Kentucky',
                'LA' => 'Louisiana',
                'ME' => 'Maine',
                'MD' => 'Maryland',
                'MA' => 'Massachusetts',
                'MI' => 'Michigan',
                'MN' => 'Minnesota',
                'MS' => 'Mississippi',
                'MO' => 'Missouri',
                'MT' => 'Montana',
                'NE' => 'Nebraska',
                'NV' => 'Nevada',
                'NH' => 'New Hampshire',
                'NJ' => 'New Jersey',
                'NM' => 'New Mexico',
                'NY' => 'New York',
                'NC' => 'North Carolina',
                'ND' => 'North Dakota',
                'OH' => 'Ohio',
                'OK' => 'Oklahoma',
                'OR' => 'Oregon',
                'PA' => 'Pennsylvania',
                'PR' => 'Puerto Rico',
                'RI' => 'Rhode Island',
                'SC' => 'South Carolina',
                'SD' => 'South Dakota',
                'TN' => 'Tennessee',
                'TX' => 'Texas',
                'UT' => 'Utah',
                'VT' => 'Vermont',
                'VI' => 'Virgin Islands',
                'VA' => 'Virginia',
                'WA' => 'Washington',
                'WV' => 'West Virginia',
                'WI' => 'Wisconsin',
                'WY' => 'Wyoming'
            ]
        ];
    }
}
