php<?php
// 관리자 계정 설정 (보안을 위해 변경하세요!)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', password_hash('1234', PASSWORD_DEFAULT));

// 데이터베이스 역할의 JSON 파일 경로
define('DATA_FILE', __DIR__ . '/data/content.json');

// 업로드 폴더 경로
define('UPLOAD_IMAGES', __DIR__ . '/images/');
define('UPLOAD_VIDEOS', __DIR__ . '/videos/');
define('UPLOAD_AUDIO', __DIR__ . '/audio/');

// 세션 시작
session_start();

// 로그인 확인 함수
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// 데이터 로드 함수
function loadData() {
    if (!file_exists(DATA_FILE)) {
        $defaultData = [
            'profile' => [
                'title' => '서울중독심리연구소',
                'description' => '자기 사랑을 통한 심신 건강 회복',
                'image' => 'https://postfiles.pstatic.net/MjAyNTEwMTRfMTA5/MDAxNzYwNDE1MTg2OTI4.yD8lPs2N9h2DHpNWGYm7Q2hrlTnhsvhUnl0eeGlYIjMg.nnfspaY5_W6Jhv9cXQZxHIgrPLEpWP83oxpstOF-cEUg.PNG'
            ],
            'links' => [
                ['title' => '콜링유', 'url' => 'https://callingu.kr/'],
                ['title' => '🎙️ 중독회복방송', 'url' => 'https://example.com/중독회복']
            ],
            'media' => [
                'audio' => [],
                'videos' => [],
                'images' => []
            ]
        ];
        file_put_contents(DATA_FILE, json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $defaultData;
    }
    return json_decode(file_get_contents(DATA_FILE), true);
}

// 데이터 저장 함수
function saveData($data) {
    return file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// 파일 업로드 함수
function uploadFile($file, $type) {
    $allowed = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'video' => ['mp4', 'webm', 'mov', 'avi'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a']
    ];
    
    $uploadDir = [
        'image' => UPLOAD_IMAGES,
        'video' => UPLOAD_VIDEOS,
        'audio' => UPLOAD_AUDIO
    ];
    
    if (!isset($allowed[$type]) || !isset($uploadDir[$type])) {
        return ['success' => false, 'message' => '잘못된 파일 타입입니다.'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed[$type])) {
        return ['success' => false, 'message' => '허용되지 않는 파일 형식입니다.'];
    }
    
    $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', basename($file['name'], '.' . $ext)) . '.' . $ext;
    $filepath = $uploadDir[$type] . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => str_replace(__DIR__ . '/', '', $filepath)];
    }
    
    return ['success' => false, 'message' => '파일 업로드에 실패했습니다.'];
}

// 필수 폴더 생성
$folders = [__DIR__ . '/data', UPLOAD_IMAGES, UPLOAD_VIDEOS, UPLOAD_AUDIO, __DIR__ . '/admin'];
foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
}
?>
