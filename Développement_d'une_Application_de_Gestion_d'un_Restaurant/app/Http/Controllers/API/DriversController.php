<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DriversController extends Controller
{
    use ApiResponse;
    public function __construct()
    {
        $this->middleware(['permission:store driver'])->only('store');
        $this->middleware(['permission:show driver'])->only(['show','index']);
        $this->middleware(['permission:update driver'])->only('update');
        $this->middleware(['permission:delete driver'])->only(['destroy','deleteAll']);



    }

    public function index()
    {
        try {
            return $this->apiResponse(Driver::all(),200,'Driver retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,400,'retrieved failed : '.$e->getMessage());
        }

    }

    public function store(Request  $request){

        $validator=Validator::make($request->all(),[
            'first_name'=>'required',
            'last_name'=>'required',
            'phone'=>'required|unique:drivers,phone',
            'email'=>'required|unique:drivers,email',

        ]);
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());


        try {
            $driver=Driver::create($request->all());
            return $this->apiResponse($driver,201,'Driver created successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not create driver: '.$e->getMessage());
        }


    }
    public function show($id){
        $validator=Validator::make(['id'=>$id],
            ['id'=>'required|numeric|exists:drivers,id']
        );
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {
            $driver=Driver::findOrFail($id);
            return $this->apiResponse($driver,200,'Driver retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not find driver: '.$e->getMessage());
        }

    }
    public function update(Request $request, $id){
        $validator=Validator::make(array_merge($request->all(),['id'=>$id]),[
            'phone'=>'unique:drivers,phone,'.$id,
            'email'=>'unique:drivers,email,'.$id,
            'id'=>'required|numeric|exists:drivers,id'
        ]);
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {
            $driver=Driver::findOrFail($id);
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not find driver: '.$e->getMessage());
        }

        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {
            $driver = $driver->update($request->all());
                return $this->apiResponse($driver,200,'Driver updated successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not update driver: '.$e->getMessage());
        }


    }
    public function destroy($id){
        $validator=Validator::make(['id'=>$id],
        ['id'=>'required|numeric|exists:drivers,id']);
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {

            $driver=Driver::findOrFail($id);
                $driver=$driver->delete();

            return $this->apiResponse(  $driver,200,'Driver deleted successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not delete driver: '.$e->getMessage());
        }

    }
    public function deleteAll(){
        try {

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Driver::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return $this->apiResponse(true,'200','all Drivers deleted successfully');

        }catch(\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete all the Drivers: '.$e->getMessage());
        }
    }
}
