<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{

    use ApiResponse;

    public function __construct()
    {
        $this->middleware(['permission:store employ'])->only('storeEmployee');
        $this->middleware(['permission:show employ'])->only(['showEmployee','index']);
        $this->middleware(['permission:update employ'])->only('updateEmployee');
        $this->middleware(['permission:delete employ'])->only(['deleteEmployee','deleteAllEmployee']);

    }

    public function index(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'role'=>'nullable|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,404,$validator->errors());
        }
        try {
            $query=Admin::with('roles')->whereDoesntHave('roles',function($q){
                $q->where('name','Super-Admin');
            });
            if ($request->has('role')){
                $query->whereHas('roles',function($query) use ($request){
                    $query->where('name',$request->get('role'));
                });
            }
            $employees=$query->get();
            return $this->apiResponse($employees,200,'employ retrieved successfully');

        }catch (\Exception $e){
            return $this->apiResponse('null',400,$e->getMessage());
        }

    }
    public function storeEmployee(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'name'=>'required|string',
            'email'=>'required|string|email|unique:admins',
            'password'=>'required|string|confirmed|min:6',
            'role'=>'required|exists:roles,name',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'gender'     => 'required|in:male,female',
            'birthday'   => 'required|date|before:today',
            'address'    => 'required|string|max:255',
            'city'       => 'required|string|max:255'

        ]);
        if($validator->fails()){
            return $this->apiResponse(null,404,$validator->errors());
        }
        try {
            $employee=Admin::create(array_merge($request->except('role','password'),['password'=>bcrypt($request->password)]));
            $employee->profile()->create($request->only('first_name','last_name','phone','gender','birthday','address','city'));
            $role=Role::findByName($request->role);
            $employee->assignRole($role);
            return $this->apiResponse($employee,200,'Employee created successfully');
        }catch (\Exception $e){
            return $this->apiResponse('null',400,$e->getMessage());
        }
    }
    public function updateEmployee(Request $request,$id){
        $validator=Validator::make(array_merge($request->all(),['employee_id'=>$id]),[
            'name'=>'required|string',
            'email'=>'required|string|email|unique:admins,email,'.$id,
            'password'=>'nullable|string|confirmed|min:6',
            'employee_id'=>'required|exists:admins,id',
            'role'=>'required|exists:roles,name',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'gender'     => 'required|in:male,female',
            'birthday'   => 'required|date|before:today',
            'address'    => 'required|string|max:255',
            'city'       => 'required|string|max:255'

        ]);
        if($validator->fails()){
            return $this->apiResponse(null,404,$validator->errors());
        }
        try {
            $employee=Admin::findOrfail($id);
            $employee->update($request->except('role','first_name','last_name','phone','gender','birthday','address','city','password'));
            if($request->has('password')&&$request->password!=null){
                $employee->update(['password'=>bcrypt($request->password)]);
            }
            $employee->profile()->update($request->only('first_name','last_name','phone','gender','birthday','address','city'));
            $employee->syncRoles($request->role);
            return $this->apiResponse($employee,200,'Employee updated successfully');

        }catch (\Exception $e){
            return $this->apiResponse('null',400,$e->getMessage());
        }
    }
    public function updateMyPassword(Request $request){
        $validator=Validator::make($request->all(),[
            'password_actual'=>'required|string|min:6',
            'password'=>'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return $this->apiResponse(null,404,$validator->errors());
        }
        try {
            $user=auth('admins')->user();
            if (!(Hash::check($request->get('password_actual'),$user->getAuthPassword())))
                return $this->apiResponse('null',400,'Password Actual incorrect');


            $user->update(['password'=>bcrypt($request->password)]);
            return $this->apiResponse($user,200,'Password updated successfully');

        }catch (\Exception $e){
            return $this->apiResponse('null',400,$e->getMessage());
        }
    }
    public function showEmployee($id)
    {
        $validator=Validator::make(['employee_id'=>$id],
            ['employee_id'=>'required|exists:admins,id']);
        if($validator->fails()){
            return $this->apiResponse(null,404,$validator->errors());
        }
        try {
            $employee=Admin::with('profile')->findOrfail($id);
            return $this->apiResponse($employee,200,'Employee retrieved successfully');

        } catch (\Exception $e){
            return $this->apiResponse('null',400,$e->getMessage());
        }
    }
    public function deleteEmployee($id)
    {
        $validator = Validator::make(
            ['employee_id' => $id],
            [
                'employee_id' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $exists = \App\Models\Admin::where('id', $value)
                            ->whereDoesntHave('roles', function ($query) {
                                $query->where('name', 'Super-Admin');
                            })
                            ->exists();

                        if (!$exists) {
                            $fail('The selected ' . $attribute . ' is invalid.');
                        }
                    },
                ],
            ]
        );
        if($validator->fails()){
            return $this->apiResponse(null,404,$validator->errors());
        }
        try {
            $employee=Admin::findOrfail($id);
            $employee->roles()->detach();
            $employee->profile()->delete();
            $employee->delete();
            return $this->apiResponse('true',400,'employee deleted successfully');
        }catch (\Exception $e){
            return $this->apiResponse('null',400,$e->getMessage());
        }
    }
    public function deleteAllEmployee()
    {
        Admin::whereDoesntHave('roles', function($query) {
            $query->where('name', 'Super-Admin');
        })->each(function($admin) {
            $admin->roles()->detach();
            $admin->delete();
        });
        Profile::where('profileable_type',Admin::class)->delete();

    }
    //
}
