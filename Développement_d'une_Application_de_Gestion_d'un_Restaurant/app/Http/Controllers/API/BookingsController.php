<?php

namespace App\Http\Controllers\API;
use App\Enums\BookingStatus;
use App\Events\BookingEvent;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BookingsController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware(['permission:store booking'])->only('bookingCustomer');
        $this->middleware(['permission:show booking'])->only(['show','index']);
        $this->middleware(['permission:update booking'])->only('updateBookingAdmin');
        $this->middleware(['permission:delete booking'])->only(['destroy','deleteAll']);

    }

   public function index(Request $request)
   {
       $validator = Validator::make($request->all(), [
           'table_id' => 'exists:tables,id',
           'date_from' => 'date_format:Y-m-d',
           'date_to' => 'date_format:Y-m-d|after_or_equal:date_from',
           'status_booking'=>Rule::in(BookingStatus::getValues()),
           'source_booking' => Rule::in(['user','employ']),
       ]);
       if ($validator->fails()) {
           return $this->apiResponse('null',404,$validator->errors());
       }

       try {
           $query=Booking::with('user','customer','table');
           if ($request->has('source_booking')) {
               if ($request->get('source_booking')=='user') {
                   $query->whereNotNull('user_id');
               }else
                   $query->whereNull('user_id');

               }


           if (request()->has('table_id')) {
               $query->where('table_id', request('table_id'));
           }
           if (request()->has('date_from')&&request()->has('date_to')) {
               $query->whereBetween('created_at', [request('date_from'), request('date_to')]);
           }
           if (request()->has('status_booking')) {
               $query->where('status', request('status_booking'));
           }
           return $this->apiResponse( $query->get(),200,'bookings retrieved successfully.');


       }catch (\Exception $exception){
           return $this->apiResponse(null,404,$exception->getMessage());

       }

   }

   public function bookingCustomer(Request  $request){
       $validator = Validator::make($request->all(), [
           'first_name' => 'required',
           'last_name' => 'required',
           'phone' => 'required|digits:8',
           'number_people' => 'required|integer|min:1',
           'date_hour_booking' => 'required|date|date_format:Y-m-d H:i|after:'. now(),
           'table_id'=> [Rule::exists('tables','id')],
            'status'=>Rule::in(BookingStatus::getValues())
       ]);
       if($validator->fails()){
           return $this->apiResponse(null,400,'validation errors:'.$validator->errors());
       }
       try {


           $booking=Booking::create(
               ['number_people'=>$request->number_people,
                   'date_hour_booking'=>$request->date_hour_booking,
                   'status'=>$request->status

               ]);
           $customer=$booking->customer()->create(['first_name'=>$request->first_name,
               'last_name'=>$request->last_name,
               'phone'=>$request->phone]);
           return $this->apiResponse($booking,201,'booking added successfully.');

       }catch (\Exception $exception){
           return $this->apiResponse($exception,404,'bookings not found:'.$exception->getMessage());
       }



   }
   public function show($id){
       $validator=Validator::make(array('id'=>$id),[
           'id'=>'exists:bookings,id'
       ]);
       if($validator->fails()){
           return $this->apiResponse(null,400,'validation errors:'.$validator->errors());

       }
       try {
           $booking=Booking::with('customer','table','user')->findOrfail($id);
           return $this->apiResponse($booking,200,'booking retrieved successfully.');

       }catch (\Exception $exception){
           return $this->apiResponse($exception,404,'bookings not found:'.$exception->getMessage());
       }

   }
   public function updateBookingAdmin(Request $request, $id){
       $validator=Validator::make(array_merge($request->all(),['id'=>$id]),[
           'id'=>'exists:bookings,id',
           'date_hour_booking' => 'date|date_format:Y-m-d H:i|after:'.now(),
           'status'=>[Rule::in(BookingStatus::getValues())],
           'number_people' => 'min:1',
           'table_id'=>['nullable',Rule::exists('tables','id')],
           'first_name'=>'nullable|string',
            'last_name'=>'nullable',
            'phone'=>'nullable|digits:8',

       ]);
       if($validator->fails()){
           return $this->apiResponse(null,400,'validation errors:'.$validator->errors());
       }

       try {

               $booking=Booking::findOrfail($id);
               $booking->update(
                    $request->except('phone','last_name','first_name')
               );
           BookingEvent::dispatchIf(strcmp($request->get('status'),BookingStatus::accepted)==0,Booking::findOrfail($id));
           BookingEvent::dispatchIf(strcmp($request->get('status'),BookingStatus::cancel)==0,Booking::findOrfail($id));

               $booking->customer()->update([
                   'first_name'=>$request->first_name,
                   'last_name'=>$request->last_name,
                   'phone'=>$request->phone

               ]);
               return $this->apiResponse($booking,'200','booking updated successfully.');

       }catch (\Exception $exception){
           return $this->apiResponse(null,404,$exception->getMessage());
       }

   }
   public function destroy($id){
       $validator=Validator::make(array('Booking_id'=>$id),[
           'Booking_id'=>Rule::exists('bookings','id'),
       ]);
       if($validator->fails()){
           return $this->apiResponse(null,400,'validation errors:'.$validator->errors());
       }
       try {
           $booking=Booking::findOrfail($id);
           return $this->apiResponse($booking->delete(),200,'booking deleted successfully.');

       }catch (\Exception $exception){
           return $this->apiResponse($exception,404,$exception->getMessage());
       }


   }
    public function deleteAll()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            User::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return $this->apiResponse(true,'200','all sub categories deleted successfully');

        }catch(\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete all the  sub categories: '.$e->getMessage());
        }

    }
    public function bookingUser(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'date_hour_booking' => 'required|date|date_format:Y-m-d H:i|after:'.now(),
            'number_people' => 'required|integer|min:1',
            'first_name'=>'nullable|string',
            'last_name'=>'nullable',
            'phone'=>'nullable|digits:8',

        ]);
        if($validator->fails()){
            return $this->apiResponse(null,400,'validation errors:'.$validator->errors());
        }
        try {
           $user=auth('users')->user();
           $booking=$user->bookings()->create(
               [
                   'date_hour_booking'=>$request->date_hour_booking,
                   'number_people'=>$request->number_people,
               ]
           );
           $booking->customer()->create([
               'first_name'=>$request->first_name,
               'last_name'=>$request->last_name,
               'phone'=>$request->phone

           ]);

            return  $this->apiResponse($booking,'200','booking added successfully.');

        }catch (\Exception $exception){
            return $this->apiResponse(null,404,$exception->getMessage());
        }



    }
    public function updateBookingUser(Request $request,$Booking_id)
    {

        $validator=Validator::make(array_merge($request->all(),['booking_id'=>$Booking_id]),[
            'date_hour_booking' => 'required|date|date_format:Y-m-d H:i|after:'.now(),
            'status'=>['required',Rule::in([BookingStatus::pending,BookingStatus::cancel])],
            'number_people' => 'required|min:1',
            'booking_id'=>['required',Rule::exists('bookings','id')->where(function($query){
                $query->where('user_id',auth('users')->id());
            })],
            'first_name'=>'nullable|string',
            'last_name'=>'nullable',
            'phone'=>'nullable|digits:8',

        ]);
        if($validator->fails()){
            return $this->apiResponse(null,400,'validation errors:'.$validator->errors());
        }

        try {
            $booking=Booking::findOrFail($Booking_id);
                $booking->update([
                'date_hour_booking'=>$request->date_hour_booking,
                'status'=>$request->status,
                'number_people'=>$request->number_people
            ]);
            $booking->customer()->update([
                'first_name'=>$request->first_name,
                'last_name'=>$request->last_name,
                'phone'=>$request->phone
            ]);



                return $this->apiResponse($booking,200,'booking updated successfully.');

        }catch (\Exception $exception){
            return $this->apiResponse(null,404,$exception->getMessage());
        }
    }
    public function showBookingUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'date_format:Y-m-d',
            'date_to' => 'date_format:Y-m-d|after_or_equal:date_from',
            'status_booking'=>Rule::in(BookingStatus::getValues()),
        ]);
        if ($validator->fails()) {
            return $this->apiResponse('null',404,$validator->errors());
        }

        $user=auth('users')->user();
        $query= $user->bookings()->with('table','customer');

        if (request()->has('date_from')&&request()->has('date_to')) {
            $query->whereBetween('created_at', [request('date_from'), request('date_to')]);
        }
        if (request()->has('status_booking')) {
            $query->where('status', request('status_booking'));
        }
       return $this->apiResponse($query->get(),200,'booking retrieved successfully.');

    }
}
