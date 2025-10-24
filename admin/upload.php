<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$data = loadData();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $title = $_POST['title'] ?? '';
    
    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === 0) {
        $result = uploadFile($_FILES['media_file'], $type);
        
        if ($result['success']) {
            $mediaType = $type === 'image' ? 'images' : ($type === 'video' ? 'videos' : 'audio');
            
            $data['media'][$mediaType][] = [
                'title' => $title,
                'file' => $result['path'],
                'uploaded_at' => date('Y-m-d H:i:s')
            ];
            
            saveData($data);
            $message = '파일이 성공적으로 업로드되었습니다!';
        } else {
            $message = '업로드 실패: ' . $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>미디어 업로드</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #333;
            font-size: 24px;
        }
        
        .back-btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        
        input[type="text"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #667eea;
            border-radius: 8px;
            background: #f8f9ff;
        }
        
        .btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #e74c3c;
            padding: 8px 16px;
            font-size: 12px;
        }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .media-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            position: relative;
        }
        
        .media-item img,
        .media-item video {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .media-item audio {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .media-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .media-date {
            font-size: 12px;
            color: #999;
        }
        
        .delete-form {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📤 미디어 업로드</h1>
        <a href="dashboard.php" class="back-btn">← 대시보드로</a>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, '실패') !== false ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- 업로드 폼 -->
        <div class="section">
            <h2>새 미디어 업로
