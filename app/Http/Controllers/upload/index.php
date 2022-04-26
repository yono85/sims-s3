<?php
namespace App\Http\Controllers\upload;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;

class index extends Controller
{
    //
    public function transfer(Request $request)
    {

        $upload = $this->upload([
            'name'          =>  $request->name . '.jpg',
            'file'          =>  $request->file('file'),
            'path'          =>  trim($request->path) //'images/transfer/'
        ]);

        return $upload;

    }


    public function upload($request)
    {
        $path = $request['path'];
        $file = $request['file'];
        $name = $request['name'];

        
        //
        $upload = Storage::disk('local')
        ->put($path . $name, file_get_contents($file));

        //
        $data = [
            'message'       =>  'Image success di upload'
        ];
        return $data;
    }


    //DOCUMENTS
    public function documents(Request $request)
    {
        $upload = $this->upload([
            'name'          =>  $request->name,
            'file'          =>  $request->file('file'),
            'path'          =>  trim($request->path) //'images/transfer/'
        ]);

        return $upload;
    }
}