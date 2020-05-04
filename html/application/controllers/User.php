<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
	public $http_method;

	public function __construct()
	{
		parent::__construct();

		// 클라에서 요청한 (GET, POST, PATCH, DELETE) HTTP 메서드 확인
		$this->http_method = $_SERVER["REQUEST_METHOD"];
//        return $this->http_method;
	}

	// request url : {서버 ip}/user
	public function index()
	{
		// 클라이언트가 회원가입 요청함.
		if ($this->http_method == "POST") {
			// postman에서 데이터를 body - x-www-form-urlencoded로 보내는 방법
			$json_data = $this->input->post('user_info', true);
			$json_data = json_decode($json_data, true);

			$this->load->model("UserModel");
			// db에 사용자를 추가한다.
			$result = $this->UserModel->putUser($json_data);

			echo $result;
		}
	}

	public function headerData($jwtToken)
	{
	}

	// 닉네임 중복 체크 메서드
	public function getNickNameCheck()
	{
		// 사용자가 닉네임 중복 체크 요청 - nick_name 가지고있다.
		$json_data = $this->input->get('nick_name_request', true);
		$json_data = json_decode($json_data, true);
		$this->load->model('UserModel');
		$result = $this->UserModel->getNickCheck($json_data);
		echo $result;
	}

	// 로그인 요청 메서드
	public function loginRequest()
	{
		// 사용자가 로그인 요청 - id, pw 정보를 가지고있다.
		$json_data = $this->input->post('login_request', true);
		$json_data = json_decode($json_data, true);

		// 사용자가 보낸 id, pw 정보를 db에 있는 id, pw와 비교한다.
		$this->load->model('UserModel');
		$result = $this->UserModel->getLoginStatus($json_data);
		echo $result;
	}

	// 클라이언트가 사용자에 대한 데이터를 요청할때
	// request url : {서버 ip}/user/{data}
	public function requestData($client_data)
	{
		// 사용자 정보를 조회 할 때
		if ($this->http_method == "GET") {
			if ($client_data == 'nick_name') {
				// 사용자 닉네임 중복체크
				$this->getNickNameCheck();
			}
		}
		else if($this->http_method == "POST"){
			// 클라이언트가 로그인 요청
			if ($client_data == "login") {
				$this->loginRequest();
			}
		}
		else if ($this->http_method == "PATCH"){
			print_r("PATCH");
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
				$smsobj = new Clientapi();
				$smsobj->init();
				$user_phone = $this->input->post('user_phone', true);


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