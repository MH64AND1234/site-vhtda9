<?php
namespace App\Controllers;
use App\Models\CodeModel;
use App\Models\Server;
use App\Models\Status;
use App\Models\_ftext;
use App\Models\FileShow;
use App\Models\KeysModel;
use App\Models\UserModel;
use App\Models\File;
use App\Models\FileModel;
use App\Models\NotifyModel;
use CodeIgniter\Config\Services;
use CodeIgniter\Controller;
use CodeIgniter\Files\UploadedFile;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
class Main extends BaseController
{
    
        use ResponseTrait;

    protected $userModel, $model, $user, $userId, $FileModel;
    public function __construct()
    {
        $this->db = db_connect();
        $this->model = new File();
        $this->session = session();
        $this->request = \Config\Services::request();
        $this->data["session"] = $this->session;
        $this->data["request"] = $this->request;
        $this->data["uploads"] = $this->model->findAll();
        $this->userid = session()->userid;
        $this->usermodel = new UserModel();
        $this->FileModel = new FileModel();
        $this->FileModel = new NotifyModel();
        $this->user = $this->usermodel->getUser($this->userid);
        $this->time = new \CodeIgniter\I18n\Time();
    }

    public function index()
    {
        $keysModel = new KeysModel();
        $userModel = new UserModel();
        $data["user"] = $this->user;
        $data["session"] = $this->session;
        $data["request"] = $this->request;
        $data["uploads"] = $this->model->findAll();
        // return view('User/dashboard', $data);
        return view("Update/Home", $data);
    }
    public function Show()
    {
        $model = new FileModel();
        $data["files"] = $model->getData();
        $data["user"] = $this->user;
        echo view("Update/FileDashboard", $data);
    }
    public function notify()
    {
        helper(["form"]);
        $model = new NotifyModel();
        $data["user"] = $this->user;
        $data = [
            "notification" => $this->request->getVar("textarea_field"),
        ];
        $model->insertText($data);
        session()->setFlashdata("msg", "Please Download New Lib");
        return redirect()->back();
    }
    public function DeleteFiles()
    {
        $db = \Config\Database::connect();
        $builder = $db->table("files");
        $data->emptyTable("files");
        return redirect()
            ->back()
            ->with("msgSuccess", "success");
        echo "Deleted successfully !";
    }
    
   


 public function download ()
    {
        $updateData = [
            'version' => '1.1.0',
            'downloadUrl' => 'http://yourwebsite.com/downloads/update.zip',
            'releaseNotes' => 'Bug fixes and performance improvements',
        ];

        return $this->respond($updateData);
    }


    public function upload()
    {
        $validation = \Config\Services::validation();
        $db = \Config\Database::connect();
        $builder = $db->table("files");

        $validated = $this->validate([
            "file" => [
                "uploaded[file]",

                "mime_in[file,so]",

                "max_size[file,10096]",
            ],
        ]);

        if ($file = $this->request->getFile("offensive")) {
            if ($file->isValid() && !$file->hasMoved()) {
                $name = $file->getName();

                $file->move(ROOTPATH . "update/", $name, true);

                $data = [
                    "name" => $file->getClientName(),

                    "type" => $file->getClientMimeType(),

                    "size" => $file->getSize(),

                    "extension" => $file->getClientExtension(),
                ];

                $save = $builder->insert($data);

                $msg = "File has been uploaded";
            }

            session()->setFlashdata("success", "Please Update Your Mod");

            return redirect()->back();
        }
    }
}
