<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use   ApiResponse;
    public function __construct()
    {
        $this->middleware(['permission:store role|update role']);

    }

    public function __invoke()
    {
        try {
           $permission= Permission::all();
           return $this->apiResponse($permission,400,'retrieved successfully');

        }catch (\Exception $exception){
            return $this->apiResponse(null,400,$exception->getMessage());
        }
    }
}
