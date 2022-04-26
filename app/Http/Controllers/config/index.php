<?php
namespace App\Http\Controllers\config;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class index extends Controller
{

    
    //create new id to insert data
    public function createnewid($request)
    {
        $test = (int)$request['value'];
        $length = ( (int)$request['length'] - 1);
        $sprint = sprintf('%0'.$length.'s', 0);

        $condition = [ 
            10 . $sprint =>  9,
            9 . $sprint  =>  8,
            8 . $sprint  =>  7,
            7 . $sprint  =>  6,
            6 . $sprint  =>  5,
            5 . $sprint  =>  4,
            4 . $sprint  =>  3,
            3 . $sprint  =>  2,
            2 . $sprint  =>  1
        ];

        $sprintnew = strlen($test) === (int)$request['length'] ? substr($test, 1) : $test;

        foreach($condition as $row => $val)
        {
            if( $test < $row )
            {
                $value = $val . sprintf('%0'.$length.'s', $sprintnew);;
            }
        }


        return $value;
    }


    // default date for table
    public function date()
    {
        return date('Y-m-d H:i:s', time());
    }


    //number
    public function number($request)
    {
        return preg_replace('/\D/', '', $request);
    }


    //create new uniq (number and char A-Z)
    public function createuniq($q)
    {
        $length = (int)$q['length'];
        $value = (int)$q['value'];

        //
        $char = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $value;
        $charlength = strlen($char);
        $rand = '';

        //
        for ($i = 0; $i < $length; $i++)
        {
            $rand .= $char[rand(0, $charlength - 1)];
        }
        return $rand;
    }


    //create uniq number
    public function createuniqnum($q)
    {
        $length = (int)$q['length'];
        $value = (int)$q['value'];

        //
        $char = '0123456789' . $value;
        $charlength = strlen($char);
        $rand = '';

        //
        for ($i = 0; $i < $length; $i++)
        {
            $rand .= $char[rand(0, $charlength - 1)];
        }
        return $rand;
    }


    //apps
    public function apps()
    {
        
        $data = [
            'company'       =>  [
                'name'          =>  'Herbindo',
                'url_logo'      =>  'https://haloherbal.id/wp-content/uploads/2020/12/Logo-Herbindo-Persada.png',
                'url'           =>  'https://herbindo.id',
                'url_help'      =>  'https://help.herbindo.id'
                ],
            'crm'           =>  [
                'name'          =>  'CRM Herbindo',
                'url'           =>  'https://crm.herbindo.id',
                'url_help'      =>  'https://help-crm.herbindo.id'
                ],
            'distributor'   =>  [
                'name'          =>  'Herbindo',
                'url'           =>  'https://distributor.herbindo.id',
                'url_help'      =>  'https://help-distributor.herbindo.id'
                ]
        ];


        return $data;

    }


    public function subURI()
    {
    	$subURI = explode("/", url()->full());
    	return $subURI;
    }


    public function cekURI($request)
    {
        return $request->getRequestUri();
    }


}