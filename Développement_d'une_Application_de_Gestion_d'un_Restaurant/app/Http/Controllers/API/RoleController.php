<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use ApiResponse;
    public function __construct()
    {
        $this->middleware(['permission:store role'])->only('storeRole');
        $this->middleware(['permission:show role'])->only(['showRole','index']);
        $this->middleware(['permission:update role'])->only('updateRole');
        $this->middleware(['permission:delete role'])->only('deleteRole');

    }


    function index()
    {
        try {
            $roles = Role::with('permissions')->get();
            return $this->apiResponse($roles,400,"role retrieved successfully");

        }catch (\Exception $e){
            return $this->apiResponse(null,400,$e->getMessage());
        }

    }
    function storeRole(Request $request){
        $validator=Validator::make($request->all(),[
            'name'=>'required|string',
            'permissions'=>'required|array',
            'permissions.*'=>['required','exists:permissions,name'],

        ]);
        if($validator->fails()){
            return  $this->apiResponse(null,404,$validator->errors());
        }


        try {
            $role=Role::create(['name'=>$request->name]);
            $role->syncPermissions($request->permissions);
            return $this->apiResponse($role,200,'Role created successfully');

        }catch (\Exception $e){
            return $this->apiResponse('null',400,$e->getMessage());
        }

    }
    public function updateRole(Request $request,$id)
    {
        $validator=Validator::make(array_merge($request->all(),['role_id'=>$id]),[
            'name'=>'required|string',
            'permissions'=>'required|array',
            'permissions.*'=>['required','exists:permissions,name'],
            'role_id'=>['required',Rule::exists('roles','id')->where(function ($query) {
                $query->where('name', '<>', 'super-admin');
            })],

        ]);
        if ($validator->fails()){
            return $this->apiResponse(null,404,$validator->errors());
        }
        try {
            $role=Role::findById($id);
            $role->update(['name'=>$request->name]);
            $role->syncPermissions($request->permissions);

            return $this->apiResponse($role,200,'Role updated successfully');
        }catch (\Exception $e){
            return $this->apiResponse('null',400,$e->getMessage());
        }

    }
    public function showRole($id)
    {
        $validator=Validator::make(['role_id'=>$id],[
            'role_id'=>'required|exists:roles,id',
        ]);
        if ($validator->fails()){
            return $this->apiResponse(null,404,$validator->errors());
        }
        try {
            $role=Role::with('permissions')->findOrFail($id);
            return $this->apiResponse($role,200,'Role retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse('null',400,$e->getMessage());
        }

    }

    public function deleteRole($id)
    {
        $validator=Validator::make(['role_id'=>$id],
            ['role_id'=>Rule::exists('roles','id')->where(function ($query) {
                $query->where('name', '<>', 'super-admin');
            })
            ]);

        if ($validator->fails()){
            return $this->apiResponse(null,404,$validator->errors());
        }
        try {
            $role=Role::findById($id);
            $role->users()->detach();
            $role->permissions()->detach();
            $role->delete();
            return $this->apiResponse($role,200,'Role deleted successfully');

        }catch (\Exception $e){
            return $this->apiResponse('null',400,$e->getMessage());
        }

    }

}
