<?php

namespace App\Controllers;

use App\Models\CodeModel;
use App\Models\Server;
use App\Models\UserModel;
use App\Models\BTShow;

use App\Models\KeysModel;
use CodeIgniter\Config\Services;
use CodeIgniter\Controller;
 
class Memo extends BaseController
{


 protected $userModel, $model,$keysModel, $user,$userId,$load;

    public function __construct(){
        $this->db = db_connect();
        
        $this->model= new Server;
        $this->model= new BTShow;
        $this->keysModel = new KeysModel();
        $this->session = session();
        $this->request =  \Config\Services::request();   
        $this->userid = session()->userid;
        $this->usermodel = new userModel();
        $this->user = $this->usermodel->getUser($this->userid);
        $this->time = new \CodeIgniter\I18n\Time;

    }
    public function index(){
        
        $data['user']= $this->user;        
        $data['session']= $this->session ;
        $data['request'] = $this->request ;
        if ($this->request->getMethod() == 'post') {
            $model = $this->keysModel;
            $key = $this->request->getPost('key');
            $type = $this->request->getPost('type');
            $time = $this->request->getPost('time');
            $data = [
                'key' => $key,
                'type' => $type,
                'time' => $time
            ];
            // return $this->response->setJSON($data);
            $model = $this->model;
            $db_key = $model->getKeys($key);
            $old_date = $db_key->expired_date;
            $oldTimestamp = strtotime($old_date);
            $newTimeStamp = $oldTimestamp + $this->hourToMS($time);
            $newDate = date("d/m/Y H:i:s", $newTimeStamp);
            if ($type == 1) {
                $model->set('expired_date', $newDate)
                    ->where('user_key', $key)
                    ->update();
            }
        }
        return view('Server/Memory', $data);
    }
    
    
    public function Offensive()
    {
         $serverModel = new Server();
        $data['serverData'] = $serverModel->getServerData();
                $data["user"] = $this->user;

        return view('Server/FileDashb', $data);
}
    
    
  


public function update()
{
    $Server = new Server();
    $keysModel = new KeysModel();

    if ($this->request->getMethod() === 'post' && $this->request->getPost('Update')) {
        $online = ($this->request->getPost('radios') == '1') ? 'true' : 'false';
        $maintenance = $this->request->getPost('myInput');
        $Server->update(1, ['Online' => $online, 'Maintenance' => $maintenance]);
        return redirect()->to('memory')->with('success', 'Successfully updated function!');
    }

   if ($this->request->getMethod() === 'post' && $this->request->getPost('UpdateKey')) {
        // Update the keys model
        $time = $this->request->getPost('myInputKey');
        $keysModel->where('expired_date', "DATE_ADD(expired_date, INTERVAL $time HOUR)", false)->update();
        return redirect()->to('memory')->with('success', 'Successfully added hours!');
    }

    if ($this->request->getMethod() === 'post' && $this->request->getPost('Reset')) {
        // Reset the keys model
        $keysModel->where('devices', null)->update();
        return redirect()->to('memory')->with('success', 'Successfully reset!');
    }

    
    
    
    
    if ($this->request->getMethod() === 'post' && $this->request->getPost('onAimbot')) {
        $Server->update(1, ['Aimbot' => 'true']);
        return redirect()->to('memory')->with('success', 'Successfully turned on Aimbot!');
    }
     if ($this->request->getMethod() === 'post' && $this->request->getPost('offAimbot')) {
        $Server->update(1, ['Aimbot' => 'false']);
        return redirect()->to('memory')->with('success', 'Successfully turned Off Aimbot!');
    }
    
    
    
    
    if ($this->request->getMethod() === 'post' && $this->request->getPost('onBulletTrack')) {
        $Server->update(1, ['Bullet' => 'true']);
        return redirect()->to('memory')->with('success', 'Successfully turned on BulletTrack!');
    }
     if ($this->request->getMethod() === 'post' && $this->request->getPost('offBulletTrack')) {
        $Server->update(1, ['Bullet' => 'false']);
        return redirect()->to('memory')->with('success', 'Successfully turned Off BulletTrack!');
    }
    
    
    
    
    
    if ($this->request->getMethod() === 'post' && $this->request->getPost('onMemory')) {
        $Server->update(1, ['Memory' => 'true']);
        return redirect()->to('memory')->with('success', 'Successfully turned on Memory!');
    }
     if ($this->request->getMethod() === 'post' && $this->request->getPost('offMemory')) {
        $Server->update(1, ['Memory' => 'false']);
        return redirect()->to('memory')->with('success', 'Successfully turned Off Memory!');
    }
}


public function updateCurrency()
{
  if(isset($_POST['buttonCurrency'])){

if($_POST['radios'] == "1"){ $Funxxx = "₹"; }
if($_POST['radios'] == "2"){ $Funxxx = "$"; }
if($_POST['radios'] == "3"){ $Funxxx = "€"; }
$conn->query("UPDATE function_code SET Currency='$Funxxx' WHERE `id_path` = '1'");
	 alert("Successfully Update Currency!");
}

}

}