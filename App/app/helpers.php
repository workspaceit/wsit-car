<?php

use App\Helpers\Scraping as ScrapingHelper;
use App\Models\Car;
use App\Models\Dealer;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\OfferMessage;
use App\Models\UserLog;
use App\Models\Customer;
use App\Models\Status;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use QL\QueryList;
use SimpleUserAgent\UserAgent;
use App\Helpers\IdenticalContact;

function getCurl($link)
{

    $client = new \GuzzleHttp\Client();

    $response = $client->request('GET', $link);
    $results = ($response->getBody());

    $results = [];
    if ($response->getStatusCode() == 200) {
        $results = json_decode($response->getBody());
    }
    return $results;

}



function getPriceRange($price)
{

    $price_range = 0;
    if ($price < 4000)
        $price_range = '4K';
    else if ($price >= 4000 && $price < 6000)
        $price_range = '4K - 6K';
    else if ($price >= 6000 && $price < 8000)
        $price_range = '6K - 8K';
    else if ($price >= 8000 && $price < 10000)
        $price_range = '8K - 10K';
    else if ($price >= 10000 && $price < 12000)
        $price_range = '10K - 12K';
    else if ($price >= 12000 && $price < 14000)
        $price_range = '12K - 14K';
    else if ($price >= 14000 && $price < 16000)
        $price_range = '14K - 16K';
    else if ($price >= 16000)
        $price_range = '16K +';
    else
        $price_range = '16K +';

    return $price_range;


}

if (!function_exists('slug')) {

    function slug($string)
    {
        return preg_replace('/\s+/u', '-', trim($string));
    }
}


function destroyFile($files)
{

    if (!is_array($files))
        $files = [$files];
    foreach ($files as $file) {
        if (!empty($file) and File::exists(public_path($file)))
            File::delete(public_path($file));
    }

}


function cached($index = 'settings', $col = false)
{
    // Cache::forget('settings');
    $cache['settings'] = Cache::remember('settings', 60 * 48, function () {
        return \App\Models\Setting::first();
    });

    if (!isset($cache[$index]))
        return $index;
    if (!$col)
        return $cache[$index];
    return $cache[$index]->{$col};

}


if (!function_exists('uuid4')) {
    function uuid4()
    {
        return \Ramsey\Uuid\Uuid::uuid4();
    }
}


if (!function_exists('uniqe_file_name')) {
    function uniqe_file_name($ext = 'png')
    {
        $name = $unique_id = substr(base_convert(time(), 10, 36) . md5(microtime()), 0, 40) . '.' . $ext;
        return $name;
    }
}


if (!function_exists('upload_path')) {
    function upload_path($path = '')
    {
        return '/uploads/' . $path;
    }
}

function dispatchLumen($job_class, $job_arguments)
{
    dispatch(new \App\Jobs\DispatchLumenJob($job_class, $job_arguments));
}


if (!function_exists('array_key_last')) {
    /**
     * Polyfill for array_key_last() function added in PHP 7.3.
     *
     * Get the last key of the given array without affecting
     * the internal array pointer.
     *
     * @param array $array An array
     *
     * @return mixed The last key of array if the array is not empty; NULL otherwise.
     */
    function array_key_last($array)
    {
        $key = NULL;

        if (is_array($array)) {

            end($array);
            $key = key($array);
        }

        return $key;
    }
}


if (!function_exists('word_limit')) {
    function word_limit($string, $limit = 1, $separator = ' ..')
    {
        return \Illuminate\Support\Str::words($string, $limit, $separator);
    }
}


function normalize_path($path)
{
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('|(?<=.)/+|', '/', $path);
    if (':' === substr($path, 1, 1)) {
        $path = ucfirst($path);
    }
    return $path;
}


function checkForAndMakeDirs($connection, $file)
{
    $origin = ftp_pwd($connection);
    $parts = explode("/", dirname($file));

    foreach ($parts as $curDir) {
        // Attempt to change directory, suppress errors
        if (@ftp_chdir($connection, $curDir) === false) {
            ftp_mkdir($connection, $curDir); //directory doesn't exist - so make it
            ftp_chdir($connection, $curDir); //go into the new directory
        }
    }

    //go back to the origin directory
    ftp_chdir($connection, $origin);
}

function ftpErrorMessage($errorMessage, $dealer, $ftpServer, $ftpType='') {

    return "<div>
                <h2 style='color:red;'> Error: $errorMessage </h2>
                <p>
                    Dealer ID: <b>".$dealer->id."</b>  <br/>
                    Dealer Name: <b>".$dealer->name."</b>  <br/>
                    Server: <b>$ftpServer</b>  <br/>
                    FTP Type: <b>$ftpType</b>
                </p>
            </div>";
}


function fix_null_value($value)
{
    if (!$value)
        $value = '';

    return $value;

}


function get_base_url($url)
{

    $url_info = parse_url($url);
    return $url_info['scheme'] . '://' . $url_info['host'];
}


function logBug($error)
{

    $message = json_encode($error);
    \Log::error($message);
    \Mail::send((new \App\Mail\SendLogError(compact('message'))));

}

/**
 * Correct french accent
 */
if (!function_exists('correct_accent')) {
    function correct_accent($string)
    {
        $string = html_entity_decode($string, ENT_QUOTES, "utf-8");
        $breaks = array("<br />","<br>","<br/>");
        $string = str_ireplace($breaks, "\r\n", $string);
        return $string;
    }
}


function test($url)
{
    $client = new \GuzzleHttp\Client();
    $response = $client->request('GET', $url);
    $response_status_code = $response->getStatusCode();
    $html = $response->getBody()->getContents();
    dd($html);

}

/**
 * remove unnecessary thing after file type url,
 * Ex. .jpeg-1024x768 --- -1024x768 will be removed thus .jpeg will remain only
 * @param $string
 * @return final
 */

function cleanImageFileType($string)
{
    $section = explode('.', $string);
    $last_section = end($section);
    $last_section = explode('-', $last_section);
    $section[count($section) - 1] = $last_section[0];
    $final  = implode('.', $section);

    return $final;
}

/**
 * This function will update webp photo string jpg, to support certain senarios
 * The modified url is comfigured in server side
 * When hitting to the url, webp images will be rendered to jpg
 * @param $photos : It should be type string and  comma(,) separated
 * @return modifiedPhotos string
 */
function photosWebpToJpg($photos){

    $photos = explode(',', $photos);
    $modifiedPhotos = [];

    foreach ($photos as $photo) {

        /**
         * Check for multiple sub-string in string
         * "/i" is for sub string check
         * "\b" is for exact sub-string check
         */
        if (preg_match('/\bcdn.monezsoft.ca\b|\bcdn.drivegood.com\b/i', $photo)) {
            if (strpos($photo, 'webp') !== false) {

                $modifiedPhotos[] = photoWebpToJpg($photo);
            } else {
                $modifiedPhotos[] = $photo;
            }
        } else {
            $modifiedPhotos[] = $photo;
        }
    }

    $photos = implode(',', $modifiedPhotos);

    return $photos;
}

/**
 * This function will remove parameters from photo URLs
 * @param $photos : It should be type string and  comma(,) separated
 * @return modifiedPhotos string
 */
function photosRemoveParams($photos){

    $photos = explode(',', $photos);
    $modifiedPhotos = [];

    foreach ($photos as $photo) {

        $modifiedPhotos[] = photoRemoveParams($photo);
    }

    $photos = implode(',', $modifiedPhotos);

    return $photos;
}

function photoRemoveParams($photo){

    $section = explode('.', $photo);
    $last_section = end($section);
    $last_section = explode('?', $last_section);
    $section[count($section) - 1] = $last_section[0];
    $photo  = implode('.', $section);

    return $photo;
}

function LoanToView($loan){

    $form = [];
    foreach ($loan->data as $i => $row) {
        $form[$i]['question'] = $row['text'];
        foreach ($row['cols'] as $col) {
            if($col['type'] == 'text') {
                $form[$i]['answers'][] = [
                    'label' => fix_label($col['name']),
                    'comment' => $row[$col['name']],
                    'type' => $col['type']
                ];
            }
            else if($col['type'] == 'radio')
            {
                $form[$i]['answers'][] = [
                    'label' => fix_label($col['name']),
                    'comment' => $row['responses'][$row[$col['name']]-1 ],
                    'responses' => $row['responses'],
                    'type' => $col['type']
                ];
            }
            else
            {
                $form[$i]['answers'][] = [
                    'label' => fix_label($col['name']),
                    'comment' => $row['responses'][$row[$col['name']]-1 ],
                    'type' => $col['type']
                ];
            }
        }
    }

    return $form;

}

if (!function_exists('fix_label')) {

    function fix_label($string)
    {
        return ucfirst(str_replace('_', ' ', trim($string)));
    }
}

if (!function_exists('aclUser')) {
    /**
     * @param ...$permissions
     * @return mixed
     */
    function aclUser(...$permissions)
    {
        $permissions = collect($permissions);
        $matches = $permissions->filter(function ($permission) {
            return auth()->guard(getUserGuard())->user()->can($permission);
        });

        if ($matches->isEmpty()) {
             abort(403);
        }
    }
}

if (!function_exists('getUserGuard')) {
    /**
     * @return mixed
     */
    function getUserGuard()
    {
        $guards = array_keys(config('auth.guards'));
        return collect($guards)->first(function($guard){
            return auth()->guard($guard)->check();
        });
    }
}

/**
 * Get the seller total notification number
 *
 * @return int
 */
function getSellerTotalNotificationsNumber(): int
{
    $totalOffersMessagesNotifications = OfferMessage::hasSellerMessage()->where('read', 0)->count();
    $totalNewOffersNotifications = Offer::where('offered_to', Auth::guard('web')->user()->id)
        ->where('seller_read', 0)->where('action', Offer::ACTION_PENDING)->count();

    return (int) $totalOffersMessagesNotifications + (int) $totalNewOffersNotifications;
}

/**
 * Get total notification number
 * @return int
 */
function getTotalNotifications()
{
    return (int)  Notification::where('notified_to', auth()->user()->id)->where('seen_by_user', 0)->whereHas('sender')->whereHas('receiver')->count();
}

/**
 * @return mixed
 */
function getNotifications()
{
    return Notification::where('notified_to', auth()->user()->id)->whereHas('sender')->whereHas('receiver')
        ->orderBy('created_at', 'DESC')->orderBy('seen_by_user', 'ASC')->take(5)->get();
}

/**
 * @param     $text
 * @param int $chars
 * @return string
 */
function truncate($text, int $chars = 25): string
{
    if (strlen($text) <= $chars) {
        return $text;
    }

    $text = $text . " ";
    $text = substr($text,0, $chars);
    $text = substr($text,0, strrpos($text,' '));

    return $text . "...";
}

/**
 * Fetch cars list based with old car and dealer id
 *
 * @param $cars
 * @return mixed
 */
function getSelectedCar($cars)
{
    try {
        list($ids, $dealers) = [[], []];
        foreach ($cars as $car){
            array_push($ids, explode('-', $car)[0] ?? null);
            array_push($dealers, explode('-', $car)[1] ?? null);
        }

        return Car::whereIn('dealer_id', $dealers)->whereIn('ID', $ids)->get();
    }catch (Throwable $throwable){
        return collect([]);
    }

    return collect([]);
}

if (!function_exists('getDrivetrainAttribute')) {
    /**
     * Return car derivations
     *
     * @param $derivation
     * @return string
     */
    function getDrivetrainAttribute($derivation): string
    {
        $derivations = ["4X2", "4X4", "AWD", "FWD", "RWD", "OTHER", "NONE"];
        $map = [
            "n/d" => "NONE",
            "N/A" => "NONE",
            "Avant" => "FWD",
            "Arrière" => "RWD",
            "Traction avant" => "FWD",
            "Traction intégrale" => "4X4",
            "other" => "OTHER",
        ];

        if (in_array($derivation, $derivations)) {
            return $derivation;
        }
        if (array_key_exists($derivation, $map)){
            return $map[$derivation] ?? "NONE";
        }
        if (!empty($derivation)){
            return "Other";
        }

        return "NONE";
    }

    function domHTML($email_content){
        $dom = new \DomDocument();

        /**
         * LIBXML_HTML_NOIMPLIED - Turns off automatic adding of implied elements
         * LIBXML_HTML_NODEFDTD - Prevents adding HTML doctype if default not found
         */
        @$dom->loadHtml($email_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        /** I want all the images to upload them locally and replace from base64 to new image name */
        $images = $dom->getElementsByTagName('img');

        foreach ($images as $image) {
            $imageSrc = $image->getAttribute('src');
            /** if image source is 'data-url' */
            if (preg_match('/data:image/', $imageSrc)) {
                /** etch the current image mimetype and stores in $mime */
                preg_match('/data:image\/(?<mime>.*?)\;/', $imageSrc, $mime);
                $mimeType = $mime['mime'];
                /** Create new file name with random string */
                $filename = uniqid(uniqid());

                /** Public path. Make sure to create the folder
                 * public/uploads
                 */
                $folder = "images/email_content";
                if (!File::isDirectory($folder)) {
                    File::makeDirectory($folder, 0775, true, true);
                    $filePath = "$folder/$filename";
                } else {
                    $filePath = "$folder/$filename";
                }

                /** Using Intervention package to create Image */
                Image::make($imageSrc)
                    /** encode file to the specified mimeType / webp */
                    ->encode('webp', 100)
                    ->resize(500, 375, function ($constraint) {
                        $constraint->aspectRatio();
                    })
                    /** public_path - points directly to public path */
                    ->save(public_path($filePath . '.webp'));

                $newImageSrc = asset($filePath . '.webp');
                $image->removeAttribute('src');
                $image->setAttribute('src', $newImageSrc);
            }
        }
        /** Save this new message body in the database table */
        $email_content = $dom->saveHTML();
        return $email_content;
    }

}


/**
 * Fetch docuemnt cache data
 *
 * @param string $name
 * @return void
 */
function getDocumentCache(string $name)
{
    if (\auth()->check()) {
        return Cache::get(sprintf("%s_%s", auth()->user()->username, $name), "");
    }

    return null;
}

/**
 * download file with given name.
 */
function saveFileFromURL($url, $temp_name)
{
    try {
        $contents = file_get_contents($url);

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->buffer(file_get_contents($url));
        $extension = explode('/', $type )[1];

        $fileName = $temp_name.'.'.$extension;
        if ($contents) {
            file_put_contents(public_path('temp/'.$fileName), $contents);
        }

        return public_path('temp/'.$fileName);
    } catch (\Throwable $th) {

        \Log::error('Error Occured when downloading file: '. $th->getMessage());

        return null;
    }
}

/**
 * Get interger from string
 */
function getIntergerFromString($string)
{
    return (int) filter_var($string, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Get Contact Id For Finance Deal Duplication
 */
function getContactIdForFinanceDealDuplication($request, $dealerId)
{
    //Contact duplication check
    $customer = Customer::findOrFail($request->customer_id);
    $request->request->add([
        'first_name' => $customer->first_name,
        'last_name' => $customer->last_name,
        'email' => $customer->email,
        'phone' => $customer->phone,
        'mobile' => $customer->mobile,
    ]);

    $identical    = (new IdenticalContact($request, $dealerId))
            ->getIdenticalContact();

    $createNewContact = false;
    $contact          = $identical['contact'] ?? null;
    $exactMatched     = $identical['exactMatch'] ?? false;

    if (!empty($contact)) {
        if ($exactMatched) { //if contact already exist keep it
            $contactId = $contact->id;
        } else {
            $createNewContact = true;
        }
    } else {
        $createNewContact = true;
    }

    if ($createNewContact) {
        $newCustomerObj = $customer->replicate();
        $newCustomerObj->dealer_id = $dealerId;
        $newCustomerObj->save();

        $contactId = $newCustomerObj->id;
    }

    return $contactId;
}

/**
 * get general deal type with translated deal type values from WP
 */
function simpleDealType($needle)
{
    $arr = [
        "Deals"        => ["Deals", "Pas intéressé par le financement"],
        "finance-form" => ["finance-form", "Intéressé par le financement", "Interested in financing"],
    ];

    foreach ($arr as $key => $value) {
        if (array_search($needle, $value) !== false) {
            return $key;
        }
    }
}

function getFileMimeType($link)
{
    return pathinfo($link, PATHINFO_EXTENSION);
}

if (!function_exists('getAuthUserId')) {
    function getAuthUserId()
    {
        return auth()->user()->id;
    }
}

if (!function_exists('removeEmojis')) {
    function removeEmojis($data)
    {
        $symbols = "\x{1F100}-\x{1F1FF}" // Enclosed Alphanumeric Supplement
            ."\x{1F300}-\x{1F5FF}" // Miscellaneous Symbols and Pictographs
            ."\x{1F600}-\x{1F64F}" //Emoticons
            ."\x{1F680}-\x{1F6FF}" // Transport And Map Symbols
            ."\x{1F900}-\x{1F9FF}" // Supplemental Symbols and Pictographs
            ."\x{2600}-\x{26FF}" // Miscellaneous Symbols
            ."\x{2700}-\x{27BF}"; // Dingbats

        return preg_replace('/['. $symbols . ']+/u', '', $data);
    }
}
