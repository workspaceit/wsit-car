<?php

namespace App\Http\Controllers\Panels\Dealer;

use App\Http\Controllers\Controller;
use App\Libraries\Support\DateUtilities;
use App\Models\Car;
use App\Models\User;
use App\Models\Dealer;
use App\Models\FinanceForm;
use App\Models\Lead;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Plugins\Products\Models\Product;
use Plugins\TaskManager\Models\Tasks;
use Symfony\Contracts\Service\Attribute\Required;

class DashboardController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function dashboard()
    {
        if (!in_array(auth()->user()->type, [User::TYPE_VIEWER, User::TYPE_INDIVIDUAL, User::TYPE_DEALER, User::TYPE_ADMIN, User::TYPE_TEAM_MEMBER]) || !auth()->user()->canManageDashboard()) {
            abort(403);
        }

        $sources = __('dashboard.sources');
        $dealers = (array) auth()->user()->dealers ?? [];

        $total_cars = Car::whereIn('dealer_id', $dealers)
            ->whereHas('dealer', function($q) use ($dealers){
                foreach ($dealers as $id) {
                    $q->whereHas('setting', function($q1){
                        $q1->where('no_vehicles', false);
                    });
                }
            })
            ->whereIn('status', [19, 29])->whereNotNull('photos')->whereNotNull('photo')->whereNotNull('car_vin')->count() ?? 0;

        $total_item = Product::whereIn('dealer_id', $dealers)->whereIn('status_id', [19, 29])->whereNotNull('photos')->count() ?? 0;

        $interval_one   = [DateUtilities::subDaysFromToday(), Carbon::parse(Carbon::now()->subDays(14)->format('Y-m-d'))->toDateTimeString()];
        $interval_two   = [DateUtilities::subDaysFromToday(15), Carbon::parse(Carbon::now()->subDays(29)->format('Y-m-d'))->toDateTimeString()];
        $interval_three = [DateUtilities::subDaysFromToday(30), Carbon::parse(Carbon::now()->subDays(44)->format('Y-m-d'))->toDateTimeString()];
        $interval_four  = [DateUtilities::subDaysFromToday(45), Carbon::parse(Carbon::now()->subDays(59)->format('Y-m-d'))->toDateTimeString()];
        $interval_five  = [DateUtilities::subDaysFromToday(60), Carbon::parse(Carbon::now()->subYear(Carbon::now()->format('Y'))->format('Y-m-d'))->toDateTimeString()];

        $months = (new DateUtilities)->getMonths();
        $ids  = sprintf("(%s)", implode(',', $dealers));
        $data = DB::select("SELECT
           SUM(CASE WHEN created_at BETWEEN '" . $interval_one[1] . "' AND '" . $interval_one[0] . "'THEN 1 ELSE 0 END) invt_one,
           SUM(CASE WHEN created_at BETWEEN '" . $interval_two[1] . "' AND '" . $interval_two[0] . "'THEN 1 ELSE 0 END) invt_two,
           SUM(CASE WHEN created_at BETWEEN '" . $interval_three[1] . "' AND '" . $interval_three[0] . "'THEN 1 ELSE 0 END) invt_three,
           SUM(CASE WHEN created_at BETWEEN '" . $interval_four[1] . "' AND '" . $interval_four[0] . "'THEN 1 ELSE 0 END) invt_four,
           SUM(CASE WHEN created_at BETWEEN '" . $interval_five[1] . "' AND '" . $interval_five[0] . "'THEN 1 ELSE 0 END) invt_five
        FROM cars where status = '29' AND deleted_at  IS NULL AND photos IS NOT NULL AND photo IS NOT NULL AND car_vin IS NOT NULL AND dealer_id IN $ids;");

        $product_data = DB::select("SELECT
           SUM(CASE WHEN created_at BETWEEN '" . $interval_one[1] . "' AND '" . $interval_one[0] . "'THEN 1 ELSE 0 END) invt_one,
           SUM(CASE WHEN created_at BETWEEN '" . $interval_two[1] . "' AND '" . $interval_two[0] . "'THEN 1 ELSE 0 END) invt_two,
           SUM(CASE WHEN created_at BETWEEN '" . $interval_three[1] . "' AND '" . $interval_three[0] . "'THEN 1 ELSE 0 END) invt_three,
           SUM(CASE WHEN created_at BETWEEN '" . $interval_four[1] . "' AND '" . $interval_four[0] . "'THEN 1 ELSE 0 END) invt_four,
           SUM(CASE WHEN created_at BETWEEN '" . $interval_five[1] . "' AND '" . $interval_five[0] . "'THEN 1 ELSE 0 END) invt_five
        FROM products where status_id = '29' AND deleted_at  IS NULL AND photos IS NOT NULL AND dealer_id IN $ids;");

        $report = (array) ($data[0] ?? $data);
        $product_report = (array) ( $product_data[0] ?? $product_data);
        // $creators = Tasks::getTmTaskCreators();
        $assigner = auth()->user()->id;
        $totalPendingTask = Tasks::where("assigner_id", $assigner)
            ->whereIn("status_id", [21, 31])->count();

        $leads = Lead::countPreviousSevenDaysTotalDeals();
        $finances = FinanceForm::countPreviousSevenDaysTotalPrimeFinances();
        $today_total_leads = $leads->union($finances)->count();
        return view('panels.dealer.dashboard', compact('total_cars', 'total_item' , 'report', 'product_report', 'today_total_leads', 'sources', 'totalPendingTask', 'assigner', 'months'));
    }

    /**
     * Fetch lead report chart data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchLeadReport(Request $request)
    {
        $period        = $request->period;
        $dateUtilities = new DateUtilities();
        if ($period === "year") {
            [$start, $end] = [$dateUtilities->getMonthFirstDate(1), $dateUtilities->getMonthFirstDate(12)];
        } else {
            [$start, $end] = $dateUtilities->getInterval($period);
        }

        $end     = Carbon::parse($end)->endOfMonth()->toDateTimeString();
        $leads = Lead::whereIn('dealer_id', Lead::getFetchableDealers())
                ->where(function ($query) {
                    $query->where('created_by', auth()->user()->id)
                        ->when(!auth()->user()->isTmMember(), function($q){
                            $q->orWhereNull('created_by');
                        })->orWhere('assinged_to', auth()->user()->id);
                })->select(array(
                DB::raw('COUNT(*) total'),
                DB::raw('"deal" as `type`'),
                DB::raw('SUM(CASE WHEN source_id = "1" THEN 1 ELSE 0 END) as `auto_trader`'),
                DB::raw('SUM(CASE WHEN source_id = "2" THEN 1 ELSE 0 END) as `cargurus`'),
                DB::raw('SUM(CASE WHEN source_id = "3" THEN 1 ELSE 0 END) as `carpages`'),
                DB::raw('SUM(CASE WHEN source_id = "4" THEN 1 ELSE 0 END) as `drive_good`'),
                DB::raw('SUM(CASE WHEN source_id = "5" THEN 1 ELSE 0 END) as `fb_mk`'),
                DB::raw('SUM(CASE WHEN source_id = "6" THEN 1 ELSE 0 END) as `facebook`'),
                DB::raw('SUM(CASE WHEN source_id = "7" THEN 1 ELSE 0 END) as `instagram`'),
                DB::raw('SUM(CASE WHEN source_id = "8" THEN 1 ELSE 0 END) as `kijiji`'),
                DB::raw('SUM(CASE WHEN source_id = "9" THEN 1 ELSE 0 END) as `messenger`'),
                DB::raw('SUM(CASE WHEN source_id = "10" THEN 1 ELSE 0 END) as `microsoft`'),
                DB::raw('SUM(CASE WHEN source_id = "11" THEN 1 ELSE 0 END) as `phone`'),
                DB::raw('SUM(CASE WHEN source_id = "12" THEN 1 ELSE 0 END) as `referal`'),
                DB::raw('SUM(CASE WHEN source_id = "13" THEN 1 ELSE 0 END) as `walk_in`'),
                DB::raw('SUM(CASE WHEN source_id = "14" THEN 1 ELSE 0 END) as `website`'),
                DB::raw('SUM(CASE WHEN source_id = "15" THEN 1 ELSE 0 END) as `fb_lg_ads`'),
                DB::raw('SUM(CASE WHEN source_id = "16" THEN 1 ELSE 0 END) as `drive_good_fb_lg_ads`'),
                DB::raw('SUM(CASE WHEN source_id = "17" THEN 1 ELSE 0 END) as `monezsoft`'),
            ))->when($period === 'year', function($q){
                $q->selectRaw('YEAR(created_at) AS year')
                ->selectRaw('DATE_FORMAT(created_at, "%b") As month')
                ->groupBy('year', 'month')->orderBy('year', 'desc');
            })->when($period !== 'year', function($q){
                $q->selectRaw('DATE(created_at) As date')
                ->groupBy('date')->orderBy('date', 'asc');
            })->whereBetween('created_at', [$start, $end]);

        $finances = FinanceForm::whereIn('dealer_id', Lead::getFetchableDealers())
                ->where(function ($query) {
                    $query->when(!auth()->user()->isTmMember(), function($q){
                        $q->whereNull('assinged_to')->orWhere('assinged_to', auth()->user()->id);
                    })->when(auth()->user()->isTmMember(), function($q){
                        $q->where('assinged_to', auth()->user()->id);
                    });
                })->select(array(
                DB::raw('COUNT(*) total'),
                DB::raw('"finance" as `type`'),
                DB::raw('SUM(CASE WHEN source_id = "1" THEN 1 ELSE 0 END) as `auto_trader`'),
                DB::raw('SUM(CASE WHEN source_id = "2" THEN 1 ELSE 0 END) as `cargurus`'),
                DB::raw('SUM(CASE WHEN source_id = "3" THEN 1 ELSE 0 END) as `carpages`'),
                DB::raw('SUM(CASE WHEN source_id = "4" THEN 1 ELSE 0 END) as `drive_good`'),
                DB::raw('SUM(CASE WHEN source_id = "5" THEN 1 ELSE 0 END) as `fb_mk`'),
                DB::raw('SUM(CASE WHEN source_id = "6" THEN 1 ELSE 0 END) as `facebook`'),
                DB::raw('SUM(CASE WHEN source_id = "7" THEN 1 ELSE 0 END) as `instagram`'),
                DB::raw('SUM(CASE WHEN source_id = "8" THEN 1 ELSE 0 END) as `kijiji`'),
                DB::raw('SUM(CASE WHEN source_id = "9" THEN 1 ELSE 0 END) as `messenger`'),
                DB::raw('SUM(CASE WHEN source_id = "10" THEN 1 ELSE 0 END) as `microsoft`'),
                DB::raw('SUM(CASE WHEN source_id = "11" THEN 1 ELSE 0 END) as `phone`'),
                DB::raw('SUM(CASE WHEN source_id = "12" THEN 1 ELSE 0 END) as `referal`'),
                DB::raw('SUM(CASE WHEN source_id = "13" THEN 1 ELSE 0 END) as `walk_in`'),
                DB::raw('SUM(CASE WHEN source_id = "14" THEN 1 ELSE 0 END) as `website`'),
                DB::raw('SUM(CASE WHEN source_id = "15" THEN 1 ELSE 0 END) as `fb_lg_ads`'),
                DB::raw('SUM(CASE WHEN source_id = "16" THEN 1 ELSE 0 END) as `drive_good_fb_lg_ads`'),
                DB::raw('SUM(CASE WHEN source_id = "17" THEN 1 ELSE 0 END) as `monezsoft`'),
            ))->when($period === 'year', function($q){
                $q->selectRaw('YEAR(created_at) AS year')
                ->selectRaw('DATE_FORMAT(created_at, "%b") As month')
                ->groupBy('year', 'month')->orderBy('year', 'desc');
            })->when($period !== 'year', function($q){
                $q->selectRaw('DATE(created_at) As date')
                ->groupBy('date')->orderBy('date', 'asc');
            })->whereBetween('created_at', [$start, $end]);

        $reports        = $leads->union($finances)->get();
        $data           = ['labels' => [], 'datasets' => [], 'sources' => []];
        $data['labels'] = $period === 'year' ? DateUtilities::getMonthListFromDate(Carbon::parse($start), Carbon::parse($end)) :
        DateUtilities::getMonthDateList(Carbon::parse($start), Carbon::parse($end));

        foreach ($data['labels'] as $key => $label) {
            if($period === 'year'){
                $report = $reports->where('type', 'deal')->where('month', $label)->first();
                $finance = $reports->where('type', 'finance')->where('month', $label)->first();
            }else{
                $report = $reports->where('type', 'deal')->where('date', $key)->first();
                $finance = $reports->where('type', 'finance')->where('date', $key)->first();
            }

            array_push($data['datasets'], ($report->total ?? 0) + ($finance->total ?? 0));

            $data['sources'] = [
                "auto_trader" => ($data['sources'] ['auto_trader'] ?? 0) + (int) ($report ? $report->auto_trader : 0) + (int) ($finance ? $finance->auto_trader : 0),
                "cargurus"    => ($data['sources'] ['cargurus'] ?? 0) + (int) ($report ? $report->cargurus : 0)+ (int) ($finance ? $finance->cargurus : 0),
                "drive_good"  => ($data['sources'] ['drive_good'] ?? 0) + (int) ($report ? $report->drive_good : 0) + (int) ($finance ? $finance->drive_good : 0),
                "fb_mk"       => ($data['sources'] ['fb_mk'] ?? 0) + (int) ($report ? $report->fb_mk : 0) + (int) ($finance ? $finance->fb_mk : 0),
                "facebook"    => ($data['sources'] ['facebook'] ?? 0) + (int) ($report ? $report->facebook : 0) + (int) ($finance ? $finance->facebook : 0),
                "instagram"   => ($data['sources'] ['instagram'] ?? 0) + (int) ($report ? $report->instagram : 0) + (int) ($finance ? $finance->instagram : 0),
                "kijiji"      => ($data['sources'] ['kijiji'] ?? 0) + (int) ($report ? $report->kijiji : 0) + (int) ($finance ? $finance->kijiji : 0),
                "messenger"   => ($data['sources'] ['messenger'] ?? 0) + (int) ($report ? $report->messenger : 0) + (int) ($finance ? $finance->messenger : 0),
                "monezsoft"   => ($data['sources'] ['monezsoft'] ?? 0) + (int) ($report ? $report->monezsoft : 0) + (int) ($finance ? $finance->monezsoft : 0),
                "phone"       => ($data['sources'] ['phone'] ?? 0) + (int) ($report ? $report->phone : 0) + (int) ($finance ? $finance->phone : 0),
                "referal"     => ($data['sources'] ['referal'] ?? 0) + (int) ($report ? $report->referal : 0) + (int) ($finance ? $finance->referal : 0),
                "walk_in"     => ($data['sources'] ['walk_in'] ?? 0) + (int) ($report ? $report->walk_in : 0) + (int) ($finance ? $finance->walk_in : 0),
                "website"     => ($data['sources'] ['website'] ?? 0) + (int) ($report ? $report->website : 0) + (int) ($finance ? $finance->website : 0),
                "carpages"    => ($data['sources'] ['carpages'] ?? 0) + (int) ($report ? $report->carpages : 0) + (int) ($finance ? $finance->carpages : 0),
                "microsoft"   => ($data['sources'] ['microsoft'] ?? 0) + (int) ($report ? $report->microsoft : 0) + (int) ($finance ? $finance->microsoft : 0),
                "fb_lg_ads"   => ($data['sources'] ['fb_lg_ads'] ?? 0) + (int) ($report ? $report->fb_lg_ads : 0) + (int) ($finance ? $finance->fb_lg_ads : 0),
                "drive_good_fb_lg_ads" => ($data['sources'] ['drive_good_fb_lg_ads'] ?? 0) + (int) ($report ? $report->drive_good_fb_lg_ads : 0) + (int) ($finance ? $finance->drive_good_fb_lg_ads : 0),
            ];
        }

        array_multisort($data['sources'], SORT_DESC);
        $data['labels'] = array_values($data['labels']);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Fetch new and latest modified leads
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchLatestLead(): \Illuminate\Http\JsonResponse
    {
        $leads = Lead::whereIn('leads.dealer_id', Lead::getFetchableDealers())
            ->where(function($q){
                $q->whereNotNull('product_id')->orWhereHas("car");
            })->whereHas('customer')->with('customer:id,first_name,last_name')
            ->where(function ($query) {
                $query->where('created_by', auth()->user()->id)
                ->when(!auth()->user()->isTmMember(), function($q){
                    $q->orWhereNull('created_by');
                })->orWhere('assinged_to', auth()->user()->id);
            });

        $leads->orderByRaw("case WHEN status = '24' THEN 0 else 5 END ASC, created_at DESC");
        $leads = $leads->select(['leads.*'])->paginate();

         collect( $leads->items())->each(function ($lead) {
            $lead->setAttribute('date', $lead->created_at->format('Y-m-d'));
            $lead->setAttribute('status', ucwords(str_replace("_", " ", $lead->status()->first()->name ?? "")));
            $lead->setAttribute('car_name', $lead->product_title ?? sprintf('%s %s %s', $lead->car_year, $lead->car_maker, $lead->car_model));
        });

        return response()->json(['success' => true, 'leads' => $leads]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCarTopInquiries(): \Illuminate\Http\JsonResponse
    {
        $cars = Car::whereIn('dealer_id', Lead::getFetchableDealers())
            ->withCount('leads')->whereHas('leads')->orderBy('leads_count', 'DESC')
            ->whereHas('status', function($q){
                return $q->where('name', 'publish');
            })->paginate();

        collect($cars->items())->each(function ($car) {
            $car->setAttribute('days', $car->days);
            $car->setAttribute('car_price', number_format($car->car_price));
        });

        return response()->json(['success' => true, 'cars' => $cars]);
    }

    public function fetchPendingTaskRecords(Request $request)
    {
        $request->validate([ 'statuses' => 'required' ]);
        $statuses = json_decode($request->input('statuses'));

        $tasks = Tasks::with('user:id,username,type','assigner:id,username,type','status:id,name')
                ->where("assigner_id", auth()->user()->id)
                ->whereIn("status_id", $statuses ?? [])
                ->orderBy('delivery_date')->paginate();

        collect($tasks->items())->each(function ($task) {
            $task->setAttribute('task_status',  ucwords($task->status->name ?? ""));
        });

        return response()->json(['success' => true, 'tasks' => $tasks]);
    }
}
