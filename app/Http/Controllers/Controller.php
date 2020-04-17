<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function returnJson($status, $message = 'success', $data = array()){
        $result = array(
            'status' => $status,
            'message' =>$message,
        );
        $result['data'] = $data;
        return response()->json($result);
    }

}
