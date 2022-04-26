<?php
namespace App\Http\Controllers\testing\upload;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;

class index extends Controller
{

    public function test(Request $request)
    {

        return 'ok';
    }

    //
    public function image(Request $request)
    {

        $path = 'images/transfer/';
        $file = $request->file('file');
        $name = $request->name;
        $upload = Storage::disk('local')
        ->put($path . $name, file_get_contents($file));

        //
        $data = [
            'message'       =>  'Image success di upload',
            'name'          =>  $request->name,
        ];


        return $data;
        // return Response()->json($data, 200);
    }
}