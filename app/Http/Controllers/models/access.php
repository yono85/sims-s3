<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use App\Http\Controllers\config\index as Config;
use Illuminate\Http\Request;
use App\reset_passwords as tblResetPasswords;
use App\auto_senders as tblAutoSenders;

class access extends Controller
{


    //reset password
    public function resetpassword($request)
    {
        //
        $Config = new Config;

        //get count row
        $newid = tblResetPasswords::count();
        $newid++;
        $newid = $newid++;


        //create new id
        $newid = $Config->createnewid([
            'value'         =>  $newid,
            'length'        =>  14
        ]);


        $geopip = json_decode($request['info'], true)['geoip'];
        $uagent = json_decode($request['info'], true)['uagent'];


        //update
        $upresetpassword = tblResetPasswords::where([
            'user_id'           =>  $request['user_id'],
            'status'            =>  1
        ])->update(['status'=>0]);

        $addresetpassword = new tblResetPasswords;
        $addresetpassword->id           =   $newid;
        $addresetpassword->token        =   md5($newid);
        $addresetpassword->user_id      =   $request['user_id'];
        $addresetpassword->ip_address   =   $geopip['ip'];
        $addresetpassword->device       =   $uagent['device'];
        $addresetpassword->info         =   $request['info'];
        $addresetpassword->status       =   1;
        $addresetpassword->save();


        $infotemplate = [
            'id'            =>  10003,
            'dir'           =>  'resetpassword'
        ];

        //level root
        $levelroot = $request['user_level'] === 1 ? 'apps' : ( $request['user_level'] === 2 ? 'crm' : ( $request['user_level'] === 3 ? 'distributor' : 'user') );

        $infoautosender = [
            'user'          =>  [
                'id'                =>  $request['user_id'],
                'email'             =>  $request['email'],
                'name'              =>  $request['name']
            ],
            'apps'          =>  [
                'name'              =>  $Config->apps()[$levelroot]['name'],
                'url'               =>  $Config->apps()[$levelroot]['url'],
                'url_help'          =>  $Config->apps()[$levelroot]['url_help'],
                'url_logo'          =>  $Config->apps()['company']['url_logo'],
                'url_link'          =>  $Config->apps()[$levelroot]['url'] . '/account/verification?token=' . md5($newid)
            ]
        ];


        $dataautosender = [
            'user_id'           =>  $request['user_id'],
            'email'             =>  $request['email'],
            'name'              =>  $request['name'],
            'info'              =>  $request['info'],
            'type'              =>  1, //1. access
            'sub_type'          =>  3, //3. reset password
            'sender_type'       =>  1, //1. send by email
            'sender_id'         =>  10001,
            'infotemplate'      =>  $infotemplate,
            'infosender'        =>  $infoautosender
        ];

        $addautosender = new \App\Http\Controllers\models\autosenders;
        $addautosender = $addautosender->email($dataautosender);

        return $newid;
    }
}