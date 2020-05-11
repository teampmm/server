<?php

defined('BASEPATH') or exit('No direct script access allowed');

// CORS 요청처리 시작
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, PATCH, OPTIONS");
// CORS 요청처리 끝

// :any -> [^/]
// :num -> [0-9]


$route['user/(:any)'] = 'user/requestData/$1';
//핸드폰인증
$route['sms/(:any)'] = 'user/sms/$1';

// 정치인 정보
$route['politician/(:any)'] = 'politician/requestData/$1';

//법안 정보
$route['bill/(:any)']='bill/requestData/$1';






//법안 모아보기
//$route['bill/(:num)'] = 'bill/page/$1';
//법안 상세보기
//$route['billinfo/(:num)'] = 'bill/billinfo/$1';
//법안 상세보기 = 법안에 좋아요 , 싫어요 보여주기
//$route['billinfo/evaluation/(:num)'] = 'bill/billUserEvaluation/$1';
//법안 상세보기 - 법안에 대한 댓글 보여주기
//$route['billinfo/commment/(:num)']='bill/billComment/$1';


//$route['main_exam/(:num)']='main_exam/get/$1';

// 홈페이지 기본경로
// ex : url에 52.78.106.225를 입력했을때 controllers - DefaultController.php파일이 실행된다.
$route['default_controller'] = 'DefaultController';
$route['404_override'] = '';
$route['translate_uri_dashes'] = false;
