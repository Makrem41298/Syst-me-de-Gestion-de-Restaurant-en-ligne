<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoriesController extends Controller implements ManagerTable
{
    use ApiResponse;
    public function __construct()
    {
        $this->middleware(['permission:delete category'])->only(['destroy','deleteAll']);
        $this->middleware(['permission:update category'])->only('update');
        $this->middleware(['permission:store category'])->only('store');
    }
    public function index()
    {
        try {
            $Categories=Category::all();
            return $this->apiResponse($Categories,200,'Categories retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Failed to retrieve Categories: '.$e->getMessage());

        }

    }

    public function store(Request  $request){
        $validator=validator::make($request->all(),[
            'name'=>'required|unique:categories,name',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,400,$validator->errors());
        }
        try {
            $category=Category::create($request->all());
            return $this->apiResponse($category,200,'Category created successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to create Category: '.$e->getMessage());
        }


    }


    public function show($id){
        $validator = Validator::make(['id'=>$id],['id'=>'required|exists:categories,id']);
        if($validator->fails()){
            return $this->apiResponse(null,400,$validator->errors());
        }

        try {
            $Category=Category::findOrFail($id);
            return  $this->apiResponse($Category,200,'Category retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse(null,404,'Failed to retrieve Category: '.$e->getMessage());
        }

    }


    public function update(Request $request, $id){
        $validator=validator::make(array_merge($request->all(),['id'=>$id]),[
            'id'=>'required|exists:categories,id',
            'name'=>'required|unique:categories,name,'.$id,
        ]);


        if($validator->fails())
            return $this->apiResponse(null,400,$validator->errors());


        try {
            $category=Category::findOrfail($id)->update($request->all());

            return $this->apiResponse($category,200,'Category updated successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to update tCategory: '.$e->getMessage());
        }

    }



    public function destroy($id){
        try {
            $category=Category::findOrFail($id);
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to find this Category: '.$e->getMessage());
        }
        try {

            return  $this->apiResponse($category->delete(),200,'Category deleted successfully');
        }catch (\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete Category: '.$e->getMessage());
        }

    }



    public function deleteAll()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Category::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return $this->apiResponse(true,'200','all $categories deleted successfully');

        }catch(\Exception $e){
            return $this->apiResponse(null,400,'Failed to delete all the categories: '.$e->getMessage());
        }

    }

    //
}
