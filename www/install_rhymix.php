<?php
/**
 * 라이믹스 CMS 자동 설치 스크립트
 * 
 * 이 스크립트는 라이믹스 CMS의 최신 버전을 다운로드하고 설치하는 과정을 자동화합니다.
 * 실행 후에는 보안을 위해 이 파일을 삭제하는 것을 권장합니다.
 */

// 오류 보고 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 헤더 출력
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>라이믹스 CMS 설치</title>
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .step {
            background-color: #e8f4f8;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #e8f8e8;
            border-left: 4px solid #2ecc71;
        }
        .error {
            background-color: #f8e8e8;
            border-left: 4px solid #e74c3c;
        }
        pre {
            background-color: #f8f8f8;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>라이믹스 CMS 설치</h1>
        
        <?php
        // 작업 디렉토리 설정
        $workDir = __DIR__;
        $rhymixDir = $workDir . '/rhymix';
        
        // 1단계: 필수 PHP 확장 기능 확인
        echo '<div class="step"><h2>1단계: 필수 PHP 확장 기능 확인</h2>';
        $requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'xml', 'curl', 'zip', 'fileinfo'];
        $missingExtensions = [];
        
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }
        
        if (empty($missingExtensions)) {
            echo '<p class="success">모든 필수 PHP 확장 기능이 설치되어 있습니다.</p>';
            echo '<pre>';
            echo 'PHP 버전: ' . phpversion() . "\n";
            echo 'GD 버전: ' . (function_exists('gd_info') ? gd_info()['GD Version'] : '설치되지 않음') . "\n";
            echo 'MySQL 클라이언트 버전: ' . (function_exists('mysqli_get_client_info') ? mysqli_get_client_info() : '설치되지 않음');
            echo '</pre>';
        } else {
            echo '<p class="error">다음 PHP 확장 기능이 누락되었습니다: ' . implode(', ', $missingExtensions) . '</p>';
            echo '<p>Docker 이미지를 다시 빌드하여 필요한 확장 기능을 설치하세요.</p>';
            exit;
        }
        echo '</div>';
        
        // 2단계: 라이믹스 다운로드
        echo '<div class="step"><h2>2단계: 라이믹스 다운로드</h2>';
        
        // 이미 다운로드된 경우 건너뛰기
        if (file_exists($rhymixDir) && is_dir($rhymixDir)) {
            echo '<p class="success">라이믹스가 이미 다운로드되어 있습니다.</p>';
        } else {
            echo '<p>라이믹스 최신 버전을 다운로드합니다...</p>';
            
            // GitHub에서 최신 릴리스 다운로드
            $releaseUrl = 'https://github.com/rhymix/rhymix/releases/latest/download/rhymix.zip';
            $zipFile = $workDir . '/rhymix.zip';
            
            // cURL을 사용하여 파일 다운로드
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $releaseUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $data = curl_exec($ch);
            
            if (curl_errno($ch)) {
                echo '<p class="error">다운로드 오류: ' . curl_error($ch) . '</p>';
                exit;
            }
            
            curl_close($ch);
            
            // 파일 저장
            if (file_put_contents($zipFile, $data)) {
                echo '<p class="success">다운로드 완료</p>';
                
                // ZIP 파일 압축 해제
                $zip = new ZipArchive;
                if ($zip->open($zipFile) === TRUE) {
                    $zip->extractTo($workDir);
                    $zip->close();
                    echo '<p class="success">압축 해제 완료</p>';
                    
                    // 압축 파일 삭제
                    unlink($zipFile);
                } else {
                    echo '<p class="error">압축 해제 실패</p>';
                    exit;
                }
            } else {
                echo '<p class="error">파일 저장 실패</p>';
                exit;
            }
        }
        echo '</div>';
        
        // 3단계: 파일 권한 설정
        echo '<div class="step"><h2>3단계: 파일 권한 설정</h2>';
        
        // 권한 설정이 필요한 디렉토리 목록
        $permissionDirs = [
            $rhymixDir . '/files',
            $rhymixDir . '/modules',
            $rhymixDir . '/widgets',
            $rhymixDir . '/layouts',
            $rhymixDir . '/themes',
            $rhymixDir . '/common/tpl/cache',
            $rhymixDir . '/common/images',
            $rhymixDir . '/common/fonts',
            $rhymixDir . '/common/js/plugins',
            $rhymixDir . '/common/css/plugins',
        ];
        
        $success = true;
        foreach ($permissionDirs as $dir) {
            if (file_exists($dir)) {
                if (!is_writable($dir)) {
                    if (!chmod($dir, 0777)) {
                        echo '<p class="error">권한 설정 실패: ' . $dir . '</p>';
                        $success = false;
                    }
                }
            }
        }
        
        if ($success) {
            echo '<p class="success">파일 권한 설정 완료</p>';
        }
        echo '</div>';
        
        // 4단계: 설치 안내
        echo '<div class="step"><h2>4단계: 설치 안내</h2>';
        echo '<p>라이믹스 CMS가 성공적으로 다운로드되었습니다.</p>';
        echo '<p>이제 다음 단계를 진행하세요:</p>';
        echo '<ol>';
        echo '<li>웹 브라우저에서 <a href="/rhymix" target="_blank">/rhymix</a> 경로로 접속하세요.</li>';
        echo '<li>화면에 표시되는 설치 마법사를 따라 라이믹스를 설치하세요.</li>';
        echo '<li>데이터베이스 정보 입력 시 다음 정보를 사용하세요:';
        echo '<ul>';
        echo '<li>데이터베이스 종류: MySQL</li>';
        echo '<li>호스트: db</li>';
        echo '<li>포트: 3306</li>';
        echo '<li>데이터베이스 이름: smartfarm</li>';
        echo '<li>사용자 이름: smartfarm</li>';
        echo '<li>비밀번호: smartfarm</li>';
        echo '</ul></li>';
        echo '<li>설치가 완료되면 이 파일을 삭제하세요.</li>';
        echo '</ol>';
        echo '<a href="/rhymix" class="btn" target="_blank">라이믹스 설치 페이지로 이동</a>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
