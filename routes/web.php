<?php

/** @var \Laravel\Lumen\Routing\Router $router */



//group middleware cekrequest
// $router->group(['prefix' => 'api',  'middleware' => 'cekrequest'], function($router){

//     //access
//     $router->post('/login', 'access\manage@login');
//     $router->post('/signup', 'access\manage@signup');

//     //registers success
//     $router->get('/registers/success', 'account\manage@registersuccess');
//     $router->post('/reverifaccount', 'account\manage@reverifaccount');

//     //reset password
//     $router->post('/resetpassword', 'access\manage@resetpassword');
//     $router->get('/account/changepassword', 'account\index@getchangepassword');
//     $router->post('/account/changepassword', 'account\manage@sendchangepassword');

//     // verifcation
//     $router->get('/account/verification', 'account\manage@verification');

//     $router->post('/account/send-verification', 'account\manage@sendverification');
// });

// //group middleware cekrequest and auth
// $router->group(['prefix'=>'api', 'middleware'=>['cekrequest','auth']], function($router)
// {
//     $router->post('/logout', 'access\manage@logout');
//     $router->get('/profile', 'access\manage@profile');
    
    
//     // $router->get('/refresh', 'access\manage@refresh');
// });


$router->group(['prefix' => 's3',  'middleware' => 'cekrequest'], function($router){
    $router->post('/upload/transfer', 'upload\index@transfer');
    $router->post('/upload/documents', 'upload\index@documents');
    $router->post('/delete', 'delete\manage@main');
});


$router->group(['prefix' => 's3/testing',  'middleware' => 'cekrequest'], function($router){
    $router->post('/delete', 'testing\upload\delete@main');
});







//testing
// $router->post('/testing/upload', 'testing\upload\index@image');

