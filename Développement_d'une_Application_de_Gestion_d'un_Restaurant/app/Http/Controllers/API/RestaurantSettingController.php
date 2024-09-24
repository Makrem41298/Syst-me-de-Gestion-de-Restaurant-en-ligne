<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RestaurantSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RestaurantSettingController extends Controller
{
    /**
     * Display a listing of the restaurant settings.
     */
    use ApiResponse;
    public function __construct()
    {
        $this->middleware(['role:Super-Admin'])->only('update');

    }
    public function index()
    {
        try {
            $settings = RestaurantSetting::get()->first();
            return $this->apiResponse($settings,200,400);

        }catch (\Exception $exception){
            return $this->apiResponse(null,400,$exception->getMessage());
        }

    }




    /**
     * Update the specified setting in the database.
     */
    public function update(Request $request)
    {
        $validator=Validator::make($request->all(),[
                'name' => 'sometimes|required|string|max:255',
                'address' => 'sometimes|required|string',
                'phone_number' => 'sometimes|required|string|max:20',
                'email' => 'nullable|string|email|max:255',
                'opening_hours' => 'sometimes|required',
                'reservation_policy' => 'nullable|string',
                'max_capacity' => 'sometimes|required|integer',
                'currency' => 'sometimes|required|string|max:10',
                'tax_rate' => 'sometimes|required|numeric|min:0',
                'service_charge' => 'sometimes|required|numeric|min:0',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,404,$validator->errors());
        }
        try {
            $setting = RestaurantSetting::get()->first();




            $setting->update($request->all());

            return $this->apiResponse($setting,200,"updated successfully")  ;

        }catch (\Exception $exception){
            return $this->apiResponse(null,400,$exception->getMessage());
        }

    }

    /**
     * Remove the specified setting from the database.
     */

}
