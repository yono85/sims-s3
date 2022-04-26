<?php
namespace App\Http\Controllers\account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user_registers as tblUserRegisters;
use App\users as tblUsers;
use App\auto_senders as tblAutoSenders;
use App\user_configs as tblUserConfigs;
use App\user_companies as tblUserCompanies;
use App\reset_passwords as tblResetPasswords;
use App\Http\Controllers\config\index as Config;
use Illuminate\Support\Facades\Hash;
use DB;

class manage extends Controller
{
    //register success
    public function registersuccess(Request $request)
    {

        $ceking = tblUsers::select(
            'id','name','level','sub_level','email'
        )
        ->where([
            'token'         =>  $request->header('key'),
            'registers'     =>  0
        ])
        ->first();

        $response = $ceking === null ? '' : ['id'=>$ceking->id,'name'=>$ceking->name, 'email'=>$ceking->email];
        
        //
        $data = [
            'message'           =>  $ceking === null ? 'Key/Token not found' : '',
            'response'          =>  $response
        ];

        $status = $ceking === null ? 404 : 200;
        
        return response()->json($data, $status);
    }


    // resend verification account
    public function reverifaccount(Request $request)
    {

        //
        $Config = new Config;

        //
        $thisday = date('Y-m-d', time());

        //
        $ceking = tblUsers::where([
            'token'         =>  $request->token,
            'registers'     =>  0
        ])->first();


        //hendling if null
        if( $ceking == null )
        {
            $data = [
                'message'           =>  'Token tidak valid'
            ];

            return response()->json($data, 401);
        }



        $cekregisters = tblUserRegisters::where([
            ['user_id', '=',    $ceking->id],
            ['created_at',  'like', '%' . $thisday. '%']
        ])->count();

        if( $cekregisters >= 3 )
        {
            $data = [
                'message'       =>  'Permintaan verifikasi Akun dibatasi maksimal 3x dalam 1 hari'
            ];

            return response()->json($data, 401);
        }


        //
        // call registers
        $dataregisters = [
            'user_id'       =>  $ceking->id,
            'info'          =>  $request->info
        ];

        $addregisters = new \App\Http\Controllers\models\users;
        $addregisters = $addregisters->registers($dataregisters);
        


        //call autosenders
        $infosender = [
            'user'          =>  [
                'id'                =>  $ceking->id,
                'email'             =>  $ceking->email,
                'name'              =>  $ceking->name
            ],
            'apps'          =>  [
                'name'              =>  $Config->apps()['crm']['name'],
                'url'               =>  $Config->apps()['crm']['url'],
                'url_help'          =>  $Config->apps()['crm']['url_help'],
                'url_logo'          =>  $Config->apps()['company']['url_logo'],
                'url_link'  =>  $Config->apps()['crm']['url'] . '/account/verification?token=' . md5($addregisters['id'])
            ]
        ];

        $infotemplate = [
            'id'            =>  10002,
            'dir'           =>  'verifaccount'
        ];

        $dataautoemail = [
            'user_id'           =>  $ceking->id,
            'type'              =>  1, //1. access
            'sub_type'          =>  2, //vresen erification account
            'sender_type'       =>  1, //1 send by email
            'sender_id'         =>  10001,
            'infotemplate'      =>  $infotemplate,
            'infosender'        =>  $infosender,
        ];



        //
        $upautosendermail = tblAutoSenders::where([
            'user_id'           =>  $ceking->id,
            'type'              =>  1,
            'sub_type'          =>  1,
            'sender_email'      =>  0,
            'status'            =>  1
        ])
        ->update(['status'=>0]);
        
        $addautosendermail = new \App\Http\Controllers\models\autosenders;
        $addautosendermail = $addautosendermail->email($dataautoemail);


        return response()->json([
            'message'=>'',
            'response'=>'Berhasil'
        ],200);
        

    }

    // verification account
    public function verification(Request $request)
    {
        $Config = new Config;

        //
        $key = trim($request->header('key') ) ;


        //
        $ceking = tblUserRegisters::select(
            'u.id', 'u.level', 'u.sub_level', 'u.name'
        )
        ->join('users as u', 'u.id', '=', 'user_registers.user_id')
        ->where([
            'user_registers.token'         =>  $key,
            'user_registers.status'        =>  1,
            'u.registers'                  =>   0,
            'u.status'                     =>  1
        ])
        ->first();

        if( $ceking )
        {


            if( $ceking->level > 3) //level not account personal
            {
                $response = $this->verifaccountpersonal($ceking);
            }
            else
            {
                $response = $this->verifaccountcomp($ceking);
            }

            //true
            $data = [
                'message'           =>  '',
                'response'          =>  $response
            ];

            $status = 200;

        }
        else
        {
            $data = [
                'message'           =>  'Data tidak ditemukan'
            ];

            $status = 404;
        }


        return response()->json($data, $status);
    }


    //personal level
    public function verifaccountpersonal($request)
    {
        $data = [ 
            'id'                =>  $request['id'],
            'level'             =>  $request['level'],
            'sub_level'         =>  $request['sub_level'],
            'name'              =>  $request['name']
        ];

        return $data;
    }

    // company level
    public function verifaccountcomp($request)
    {

        $getconfig = tblUserConfigs::select(
            'uc.type', 'uc.name'
        )
        ->leftJoin('user_companies as uc', function($join)
        {
            $join->on('uc.id', '=', 'user_configs.company_id');
        })
        ->where([
            'user_configs.user_id'      =>  $request['id']
        ])
        ->first();

        //
        $company = [
            'type'          =>  $getconfig->type === 1 ? 'Apps' : ( $getconfig->type === 2 ? 'Produsen' : 'Distributor'),
            'name'        =>  $getconfig->name
        ];


        $data = [ 
            'id'                =>  $request['id'],
            'level'             =>  $request['level'],
            'sub_level'         =>  $request['sub_level'],
            'name'              =>  $request['name'],
            'company'           =>  $company
        ];

        return $data;
    }


    public function sendverification(Request $request)
    {
        $Config = new Config;

        //
        $ceking = tblUserRegisters::where([
            'token'         =>  $request->header('key'),
            'status'        =>  1
        ])->first();

        //jika token tidak ditemukan atau status null
        if ( $ceking == null )
        {

            $data = [
                'message'       =>  'Key/Token tidak valid'
            ];

            return response()->json($data, 404);
        }


        if( $request->type == "1")
        {
            $verificatin = $this->sendverificationcomp($request);
        }
        else
        {
            $verificatin = $this->sendverificationuser($request);
        }
        

        if( $verificatin['message'] != '')
        {
            return response()->json(['message'=>$verificatin['message']],401);
        }

        return $verificatin;

        
    }


    //send verification company
    public function sendverificationcomp($request)
    {
        $username = trim($request->username);
        $password = trim($request->password);
        $terms = trim($request->terms);


        $getuser = tblUserRegisters::select(
            'u.id', 'u.email'
        )
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'user_registers.user_id');
        })
        ->where([
            'user_registers.token'         =>  $request->header('key')
        ])
        ->first();


        //update tbl user registers
        $upregisters = tblUserRegisters::where([
            'token'         =>  $request->header('key'),
            'status'        =>  1
        ])->update([
            'status'=>0
        ]);


        //update table user
        $upuser = tblUsers::where([
            'id'        =>  $getuser->id
        ])
        ->update([
            'username'          =>  $username,
            'password'          =>  Hash::make($password),
            'registers'         =>  1
        ]);


        //update table user config
        $upuserconfig = tblUserConfigs::where([
            'user_id'           =>  $getuser->id
        ])->update([
            'terms'             =>  1,
            'terms_date'        =>  date('Y-m-d H:i:s', time())
        ]);


        
        // login
        $datalogin = [
            'email'         =>  $getuser->email,
            'password'      =>  $password
        ];

        $login = new \App\Http\Controllers\access\manage;
        $login = $login->truelogin($datalogin);

        return $login;
    }


    public function sendverificationuser($request)
    {
        return "user";
    }



    //change password
    public function sendchangepassword(Request $request)
    {
        
        //
        $Config = new Config;

        //
        $key = $request->header('key');
        $password = trim($request->password);


        $ceking = tblResetPasswords::select(
            'u.id', 'u.email'
        )
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'reset_passwords.user_id');
        })
        ->where([
            'reset_passwords.token'         =>  $key,
            'reset_passwords.status'        =>  1
        ])->first();


        //error
        if( $ceking == null )
        {
            return response()->json([
                'message'   =>  'Key/Token tidak valid atau kadaluwarsa'
            ], 404);
        }


        //update reset passord
        $upresetpassword = tblResetPasswords::where([
            'token'         =>  $key,
            'status'        =>  1
        ])->update([
            'reset'         =>  1,
            'status'        =>  0
        ]);


        //update table login
        $upuser = tblUsers::where([
            'id'            =>  $ceking->id
        ])
        ->update([
            'password'      =>  Hash::make($password)
        ]);


        // login
        $datalogin = [
            'email'         =>  $ceking->email,
            'password'      =>  $password
        ];

        $login = new \App\Http\Controllers\access\manage;
        $login = $login->truelogin($datalogin);

        return $login;

    }




    
    
}