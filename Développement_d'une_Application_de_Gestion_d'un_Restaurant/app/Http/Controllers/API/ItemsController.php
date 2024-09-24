<?php

namespace App\Http\Controllers\API;

use App\Enums\ImageType;
use App\Enums\ItemStatus;
use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Item;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:store store item'])->only('store');
        $this->middleware(['permission:update update item'])->only('update');
        $this->middleware(['permission:delete delete delete item'])->only(['destroy','deleteAll']);

    }

    use ApiResponse;

    public function index(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'status_item'=>Rule::in(ItemStatus::getValues()),
            'min_price'=>'numeric|min:0',
            'max_price' => [
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($value < request('min_price')) {
                        $fail("The $attribute must be greater than or equal to the min_price.");
                    }
                },
            ],
            'sub_category_id'=>'exists:items,sub_category_id',


        ]);
        if($validator->fails()){
            return $this->apiResponse('null',404,$validator->errors());
        }

        try {
            $query = Item::select('items.*', DB::raw('avg(ratingables.rating) as Avg_rating'))
                ->leftJoin('ratingables', function($join) {
                    $join->on('ratingables.ratingable_id', '=', 'items.id')
                        ->where('ratingables.ratingable_type', Item::class);
                })
                ->groupBy('items.id');
            if (request('sub_category_id')) {
                $query->where('items.sub_category_id', request('sub_category_id'));
            }

            if ($request->has('status_item')) {
                $query->where('items.status', $request->get('status_item'));
            }

            if ($request->has('min_price') && $request->get('min_price') && $request->has('max_price') && $request->get('max_price')) {
                $query->whereBetween('items.price', [
                    $request->get('min_price'),
                    $request->get('max_price')
                ]);
            }

            return $this->apiResponse($query->with('images')->get(), 200, 'Items retrieved successfully');


        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Erreur : '.$e->getMessage());
        }

    }

    public function store(Request  $request){
        $validator=validator::make($request->all(),[
            'name'=>'required|unique:items,name',
            'sub_category_id'=>'nullable|exists:sub_categories,id',
            'status'=>'nullable',Rule::in(ItemStatus::getValues()),
            'image_primary' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'image_gallery'=>'nullable|array',
            'image_gallery.*' => 'image|mimes:jpeg,jpg,png|max:2048',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,400,$validator->errors());
        }
        try {
            $item=Item::create($request->except(['image_gallery','image_primary']));

            if ($request->file('image_primary')!=null) {
                $pathPrimary=Storage::put('images_item/primary',$request->file('image_primary'));
            }
            $item->images()->create([
                'path'=>isset($pathPrimary)?$pathPrimary:null,
                'type'=>ImageType::primary
            ]);
            if ($request->file('image_gallery')!=null) {
                foreach (request('image_gallery') as $image){
                $pathGallery=Storage::put('images_item/gallery',$image);
                $item->images()->create([
                    'path'=>$pathGallery,
                    'type'=>ImageType::gallery
                ]);
                }
            }


            return $this->apiResponse($item,200,'Item created successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to create Item: '.$e->getMessage());
        }


    }


    public function show($id){
        $validator=validator::make(['id'=>$id],['id'=>'required|exists:items,id']);
        if($validator->fails()){
            return $this->apiResponse(null,400,$validator->errors());
        }

        try {
            $item=Item::select( 'items.*',DB::raw('avg(ratingables.rating) as Avg_rating'))
                ->leftJoin('ratingables', function ($join){
                    $join->on('ratingables.ratingable_id', '=', 'items.id')
                        ->where('ratingables.ratingable_type', Item::class);
                })
                ->where('items.id', $id)
                ->groupBy( 'items.id')->with('users','images')->get();

            return  $this->apiResponse($item,200,'Item retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Failed to retrieve item: '.$e->getMessage());
        }

    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id' => 'required|exists:items,id',
            'sub_category_id' => 'exists:sub_categories,id',
            'status' => ['nullable', Rule::in(ItemStatus::getValues())],
            'image_primary' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'image_gallery' => 'required|array',
            'image_gallery.*' => 'image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null, 400, $validator->errors());
        }

        try {
            $item = Item::findOrFail($id);
            $item->update($request->except(['image_gallery', 'image_primary']));

            // Handle the primary image
            if ($request->file('image_primary')!=null) {
                $pathPrimary=Storage::put('images_item/primary',$request->file('image_primary'));
            }
            Storage::delete($item->images()->where('type', ImageType::primary)->pluck('path')->toArray());

            $item->images()->updateOrCreate(
                ['type' => ImageType::primary],
                ['path' => $pathPrimary]
            );
            if ($request->file('image_gallery')!=null) {
                Storage::delete($item->images->where('type', ImageType::gallery)->pluck('path')->toArray());
                $item->images()->where('type', ImageType::gallery)->delete();
                foreach (request('image_gallery') as $image){
                    $pathGallery=Storage::put('images_item/gallery',$image);
                    $item->images()->create([
                        'path' => $pathGallery,
                        'type'=>ImageType::gallery
                    ]);

                }

            }



            return $this->apiResponse($item, 200, 'Item updated successfully');
        } catch (\Exception $e) {
            return $this->apiResponse(null, 400, 'Failed to update item: ' . $e->getMessage());
        }
    }




    public function destroy($id){
        $validator=Validator::make(['id'=>$id],[
            'id'=>'required|numeric|exists:items,id',
        ]);

        if($validator->fails())
            return $this->apiResponse(null,400,$validator->errors());
        try {

            $item=Item::findOrFail($id);
            Storage::delete($item->images()->pluck('path')->toArray());

            Rating::where([['ratingable_id',$id],['ratingable_type',Item::class]])->delete();
            $item->delete();

            return  $this->apiResponse($item->delete(),200,'item deleted successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete item: '.$e->getMessage());
        }

    }



    public function deleteAll()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Storage::deleteDirectory('/images_item');
            Image::truncate();
            Item::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Rating::where('ratingable_type',Item::class)->delete();
            return $this->apiResponse(true,'200','all item deleted successfully');

        }catch(\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete all the  item: '.$e->getMessage());
        }

    }
    public function ratingItme(Request $request,$itemId){
        $validator=Validator::make(array_merge($request->all(),array('itemId'=>$itemId)), [
            'itemId'=>['exists:items,id'],
            'rating'=>'required|integer|min:1|max:5',
            'comment'=>'nullable|string'

        ]);

        if($validator->fails()){
            return $this->apiResponse(null,'400','Validation Error'.$validator->errors());
        }
        try {
            $userId=auth()->guard('users')->user()->getAuthIdentifier();
            $Item=Item::findOrFail($itemId);
            $Item->users()->attach($userId,['rating'=>$request->rating,'comment'=>$request->get('comment')]);
            return $this->apiResponse(true, 200, 'ratings added successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Erreur : '.$e->getMessage());
        }

    }
    public function updateRatingItem(Request $request,$itemId){
        $validator=Validator::make(array_merge($request->all(),array('itemId'=>$itemId)),
            [
            'rating'=>'required|integer|min:1|max:5',
             'itemId'=>['exists:items,id',Rule::exists('ratingables','ratingable_id')->where(function ($query){
                 $query->where('ratingable_type',Item::class)->where('user_id',auth('users')->user()->getAuthIdentifier());
             })],

            'comment'=>'nullable|string'


            ]);


        if($validator->fails()){
            return $this->apiResponse(null,'400','Validation Error'.$validator->errors());
        }
        try {
            $userId=auth('users')->user()->getAuthIdentifier();
            $Item=Item::findOrFail($itemId);
            $Item->users()->updateExistingPivot($userId,['rating'=>$request->rating,'comment'=>$request->get('comment')]);
            return $this->apiResponse(true, 200, 'ratings  updated successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Erreur : '.$e->getMessage());
        }

    }


    //
}
