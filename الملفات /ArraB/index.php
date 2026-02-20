<?php
session_start();

$correctUsername = 'arrab';
$correctPassword = 'arrabadmin123';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $correctUsername && $password === $correctPassword) {
        $_SESSION['loggedin'] = true;
        header('Location: index.php');
        exit;
    } else {
        $loginError = "اسم المستخدم أو كلمة المرور غير صحيحة!";
    }
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>تسجيل الدخول</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <style>
            body {
                background: url('https://i.gifer.com/4xqN.gif') no-repeat center center fixed;
                background-size: cover;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .login-box {
                background: rgba(255, 255, 255, 0.8);
                padding: 2rem;
                border-radius: 15px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                animation: fadeIn 1.5s ease-in-out;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2 class="text-2xl font-semibold mb-6 text-center">تسجيل الدخول</h2>
            <?php if (isset($loginError)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $loginError ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700">اسم المستخدم:</label>
                    <input type="text" name="username" id="username" class="mt-1 p-2 w-full border rounded-md" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700">كلمة المرور:</label>
                    <input type="password" name="password" id="password" class="mt-1 p-2 w-full border rounded-md" required>
                </div>
                <button type="submit" name="login" class="w-full bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600 transition duration-300">دخول</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$uploadDir = __DIR__ . '/bypass/';
$baseUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/ArraB/Ahmef/';
$allowedExtensions = ['zip', 'json', 'so'];

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToUpload'])) {
    $file = $_FILES['fileToUpload'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (in_array($fileExtension, $allowedExtensions)) {
        $filePath = $uploadDir . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $message = "تم رفع الملف بنجاح!";
        } else {
            $error = "حدث خطأ أثناء رفع الملف.";
        }
    } else {
        $error = "نوع الملف غير مسموح به.";
    }
}

if (isset($_POST['deleteFile'])) {
    $fileToDelete = $uploadDir . basename($_POST['deleteFile']);
    if (file_exists($fileToDelete)) {
        unlink($fileToDelete);
        $message = "تم حذف الملف بنجاح.";
    } else {
        $error = "الملف غير موجود.";
    }
}
$files = array_diff(scandir($uploadDir), ['.', '..']);
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الملفات</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: url('https://i.gifer.com/4xqN.gif') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 30px;
            width: 90%;
            max-width: 800px;
            margin: auto;
            animation: slideIn 1s ease-in-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .button-copy, .button-delete {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 8px;
            margin-left: 10px;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }

        .button-copy:hover, .button-delete:hover {
            background-color: #45a049;
        }

        .file-row:hover {
            background-color: #f1f5f9;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .file-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9fafb;
            border-radius: 8px;
        }

        .animate-button {
            animation: bounce 1s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="text-right mb-4">
            <form action="logout.php" method="POST">
                <button type="submit" class="bg-red-500 text-white p-2 rounded-md hover:bg-red-600 transition duration-300">تسجيل الخروج</button>
            </form>
        </div>
        <h2 class="text-xl font-semibold mb-4 text-center">إدارة الملفات</h2>

        <!-- عرض رسائل النجاح أو الخطأ -->
        <?php if (isset($message)): ?>
            <div class="text-green-600 mb-4"><?= $message ?></div>
        <?php elseif (isset($error)): ?>
            <div class="text-red-600 mb-4"><?= $error ?></div>
        <?php endif; ?>

        <!-- نموذج رفع الملفات -->
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="fileToUpload" class="border p-2 mb-4 w-full" required>
            <button type="submit" class="bg-blue-500 text-white p-2 rounded hover:bg-blue-600 w-full transition duration-300">رفع الملف</button>
        </form>
        <div class="mt-6">
            <h3 class="text-lg font-semibold">الملفات المحملة:</h3>
            <ul class="mt-4">
                <?php foreach ($files as $file): ?>
                    <?php $filePath = $uploadDir . $file; ?>
                    <li class="file-info">
                        <div class="text-gray-700">
                            <strong><?= $file ?></strong><br>
                            <span>الحجم: <?= formatFileSize(filesize($filePath)) ?></span>
                        </div>
                        <div>
                            <!-- إنشاء رابط عام -->
                            <button class="button-copy animate-button" onclick="copyToClipboard('<?= $baseUrl . $file ?>')">نسخ الرابط</button>
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="deleteFile" value="<?= $file ?>">
                                <button type="submit" class="button-delete animate-button">حذف</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="footer">
            <p>Made By <a href="https://t.me/ELPAPPA1" target="_blank">AL7ANALMOT</a></p>
        </div>
    </div>

    <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'تم نسخ الرابط بنجاح!',
                    showConfirmButton: false,
                    timer: 1500
                });
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'فشل نسخ الرابط!',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }

        <?php if (isset($message)): ?>
            Swal.fire({
                icon: 'success',
                title: '<?= $message ?>',
                showConfirmButton: false,
                timer: 1500
            });
        <?php elseif (isset($error)): ?>
            Swal.fire({
                icon: 'error',
                title: '<?= $error ?>',
                showConfirmButton: false,
                timer: 1500
            });
        <?php endif; ?>
    </script>

</body>
</html>