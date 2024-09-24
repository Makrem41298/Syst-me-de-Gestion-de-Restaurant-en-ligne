<?php

namespace App\Http\Controllers\API;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Delivery;
use App\Models\Driver;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeliveriesController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware(['permission:show delivery'])->only(['show','index']);
        $this->middleware(['permission:add driver delivery'])->only('addDriver');
        $this->middleware(['permission:update delivery'])->only('update');


    }

    public function index()
    {
        try {
            $deliveries = Delivery::with('order.items','contract.driver')->get();
            return $this->apiResponse($deliveries,200,'retrieved successfully');
        }catch (\Exception $exception){
            return $this->apiResponse(null,400,$exception->getMessage());
        }


    }

    public function addDriver(Request $request){

            $validator=Validator::make($request->all(),[
                'driver_id'=>['required',Rule::exists('contracts','driver_id')->where(function ($query){
                    $query->where('date_end','>',now())->orWhereNull('date_end');
                })],
                'delivery_id'=>['required',Rule::exists('deliveries','id')],
                'delivery_time'=>'numeric|nullable|min:1',
            ]);

            if ($validator->fails()) {
                return $this->apiResponse(null,400,$validator->errors());
            }
            try {
            $delivery=Delivery::findOrFail($request->delivery_id);
            $driver=Driver::findOrfail($request->driver_id);
            $contract=$driver->contracts()->latest()->first();
            $delivery->contract()->associate($contract);
            $delivery->status=DeliveryStatus::transit;
            $delivery->delivery_total_amount=($delivery->order->total_price+$contract->delivery_fee);
            $delivery->delivery_time=Carbon::now()->addMinute($request->delivery_time);
            $delivery->save();
            return   $this->apiResponse($delivery,200,'added successfully');

        }catch (\Exception $exception){
            return $this->apiResponse(null,400,$exception->getMessage());
        }





    }
    public function show($id){
       $validator= Validator::make(['id'=>$id],[
            'id'=>['required',Rule::exists('deliveries','id')],
        ]);
        if ($validator->fails())
            return $this->apiResponse(null,400,$validator->errors());
        try {
          $delivery=Delivery::findOrFail($id)->with('order.items','contract.driver')->first();
          return $this->apiResponse($delivery,200,'retrieved successfully');
        }catch (\Exception $exception){
            return $this->apiResponse(null,400,$exception->getMessage());
        }

    }

    public function destroy($id){
        $validator=Validator::make(['id'=>$id],[
            'id'=>['required',Rule::exists('deliveries','id')],
        ]);
        if ($validator->fails())
            return $this->apiResponse(null,400,$validator->errors());
        try {
            Delivery::destroy($id);
            return $this->apiResponse(true,200,'deleted successfully');
        }catch (\Exception $exception){
            return $this->apiResponse(null,400,$exception->getMessage());
        }

    }
    public function update(Request $request,$id){
        $validator=Validator::make(array_merge($request->all(),['id'=>$id]),[
            'id'=>['required',Rule::exists('deliveries','id')],
            'status'=>Rule::in(DeliveryStatus::getValues()),
            'first_name'=>'required',
            'last_name'=>'required',
            'phone'=>'required',
            'address'=>'required',
            'delivery_time'=>'required|numeric|nullable|min:1',
            'driver_id'=>['required',Rule::exists('contracts','driver_id')->where(function ($query){
                $query->where('date_end','>',now())->orWhereNull('date_end');
            })],
        ]);
        if ($validator->fails())
            return $this->apiResponse(null,400,$validator->errors());
        try {
            $delivery=Delivery::findOrfail($id);
            $delivery->update([
                'status'=>$request->status,
                'first_name'=>$request->first_name,
                'last_name'=>$request->last_name,
                'phone'=>$request->phone,
                'address'=>$request->address,
                'delivery_time'=>Carbon::now()->addMinute($request->delivery_time),
                'contract_id'=>Contract::where('driver_id',$request->driver_id)->latest()->first()->id,
            ]);
            return $this->apiResponse($delivery,200,'updated successfully');

        }catch (\Exception $exception){
            return $this->apiResponse(null,400,$exception->getMessage());
        }
    }
    public function confirmationDeliveryUser(Request  $request,$orderId)
    {
        $validator = Validator::make(array_merge($request->all(), ['order_id' => $orderId]), [
            'status' => ['required',Rule::in(DeliveryStatus::delivered)],
            'order_id' => [
                'required',
                Rule::exists('orders', 'id')->where(function ($query) {
                    $query->where('orderable_type', User::class)
                        ->where('orderable_id', auth('users')->id())
                        ->where('status',OrderStatus::completed);

                })
            ],
        ]);

        if($validator->fails()){
            return $this->apiResponse(null,'200','validation error'.$validator->errors());
        }


        try {

            $order=Order::findOrFail($orderId);

            $order->delivery->update(['status' => $request->get('status')]);

            return $this->apiResponse(true,'200','delivery updated');

        }catch (\Exception $e){
            return $this->apiResponse(null,'400',$e->getMessage());
        }


    }
    //
}
