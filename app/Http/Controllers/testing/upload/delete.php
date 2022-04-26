<?php
namespace App\Http\Controllers\testing\upload;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;

class delete extends Controller
{
    //
    public function main(Request $request)
    {


        $file = $request->file;
        // //
        $upload = Storage::disk('local')
        ->delete($file);

        //
        $data = [
            'message'       =>  'Image berhasil di hapus',
            'file'          =>  $file
        ];

        return response()->json($data, 200);
    }
}