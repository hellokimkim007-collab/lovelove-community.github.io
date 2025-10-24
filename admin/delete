<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $index = (int)($_POST['index'] ?? -1);
    $file = $_POST['file'] ?? '';
    
    $data = loadData();
    
    if (isset($data['media'][$type][$index])) {
        // 파일 삭제
        $filepath = __DIR__ . '/../' . $file;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        // 데이터에서 제거
        array_splice($data['media'][$type], $index, 1);
        saveData($data);
        
        $_SESSION['message'] = '파일이 삭제되었습니다.';
    }
}

header('Location: upload.php');
exit;
?>
