<?php
namespace App\Http\Controllers\delete;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;

class manage extends Controller
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