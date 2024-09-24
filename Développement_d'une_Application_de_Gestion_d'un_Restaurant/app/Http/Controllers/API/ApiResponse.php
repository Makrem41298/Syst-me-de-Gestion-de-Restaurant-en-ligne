<?php

namespace App\Http\Controllers\API;

trait ApiResponse
{
    public function apiResponse($data=null, $status=0,$message=null)
    {
        return response(['date'=>$data,'status'=>$status,'message'=>$message]) ;
    }

}
