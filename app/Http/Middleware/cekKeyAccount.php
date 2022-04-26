<?php
namespace App\Http\Middleware;
// use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;

use Closure;

class cekKeyAccount
{
    public function handle($request, Closure $next)
    {

        $cekkey = DB::table('users')
        ->where([
            'token'         =>  $request->header('key')
        ])->count();

        if( $cekkey == 0 )
        {
            $data = [
                'message'       =>  'Key tidak valid!'
            ];

            return response()->json($data, 401);
        }

        return $next($request);
    }
}