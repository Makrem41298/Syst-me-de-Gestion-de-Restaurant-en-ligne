<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboredController extends Controller

{
    use ApiResponse;
    public function __construct()
    {
        $this->middleware('role:Super-Admin');

    }
    public function index()
    {
        try {
            $topItem = OrderLine::select(DB::raw('SUM(quantity) as item_count, item_id'))
                ->groupBy('item_id')
                ->orderBy('item_count', 'desc')
                ->take(10)
                ->get();


            $todayRevenue = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as revenue'))
                ->whereBetween('created_at', [Carbon::today()->subDays(30)->toDateString(), Carbon::today()->addDay(1)->toDateString()])
                ->whereHas('payment', function ($query) {
                    $query->where('status','payed');
                })
                ->groupBy('date')
                ->orderBy('revenue', 'desc')
                ->get();
            return $this->apiResponse(['topItem' => $topItem, 'todayRevenue' => $todayRevenue],400,'retrieved dashboard successfully');


        }catch (\Exception $exception){
            return $this->apiResponse(null,400,$exception->getMessage());
        }
      }


}
