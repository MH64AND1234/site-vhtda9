<?php

namespace Config;

use CodeIgniter\Config\Services;


$routes = Services::routes();


if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}


$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);


$routes->get('recaptcha', function () {
    return file_get_contents(ROOTPATH . 'public/recaptcha_verify.html');
});


$routes->match(['get', 'post'], '/', 'Auth::login');
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->match(['get', 'post'], 'register', 'Auth::register');
$routes->get('logout', 'Auth::logout');
$routes->get('dbg', 'Auth::index');

$routes->match(['get', 'post'], 'userLogs', 'Auth::userLogs');
$routes->post('verify_recaptcha', 'RecaptchaController::verify');

// --------------------------------------------------------------------
// User / Dashboard
// --------------------------------------------------------------------
$routes->get('dashboard', 'User::index');
$routes->post('dashboard', 'User::Server');

$routes->match(['get', 'post'], 'settings', 'User::settings');
$routes->match(['get', 'post'], 'settings1', 'SettingsController::settings1');
$routes->match(['get', 'post'], 'Server', 'User::Server');

$routes->match(['get', 'post'], 'player', 'Player::index');
$routes->get('alter', 'Keys::alterUser');


$routes->get('upload', 'Upload::index');  
$routes->post('upload/upload', 'Upload::upload');
$routes->post('upload/delete', 'Upload::delete'); 


$routes->match(['get', 'post'], 'getLatestVersion', 'Main::getLatestVersion');
$routes->match(['get', 'post'], 'DeleteFiles', 'Main::DeleteFiles');
$routes->match(['get', 'post'], 'notify', 'Main::notify');
$routes->match(['get', 'post'], 'Show', 'Main::Show');


$routes->get('Memo', 'Main::index');
$routes->match(['get', 'post'], 'memory', 'Memo::index');
$routes->match(['get', 'post'], 'bullet_On', 'Memo::bullet_On');
$routes->match(['get', 'post'], 'bullet_Off', 'Memo::bullet_Off');
$routes->match(['get', 'post'], 'Memory_On', 'Memo::Memory_On');
$routes->match(['get', 'post'], 'Memory_Off', 'Memo::Memory_Off');


$routes->group('keys', function ($routes) {
    $routes->match(['get', 'post'], '/', 'Keys::index');
    $routes->match(['get', 'post'], 'generate', 'Keys::generate');
    $routes->get('(:num)', 'Keys::edit_key/$1');

    $routes->post('edit', 'Keys::edit_key');
    $routes->get('delete', 'Keys::api_key_delete');
    $routes->get('reset', 'Keys::api_key_reset');

    $routes->match(['get', 'post'], 'api', 'Keys::api_get_keys');
    $routes->get('alter', 'Keys::alterKeys');
    $routes->get('resetAll', 'Keys::resetAllKeys');

    $routes->get('download/all', 'Keys::download_all_Keys');
    $routes->get('download/new', 'Keys::download_new_Keys');

    $routes->get('deleteAll', 'Keys::deleteKeys');
    $routes->get('start', 'Keys::startDate');
});


$routes->group('keyz', function ($routes) {
    $routes->match(['get', 'post'], '/', 'Keyz::index');
    $routes->match(['get', 'post'], 'generates', 'Keyz::generates');
    $routes->get('(:num)', 'Keyz::edit_keyz/$1');

    $routes->post('edit', 'Keyz::edit_keyz');
    $routes->get('delete', 'Keyz::api_keyz_delete');
    $routes->get('reset', 'Keyz::api_keyz_reset');

    $routes->match(['get', 'post'], 'api', 'Keyz::api_get_keyz');
    $routes->get('alter', 'Keyz::alterKeyz');
    $routes->get('resetAll', 'Keyz::resetAllKeyz');

    $routes->get('download/all', 'Keyz::download_all_Keyz');
    $routes->get('download/new', 'Keyz::download_new_Keyz');

    $routes->get('deleteAll', 'Keyz::deleteKeyz');
    $routes->get('start', 'Keyz::startDate');
});


$routes->group('admin', ['filter' => 'admin'], function ($routes) {
    $routes->match(['get', 'post'], 'create-referral', 'User::ref_index');
    $routes->match(['get', 'post'], 'manage-users', 'User::manage_users');
    $routes->match(['get', 'post'], 'user/(:num)', 'User::user_edit/$1');
    $routes->get('user/alter', 'User::alterUser');
    $routes->match(['get', 'post'], 'user/singledelete/(:num)', 'User::singleDelete/$1');

    $routes->group('api', function ($routes) {
        $routes->match(['get', 'post'], 'users', 'User::api_get_users');
    });
});


$routes->match(['get', 'post'], 'connect', 'Connect::index');


if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}