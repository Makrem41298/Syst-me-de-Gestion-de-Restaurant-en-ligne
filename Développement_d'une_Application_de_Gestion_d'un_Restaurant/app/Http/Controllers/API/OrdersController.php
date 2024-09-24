<?php

namespace App\Http\Controllers\API;

use App\Enums\DeliveryStatus;
use App\Enums\ItemStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderTypeStatus;
use App\Enums\PaymentMethodeStatus;
use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Payment;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrdersController extends Controller
{
    use ApiResponse;
    public function __construct()
    {
        $this->middleware(['permission:store order'])->only('storeOrder');
        $this->middleware(['permission:show order'])->only(['show','index']);
        $this->middleware(['permission:update order'])->only('updateOrder');
        $this->middleware(['permission:delete order'])->only(['destroy','deleteAll']);

    }

    public function index(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'order_date_from' => 'date_format:Y-m-d',
            'order_date_to' => 'date_format:Y-m-d|after:order_date_from',
            'order_type'=>Rule::in(OrderTypeStatus::getValues()),
            'methode_payment'=>Rule::in(PaymentMethodeStatus::getValues()),
            'status_order'=>[Rule::in(OrderStatus::getValues())],
            'status_delivery'=>Rule::in(DeliveryStatus::getValues()),
            'employ'=>'exists:admins,id',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,'200','validation error'.$validator->errors());
        }

        try {
            $query=Order::with('orderable','items','payment','delivery');
            if ($request->has('order_date_from') && $request->has('order_date_to')) {
                $query->whereBetween('created_at',[$request->get('order_date_from'),$request->get('order_date_to')]);
            }
            if ($request->has('order_type')) {
                $query->where('order_type',$request->get('order_type'));
            }
            if ($request->has('methode_payment')) {
                $query->whereHas('payment', function ($subquery) use ($request) {
                    $subquery->where('methode', $request->get('methode_payment'));
                });            }
            if ($request->has('status_order')) {
                $query->where('status',$request->get('status_order'));
            }
            if ($request->has('status_delivery'))
                $query->whereHas('delivery',function ($subquery) use ($request) {
                    $subquery->where('status',$request->get('status_delivery'));
                });
            if ($request->has('status_payment')){
                $query->whereHas('payment',function ($subquery) use ($request) {
                    $subquery->where('status',$request->get('status_payment'));
                });
            }


            $query->orderBy('created_at','desc');
            return $this->apiResponse($query->get(),'200','retrieved successfully');


        }catch (\Exception $e){
            return $this->apiResponse(null,'400',$e->getMessage());
        }

    }
    public function showMyOrderAdmin(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'order_date_from' => 'date_format:Y-m-d',
            'order_date_to' => 'date_format:Y-m-d|after:order_date_from',
            'order_type'=>Rule::in(OrderTypeStatus::getValues()),
            'methode_payment'=>Rule::in(PaymentMethodeStatus::getValues()),
            'status_order'=>[Rule::in(OrderStatus::getValues())],
            'status_delivery'=>Rule::in(DeliveryStatus::getValues()),
            'employ'=>'exists:admins,id',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,'200','validation error'.$validator->errors());
        }

        try {
            $query=Order::with('orderable','items','payment','delivery')->where('admin_id',auth('admins')->id());
            if ($request->has('order_date_from') && $request->has('order_date_to')) {
                $query->whereBetween('created_at',[$request->get('order_date_from'),$request->get('order_date_to')]);
            }
            if ($request->has('order_type')) {
                $query->where('order_type',$request->get('order_type'));
            }
            if ($request->has('methode_payment')) {
                $query->whereHas('payment', function ($subquery) use ($request) {
                    $subquery->where('methode', $request->get('methode_payment'));
                });            }
            if ($request->has('status_order')) {
                $query->where('status',$request->get('status_order'));
            }
            if ($request->has('status_delivery'))
                $query->whereHas('delivery',function ($subquery) use ($request) {
                    $subquery->where('status',$request->get('status_delivery'));
                });

            $query->orderBy('created_at','desc');
            return $this->apiResponse($query->get(),'200','retrieved successfully');


        }catch (\Exception $e){
            return $this->apiResponse(null,'400',$e->getMessage());
        }

    }

    public function storeOrderUser(Request  $request){


        $validator = Validator::make($request->all(),[
            'items.*.quantity' => 'required|numeric|min:1',
            'items' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    // Fetch all existing item IDs from the database
                    $existingItemIds = Item::pluck('status','id')->toArray();

                    // Check if all keys in the items array exist in the database
                    foreach ($value as $key => $item) {
                        if (!in_array($key,array_keys($existingItemIds))) {
                            $fail("The item with ID $key does not exist.");
                        }
                        if (isset($existingItemIds[$key])){
                            if (!($existingItemIds[$key]==ItemStatus::available)){
                                $fail("The item with ID $key not available.");


                            }

                        }

                    }
                }
            ],
            'order_date' => 'required|date_format:Y-m-d H:i|after_or_equal:'.now(),
            'order_type'=>['required',Rule::in(OrderTypeStatus::getValues())],
            'methode_payment'=>['required',Rule::in(PaymentMethodeStatus::getValues())],

        ]);
        if($validator->fails()){
            return $this->apiResponse(null,'200','validation error'.$validator->errors());
        }
        try {

            $userId=auth('users')->user()->getAuthIdentifier();
            //inset Order
            $user=User::findOrFail($userId);
            $order=$user->orders()->create([
                    'order_date'=>$request->order_date,
                    'order_type'=>$request->order_type

                ]
            );
            //inset OrderLine


            $this->storeOrdreLine($request->items,$order);
            //calculate total price
            $price_total=OrderLine::select(DB::raw('sum(quantity*unit_price) as total_price'))
                ->where('order_id',$order->id)
                ->first()->total_price;
            //update_order
            $order->update(['total_price'=>$price_total]);
            //inset Payment
            $this->storePayment($request->methode_payment,$order,$price_total);
            if ($request->order_type==OrderTypeStatus::delivery){
                $order->delivery()->create(
                    $request->customer

                );
            }

            return $this->apiResponse($order,'200','order created successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,'400',$e->getMessage());
        }


    }


    public function storeOrder(Request  $request){

        $validator = Validator::make($request->all(),[
            'items.*quantity' => 'required|numeric|min:1',
            'items' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    // Fetch all existing item IDs from the database
                    $existingItemIds = Item::pluck('status','id')->toArray();

                    // Check if all keys in the items array exist in the database
                    foreach ($value as $key => $item) {
                        if (!in_array($key,array_keys($existingItemIds))) {
                            $fail("The item with ID $key does not exist.");
                        }
                        if (isset($existingItemIds[$key])){
                            if (!($existingItemIds[$key]==ItemStatus::available)){
                                $fail("The item with ID $key not available.");


                            }

                        }


                    }
                }
            ],
            'order_date' => 'required|date_format:Y-m-d H:i|after_or_equal:'.now(),
            'methode_payment'=>['required',Rule::in(PaymentMethodeStatus::getValues())],
            'order_type'=>['required',Rule::in(OrderTypeStatus::getValues())],
            'customer.first_name' => 'nullable|string|max:255',
            'customer.last_name' => 'nullable|string|max:255',
            'customer.phone' => 'nullable|string|max:255',
            'status'=>['required',Rule::in(OrderStatus::getValues())],
            'table_id'=>['nullable',Rule::exists('tables','id')->where(function ($query) {
                $query->where('status',TableStatus::available);
            })],

        ]);
        if($validator->fails()){
            return $this->apiResponse(null,'200','validation error'.$validator->errors());
        }
        try {
            //inset Order
            $order = Order::create([
                'order_date' => $request->order_date,
                'order_type' => $request->order_type,
                'admin_id' => auth('admins')->user()->getAuthIdentifier(),
                'status'=>$request->status
            ]);

            if (isset($request->table_id)) {
                $table = Table::findOrFail($request->table_id);
                $table->orders()->save($order);
                $table->update(['status' => TableStatus::occupied]);
            }

            //inset OrderLine

            $this->storeOrdreLine($request->items,$order);
            //calculate total price
            $price_total=OrderLine::select(DB::raw('sum(quantity*unit_price) as total_price'))
                ->where('order_id',$order->id)
                ->first()->total_price;
            //update_order
            $order->update(['total_price'=>$price_total]);
            //inset Payment
            $this->storePayment($request->methode_payment,$order,$price_total);
            if ($request->order_type==OrderTypeStatus::delivery){
                $order->delivery()->create(
                    $request->customer

                );};
            return $this->apiResponse($order,'200','order created successfully');


        }catch (\Exception $e){
            return $this->apiResponse(null,'400',$e->getMessage());
        }


    }



    public function storeOrdreLine($ordreLine,$order)
    {
        $unitPrices = Item::find(array_keys($ordreLine))->pluck('price', 'id')->toArray();


        $updatedoOrderLine = [];
        foreach ($ordreLine as $item_id => $item) {

            $item['unit_price'] = $unitPrices[$item_id];

            $updatedoOrderLine[$item_id] = $item;
        }
            $order->items()->attach($updatedoOrderLine );


    }
    public function storePayment($PaymentMethode,$order,$totalPrice)
    {
       $order->payment()->create([
            'methode'=>$PaymentMethode,
            'total_price'=>$totalPrice,
        ]);

    }
    public function updateOrder(Request  $request,$orderId){

        $validator = Validator::make(array_merge($request->all(),['order_id'=>$orderId]),[
            'items' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    // Fetch all existing item IDs from the database
                    $existingItemIds = Item::pluck('status','id')->toArray();

                    // Check if all keys in the items array exist in the database
                    foreach ($value as $key => $item) {
                        if (!in_array($key,array_keys($existingItemIds))) {
                            $fail("The item with ID $key does not exist.");
                        }
                        if (isset($existingItemIds[$key])){
                            if (!($existingItemIds[$key]==ItemStatus::available)){
                                $fail("The item with ID $key not available.");


                            }

                        }

                    }
                }
            ],
            'items.*.quantity' => 'required|numeric|min:1',
            'order_date' => 'required|date_format:Y-m-d H:i',
            'methode_payment'=>['required',Rule::in(PaymentMethodeStatus::getValues())],
            'status'=>Rule::in(OrderStatus::getValues()),
            'order_id'=>'required|exists:orders,id',

        ]);
        if($validator->fails()){
            return $this->apiResponse(null,'200','validation error'.$validator->errors());
        }
        try {

            $order=Order::findOrFail($orderId);
            $order->update([
                    'order_date'=>$request->order_date,
                    'order_type'=>$request->order_type,
                    'admin_id'=>auth('admins')->user()->getAuthIdentifier()

                ]
            );
            //inset OrderLine
            $order->items()->sync($request->items);
            //calculate total price
            $price_total=OrderLine::select(DB::raw('sum(quantity*unit_price) as total_price'))
                ->where('order_id',$order->id)
                ->first()->total_price;
            //update_order
            $order->update(['total_price'=>$price_total]);
            //inset Payment
            $order->payment()->update(['methode'=>$request->methode_payment,'total_price'=>$price_total]);

            return $this->apiResponse($order,'200','order created successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,'400',$e->getMessage());
        }


    }




    public function showOrderUser(Request  $request)
    {
        $validator=Validator::make($request->all(),[
            'order_date_from' => 'date_format:Y-m-d',
            'order_date_to' => 'date_format:Y-m-d|after:order_date_from',
            'order_type'=>Rule::in(OrderTypeStatus::getValues()),
            'methode_payment'=>Rule::in(PaymentMethodeStatus::getValues()),
            'status_order'=>[Rule::in(OrderStatus::getValues())],
            'status_delivery'=>Rule::in(DeliveryStatus::getValues()),
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,'200','validation error'.$validator->errors());
        }

        try {
            $user=auth()->guard('users')->user();
            $query=$user->orders()->with('orderable','items','payment','delivery');
            if ($request->has('order_date_from') && $request->has('order_date_to')) {
                $query->whereBetween('created_at',[$request->get('order_date_from'),$request->get('order_date_to')]);
            }
            if ($request->has('order_type')) {
                $query->where('order_type',$request->get('order_type'));
            }
            if ($request->has('methode_payment')) {
                $query->whereHas('payment', function ($subquery) use ($request) {
                    $subquery->where('methode', $request->get('methode_payment'));
                });            }
            if ($request->has('status_order')) {
                $query->where('status',$request->get('status_order'));
            }
            if ($request->has('status_delivery'))
                $query->whereHas('delivery',function ($subquery) use ($request) {
                    $subquery->where('status',$request->get('status_delivery'));
                });
            $query->orderBy('created_at','desc');
            return $this->apiResponse($query->get(),'200','retrieved successfully');


        }catch (\Exception $e){
            return $this->apiResponse(null,'400',$e->getMessage());
        }

    }
    public function showOrder($id){
        $validator=Validator::make(['id'=>$id],['id'=>'required|numeric|min:1|exists:orders,id']);
        if($validator->fails()){
            return $this->apiResponse(null,'200','validation error'.$validator->errors());
        }
        try {
           $order=Order::with('orderable','items','payment','delivery')->findOrFail($id);
            return $this->apiResponse($order,'200','order created successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,'400',$e->getMessage());
        }


    }
    public function updateOrderUser(Request  $request,$orderId)
    {
        $validator = Validator::make(array_merge($request->all(), ['order_id' => $orderId]), [
            'status' => ['required',Rule::in(OrderStatus::cancelled)],
            'order_id' => [
                'required',
                Rule::exists('orders', 'id')->where(function ($query) {
                    $query->where('orderable_type', User::class)
                        ->where('orderable_id', auth('users')->id())
                    ->whereNot('status',OrderStatus::cancelled);

                })
            ],
        ]);

        if($validator->fails()){
            return $this->apiResponse(null,'200','validation error'.$validator->errors());
        }


        try {

            $order=Order::findOrFail($orderId);

            $order->update(['status' => $request->get('status')]);

            return $this->apiResponse(true,'200','order created successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,'400',$e->getMessage());
        }


    }



    public function destroy($id){
        $validator = Validator::make(["id"=>$id],["id"=>'required|numeric|exists:orders,id']);
        if($validator->fails()){
            return $this->apiResponse(null,'200','validation error'.$validator->errors());
        }
        try {
            $order=Order::findOrFail($id);
            $order->payment()->delete();
            $order->items()->detach();
            $order->delete();

            return $this->apiResponse($order,'200','order deleted successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,'400',$e->getMessage());
        }

    }
    public function deleteAll(){
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            OrderLine::truncate();
            Payment::truncate();
            Order::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return $this->apiResponse(true,500,'delete all successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,'400',$e->getMessage());
        }

    }


    //
}
