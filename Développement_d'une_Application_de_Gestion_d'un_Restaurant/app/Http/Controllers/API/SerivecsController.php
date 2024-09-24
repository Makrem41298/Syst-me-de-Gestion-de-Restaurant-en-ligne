<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Rating;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SerivecsController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:store service'])->only('store');
        $this->middleware(['permission:update service'])->only('update');
        $this->middleware(['permission:delete service'])->only(['destroy','deleteAll']);

    }
    use ApiResponse;

    public function index()
    {
        try {
            $services = Service::select('services.*', DB::raw('avg(ratingables.rating) as Avg_rating'))
                ->leftJoin('ratingables', function($join) {
                    $join->on('ratingables.ratingable_id', '=', 'services.id')
                        ->where('ratingables.ratingable_type', '=', Service::class);
                })
                ->groupBy('services.id')
                ->get();
            return $this->apiResponse($services,200,'rservices retrieved successfully');


        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Erreur : '.$e->getMessage());
        }

    }

    public function store(Request  $request){

        $validator=Validator::make($request->all(),[
            'name'=>'unique:services,name,',
        ]);
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());


        try {
            $service=Service::create($request->all());
            return $this->apiResponse($service,201,'$service created successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not create $service: '.$e->getMessage());
        }


    }
    public function show($id){
        $validator = Validator::make(['id' => $id],
            ['id' => 'required|numeric|exists:services,id']);
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {
            $service=Service::select('services.*', DB::raw('avg(ratingables.rating) as Avg_rating'))
                ->leftJoin('ratingables', function($join) {
                    $join->on('ratingables.ratingable_id', '=', 'services.id')
                        ->where('ratingables.ratingable_type', '=', Service::class);
                })
                ->groupBy('services.id')
                ->where('services.id',$id)
                ->get();
            return $this->apiResponse($service,200,'service retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not find service '.$e->getMessage());
        }

    }
    public function update(Request $request, $id){
        $validator=Validator::make(array_merge($request->all(),['id' => $id]),[
            'name'=>'unique:services,name,'.$id,
            'id'=>'required|numeric|exists:services,id',
        ]);
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {
            $service=Service::findOrFail($id);
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not find service: '.$e->getMessage());
        }

        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {
            $service = $service->update($request->all());
            return $this->apiResponse($service,200,'service updated successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not update service: '.$e->getMessage());
        }


    }
    public function destroy($id){
        $validator = Validator::make(['id' => $id],
        ['id' => 'required|numeric|exists:services,id']);
        if ($validator->fails())
            return $this->apiResponse(null,406,'validation errors : '.$validator->errors());

        try {

            $service=Service::findOrFail($id);
            Rating::where([['ratingable_id',$id],['ratingable_type',Service::class]])->delete();
            $service=$service->delete();

            return $this->apiResponse( $service,200,'service deleted successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'can not delete service: '.$e->getMessage());
        }

    }
    public function deleteAll(){
        try {

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Service::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Rating::where('ratingable_type',Service::class)->delete();
            return $this->apiResponse(true,'200','all services deleted successfully');

        }catch(\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete all the services: '.$e->getMessage());
        }
    }
    public function ratingService(Request $request,$serviceId){
        $validator=Validator::make(array_merge($request->all(),array('serviceId'=>$serviceId)), [
            'serviceId'=>['exists:services,id',Rule::unique('ratingables','ratingable_id')->where(function ($query){
                return $query->where('ratingable_type', Service::class)->where('user_id', auth()->guard('users')->user()->getAuthIdentifier());
            })],
            'rating'=>'required|integer|min:1|max:5',
        ]);

        if($validator->fails()){
            return $this->apiResponse(null,'400','Validation Error'.$validator->errors());
        }
        try {
            $userId=auth()->guard('users')->user()->getAuthIdentifier();
            $service=Service::findOrFail($serviceId);
            $service->users()->attach($userId,['rating'=>$request->rating,'comment'=>$request->get('comment')]);
            return $this->apiResponse(true, 200, 'ratings added successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Erreur : '.$e->getMessage());
        }

    }
    public function updateRatingService(Request $request,$serviceId){
        $validator=Validator::make(array_merge($request->all(),array('itemId'=>$serviceId)), [
            'rating'=>'required|integer|min:1|max:5',
            'serviceId'=>['exists:items,id',Rule::exists('ratingables','ratingable_id')->where(function ($query){
                $query->where('ratingable_type',Service::class)->where('user_id',auth()->guard('users')->user()->getAuthIdentifier());
            })],
            'comment'=>'nullable|string'
        ]);


        if($validator->fails()){
            return $this->apiResponse(null,'400','Validation Error'.$validator->errors());
        }
        try {
            $userId=auth()->guard('users')->user()->getAuthIdentifier();
            $service=Service::findOrFail($serviceId);
            $service->users()->updateExistingPivot($userId,['rating'=>$request->rating,'comment'=>$request->get('comment')]);
            return $this->apiResponse(true, 200, 'ratings  updated successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Erreur : '.$e->getMessage());
        }

    }



}
