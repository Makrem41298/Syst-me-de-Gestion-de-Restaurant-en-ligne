<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SubCategoriesController extends Controller
{

    use ApiResponse;
    function __construct()
    {
        $this->middleware(['permission:delete subcategory'])->only('destroy','deleteAll');
        $this->middleware(['permission:update subcategory'])->only(['update','deleteItem','addItem']);
        $this->middleware(['permission:store subcategory'])->only('store');
        $this->middleware(['permission:update item'])->only(['deleteItem','addItem']);




    }
    public function index(Request $request)
    {
        $validator=Validator::make(request()->all(),[
            'category_id'=>'exists:sub_categories,category_id',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,404,$validator->errors());
        }
        try {
            $subCategories=SubCategory::query();
            if ($request->has('category_id')) {
                $subCategories->where('category_id', $request->get('category_id'));
            }

            return $this->apiResponse($subCategories->get(),200,'SubCategories retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Failed to retrieve sub Categories: '.$e->getMessage());

        }

    }

    public function store(Request  $request){
        $validator=validator::make($request->all(),[
            'category_id'=>'required|exists:categories,id',
            'name'=>'required|unique:sub_categories,name,NULL,id,category_id,' . $request->category_id,
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,400,$validator->errors());
        }
        try {
            $category=Category::find(request('category_id'));
            $subcategory=$category->subCategory()->create($request->all());
            return $this->apiResponse($subcategory,200,'Sub Category created successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to create sub category: '.$e->getMessage());
        }


    }


    public function show($id){
        $validator=validator::make(['id'=>$id],
            ['id'=>'required|exists:sub_categories,id']
        );
        if($validator->fails()){
            return $this->apiResponse(null,400,$validator->errors());
        }

        try {
            $subCategory=SubCategory::with('category')->where('id',$id)->first();
            return  $this->apiResponse($subCategory,200,'Sub Category retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Failed to retrieve sub Category: '.$e->getMessage());
        }

    }


    public function update(Request $request, $id){
        $validator=validator::make(array_merge($request->all(),['id'=>$id]),[
            'id'=>'required|exists:sub_categories,id',
            'category_id'=>'exists:categories,id',
            'name'=>'unique:sub_categories,name,NULL,id,category_id,' . $request->category_id,
        ]);


        if($validator->fails())
            return $this->apiResponse(null,400,$validator->errors());

        try {
            $subcategory = SubCategory::findOrFail($id);
        } catch (\Exception $e) {
            return $this->apiResponse(null, 400, 'Failed to find this sub category: ' . $e->getMessage());
        }
        try {

            return $this->apiResponse($subcategory->update($request->all()),200,'sub category updated successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to update sub Category: '.$e->getMessage());
        }

    }



    public function destroy($id){
        $validator=validator::make(['id'=>$id],
            ['id'=>'required|exists:sub_categories,id']);
        if($validator->fails()){
            return $this->apiResponse(null,400,$validator->errors());
        }
        try {
            $subcategory=SubCategory::findOrFail($id);
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to find this sub category: '.$e->getMessage());
        }
        try {

            return  $this->apiResponse($subcategory->delete(),200,'sub category deleted successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete sub category: '.$e->getMessage());
        }

    }



    public function deleteAll()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            SubCategory::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return $this->apiResponse(true,'200','all sub categories deleted successfully');

        }catch(\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete all the  sub categories: '.$e->getMessage());
        }

    }

    public function addItem(Request $request,$id){
        $validator=validator::make(array_merge(array('id'=>$id),$request->all()),[
            'id'=>'exists:sub_categories,id',
            'item_id'=>'exists:items,id',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,400,'failed validation: '.$validator->errors());
        }
        try {
            $subcategory=SubCategory::findOrFail($id);
            $item=Item::findOrFail($request->item_id);

            $item=$item->SubCategory()->associate($subcategory);

            return $this->apiResponse($item->save(),200,'sub category added successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to add item: '.$e->getMessage());
        }



    }
    public function deleteItem($item_id){
        $validator=validator::make(array($item_id),[
            '$item_id'=>'exists:items,id',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,400,$validator->errors());
        }
        try {
            $item=Item::findOrFail($item_id);
            $item=$item->SubCategory()->dissociate();
            return $this->apiResponse($item->save(),200,'item dissociate successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to dissociate item: '.$e->getMessage());
        }

    }
}
