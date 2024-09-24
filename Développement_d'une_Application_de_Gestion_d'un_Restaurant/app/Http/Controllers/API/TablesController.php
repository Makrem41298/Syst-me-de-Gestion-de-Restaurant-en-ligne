<?php

namespace App\Http\Controllers\API;

use App\Enums\BookingStatus;
use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Table;
use BenSampo\Enum\Rules\Enum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TablesController extends Controller
{
    use ApiResponse;



   public function __construct()
    {
        $this->middleware(['permission:delete table'])->only(['destroy', 'deleteAll']);
        $this->middleware(['permission:update table'])->only(['update']);
        $this->middleware(['permission:store table'])->only(['store']);
        $this->middleware(['permission:show table'])->only(['show','index']);
    }


    public function index(Request $request)
    {

        try {
            $query=Table::query();
            if ($request->has('status')){
                $query->where('status',$request->get('status'));
            }
            return $this->apiResponse($query->get(),200,'tables retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Failed to retrieve tables: '.$e->getMessage());

        }

    }

    public function store(Request  $request){
        $validator=validator::make($request->all(),[
           'number_place'=>'required|integer|min:1',
            'id'=>'integer|min:1|unique:tables,id',

        ]);
        if($validator->fails()){
            return $this->apiResponse(null,400,$validator->errors());
        }
        try {
            $table=Table::create($validator->validated());
            return $this->apiResponse($table,200,'table created successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to create tables: '.$e->getMessage());
        }


    }


    public function show($id){

        try {
            $table=Table::findOrFail($id);
            return  $this->apiResponse($table,200,'table retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Failed to retrieve table: '.$e->getMessage());
        }

    }


    public function update(Request $request, $id){
        $validator=validator::make(array_merge($request->all(),['id'=>$id]),[
            'number_place'=>'integer|min:1',
            'status' => ['required', Rule::in(TableStatus::getValues())],
            'id'=>'integer|min:1|exists:tables,id',

        ]);


        if($validator->fails())
            return $this->apiResponse(null,400,$validator->errors());

        try {
            $table = Table::findOrFail($id);
        } catch (\Exception $e) {
            return $this->apiResponse(null, 404, 'Failed to find this table: ' . $e->getMessage());
        }
        try {

            return $this->apiResponse($table->update($request->all()),200,'table updated successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to update table: '.$e->getMessage());
        }

    }



    public function destroy($id){
        $validator=validator::make(['id'=>$id],[
            'id'=>'integer|min:1|exists:tables,id',

        ]);


        if($validator->fails())
            return $this->apiResponse(null,400,$validator->errors());


        try {
            $table=Table::findOrfail($id)->delete();

            return  $this->apiResponse($table,200,'table deleted successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete table: '.$e->getMessage());
        }

    }



    public function deleteAll()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Table::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return $this->apiResponse(true,'200','all tables deleted successfully');

        }catch(\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete all the tables: '.$e->getMessage());
        }

    }
    //
}
