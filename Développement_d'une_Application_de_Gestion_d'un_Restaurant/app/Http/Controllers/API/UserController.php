<?php

namespace App\Http\Controllers\API;

use App\Enums\BookingStatus;
use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Profile;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware(['permission:show user'])->only(['show','index']);
        $this->middleware(['permission:delete user'])->only('deleteAll');


    }
    public function index(){

        try {
            $users=User::with('profile')->get();
            return $this->apiResponse($users,200,"All users retrieved successfully");
        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Erreur : '.$e->getMessage());
        }
    }
    public function show($id){
        $validator=Validator::make(array('id'=>$id), [
            'id'=>'exists:users,id'
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,406,$validator->errors());
        }

        try {
            $user=User::findOrfail($id)->with('profile')->firstOrFail();
            return $this->apiResponse($user,200,"user retrieved successfully");
        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Erreur : '.$e->getMessage());
        }
    }
    public function destroy($id){
        $validator=Validator::make(array('id'=>$id), [
            'id'=>'exists:users,id'
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,406,$validator->errors());
        }
        try {
            User::findOrFail($id)->delete();
            return $this->apiResponse(true, 200, "user deleted successfully");

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Erreur : '.$e->getMessage());
        }


    }
    public function UpdateProfile(Request $request)
    {
        $validator=Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'gender'     => 'nullable|in:male,female',
            'birthday'   => 'nullable|date|before:today',
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:255',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,400,$validator->errors());
        }
        $user=auth('users')->user();
        $user->profile()->update($request->all());
        return $this->apiResponse($user,200,"user updated successfully");

    }
    public function changePassword(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'password_actual'=>'required|min:8',
            'password'=>'required|min:8|confirmed'


        ]);
        if($validator->fails()){
            return $this->apiResponse(null,400,$validator->errors());
        }
        if (!(Hash::check($request->get('password_actual'),auth()->guard('users')->user()->getAuthPassword())))
            return $this->apiResponse(null, 422, "password mismatch");

        $user=auth()->guard('users')->user()->update(['password'=>Hash::make($request->get('password'))]);


        return $this->apiResponse($user, 200, "password changed successfully");



    }
    public function deleteAll()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            User::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Profile::where('profileable_type',User::class)->delete();

            $booking=Booking::where([['bookingable_type',User::class],['status',BookingStatus::accepted]]);
            $table=$booking->get()->pluck('table_id')->toArray();
            Table::WhereIn('id',$table)->update(['status'=>TableStatus::available]);
            $booking->update(['status'=>BookingStatus::cancel]);


            return $this->apiResponse(true,'200','all sub categories deleted successfully');

        }catch(\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete all the  sub categories: '.$e->getMessage());
        }
    }



}
