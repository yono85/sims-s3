<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\user_registers as tblUserRegisters;
use App\users as tblUsers;
use App\user_configs as tblUserConfigs;
use Illuminate\Support\Facades\Hash;

class users extends Controller
{

    //new users
    public function new($request)
    {
        $Config = new Config;

        //
        $newidusers = tblUsers::count();
        $newidusers++;
        $newidusers = '9' . sprintf('%010s', $newidusers++);

        //
        $users = new tblUsers;
        $users->id          =   $newidusers;
        $users->token       =   md5($newidusers);
        $users->name        =   $request['name'];
        $users->email       =   $request['email'];
        $users->password    =   Hash::make($request['password']);
        $users->username    =   $request['username'];
        $users->level       =   $request['level'];
        $users->sub_level   =   $request['sub_level'];
        $users->gender      =   $request['gender'];
        $users->registers   =   0;
        $users->status      =   1;
        $users->save();


        //add user config
        $dataconfig = [
            'user_id'       =>  $newidusers,
            'request'       =>  $request
        ];

        $adduserconfig = $this->userconfig($dataconfig);

        //
        $data = [
            'id'        =>  $newidusers
        ];
        return $data;

    }


    public function userconfig($request)
    {
        //config
        $Config = new Config;

        //request
        $user_id = $request['user_id'];
        $request = $request['request'];

        //
        $newidconfig = tblUserConfigs::count();
        $newidconfig++;
        $newidconfig = $newidconfig++;

        
        //create new id
        $newidconfig = $Config->createnewid([
            'value'         =>  $newidconfig,
            'length'        =>  11
        ]);

        $newaddconfig = new tblUserConfigs;
        $newaddconfig->id       =   $newidconfig;
        $newaddconfig->type     =   $request['level'];
        $newaddconfig->user_id  =   $user_id;
        $newaddconfig->company_id       =   $request['company_id'];
        $newaddconfig->homepage         =   '/dashboard';
        $newaddconfig->aside_id         =   1;
        $newaddconfig->admin_id         =   $request['admin_id'];
        $newaddconfig->status           =   1;
        $newaddconfig->save();



    }

    //insert registers
    public function registers($request)
    {

        //
        $Config = new Config;

        //
        $upregisters = tblUserRegisters::where([
            'user_id'       =>  $request['user_id']
        ])->update([
            'status'        =>  0
        ]);

        //
        $newidregisters = tblUserRegisters::count();
        $newidregisters++;
        $newidregisters = $newidregisters++;

        //create new code
        $newcode = $Config->createuniqnum([
            'value'         =>  $newidregisters,
            'length'        =>  4
        ]);
        
        //create new id
        $newidregisters = $Config->createnewid([
            'value'         =>  $newidregisters,
            'length'        =>  11
        ]);

        // //
        $geoip = json_decode($request['info'], true)['geoip'];
        $uagent = json_decode($request['info'],true)['uagent'];



        $registers = new tblUserRegisters;
        $registers->id              =   $newidregisters;
        $registers->user_id         =   $request['user_id'];
        $registers->token           =   md5($newidregisters);
        $registers->code            =   $newcode;
        $registers->ip_address      =   $geoip['ip'];
        $registers->device          =   $uagent['device'];
        $registers->info            =   $request['info'];
        $registers->status          =   1;
        $registers->save();

        // return $newidregisters;

        $data = [
            'id'        =>  $newidregisters
        ];
        return $data;

    }


}