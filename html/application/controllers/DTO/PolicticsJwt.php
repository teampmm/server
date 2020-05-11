<?php

// php-jwt 라이브러리를 사용 하기 위해서 autoload.php를 불러와야함.
require '../../../home/ubuntu/vendor/autoload.php';
use \Firebase\JWT\JWT;

class PolicticsJwt
{
    private $key;

    public function __construct()
    {
        include "/home/ubuntu/db/TokenKey.php";
        $this->key = getTokenKey();
    }

    // 사용자가 로그인 요청 시 - 토큰 생성
    function createToken($user_info){

        // 현재 시간 : 년도 월 일 : 20201029
        $currentTime = str_replace('-','',date("Y-m-d"));
        // 토큰 만료 시간 - 발급 시간 기준 +1일 // 토큰 얼마나 보관해야 하는지 잘 몰라서 우선 1일으로 해놓음
        $deadlineTime = (string)((int)$currentTime + 1);

        /** 클라이언트에 보내는 토큰 정보
        사용자 id
        사용자 인덱스
        사용자 닉네임
        토큰 발급시간
        토근 만료시간
         */
//        print_r($id->email_id);
        $payload = array(
            "idx" => $user_info->idx,
            "id" => $user_info->id,
            "nick_name" => $user_info->nick_name,
            "token issue time" => $currentTime,
            "token expiration time" => $deadlineTime
            //    "data" => [
            //    "name" => "ZiHang Gao",
            //    "admin" => true
            //],
        );
        // 토큰에 담길 정보 인코딩
        $jwt = JWT::encode($payload, $this->key);

        // 인코딩 하는 과정에서 ` = ` 이라는 문자가 포함 되는 경우도 있다.
        // 토큰은 url 파라미터도 전달되는 경우가 있다고 하는데, url-safe하지 않는다고 지워주라고 한다. // 아직 잘 모르겠음
        $jwt = str_replace('=','',$jwt);

        return $jwt;
    }

    // 사용자 정보 파싱하기
    function tokenParsing($jwt){
        $decoded = JWT::decode($jwt, $this->key, array('HS256'));
        return $decoded;
    }

    // 사용자가 로그아웃 요청 시 - 토큰 제거
    function tokenRemove(){

    }
}





//$decoded = JWT::decode($jwt, $key, array('HS256'));

//$sdf = explode('.',$jwt,3);
//echo $sdf[0].'<br>'.$sdf[1].'<br>'.$sdf[2].'<br><br>';

//$decoded_array = (array)$decoded;
//print_r($decoded_array);

//JWT::$leeway = 60; // $leeway in seconds
//$decoded = JWT::decode($jwt, $key, array('HS256'));

//print_r($decoded);