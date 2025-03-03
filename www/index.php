<?php
// 스마트팜 제어 커뮤니티 웹사이트 - 초기 테스트 페이지
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>스마트팜 제어 커뮤니티</title>
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
        .info-box {
            background-color: #e8f4f8;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .server-info {
            margin-top: 30px;
            font-size: 0.9em;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>스마트팜 제어 커뮤니티</h1>
        
        <div class="info-box">
            <h2>프로젝트 개요</h2>
            <p>스마트팜 운영 및 자동화에 관심 있는 사용자들을 위한 온라인 커뮤니티 플랫폼입니다.</p>
            <p>아두이노 기반 스마트팜 장치를 제어하고 모니터링할 수 있는 기능을 제공합니다.</p>
        </div>
        
        <div class="info-box">
            <h2>핵심 기능</h2>
            <ul>
                <li>장치 제어 기능</li>
                <li>센서 데이터 모니터링 기능</li>
                <li>커뮤니티 기능</li>
                <li>개인 계정 기반 장치 관리</li>
            </ul>
        </div>
        
        <div class="server-info">
            <h3>서버 정보</h3>
            <p>PHP 버전: <?php echo phpversion(); ?></p>
            <p>서버 시간: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>서버 환경: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
        </div>
    </div>
</body>
</html>
