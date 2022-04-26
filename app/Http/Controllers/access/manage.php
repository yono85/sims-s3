<?php
namespace App\Http\Controllers\access;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Manager;
use App\Http\Controllers\config\index as Config;
use Auth;
use DB;
use App\users as tblUsers;
use App\user_logins as tblUserlogins;
use App\user_registers as tblRegisters;
use App\reset_passwords as tblResetPasswords;

use Illuminate\Support\Facades\Hash;

class manage extends Controller
{

    //manage login
    public function login(Request $request)
    {

        $Config = new Config;

        //ceking field jika tidak lengkap maka munculkan error
        if( !isset($request->email) || !isset($request->password) )
        {
            $data = [
                'message'   =>  'Field Email atau Password tidak lengkap'
            ];

            return response()->json($data, 401);
        }


        //ceking email password
        $ceklogin = tblUsers::where([
            'email'     =>  trim($request->email)
        ])->first();

        if( $ceklogin != null )
        {

            


                        

            $pwd = $ceklogin->password;
            $cekpwd = Hash::check($request->password, $pwd) ? 1 : 0;

            //wrong password
            if( $cekpwd == 0)
            {
                $data = [
                    'message'       =>  'Harap periksa kembali email dan password Anda.'
                ];

                return response()->json($data, 401);

            }


            //halaman auth
            if( $request->level == '1' && $ceklogin->level != 1 )
            {
                $data = [
                    'message'       =>  'Maaf akses Anda tidak di ijinkan!'
                ];

                return response()->json($data, 401);
            }


            //halaman CRM
            if( $request->level == '2')
            {
                if( ($ceklogin->level != 1 ) && ($ceklogin->level !== 2 ) )
                {

                    $data = [
                        'message'       =>  'Maaf akses Anda tidak di ijinkan!'
                    ];
    
                    return response()->json($data, 401);
                }
            }


            //halaman reselller
            if( $request->level == '3')
            {
                if( ($ceklogin->level != 1 ) && ($ceklogin->level !== 3 ) )
                {

                    $data = [
                        'message'       =>  'Maaf akses Anda tidak di ijinkan!'
                    ];
    
                    return response()->json($data, 401);
                }
            }

            if( $request->level == '0' )
            {
                if( ($ceklogin->level != 1 ) && ($ceklogin->level !== 0 ) )
                {

                    $data = [
                        'message'       =>  'Maaf akses Anda tidak di ijinkan!'
                    ];
    
                    return response()->json($data, 401);
                }

            }

            //

            //cek status
            if( $ceklogin->status != 1 )
            {
                $data = [
                    'message'       =>  'Akun Anda ditangguhkan!'
                ];

                return response()->json($data, 401);
            }

            //not register
            if( $ceklogin->registers == 0)
            {
                // $getregister = tblRegisters::where([
                //     'user_id'       =>  $ceklogin->id,
                //     'status'        =>  1
                // ])
                // ->first();

                $data = [
                    // 'message'       =>  'Akun Anda belum di verifikasi, <a href="/account/verification?token='.$getregister->token.'">Verifikasi sekarang?</a>'
                    'message'       =>  'Akun Anda belum di verifikasi, <a href="/registers/success?token='.$ceklogin->token.'">Verifikasi sekarang?</a>'
                ];

                return response()->json($data, 401);

            }


            // cek jika token terisi cek ke tabel userlogins
            if( trim($request->token) != '' )
            {
                $cektoken = tblUserlogins::where([
                    'token_jwt'         =>  $request->token,
                    'status'            =>  1
                ])->count();


                if( $cektoken > 0 )
                {
                    $data = [
                        'message'           =>  'Keep login',
                        'token'             =>  $request->token
                    ];
        
                    return response()->json($data, 200);
                }


            }


            //login
            //buat token JWT
            $datalogin = [
                'email'         =>  trim($request->email),
                'password'      =>  trim($request->password),
                'info'          =>  $request['info']
            ];

            $truelogin = $this->truelogin($datalogin);
            
            if( $truelogin['message'] != '')
            {
                return response()->json($truelogin['message'], 401);
            }


            return response()->json($truelogin, 200);


        }

        $data = [
            'message'       =>  'Harap periksa kembali email dan password Anda.'
        ];

        return response()->json($data, 401);


    }


    //true login
    public function truelogin($request)
    {
        //request

        $credentials = [
            'email'     =>  $request['email'],
            'password'  =>  $request['password']
        ];
        $token = $this->guard()->attempt($credentials);

        if( $token == false )
        {
            $data = [
                'message'       =>  'Proses login gagal'
            ];

            return $data;
        }

        //account
        $account = new \App\Http\Controllers\account\index;
        $account = $account->show($this->guard()->user());

        $datalogins = [
            'account'       =>  [
                'id'            =>  $account['id']
                ],
            'token'         =>  $token,
            'info'          =>  $request['info']
        ];


        //create new log in table logins
        $logins = new \App\Http\Controllers\log\access\manage;
        $logins = $logins->logins($datalogins);

        $cookie = [
            'account'       =>  $account,
            'token'         =>  $token
        ];

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'cookie'        =>  $cookie,
                'homepage'      =>  '/dashboard'
            ]
        ];

        return $data;

    }
    

    //manage logout
    //cek expire session token
    public function logout(Request $request)
    {

        
        // $token = trim($request->token);

        // $logout = new \App\Http\Controllers\log\access\manage;
        // $logout = $logout->logout($token);

        // //logout 
        // $this->guard()->logout();

        //response
        $data = [
            'message'       =>  '',
            'response'      =>  [
                'redirect'      =>  '/login'
            ]
        ];

        return response()->json($data,200);
    }

    // signup
    public function signup(Request $request)
    {


        $name = trim($request->name);
        $email = trim($request->email);
        $password = trim($request->password);

        $cekemail = tblUsers::where([
            'email'         =>  $email
        ])->first();

        if( $cekemail != null )
        {

            //cek registers
            if( $cekemail->status != 1 )
            {
                $data = [
                    'message'       =>  'Akun anda ditangguhkan!'
                ];
            }
            else
            {

                if( $cekemail->registers == 0 )
                {
                    // $getregisters = tblRegisters::where([
                    //     'user_id'       =>  $cekemail->id,
                    //     'status'        =>  1
                    // ])->first();
    
                    $data = [
                        'message'           =>  'Email telah terdaftar dan belum verifikasi akun, <a href="/registers/success?token='.$cekemail->token.'">Verifikasi sekarang?</a>'
                    ];
                }
                else
                {
                    $data = [
                        'message'           =>  'Email telah terdaftar, <a href="/login">masuk akun Anda?</a>'
                    ];
                }
            }

            return response($data, 401);
        }
        else
        {


            //insert table user
            $addtbluser = new \App\Http\Controllers\account\signup;
            $addtbluser = $addtbluser->main($request);


            return $addtbluser;

            if( $addtbluser['message'] == '')
            {
                $data = [
                    'redirect'      =>  $addtbluser['response']
                ];
            }
            else
            {
                $data = [
                    'message'      =>  $addtbluser['message']
                ];
            }
            return response()->json($data, $addtbluser['message'] === '' ? 200 : 401);
        }

        


    }

    //reset password
    public function resetpassword(Request $request)
    {

        // config
        $Config = new Config;


        // request
        $email = trim($request->email);


        //start
        $cekemail = tblUsers::where([
            'email'         =>  $email
        ])->first();

        if( $cekemail == null )
        {

            $data = [
                'message'           =>  'Email tidak terdaftar',
            ];

            return response()->json($data, 404);
            
        }
        else
        {

            //halaman auth
            if( $request->level == '1' && $cekemail->level != 1 )
            {
                $data = [
                    'message'       =>  'Maaf akses Anda tidak di ijinkan!'
                ];

                return response()->json($data, 401);
            }


            //halaman CRM
            if( $request->level == '2')
            {
                if( ($cekemail->level != 1 ) && ($cekemail->level !== 2 ) )
                {

                    $data = [
                        'message'       =>  'Maaf akses Anda tidak di ijinkan!'
                    ];
    
                    return response()->json($data, 401);
                }
            }


            //halaman reselller
            if( $request->level == '3')
            {
                if( ($cekemail->level != 1 ) && ($cekemail->level !== 3 ) )
                {

                    $data = [
                        'message'       =>  'Maaf akses Anda tidak di ijinkan!'
                    ];
    
                    return response()->json($data, 401);
                }
            }

            if( $request->level == '0' )
            {
                if( ($cekemail->level != 1 ) && ($cekemail->level !== 0 ) )
                {

                    $data = [
                        'message'       =>  'Maaf akses Anda tidak di ijinkan!'
                    ];
    
                    return response()->json($data, 401);
                }

            }

            //cek registers
            if( $cekemail->status != 1 )
            {
                $data = [
                    'message'       =>  'Akun anda ditangguhkan!'
                ];

                return response()->json($data, 401);
            }

            if( $cekemail->registers == 0 )
            {
                $getregisters = tblRegisters::where([
                    'user_id'       =>  $cekemail->id,
                    'status'        =>  1
                ])->first();

                $data = [
                    'message'           =>  'Email telah terdaftar dan belum verifikasi akun, <a href="/account/verification?token='.$getregisters->token.'">verifikasi sekarang?</a>'
                ];

                return response()->json($data, 401);
            }


            //ceklimit
            $thisday = date('Y-m-d', time());
            $ceklimit = tblResetPasswords::where([
                ['user_id',     '=',    $cekemail->id],
                ['created_at',  'like', '%' . $thisday. '%']
            ])->count();

            //keep limit if maxlength > 2
            if( $ceklimit > 2 )
            {
                $data = [
                    'message'       =>  'Permintaan perubahan password dibatasi hanya boleh 3x dalam 1 hari'
                ];
    
                return response()->json($data, 401);

            }


            //next process
            $dataresetpassword = [
                'user_id'           =>  $cekemail->id,
                'user_level'        =>  $cekemail->level, //1 apps, 2 produsen, 3 distributor
                'email'             =>  $cekemail->email,
                'name'              =>  $cekemail->name,
                'info'              =>  $request->info
            ];

            //add table reset password
            $addresetpassword = new \App\Http\Controllers\models\access;
            $addresetpassword = $addresetpassword->resetpassword($dataresetpassword);

            
            return response()->json([
                'message'       =>  '',
                'response'      =>  'Permintaan perubahan Password berhasil dikirim ke alamat email ' . $email
            ], 200);

        }
    }


    // refresh token
    public function refresh()
    {

        $account = new \App\Http\Controllers\account\index;
        $account = $account->show($this->guard()->user());


        $gettoken = tblUserlogins::where([
            'user_id'       =>  $account['id'],
            'status'        =>  1
        ])->first();

        $data = [
            'refresh'      =>  [
                'account'           =>  $account,
                'token'             =>  $gettoken->token_jwt
            ]
        ];

        return $data;
        
    }


    public function guard()
    {
        return app('auth')->guard();
    }
    

    public function profile()
    {
        $refresh = $this->refresh();

        $data = [
            'message'       =>  '',
            'response'      =>  'response',
            'refresh'       =>  $refresh
        ];


        return response()->json($data, 200);
    }



}