<?php
namespace App\Http\Controllers\account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\reset_passwords as tblResetPasswords;

class index extends Controller
{
    //
    public function show($request)
    {

        $account = $request;

        $data = [
            'id'            =>  $account['id'],
            'name'          =>  $account['name'],
            'level'         =>  $account['level'],
            'sublevel'      =>  $account['sub_level'],
            'username'      =>  $account['username']
        ];

        return $data;
    }


    public function profile($request)
    {
        $account = $request;

        $data = [
            'id'            =>  $account['id'],
            'name'          =>  $account['name'],
            'email'         =>  $account['email'],
            'level'         =>  $account['level'],
            'sublevel'      =>  $account['sub_level'],
            'username'      =>  $account['username']
        ];

        return $data;
    }


    //get page change password cekin token send email
    public function getchangepassword(Request $request)
    {   
        //
        $Config = new Config;


        //
        $key = $request->header('key');

        //
        $ceking = tblResetPasswords::where([
            'token'         =>  trim($request->header('key')),
            'status'        =>  1
        ])->first();
    


        if( $ceking == null)
        {
            return response()->json([
                'message'=>'Key/Token tidak valid atau kadaluwarsa'
            ], 404);
        }

        
        return response()->json(['message'=>''],200);
    }
    
}