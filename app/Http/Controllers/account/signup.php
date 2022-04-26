<?php
namespace App\Http\Controllers\account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\users as tblUsers;
use App\user_registers as tblUserRegisters;
use App\auto_senders as tblAutoSenders;
use App\Http\Controllers\config\index as Config;
use Illuminate\Support\Facades\Hash;

class signup extends Controller
{
    //
    public function main($request)
    {
        //
        $Config = new Config;


        $datauser = [
            'name'          =>  $request['name'],
            'email'         =>  $request['email'],
            'password'      =>  $request['password'],
            'username'      =>  '',
            'level'         =>  $request['level'] ? $request['level'] : 0,
            'sub_level'     =>  $request['level'] ? $request['sub_level'] : 0,
            'company_id'       =>  $request['company_id'] ? $request['company_id'] : 0,
            'admin_id'      =>  $request['admin_id'] ? $request['admin_id'] : 0,
            'gender'        =>  0
        ];

        $addnewuser = new \App\Http\Controllers\models\users;
        $addnewuser = $addnewuser->new($datauser);
        // // end add user



        //add registers
        $dataregister = [
            'user_id'           =>  $addnewuser['id'],
            'info'              =>  $request['info']            
        ];

        $addnewregister = new \App\Http\Controllers\models\users;
        $addnewregister = $addnewregister->registers($dataregister);



        //insert table automation
        //field info sender
        $infoautosender = [
            'user'          =>  [
                'id'                =>  $addnewuser['id'],
                'email'             =>  $request['email'],
                'name'              =>  $request['name']
            ],
            'apps'          =>  [
                'name'              =>  $Config->apps()['crm']['name'],
                'url'               =>  $Config->apps()['crm']['url'],
                'url_help'          =>  $Config->apps()['crm']['url_help'],
                'url_logo'          =>  $Config->apps()['company']['url_logo'],
                'url_link'  =>  $Config->apps()['crm']['url'] . '/account/verification?token=' . md5($addnewregister['id'])
            ]
        ];

        $infotemplate = [
            'id'       =>  10001,
            'dir'      =>  'verifaccount'
        ];


        $dataautosender = [
            'user_id'           =>  $addnewuser['id'],
            'type'              =>  1, //1. access
            'sub_type'          =>  1, //1. verif account,
            'sender_type'       =>  1, //1. send by email
            'sender_id'         =>  10001,
            'infotemplate'      =>  $infotemplate,
            'infosender'        =>  $infoautosender,
        ];
        

        $addnewautosender = new \App\Http\Controllers\models\autosenders;
        $addnewautosender = $addnewautosender->email($dataautosender);

        //
        
        try{
            
            return ['message'=>'','response'=>'/registers/success?token=' . md5($addnewuser['id']) ];
        }
        catch  (Exception $e)
        {
            return ['message'=>$e->getMessage()];
        }

        
        
    }




    

}