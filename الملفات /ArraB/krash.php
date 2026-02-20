<?php
session_start();
set_time_limit(0);
ini_set('memory_limit', '-1');


$currentPath = isset($_GET['path']) ? realpath($_GET['path']) : realpath(__DIR__ . '/../');
if (!$currentPath) $currentPath = '/';

$protectedPaths = [
    '/',
    '/etc',
    '/root',
    '/var',
    '/usr',
    '/bin',
    '/sbin',
    '/proc',
    '/sys',
    '/dev'
];


function isProtectedPath($path) {
    global $protectedPaths;
    foreach ($protectedPaths as $protected) {
        if (strpos($path, $protected) === 0) {
            return true;
        }
    }
    return false;
}


if (isset($_POST['change_path'])) {
    $newPath = realpath($_POST['new_path']);
    if ($newPath && !isProtectedPath($newPath)) {
        $currentPath = $newPath;
    } else {
        $_SESSION['error'] = "المسار محمي أو غير موجود";
    }
}


if (isset($_GET['up'])) {
    $parent = dirname($currentPath);
    if ($parent && !isProtectedPath($parent)) {
        $currentPath = $parent;
    }
}


if (isset($_GET['goto'])) {
    $gotoPath = $currentPath . '/' . $_GET['goto'];
    if (is_dir($gotoPath) && !isProtectedPath($gotoPath)) {
        $currentPath = realpath($gotoPath);
    }
}

// التحقق من الصلاحيات
function checkPermissions($path) {
    if (!is_writable($path)) {
        return "المسار غير قابل للكتابة: " . basename($path);
    }
    return null;
}

// حذف الملفات/المجلدات
if (isset($_POST['delete'])) {
    $target = $currentPath . '/' . $_POST['delete'];
    if (isProtectedPath($target)) {
        $_SESSION['error'] = "لا يمكن حذف الملفات من المسارات المحمية";
    } elseif (is_dir($target)) {
        if (deleteDirectory($target)) {
            $_SESSION['success'] = "تم حذف المجلد: " . basename($target);
        } else {
            $_SESSION['error'] = "فشل حذف المجلد: " . basename($target);
        }
    } else {
        if (unlink($target)) {
            $_SESSION['success'] = "تم حذف الملف: " . basename($target);
        } else {
            $_SESSION['error'] = "فشل حذف الملف: " . basename($target);
        }
    }
    header("Location: ?path=" . urlencode($currentPath));
    exit;
}

// حذف المجلدات بشكل متكرر
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

// إعادة تسمية الملفات/المجلدات
if (isset($_POST['rename'])) {
    $oldPath = $currentPath . '/' . $_POST['old_name'];
    $newPath = $currentPath . '/' . $_POST['new_name'];
    
    if (isProtectedPath($oldPath)) {
        $_SESSION['error'] = "لا يمكن إعادة تسمية الملفات في المسارات المحمية";
    } elseif (rename($oldPath, $newPath)) {
        $_SESSION['success'] = "تمت إعادة التسمية إلى: " . $_POST['new_name'];
    } else {
        $_SESSION['error'] = "فشلت إعادة التسمية";
    }
    header("Location: ?path=" . urlencode($currentPath));
    exit;
}

// إنشاء مجلد جديد
if (isset($_POST['new_folder'])) {
    $folderPath = $currentPath . '/' . $_POST['folder_name'];
    if (!isProtectedPath($currentPath)) {
        if (!file_exists($folderPath)) {
            if (mkdir($folderPath, 0755, true)) {
                $_SESSION['success'] = "تم إنشاء المجلد: " . $_POST['folder_name'];
            } else {
                $_SESSION['error'] = "فشل إنشاء المجلد";
            }
        } else {
            $_SESSION['error'] = "المجلد موجود بالفعل";
        }
    } else {
        $_SESSION['error'] = "لا يمكن إنشاء مجلدات في المسارات المحمية";
    }
    header("Location: ?path=" . urlencode($currentPath));
    exit;
}

// إنشاء ملف جديد
if (isset($_POST['new_file'])) {
    $filePath = $currentPath . '/' . $_POST['file_name'];
    if (!isProtectedPath($currentPath)) {
        if (!file_exists($filePath)) {
            $content = $_POST['file_content'] ?? "<?php\n// ملف جديد\n?>";
            if (file_put_contents($filePath, $content)) {
                $_SESSION['success'] = "تم إنشاء الملف: " . $_POST['file_name'];
            } else {
                $_SESSION['error'] = "فشل إنشاء الملف";
            }
        } else {
            $_SESSION['error'] = "الملف موجود بالفعل";
        }
    } else {
        $_SESSION['error'] = "لا يمكن إنشاء ملفات في المسارات المحمية";
    }
    header("Location: ?path=" . urlencode($currentPath));
    exit;
}

// تعديل محتوى الملف
if (isset($_POST['edit_content'])) {
    $filePath = $currentPath . '/' . $_POST['edit_file'];
    if (!isProtectedPath($filePath)) {
        if (is_writable($filePath)) {
            if (file_put_contents($filePath, $_POST['file_content'])) {
                $_SESSION['success'] = "تم حفظ التعديلات: " . $_POST['edit_file'];
            } else {
                $_SESSION['error'] = "فشل حفظ التعديلات";
            }
        } else {
            $_SESSION['error'] = "الملف غير قابل للكتابة";
        }
    } else {
        $_SESSION['error'] = "لا يمكن تعديل الملفات في المسارات المحمية";
    }
    header("Location: ?path=" . urlencode($currentPath) . "&edit=" . urlencode($_POST['edit_file']));
    exit;
}

// رفع ملف
if (isset($_FILES['upload_file'])) {
    if (!isProtectedPath($currentPath)) {
        $uploadFile = $currentPath . '/' . basename($_FILES['upload_file']['name']);
        if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $uploadFile)) {
            $_SESSION['success'] = "تم رفع الملف: " . basename($_FILES['upload_file']['name']);
        } else {
            $_SESSION['error'] = "فشل رفع الملف";
        }
    } else {
        $_SESSION['error'] = "لا يمكن رفع ملفات في المسارات المحمية";
    }
    header("Location: ?path=" . urlencode($currentPath));
    exit;
}

// عرض محتوى الملف للتحرير
if (isset($_GET['edit'])) {
    $editFile = $currentPath . '/' . $_GET['edit'];
    if (file_exists($editFile) && is_file($editFile) && !isProtectedPath($editFile)) {
        $fileContent = htmlspecialchars(file_get_contents($editFile));
        $fileInfo = pathinfo($editFile);
        $fileExtension = strtolower($fileInfo['extension'] ?? '');
    }
}

// نسخ الملفات/المجلدات
if (isset($_POST['copy'])) {
    $source = $currentPath . '/' . $_POST['copy'];
    $destination = $currentPath . '/' . $_POST['copy_dest'];
    
    if (!isProtectedPath($source) && !isProtectedPath($destination)) {
        if (is_dir($source)) {
            if (copyDirectory($source, $destination)) {
                $_SESSION['success'] = "تم نسخ المجلد بنجاح";
            } else {
                $_SESSION['error'] = "فشل نسخ المجلد";
            }
        } else {
            if (copy($source, $destination)) {
                $_SESSION['success'] = "تم نسخ الملف بنجاح";
            } else {
                $_SESSION['error'] = "فشل نسخ الملف";
            }
        }
    } else {
        $_SESSION['error'] = "لا يمكن نسخ الملفات من/إلى المسارات المحمية";
    }
    header("Location: ?path=" . urlencode($currentPath));
    exit;
}

// نسخ المجلدات بشكل متكرر
function copyDirectory($src, $dst) {
    if (!file_exists($dst)) {
        mkdir($dst, 0755, true);
    }
    
    $dir = opendir($src);
    while (false !== ($file = readdir($dir))) {
        if ($file != '.' && $file != '..') {
            if (is_dir($src . '/' . $file)) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
    return true;
}

// نقل الملفات/المجلدات
if (isset($_POST['move'])) {
    $source = $currentPath . '/' . $_POST['move'];
    $destination = $currentPath . '/' . $_POST['move_dest'];
    
    if (!isProtectedPath($source) && !isProtectedPath($destination)) {
        if (rename($source, $destination)) {
            $_SESSION['success'] = "تم نقل العنصر بنجاح";
        } else {
            $_SESSION['error'] = "فشل نقل العنصر";
        }
    } else {
        $_SESSION['error'] = "لا يمكن نقل الملفات من/إلى المسارات المحمية";
    }
    header("Location: ?path=" . urlencode($currentPath));
    exit;
}

// تغيير صلاحيات الملفات
if (isset($_POST['chmod'])) {
    $target = $currentPath . '/' . $_POST['chmod'];
    $permissions = intval($_POST['permissions'], 8);
    
    if (!isProtectedPath($target)) {
        if (chmod($target, $permissions)) {
            $_SESSION['success'] = "تم تغيير الصلاحيات بنجاح";
        } else {
            $_SESSION['error'] = "فشل تغيير الصلاحيات";
        }
    } else {
        $_SESSION['error'] = "لا يمكن تغيير صلاحيات الملفات في المسارات المحمية";
    }
    header("Location: ?path=" . urlencode($currentPath));
    exit;
}

// ضغط الملفات
if (isset($_POST['compress'])) {
    $selected = $_POST['compress_item'];
    $zipName = "backup_" . date('Y-m-d_H-i-s') . ".zip";
    $zipFile = $currentPath . '/' . $zipName;
    
    if (!isProtectedPath($currentPath)) {
        $zip = new ZipArchive();
        
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            $_SESSION['error'] = "فشل إنشاء الملف المضغوط";
        } else {
            // ضغط كل المجلد الحالي
            if ($selected === '__CURRENT__') {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($currentPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($currentPath) + 1);
                        if (!isProtectedPath($filePath)) {
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                }
                
                $zip->close();
                $_SESSION['success'] = "تم ضغط المجلد الحالي";
                $_SESSION['download_file'] = $zipName;
            } 
            // ضغط عنصر واحد
            else {
                $selected = basename($selected);
                $targetPath = $currentPath . '/' . $selected;
                
                if (!isProtectedPath($targetPath)) {
                    if (is_dir($targetPath)) {
                        $files = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($targetPath, RecursiveDirectoryIterator::SKIP_DOTS),
                            RecursiveIteratorIterator::LEAVES_ONLY
                        );
                        
                        foreach ($files as $file) {
                            if (!$file->isDir()) {
                                $filePath = $file->getRealPath();
                                $relativePath = substr($filePath, strlen($targetPath) + 1);
                                $zip->addFile($filePath, $relativePath);
                            }
                        }
                    } else {
                        $zip->addFile($targetPath, basename($targetPath));
                    }
                    
                    $zip->close();
                    $_SESSION['success'] = "تم الضغط بنجاح: " . $selected;
                    $_SESSION['download_file'] = $zipName;
                } else {
                    $_SESSION['error'] = "لا يمكن ضغط الملفات في المسارات المحمية";
                }
            }
        }
    } else {
        $_SESSION['error'] = "لا يمكن ضغط الملفات في المسارات المحمية";
    }
    header("Location: ?path=" . urlencode($currentPath));
    exit;
}

// فك ضغط الملفات
if (isset($_POST['extract'])) {
    $zipFile = $currentPath . '/' . $_POST['extract'];
    
    if (!isProtectedPath($currentPath)) {
        $zip = new ZipArchive();
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($currentPath);
            $zip->close();
            $_SESSION['success'] = "تم فك الضغط بنجاح";
        } else {
            $_SESSION['error'] = "فشل فك الضغط";
        }
    } else {
        $_SESSION['error'] = "لا يمكن فك الضغط في المسارات المحمية";
    }
    header("Location: ?path=" . urlencode($currentPath));
    exit;
}

// البحث في الملفات
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search_query'];
    $searchResults = searchFiles($currentPath, $searchQuery);
}

// وظيفة البحث
function searchFiles($path, $query) {
    $results = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if (stripos($file->getFilename(), $query) !== false) {
            $results[] = [
                'name' => $file->getFilename(),
                'path' => $file->getPath(),
                'type' => $file->isDir() ? 'dir' : 'file',
                'size' => $file->isFile() ? filesize($file->getRealPath()) : 0,
                'modified' => date('Y-m-d H:i:s', $file->getMTime())
            ];
        }
    }
    
    return $results;
}

// عرض محتوى المجلد
function listItems($path) {
    $items = [];
    if (is_dir($path)) {
        foreach (scandir($path) as $item) {
            if ($item != '.' && $item != '..') {
                $fullPath = $path . '/' . $item;
                $items[] = [
                    'name' => $item,
                    'type' => is_dir($fullPath) ? 'dir' : 'file',
                    'size' => is_file($fullPath) ? formatSize(filesize($fullPath)) : '',
                    'modified' => date('Y-m-d H:i:s', filemtime($fullPath)),
                    'permissions' => substr(sprintf('%o', fileperms($fullPath)), -4),
                    'owner' => function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($fullPath))['name'] ?? '' : '',
                    'group' => function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($fullPath))['name'] ?? '' : ''
                ];
            }
        }
        
        // ترتيب المجلدات أولاً ثم الملفات
        usort($items, function($a, $b) {
            if ($a['type'] == $b['type']) {
                return strcasecmp($a['name'], $b['name']);
            }
            return $a['type'] == 'dir' ? -1 : 1;
        });
    }
    return $items;
}

// تنسيق حجم الملف
function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

// جلب خطوط الكتابة حسب نوع الملف
function getFileIcon($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $icons = [
        'php' => 'fab fa-php',
        'html' => 'fab fa-html5',
        'css' => 'fab fa-css3-alt',
        'js' => 'fab fa-js',
        'json' => 'fas fa-code',
        'sql' => 'fas fa-database',
        'zip' => 'fas fa-file-archive',
        'jpg' => 'fas fa-file-image',
        'jpeg' => 'fas fa-file-image',
        'png' => 'fas fa-file-image',
        'gif' => 'fas fa-file-image',
        'pdf' => 'fas fa-file-pdf',
        'doc' => 'fas fa-file-word',
        'docx' => 'fas fa-file-word',
        'xls' => 'fas fa-file-excel',
        'xlsx' => 'fas fa-file-excel',
        'txt' => 'fas fa-file-alt',
        'md' => 'fas fa-file-alt'
    ];
    
    return $icons[$extension] ?? 'fas fa-file';
}

// جلب الرسائل من الجلسة
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

$items = listItems($currentPath);
$parentPath = dirname($currentPath);

// إنشاء مسار التنقل
$breadcrumbs = [];
$pathParts = explode('/', trim($currentPath, '/'));
$accumulatedPath = '';
foreach ($pathParts as $part) {
    if ($part) {
        $accumulatedPath .= '/' . $part;
        $breadcrumbs[] = [
            'name' => $part,
            'path' => $accumulatedPath
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الملفات المتقدمة</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        header {
            background: linear-gradient(135deg, #2c3e50 0%, #1a2530 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .path-navigation {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .breadcrumb a:hover {
            background: #e9ecef;
        }
        
        .path-form {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }
        
        .actions-bar {
            background: #e9ecef;
            padding: 20px 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.95em;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-success { background: linear-gradient(135deg, #38c172 0%, #28a745 100%); color: white; }
        .btn-danger { background: linear-gradient(135deg, #e3342f 0%, #dc3545 100%); color: white; }
        .btn-warning { background: linear-gradient(135deg, #f6993f 0%, #ffc107 100%); color: white; }
        .btn-info { background: linear-gradient(135deg, #6cb2eb 0%, #17a2b8 100%); color: white; }
        .btn-dark { background: linear-gradient(135deg, #343a40 0%, #212529 100%); color: white; }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .content {
            display: flex;
            min-height: 700px;
        }
        
        .sidebar {
            width: 350px;
            background: #f8f9fa;
            padding: 30px;
            border-right: 2px solid #dee2e6;
            overflow-y: auto;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        
        .section {
            margin-bottom: 30px;
            padding: 25px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .section-title {
            font-size: 1.4em;
            margin-bottom: 20px;
            color: #2c3e50;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .file-list {
            margin-top: 20px;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .file-item:hover {
            background: #e9ecef;
            border-color: #667eea;
            transform: translateX(5px);
        }
        
        .file-icon {
            font-size: 1.8em;
            margin-left: 15px;
            width: 40px;
            text-align: center;
        }
        
        .folder-icon { color: #FFA726; }
        .file-icon i { color: #42A5F5; }
        
        .file-info {
            flex: 1;
            min-width: 0;
        }
        
        .file-name {
            font-weight: 600;
            font-size: 1.1em;
            color: #2c3e50;
            margin-bottom: 5px;
            word-break: break-all;
        }
        
        .file-meta {
            font-size: 0.85em;
            color: #6c757d;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .file-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85em;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            outline: none;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffeaa7;
        }
        
        .editor-container {
            margin-top: 20px;
        }
        
        textarea.code-editor {
            width: 100%;
            height: 500px;
            padding: 20px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
            tab-size: 4;
        }
        
        .search-results {
            margin-top: 20px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        @media (max-width: 1200px) {
            .content {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 2px solid #dee2e6;
            }
            
            .actions-bar {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .file-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .file-actions {
                width: 100%;
                margin-top: 10px;
                justify-content: flex-start;
            }
            
            .path-form {
                width: 100%;
                margin-top: 10px;
            }
            
            .actions-bar {
                grid-template-columns: 1fr;
            }
        }
        
        .protected-badge {
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-right: 10px;
        }
        
        .quick-links {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 15px;
        }
        
        .quick-link {
            padding: 10px;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #495057;
        }
        
        .quick-link:hover {
            border-color: #667eea;
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .code-editor-toolbar {
            background: #2c3e50;
            padding: 10px;
            border-radius: 8px 8px 0 0;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .editor-toolbar-btn {
            padding: 5px 10px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }
        
        .editor-language {
            color: white;
            margin-left: auto;
            font-family: monospace;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github-dark.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-server"></i> لوحة تحكم الملفات المتقدمة</h1>
            <p>تصفح و تحكم كامل في جميع مجلدات السيرفر - مثل File Manager في الاستضافات</p>
        </header>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isProtectedPath($currentPath)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-shield-alt"></i> أنت في مسار محمي. بعض العمليات قد تكون مقيدة.
            </div>
        <?php endif; ?>
        
        <div class="path-navigation">
            <div class="breadcrumb">
                <a href="?path=/" title="الجذر">
                    <i class="fas fa-home"></i>
                </a>
                <?php if ($currentPath !== '/'): ?>
                    <i class="fas fa-chevron-left"></i>
                    <a href="?path=<?php echo urlencode(dirname($currentPath)); ?>">
                        <i class="fas fa-level-up-alt"></i> صعود
                    </a>
                <?php endif; ?>
                
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <i class="fas fa-chevron-left"></i>
                    <a href="?path=<?php echo urlencode($crumb['path']); ?>">
                        <?php echo htmlspecialchars($crumb['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <form method="post" class="path-form">
                <input type="text" name="new_path" class="form-control" 
                       value="<?php echo htmlspecialchars($currentPath); ?>" 
                       placeholder="/path/to/directory" style="min-width: 300px;">
                <button type="submit" name="change_path" class="btn btn-primary">
                    <i class="fas fa-folder-open"></i> الانتقال
                </button>
            </form>
        </div>
        
        <div class="actions-bar">
            <button class="btn btn-primary" onclick="openModal('compressModal')">
                <i class="fas fa-file-archive"></i> ضغط
            </button>
            <button class="btn btn-success" onclick="openModal('uploadModal')">
                <i class="fas fa-upload"></i> رفع ملف
            </button>
            <button class="btn btn-warning" onclick="openModal('folderModal')">
                <i class="fas fa-folder-plus"></i> مجلد جديد
            </button>
            <button class="btn btn-info" onclick="openModal('fileModal')">
                <i class="fas fa-file-code"></i> ملف جديد
            </button>
            <button class="btn btn-dark" onclick="openModal('searchModal')">
                <i class="fas fa-search"></i> بحث
            </button>
            <button class="btn btn-info" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> تحديث
            </button>
            
            <?php if (isset($_SESSION['download_file'])): ?>
                <a href="?path=<?php echo urlencode($currentPath); ?>&download=<?php echo $_SESSION['download_file']; ?>" 
                   class="btn btn-success">
                    <i class="fas fa-download"></i> تحميل
                </a>
                <?php unset($_SESSION['download_file']); ?>
            <?php endif; ?>
        </div>
        
        <div class="content">
            <div class="sidebar">
                <div class="section">
                    <h2 class="section-title"><i class="fas fa-info-circle"></i> معلومات النظام</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>المسار الحالي:</strong><br>
                            <small><?php echo htmlspecialchars($currentPath); ?></small>
                        </div>
                        <div class="info-item">
                            <strong>المساحة الحرة:</strong><br>
                            <?php echo formatSize(disk_free_space($currentPath)); ?>
                        </div>
                        <div class="info-item">
                            <strong>المساحة الكلية:</strong><br>
                            <?php echo formatSize(disk_total_space($currentPath)); ?>
                        </div>
                        <div class="info-item">
                            <strong>عدد العناصر:</strong><br>
                            <?php echo count($items); ?>
                        </div>
                        <div class="info-item">
                            <strong>إصدار PHP:</strong><br>
                            <?php echo phpversion(); ?>
                        </div>
                        <div class="info-item">
                            <strong>الذاكرة:</strong><br>
                            <?php echo ini_get('memory_limit'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h2 class="section-title"><i class="fas fa-bolt"></i> روابط سريعة</h2>
                    <div class="quick-links">
                        <?php
                        $commonPaths = [
                            '/var/www/html' => 'المجلد الرئيسي',
                            '/tmp' => 'المجلد المؤقت',
                            '/home' => 'مجلد Home',
                            '/etc' => 'إعدادات النظام',
                            '/var/log' => 'ملفات السجلات'
                        ];
                        
                        foreach ($commonPaths as $path => $label):
                            if (is_dir($path) && !isProtectedPath($path)):
                        ?>
                            <a href="?path=<?php echo urlencode($path); ?>" class="quick-link">
                                <i class="fas fa-folder"></i> <?php echo $label; ?>
                            </a>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
                
                <?php if (isset($searchResults)): ?>
                <div class="section">
                    <h2 class="section-title"><i class="fas fa-search"></i> نتائج البحث</h2>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php if (empty($searchResults)): ?>
                            <p>لا توجد نتائج</p>
                        <?php else: ?>
                            <?php foreach ($searchResults as $result): ?>
                                <div style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                                    <div>
                                        <i class="fas fa-<?php echo $result['type'] == 'dir' ? 'folder' : 'file'; ?>"></i>
                                        <strong><?php echo htmlspecialchars($result['name']); ?></strong>
                                    </div>
                                    <small><?php echo htmlspecialchars($result['path']); ?></small>
                                    <br>
                                    <a href="?path=<?php echo urlencode($result['path']); ?>" class="btn btn-sm btn-primary" style="margin-top: 5px;">
                                        الانتقال
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="main-content">
                <?php if (isset($_GET['edit'])): ?>
                    <div class="section">
                        <div class="code-editor-toolbar">
                            <span class="editor-language">
                                <i class="fas fa-code"></i> <?php echo strtoupper($fileExtension ?? 'نص'); ?>
                            </span>
                            <button onclick="saveFile()" class="editor-toolbar-btn">
                                <i class="fas fa-save"></i> حفظ
                            </button>
                            <button onclick="location.href='?path=<?php echo urlencode($currentPath); ?>'" 
                                    class="editor-toolbar-btn" style="background: #e3342f;">
                                <i class="fas fa-times"></i> إلغاء
                            </button>
                        </div>
                        
                        <form method="post" id="editForm">
                            <input type="hidden" name="edit_file" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                            <div class="form-group">
                                <textarea name="file_content" class="code-editor" id="codeEditor"><?php echo $fileContent ?? ''; ?></textarea>
                            </div>
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <button type="submit" name="edit_content" class="btn btn-success">
                                    <i class="fas fa-save"></i> حفظ التعديلات
                                </button>
                                <button type="button" onclick="location.href='?path=<?php echo urlencode($currentPath); ?>'" 
                                        class="btn btn-danger">
                                    <i class="fas fa-times"></i> إلغاء
                                </button>
                            </div>
                        </form>
                        
                        <script>
                            function saveFile() {
                                document.getElementById('editForm').submit();
                            }
                            
                            // تمييز الصيغة في المحرر
                            document.addEventListener('DOMContentLoaded', function() {
                                const editor = document.getElementById('codeEditor');
                                editor.addEventListener('keydown', function(e) {
                                    if (e.key === 'Tab') {
                                        e.preventDefault();
                                        const start = this.selectionStart;
                                        const end = this.selectionEnd;
                                        this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                                        this.selectionStart = this.selectionEnd = start + 4;
                                    }
                                });
                            });
                        </script>
                    </div>
                <?php else: ?>
                    <div class="section">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h2 class="section-title" style="margin: 0;">
                                <i class="fas fa-folder"></i> 
                                <?php echo basename($currentPath) ?: 'الجذر'; ?>
                            </h2>
                            <div style="display: flex; gap: 10px;">
                                <span class="badge" style="background: #667eea; color: white; padding: 5px 10px; border-radius: 5px;">
                                    <i class="fas fa-folder"></i> <?php echo count(array_filter($items, fn($i) => $i['type'] === 'dir')); ?>
                                </span>
                                <span class="badge" style="background: #42A5F5; color: white; padding: 5px 10px; border-radius: 5px;">
                                    <i class="fas fa-file"></i> <?php echo count(array_filter($items, fn($i) => $i['type'] === 'file')); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="file-list">
                            <?php if (empty($items)): ?>
                                <div style="text-align: center; padding: 40px; color: #6c757d;">
                                    <i class="fas fa-folder-open" style="font-size: 3em; margin-bottom: 20px;"></i>
                                    <h3>المجلد فارغ</h3>
                                    <p>لا توجد ملفات أو مجلدات في هذا المسار</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($items as $item): 
                                    $isProtected = isProtectedPath($currentPath . '/' . $item['name']);
                                ?>
                                    <div class="file-item">
                                        <div class="file-icon <?php echo $item['type'] == 'dir' ? 'folder-icon' : ''; ?>">
                                            <?php if ($item['type'] == 'dir'): ?>
                                                <i class="fas fa-folder"></i>
                                            <?php else: ?>
                                                <i class="<?php echo getFileIcon($item['name']); ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="file-info">
                                            <div class="file-name">
                                                <?php if ($isProtected): ?>
                                                    <span class="protected-badge">محمي</span>
                                                <?php endif; ?>
                                                <?php if ($item['type'] == 'dir'): ?>
                                                    <a href="?path=<?php echo urlencode($currentPath . '/' . $item['name']); ?>" 
                                                       style="color: inherit; text-decoration: none;">
                                                        <?php echo htmlspecialchars($item['name']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="file-meta">
                                                <?php if ($item['type'] == 'file'): ?>
                                                    <span><i class="fas fa-weight-hanging"></i> <?php echo $item['size']; ?></span>
                                                <?php endif; ?>
                                                <span><i class="fas fa-calendar"></i> <?php echo $item['modified']; ?></span>
                                                <span><i class="fas fa-lock"></i> <?php echo $item['permissions']; ?></span>
                                                <?php if ($item['owner']): ?>
                                                    <span><i class="fas fa-user"></i> <?php echo $item['owner']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="file-actions">
                                            <?php if ($item['type'] == 'file' && !$isProtected): ?>
                                                <a href="?path=<?php echo urlencode($currentPath); ?>&edit=<?php echo urlencode($item['name']); ?>" 
                                                   class="action-btn btn-warning">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                                <button onclick="copyToClipboard('<?php echo addslashes($currentPath . '/' . $item['name']); ?>')" 
                                                        class="action-btn btn-info">
                                                    <i class="fas fa-copy"></i> نسخ مسار
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if (!$isProtected): ?>
                                                <button onclick="openRenameModal('<?php echo htmlspecialchars($item['name']); ?>')" 
                                                        class="action-btn btn-info">
                                                    <i class="fas fa-pen"></i> إعادة تسمية
                                                </button>
                                                
                                                <form method="post" style="display: inline;" 
                                                      onsubmit="return confirm('هل أنت متأكد من حذف <?php echo htmlspecialchars($item['name']); ?>؟')">
                                                    <input type="hidden" name="delete" value="<?php echo htmlspecialchars($item['name']); ?>">
                                                    <button type="submit" class="action-btn btn-danger">
                                                        <i class="fas fa-trash"></i> حذف
                                                    </button>
                                                </form>
                                                
                                                <button onclick="openPropertiesModal('<?php echo htmlspecialchars($item['name']); ?>')" 
                                                        class="action-btn btn-dark">
                                                    <i class="fas fa-cog"></i> خصائص
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- جميع الـ Modals -->
    <?php include 'modals.php'; ?>
    
    <script>
        // دوال التحكم في الـ Modals
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function openRenameModal(oldName) {
            document.getElementById('oldName').value = oldName;
            document.getElementById('newName').value = oldName;
            openModal('renameModal');
        }
        
        function openPropertiesModal(fileName) {
            document.getElementById('propFileName').innerText = fileName;
            openModal('propertiesModal');
        }
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('تم نسخ المسار: ' + text);
            });
        }
        
        // فحص الصلاحيات
        function checkAllPermissions() {
            const items = <?php echo json_encode($items); ?>;
            let issues = [];
            
            items.forEach(item => {
                if (item.permissions && item.permissions < '0644' && item.type === 'file') {
                    issues.push(`صلاحيات منخفضة: ${item.name} (${item.permissions})`);
                }
            });
            
            if (issues.length > 0) {
                alert('تم العثور على مشاكل في الصلاحيات:\n\n' + issues.join('\n'));
            } else {
                alert('✓ جميع الصلاحيات مناسبة');
            }
        }
        
        // إغلاق الـ Modal عند الضغط خارجها
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // تحميل Highlight.js لتمييز الصيغة
        if (document.querySelector('.code-editor')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github.min.css';
            document.head.appendChild(link);
            
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js';
            script.onload = function() {
                const editor = document.getElementById('codeEditor');
                if (editor) {
                    editor.addEventListener('input', function() {
                        this.style.height = 'auto';
                        this.style.height = (this.scrollHeight) + 'px';
                    });
                }
            };
            document.head.appendChild(script);
        }
        
        // اختصارات لوحة المفاتيح
        document.addEventListener('keydown', function(e) {
            // Ctrl+S لحفظ الملف
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                if (document.getElementById('editForm')) {
                    document.getElementById('editForm').submit();
                }
            }
            
            // Escape لإغلاق Modals
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });
        
        // تفعيل Drag & Drop للرفع
        document.addEventListener('DOMContentLoaded', function() {
            const uploadZone = document.querySelector('.main-content');
            uploadZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '#e9ecef';
            });
            
            uploadZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '';
            });
            
            uploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '';
                
                if (e.dataTransfer.files.length > 0) {
                    openModal('uploadModal');
                }
            });
        });
    </script>
</body>
</html>