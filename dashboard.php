<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$data = loadData();
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// 프로필 정보 업데이트
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $data['profile']['title'] = $_POST['profile_title'] ?? '';
        $data['profile']['description'] = $_POST['profile_description'] ?? '';
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $result = uploadFile($_FILES['profile_image'], 'image');
            if ($result['success']) {
                $data['profile']['image'] = $result['path'];
            }
        }
        
        saveData($data);
        $_SESSION['message'] = '프로필이 업데이트되었습니다.';
        header('Location: dashboard.php');
        exit;
    }
    
    // 링크 추가
    if ($_POST['action'] === 'add_link') {
        $data['links'][] = [
            'title' => $_POST['link_title'] ?? '',
            'url' => $_POST['link_url'] ?? ''
        ];
        saveData($data);
        $_SESSION['message'] = '링크가 추가되었습니다.';
        header('Location: dashboard.php');
        exit;
    }
    
    // 링크 삭제
    if ($_POST['action'] === 'delete_link') {
        $index = (int)$_POST['link_index'];
        array_splice($data['links'], $index, 1);
        saveData($data);
        $_SESSION['message'] = '링크가 삭제되었습니다.';
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 대시보드</title>
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
        
        .logout-btn {
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
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
        input[type="url"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        input[type="file"] {
            padding: 10px;
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
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .link-list {
            list-style: none;
        }
        
        .link-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .link-info {
            flex: 1;
        }
        
        .link-title {
            font-weight: 600;
            color: #333;
        }
        
        .link-url {
            color: #666;
            font-size: 14px;
            word-break: break-all;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .tab-btn {
            padding: 12px 24px;
            background: #e0e0e0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .tab-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .preview-img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 자기사랑 커뮤니티 관리</h1>
        <a href="logout.php" class="logout-btn">로그아웃</a>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('profile')">프로필 관리</button>
            <button class="tab-btn" onclick="showTab('links')">링크 관리</button>
            <button class="tab-btn" onclick="showTab('media')">미디어 업로드</button>
        </div>
        
        <!-- 프로필 관리 -->
        <div id="profile" class="section">
            <h2>프로필 정보</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label for="profile_title">프로필 제목</label>
                    <input type="text" id="profile_title" name="profile_title" 
                           value="<?php echo htmlspecialchars($data['profile']['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="profile_description">프로필 설명</label>
                    <textarea id="profile_description" name="profile_description" required><?php echo htmlspecialchars($data['profile']['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="profile_image">프로필 이미지</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*">
                    <?php if (!empty($data['profile']['image'])): ?>
                        <img src="../<?php echo htmlspecialchars($data['profile']['image']); ?>" class="preview-img" alt="현재 프로필">
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn">프로필 업데이트</button>
            </form>
        </div>
        
        <!-- 링크 관리 -->
        <div id="links" class="section" style="display:none;">
            <h2>링크 목록</h2>
            
            <ul class="link-list">
                <?php foreach ($data['links'] as $index => $link): ?>
                    <li class="link-item">
                        <div class="link-info">
                            <div class="link-title"><?php echo htmlspecialchars($link['title']); ?></div>
                            <div class="link-url"><?php echo htmlspecialchars($link['url']); ?></div>
                        </div>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_link">
                            <input type="hidden" name="link_index" value="<?php echo $index; ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <h2 style="margin-top: 40px;">새 링크 추가</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_link">
                
                <div class="form-group">
                    <label for="link_title">링크 제목</label>
                    <input type="text" id="link_title" name="link_title" placeholder="📞 상담 신청하기" required>
                </div>
                
                <div class="form-group">
                    <label for="link_url">링크 URL</label>
                    <input type="url" id="link_url" name="link_url" placeholder="https://example.com" required>
                </div>
                
                <button type="submit" class="btn">링크 추가</button>
            </form>
        </div>
        
        <!-- 미디어 업로드 -->
        <div id="media" class="section" style="display:none;">
            <h2>미디어 파일 업로드</h2>
            <p style="color: #666; margin-bottom: 20px;">
                이미지, 동영상, 음원 파일을 업로드하려면 
                <a href="upload.php" style="color: #667eea; font-weight: 600;">미디어 업로드 페이지</a>로 이동하세요.
            </p>
            <a href="upload.php" class="btn">미디어 업로드 페이지 열기</a>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            const tabs = ['profile', 'links', 'media'];
            const buttons = document.querySelectorAll('.tab-btn');
            
            tabs.forEach(tab => {
                document.getElementById(tab).style.display = 'none';
            });
            
            buttons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            document.getElementById(tabName).style.display = 'block';
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
