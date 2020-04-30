<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// CORS 요청처리 시작
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}
// CORS 요청처리 끝

// :any -> [^/]
// :num -> [0-9]

$route['user/(:any)']='user/requestData/$1';

// 정치인 정보
$route['politician/(:any)']='politician/requestData/$1';
//법안 모아보기
$route['bill/(:num)']='bill/page/$1';
//법안 상세보기
$route['billinfo/(:num)']='bill/billinfo/$1';
#로그인 테스트중 = 종영
//$route['login/(:any)']='user/login_test/$1';
#닉네임 테스트중 = 종영
//$route['nick/(:any)']='login/nick_test/$1';


//$route['main_exam/(:num)']='main_exam/get/$1';

// 홈페이지 기본경로
// ex : url에 52.78.106.225를 입력했을때 controllers - DefaultController.php파일이 실행된다.
$route['default_controller'] = 'DefaultController';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
