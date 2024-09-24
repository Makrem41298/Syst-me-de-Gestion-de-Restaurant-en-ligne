<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContractsController extends Controller
{

    use ApiResponse;
    public function __construct()
    {
        $this->middleware(['permission:store contract'])->only('store');
        $this->middleware(['permission:show contract'])->only(['show','index']);
        $this->middleware(['permission:update contract'])->only('update');
        $this->middleware(['permission:delete contract'])->only(['destroy','deleteAll']);

    }

    public function index(Request $request)
    {
        $validator=Validator::make(request()->all(),[
            'driver_id'=>'exists:contracts,driver_id',
            'from_date'=>'date_format:Y-m-d',
            'to_date'=>'date_format:Y-m-d|after_or_equal:from_date',
            'status'=>'in:active,inactive',

        ]);
        if ($validator->fails()){
            return $this->apiResponse('null',404,$validator->errors());
        }
        try {
            $query=Contract::with('driver');
            if ($request->has('driver_id')) {
                $query->where('driver_id', $request->get('driver_id'));
            }
            if ($request->has('from_date')&&$request->has('to_date')) {
                $query->where('date_start', '>=',$request->get('from_date'))
                    ->where('date_end', '<=',$request->get('to_date'));

            }
            if ($request->has('status')) {
                if ($request->get('status') == 'active') {
                    $query->where('date_start','>=',Carbon::now());
                }else
                    $query->where('date_start','<=',Carbon::now());
            }

            return $this->apiResponse($query->get(),200,'Contracts retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,400,'retrieved failed : '.$e->getMessage());
        }

    }

    public function store(Request  $request){

        $validator=Validator::make($request->all(),[
            'driver_id'=>'required|exists:drivers,id',
            'date_start'=>'required|date',
            'date_end'=>'nullable|date|after:'.now(),
            'delivery_fee' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/'
        ]);
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {
            $driver=Driver::findOrFail($request->driver_id);
            $contract=$driver->contracts()->create($request->all());

            return $this->apiResponse($contract,201,'Contract created successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not create Contract: '.$e->getMessage());
        }


    }
    public function show($id){
        $validator=Validator::make(['id'=>$id],
            ['id'=>'required|exists:contracts,id']);
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {
            $contract=Contract::findOrFail($id)->with('driver')->first();
            return $this->apiResponse($contract,200,'Contract retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not find contract '.$e->getMessage());
        }

    }
    public function update(Request $request, $id){

        $validator=Validator::make(array_merge($request->all(),['id'=>$id]),[
            'driver_id'=>'nullable|exists:drivers,id',
            'date_start'=>'nullable|date',
            'date_end'=>'nullable|date|after:date_start',
            'delivery_fee' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'id'=>'required|exists:contracts,id',
        ]);
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {
            $contract=Contract::findOrFail($id);
            $contract->update($request->all());
            return $this->apiResponse(true,201,'contract updated successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not update contract: '.$e->getMessage());
        }

    }
    public function destroy($id){
        $validator=Validator::make(['id'=>$id],['id'=>'required|exists:contracts,id']);
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {

            $contract=Contract::findOrFail($id);
            $contract=$contract->delete();

            return $this->apiResponse($contract,200,'service deleted successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not delete service: '.$e->getMessage());
        }

    }
    public function deleteAll(){
        try {

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Contract::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return $this->apiResponse(true,'200','all contracts deleted successfully');

        }catch(\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete all the contracts: '.$e->getMessage());
        }
    }
    //
}
