<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class RecaptchaController extends Controller
{
    public function verify()
    {
        $secret = '6Ld4Sg4rAAAAAE1nGe80M7AlpQEpKbRJx1moGC4K';
        $response = $this->request->getPost('g-recaptcha-response');
        
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}");
        $verify = json_decode($verify);

        if ($verify->success) {
            return redirect()->to('/dashboard')->with('message', 'تم التحقق بنجاح!');
        } else {
            return redirect()->to('/recaptcha')->with('error', 'فشل التحقق! حاول مرة أخرى.');
        }
    }
}