<?php

namespace App\Controllers;

use App\Models\CodeModel;
use App\Models\UserModel;
use CodeIgniter\Config\Services;

class Auth extends BaseController
{
    protected $userModel;
    private $rateLimit = 10;
    private $timeWindow = 30;
    const HEXMOD = 'https://t.me/I_2023';
    const MR_HEXMOD = 'https://t.me/I_2023';

    public function __construct()
    {
        helper('url');
        session();
        $this->userModel = new UserModel();
    }

    private function blockHackerTools()
    {
        $userAgent = $this->request->getUserAgent();
        $blacklist = [
            'HackBar', 'sqlmap', 'Wget', 'curl', 'python-requests', 
            'Postman', 'Insomnia', 'DH HackBar', 'Kiwi Browser', 
            'Mozilla/5.0 (Linux; Android 12; SM-T505N Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/134.0.6998.108 Safari/537.36'
        ];

        foreach ($blacklist as $tool) {
            if (stripos($userAgent, $tool) !== false) {
                header("HTTP/1.1 403 Forbidden");
                die('يبن المتناكه كلمني وهديك تجي غدر تتناك..');
            }
        }

        if (!preg_match('/Chrome/i', $userAgent) || preg_match('/wv/i', $userAgent)) {
            header("HTTP/1.1 403 Forbidden");
            die('يبن المتناكه كلمني وهديك تجي غدر تتناك..');
        }

    } 

    
    private function checkRequestLimit()
    {
        $ip = $this->request->getIPAddress();
        $session = session();

        $requests = $session->get("requests_$ip") ?? [];
        $currentTime = time();

        $requests = array_filter($requests, function ($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < $this->timeWindow;
        });

        if (count($requests) >= $this->rateLimit) {
            return redirect()->to(self::MR_HEXMOD)->send();
        }

        $requests[] = $currentTime;
        $session->set("requests_$ip", $requests);
    }

    public function index()
    {
        $this->blockHackerTools();
    
        $this->checkRequestLimit();

        $user = $this->userModel->getUser(session('userid'));
        dd($user, session());
    }

    public function login()
    {
        $this->blockHackerTools();
        
        $this->checkRequestLimit();

        if (session()->has('userid')) {
            return redirect()->to('dashboard');
        }

        if ($this->request->getPost()) {
            return $this->login_action();
        }

        return view('Auth/login', [
            'title' => 'Login',
            'validation' => Services::validation(),
        ]);
    }

    public function register()
    {
        $this->blockHackerTools();
    
        $this->checkRequestLimit();

        if (session()->has('userid')) {
            return redirect()->to('dashboard');
        }

        if ($this->request->getPost()) {
            return $this->register_action();
        }

        return view('Auth/register', [
            'title' => 'Register',
            'validation' => Services::validation(),
        ]);
    }

    private function login_action()
    {
        $this->blockHackerTools();
        
        $this->checkRequestLimit();

        $form_rules = [
            'username' => [
                'label' => 'Username',
                'rules' => 'required|alpha_numeric|min_length[4]|max_length[25]|is_not_unique[users.username]',
                'errors' => ['is_not_unique' => 'The username is not registered.']
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[6]|max_length[45]',
            ]
        ];

        if (!$this->validate($form_rules)) {
            return redirect()->route('login')->withInput()->with('msgDanger', 'Failed. Please check the form.');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $stay_log = $this->request->getPost('stay_log');

        $user = $this->userModel->getUser($username, 'username');
        if ($user) {
            if (password_verify(create_password($password, false), $user->password)) {
                $time = new \CodeIgniter\I18n\Time;
                session()->set([
                    'userid' => $user->id_users,
                    'unames' => $user->username,
                    'time_login' => $stay_log ? $time::now()->addHours(24) : $time::now()->addMinutes(30),
                    'time_since' => $time::now(),
                ]);
                return redirect()->to('dashboard');
            } else {
                return redirect()->route('login')->withInput()->with('msgDanger', 'Wrong password, please try again.');
            }
        }

        return redirect()->route('login')->withInput()->with('msgDanger', 'User not found.');
    }
public function register_action()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $referral = $this->request->getPost('referral');

        $form_rules = [
            'username' => [
                'label' => 'username',
                'rules' => 'required|alpha_numeric|min_length[4]|max_length[25]|is_unique[users.username]',
                'errors' => [
                    'is_unique' => 'The {field} has been taken.'
                ]
            ],
            'password' => [
                'label' => 'password',
                'rules' => 'required|min_length[6]|max_length[45]',
            ],
            'password2' => [
                'label' => 'password',
                'rules' => 'required|min_length[6]|max_length[45]|matches[password]',
                'errors' => [
                    'matches' => '{field} not match, check the {field}.'
                ]
            ],
            'referral' => [
                'label' => 'referral',
                'rules' => 'required|min_length[6]|alpha_numeric',
            ]
        ];

        if (!$this->validate($form_rules)) {
            // Form Invalid
        } else {
            $mCode = new CodeModel();
            $rCheck = $mCode->checkCode($referral);
            $validation = Services::validation();
            if (!$rCheck) {
                $validation->setError('referral', 'Wrong referral, please try again.');
            } else {
                if ($rCheck->used_by) {
                    $validation->setError('referral', "Wrong referral, code has been used &middot; $rCheck->used_by.");
                } else {
                    $hashPassword = create_password($password);
                    $data_register = [
                        'username' => $username,
                        'password' => $hashPassword,
                        'saldo' => $rCheck->set_saldo ?: 0,
                        'uplink' => $rCheck->created_by
                    ];
                    $ids = $this->userModel->insert($data_register, true);
                    if ($ids) {
                        $mCode->useReferral($referral);
                        $msg = "Register Successfuly!";
                        return redirect()->to('login')->with('msgSuccess', $msg);
                    }
                }
            }
        }
        return redirect()->route('register')->withInput()->with('msgDanger', '<strong>Failed</strong> Please check the form.');
    }
    public function logout()
    {
        if (session()->has('userid')) {
            session()->remove(['userid', 'unames', 'time_login', 'time_since']);
            session()->setFlashdata('msgSuccess', 'Logged out successfully.');
        }
        return redirect()->to('login');
    }
}