<?php

defined('BASEPATH') or exit('No direct script access allowed');
include_once 'DTO/Option.php';
include_once 'DTO/PolicticsJwt.php';

class User extends CI_Controller
{
    public $token_str;
    public $option;

	public function __construct()
	{
		parent::__construct();

		// 클라에서 요청한 (GET, POST, PATCH, DELETE) HTTP 메서드 확인
		$this->http_method = $_SERVER["REQUEST_METHOD"];
        $this->option = new Option();

    }

	// request url : {서버 ip}/user
	public function index()
	{
		// 클라이언트가 회원가입 요청함.
		if ($this->http_method == "POST") {
			// postman에서 데이터를 body - x-www-form-urlencoded로 보내는 방법
			$json_data = $this->input->post('user_info', true);
			$json_data = json_decode($json_data, true);

			$error=$this->option->jsonNullCheck($json_data,array('nick_name','id','pw','phone','social_login'));
			if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

			$this->load->model("UserModel");
			// db에 사용자를 추가한다.
			$result = $this->UserModel->putUser($json_data);

			echo $result;
		}
	}
    public function headerData()
    {
        $pmm_jwt = new PolicticsJwt();

        // 클라이언트가 header에 토큰정보를 담아 보낸걸 확인한다.
        $header_data = apache_request_headers();

        // 클라이언트의 토큰으로 인코딩도니 문자열을 해독한다.
        // == jwt_data에는 클라이언트가보낸 토큰의 정보들이 담겨있다.
        if(empty($header_data['Authorization'])){
            return (object)$result=array("idx"=>"토큰실패");
        }else{
            $this->token_str = $header_data['Authorization'];

            $token_data = $pmm_jwt->tokenParsing($this->token_str);
            return $token_data;
        }
    }

	// 닉네임 중복 체크 메서드
	public function getNickNameCheck()
	{
		// 사용자가 닉네임 중복 체크 요청 - nick_name 가지고있다.
		$nick_name = $this->input->get(null, true);

		$error=$this->option->jsonNullCheck($nick_name,array('nick_name'));
		if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

		$this->load->model('UserModel');
		$result = $this->UserModel->getNickCheck($nick_name['nick_name']);
		echo $result;
	}
	// 아이디 중복 체크 메서드
	public function getIdCheck()
	{
		// 사용자가 아이디 중복 체크 요청 - nick_name 가지고있다.
		$id = $this->input->get(null, true);

		$error=$this->option->jsonNullCheck($id,array('id'));
		if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

		$this->load->model('UserModel');
		$result = $this->UserModel->getIdCheck($id['id']);
		echo $result;
	}

	// 로그인 요청 메서드
	public function loginRequest()
	{
		// 사용자가 로그인 요청 - id, pw 정보를 가지고있다.
		$json_data = $this->input->post('login_request', true);
		$json_data = json_decode($json_data, true);

		$error=$this->option->jsonNullCheck($json_data,array('id','pw'));
		if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

		// 사용자가 보낸 id, pw 정보를 db에 있는 id, pw와 비교한다.
		$this->load->model('UserModel');

		// 사용자의 정보를 가져온다.
        $user_info = $this->UserModel->getUserInfo($json_data['id']);

        // 로그인 결과를 반환
        $result = $this->UserModel->getLoginStatus($json_data, $user_info);

        echo $result;
	}

	// 로그인 요청 메서드
	public function logOutRequest()
	{
        // 클라이언트가 header에 토큰정보를 담아 보낸걸 확인한다.
        $header_data = apache_request_headers();

        // 클라이언트가 보낸 토큰 정보가 담겨있다.
        $token_data = $this->headerData();

        // 클라이언트의 토큰으로 인코딩도니 문자열을 해독한다.
        // == jwt_data에는 클라이언트가보낸 토큰의 정보들이 담겨있다.
        if(empty($header_data['Authorization'])){
            $request_data['result'] = "토큰값이 없습니다";
            echo json_encode($request_data);
            return;
        }else{

            // 사용자가 보낸 id, pw 정보를 db에 있는 id, pw와 비교한다.
            $this->load->model('UserModel');

            // 로그인 결과를 반환
            $result = $this->UserModel->logOutRequest($token_data, $this->token_str);
        }
		echo $result;
	}

	// 카카오 로그인, 유튜브 등 키를 반환하는 메서드
	public function key($key){
	    
        include_once "/home/ubuntu/db/Key.php";
        $key_instance = new Key();

        if ($this->http_method == "GET") {
            if($key == "kakao"){
                echo $key_instance->getKakaoKey();
            }
        }
	}

	// 클라이언트가 사용자에 대한 데이터를 요청할때
	// request url : {서버 ip}/user/{data}
	public function requestData($client_data)
	{

		// 사용자 정보를 조회 할 때
		if ($this->http_method == "GET") {
			if ($client_data == 'nick_name_check') {
				// 사용자 닉네임 중복체크
				$this->getNickNameCheck();
			}
			else if ($client_data == 'id_check') {
				// 사용자 닉네임 중복체크
				$this->getIdCheck();
			}
		}
		else if($this->http_method == "POST"){
			// 클라이언트가 로그인 요청
			if ($client_data == "login") {
				$this->loginRequest();
			}
			else if ($client_data == 'kakao_login'){
				$this->kakaoLogin();
			}

			else if ($client_data == 'logout'){
				$this->logOutRequest();
			}
		}
		else if ($this->http_method == "PATCH" or $this->http_method=='patch'){
			if ($client_data=='kakao_sign'){
				$this->kakaoSign();

			}
		}
		else if($this->http_method == "DELETE"){
			print_r("DELETE");
		}

	}

	//회원가입시 핸드폰 인증을 받는과정
	function sms($client_data)
	{
		if ($this->http_method == 'POST') {
			if ($client_data == 'phone') {
				require_once "/home/ubuntu/db/sms/lib/lib.php";
				require_once "/home/ubuntu/db/sms/class/Clientapi.class.php";
                error_reporting(E_ALL ^ E_DEPRECATED);
                $smsobj = new Clientapi();
				$smsobj->init();
				$user_phone = $this->input->post(null, true);

				$error=$this->option->jsonNullCheck($user_phone,array('user_phone'));
				if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

				$user_phone = $user_phone['user_phone'];


				//이미 같은 핸드폰 번호로 회원가입이 되어있는지 확인
				$this->load->model('UserModel');
				$check_code = $this->UserModel->phoneCheck($user_phone);


				// 가입 가능
				if ($check_code == 0) {
					//인증코드는 4글자의 숫자
					$auth_code = sprintf('%04d', rand(0000, 9999));
					$result_code = $smsobj->gd_sms_signal('sms', 'send',
						$user_phone, '01077024277',
						iconv('utf8', 'euckr', '인증번호는 ' . $auth_code . ' 입니다'),
						'', '', '', '', '4');
					//sms보내기 정상적으로 왼료되면 0000코드 반환
					if ($result_code == 0000) {
						//클라이언트에는 인증 코드가 아닌 인증 코드를 sha256으로 변환한 코드를 준다
						$response_code['result'] = hash('sha256', $auth_code);
						echo json_encode($response_code);
					} else {
						$response_code['result'] = '다음에 다시시도 서버에러';
						echo json_encode($response_code);
					}
				} // 가입 불가능
				else {
					$response_code['result'] = "이미 가입한 아이디";
					echo json_encode($response_code);
				}
			}
		}
	}
	//카카오 로그인 체크
	public function kakaoLogin(){
		$uid=$this->input->post(null,true);

		$error=$this->option->jsonNullCheck($uid,array('kakao_uid'));
		if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

		$this->load->model('UserModel');

        // 사용자의 정보를 가져온다.
        $user_info = $this->UserModel->getUserInfo($uid['kakao_uid']);

		$result =  $this->UserModel->kakaoCheck($uid['kakao_uid'], $user_info);
		echo $result;

	}
	//카카오 로그인 동의 후 pmm 가입
	public function kakaoSign(){
		$info=$this->input->input_stream('kakao_user_info');

		$info=json_decode($info,true);

		$error=$this->option->jsonNullCheck($info,array('kakao_uid','nick_name','sex','phone'));
		if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}


		$this->load->model('UserModel');
		echo $this->UserModel->putKakaoUser($info);

	}
}



//-------------------------------------------------------------------------
// DB 테이블 수정 후 사용 안하는 메서드

//-------------------------------------------------------------------------

//    // 이메일 인증 요청 메서드
//    public function emailCertified(){
//    // 사용자가 이메일 인증 요청 - email_id, email_address 정보를 가지고있다.
//    $json_data = $this->input->post('email_certified_request', True);
//    $json_data = json_decode($json_data, True);
//
//    // 해당 이메일로 인증 코드를 보낸다.
//    $this->load->model('UserModel');
//    $result = $this->UserModel->sendEmailCode($json_data);
//    echo($result);
//}

//-------------------------------------------------------------------------

//// 이메일 중복 체크 메서드
//public function getEmailCheck()
//{
//	// 사용자가 이메일 중복 체크 요청 - email_id, email_address 가지고있다.
//	$json_data = $this->input->get('email_request', true);
//	$json_data = json_decode($json_data, true);
//
//	// db에 사용자가 보낸 이메일이 있는지 확인한다. ( 중복체크 과정 ).
//	$this->load->model('UserModel');
//	$result = $this->UserModel->getEmailCheck($json_data);
//	echo($result);
//}