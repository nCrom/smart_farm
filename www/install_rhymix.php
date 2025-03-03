<?php
/**
 * 라이믹스 CMS 자동 설치 스크립트
 * 
 * 이 스크립트는 라이믹스 CMS의 최신 버전을 다운로드하고 설치하는 자동화 스크립트입니다.
 */

// 오류 보고 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 필요한 함수들
function downloadFile($url, $path) {
    echo "다운로드 중: $url\n";
    $fileContent = file_get_contents($url);
    if ($fileContent === false) {
        echo "다운로드 실패: $url\n";
        return false;
    }
    
    if (file_put_contents($path, $fileContent) === false) {
        echo "파일 저장 실패: $path\n";
        return false;
    }
    
    echo "다운로드 완료: $path\n";
    return true;
}

function extractZip($zipPath, $extractPath) {
    echo "압축 해제 중: $zipPath\n";
    
    $zip = new ZipArchive;
    $res = $zip->open($zipPath);
    if ($res !== true) {
        echo "압축 파일 열기 실패: " . $res . "\n";
        return false;
    }
    
    // 압축 해제 전에 디렉토리 권한 확인 및 설정
    if (!is_writable($extractPath)) {
        echo "압축 해제 디렉토리에 쓰기 권한이 없습니다: $extractPath\n";
        chmod($extractPath, 0755);
        echo "디렉토리 권한을 변경했습니다.\n";
    }
    
    if (!$zip->extractTo($extractPath)) {
        echo "압축 해제 실패\n";
        $zip->close();
        return false;
    }
    
    $zip->close();
    echo "압축 해제 완료\n";
    return true;
}

function moveFiles($sourceDir, $targetDir) {
    echo "파일 이동 중: $sourceDir -> $targetDir\n";
    
    if (!is_dir($sourceDir)) {
        echo "소스 디렉토리가 존재하지 않습니다: $sourceDir\n";
        return false;
    }
    
    $dir = opendir($sourceDir);
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $sourcePath = $sourceDir . '/' . $file;
            $targetPath = $targetDir . '/' . $file;
            
            if (is_dir($sourcePath)) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
                moveFiles($sourcePath, $targetPath);
            } else {
                copy($sourcePath, $targetPath);
            }
        }
    }
    closedir($dir);
    
    echo "파일 이동 완료\n";
    return true;
}

// 메인 스크립트
echo "<html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>라이믹스 CMS 설치</title>";
echo "<style>
    body {
        font-family: 'Malgun Gothic', sans-serif;
        line-height: 1.6;
        margin: 20px;
        padding: 0;
        color: #333;
    }
    h1 {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    .step {
        background-color: #ecf0f1;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    .step h2 {
        color: #2c3e50;
        margin-top: 0;
    }
    .success {
        background-color: #d4edda;
        color: #155724;
        padding: 10px;
        border-radius: 3px;
        margin-bottom: 10px;
    }
    .error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 3px;
        margin-bottom: 10px;
    }
    .info {
        background-color: #d1ecf1;
        color: #0c5460;
        padding: 10px;
        border-radius: 3px;
        margin-bottom: 10px;
    }
    code {
        background-color: #f8f9fa;
        padding: 2px 4px;
        border-radius: 3px;
        font-family: monospace;
    }
</style></head><body>";

echo "<h1>라이믹스 CMS 설치</h1>";

// 1단계: 필수 PHP 확장 기능 확인
echo "<div class='step'>";
echo "<h2>1단계: 필수 PHP 확장 기능 확인</h2>";

$requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'xml', 'curl', 'zip', 'fileinfo'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (empty($missingExtensions)) {
    echo "<div class='success'>모든 필수 PHP 확장 기능이 설치되어 있습니다.</div>";
    echo "<div class='info'>
        PHP 버전: " . PHP_VERSION . "<br>
        GD 버전: " . (function_exists('gd_info') ? gd_info()['GD Version'] : '알 수 없음') . "<br>
        MySQL 클라이언트 버전: " . (function_exists('mysqli_get_client_info') ? mysqli_get_client_info() : '알 수 없음') . "
    </div>";
} else {
    echo "<div class='error'>다음 PHP 확장 기능이 누락되었습니다: " . implode(', ', $missingExtensions) . "</div>";
    echo "<div class='info'>PHP 확장 기능을 설치하려면 서버 관리자에게 문의하세요.</div>";
    exit;
}
echo "</div>";

// 2단계: 라이믹스 다운로드
echo "<div class='step'>";
echo "<h2>2단계: 라이믹스 다운로드</h2>";

// 임시 디렉토리 및 파일 경로 설정
$tempDir = __DIR__ . '/temp';
$zipFile = $tempDir . '/rhymix.zip';
$extractDir = $tempDir . '/extract';
$rhymixLatestUrl = 'https://github.com/rhymix/rhymix/archive/refs/heads/master.zip';

// 임시 디렉토리 생성
if (!is_dir($tempDir)) {
    if (!mkdir($tempDir, 0755, true)) {
        echo "<div class='error'>임시 디렉토리를 생성할 수 없습니다: $tempDir</div>";
        exit;
    }
    echo "<div class='info'>임시 디렉토리 생성됨: $tempDir</div>";
}

if (!is_dir($extractDir)) {
    if (!mkdir($extractDir, 0755, true)) {
        echo "<div class='error'>압축 해제 디렉토리를 생성할 수 없습니다: $extractDir</div>";
        exit;
    }
    echo "<div class='info'>압축 해제 디렉토리 생성됨: $extractDir</div>";
}

// 라이믹스 다운로드
if (downloadFile($rhymixLatestUrl, $zipFile)) {
    echo "<div class='success'>다운로드 완료</div>";
} else {
    echo "<div class='error'>라이믹스 다운로드 실패</div>";
    exit;
}
echo "</div>";

// 3단계: 압축 해제
echo "<div class='step'>";
echo "<h2>3단계: 압축 해제</h2>";

// 압축 해제
if (extractZip($zipFile, $extractDir)) {
    echo "<div class='success'>압축 해제 완료</div>";
} else {
    echo "<div class='error'>압축 해제 실패</div>";
    
    // 디버깅 정보 추가
    echo "<div class='info'>디버깅 정보:<br>";
    echo "PHP 버전: " . PHP_VERSION . "<br>";
    echo "ZipArchive 클래스 존재 여부: " . (class_exists('ZipArchive') ? '예' : '아니오') . "<br>";
    echo "임시 디렉토리 권한: " . substr(sprintf('%o', fileperms($tempDir)), -4) . "<br>";
    echo "압축 파일 크기: " . (file_exists($zipFile) ? filesize($zipFile) . " bytes" : "파일 없음") . "<br>";
    echo "디스크 여유 공간: " . disk_free_space(__DIR__) . " bytes<br>";
    echo "</div>";
    
    // 수동 설치 안내
    echo "<div class='info'>
        <p>자동 설치에 실패했습니다. 다음 단계에 따라 수동으로 설치해 주세요:</p>
        <ol>
            <li><a href='$rhymixLatestUrl' target='_blank'>여기</a>를 클릭하여 라이믹스를 다운로드하세요.</li>
            <li>다운로드한 파일의 압축을 풀고 모든 파일을 웹 서버의 루트 디렉토리에 업로드하세요.</li>
            <li>웹 브라우저에서 <code>http://localhost/index.php</code>에 접속하여 설치를 계속하세요.</li>
        </ol>
    </div>";
    exit;
}

// 4단계: 파일 이동
echo "<div class='step'>";
echo "<h2>4단계: 파일 이동</h2>";

// 압축 해제된 디렉토리 찾기
$extractedDir = '';
$dir = opendir($extractDir);
while (($file = readdir($dir)) !== false) {
    if ($file != '.' && $file != '..' && is_dir($extractDir . '/' . $file)) {
        $extractedDir = $extractDir . '/' . $file;
        break;
    }
}
closedir($dir);

if (empty($extractedDir)) {
    echo "<div class='error'>압축 해제된 디렉토리를 찾을 수 없습니다.</div>";
    exit;
}

// 파일 이동
if (moveFiles($extractedDir, __DIR__)) {
    echo "<div class='success'>파일 이동 완료</div>";
} else {
    echo "<div class='error'>파일 이동 실패</div>";
    exit;
}
echo "</div>";

// 5단계: 임시 파일 정리
echo "<div class='step'>";
echo "<h2>5단계: 임시 파일 정리</h2>";

// 임시 파일 삭제 함수
function removeDir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object)) {
                    removeDir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        rmdir($dir);
        return true;
    }
    return false;
}

// 임시 파일 정리
if (removeDir($tempDir)) {
    echo "<div class='success'>임시 파일 정리 완료</div>";
} else {
    echo "<div class='error'>임시 파일 정리 실패</div>";
}
echo "</div>";

// 6단계: 설치 완료
echo "<div class='step'>";
echo "<h2>6단계: 설치 완료</h2>";

echo "<div class='success'>라이믹스 CMS 파일이 성공적으로 설치되었습니다!</div>";
echo "<div class='info'>
    <p>이제 라이믹스 CMS 설치 마법사를 실행하여 설치를 완료하세요:</p>
    <ol>
        <li>웹 브라우저에서 <a href='http://localhost/index.php'>http://localhost/index.php</a>에 접속하세요.</li>
        <li>설치 마법사의 지시에 따라 데이터베이스 정보를 입력하세요:
            <ul>
                <li>데이터베이스 종류: MySQL</li>
                <li>데이터베이스 서버 주소: db</li>
                <li>데이터베이스 포트: 3306</li>
                <li>데이터베이스 이름: smartfarm</li>
                <li>데이터베이스 사용자 이름: smartfarm</li>
                <li>데이터베이스 비밀번호: smartfarm</li>
            </ul>
        </li>
        <li>관리자 정보 및 사이트 정보를 입력하세요.</li>
    </ol>
</div>";
echo "</div>";

echo "</body></html>";
?>
