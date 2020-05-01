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

			// 클라이언트가 보낸 정보에서 name 값을 찾는 코드
			// print_r($json_data['name']);

			$this->load->model("User_Model");
			// db에 사용자를 추가한다.
			$result = $this->User_Model->putUser($json_data);

			print_r($result);
			/** postman에서 데이터를 body - raw로 보내는 방법
			 * $this->output->set_content_type('application/json');
			 * $json = file_get_contents("php://input");
			 * $json = stripslashes($json);
			 * $json = json_decode($json);
			 * print_r(var_dump($json->name));
			 */
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
		$this->load->model('User_Model');
		$result = $this->User_Model->getNickCheck($json_data);
		print_r($result);
	}

	// 로그인 요청 메서드
	public function loginRequest()
	{
		// 사용자가 로그인 요청 - id, pw 정보를 가지고있다.
		$json_data = $this->input->post('login_request', true);
		$json_data = json_decode($json_data, true);

		// 사용자가 보낸 id, pw 정보를 db에 있는 id, pw와 비교한다.
		$this->load->model('User_Model');
		$result = $this->User_Model->getLoginStatus($json_data);
		print_r($result);
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
		} else {
			if ($this->http_method == "POST") {
				// 클라이언트가 로그인 요청
				if ($client_data == "login") {
					$this->loginRequest();
				}
			} else {
				if ($this->http_method == "PATCH") {
				} else {
					if ($this->http_method == "DELETE") {
					}
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
//    $this->load->model('User_Model');
//    $result = $this->User_Model->sendEmailCode($json_data);
//    print_r($result);
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
//	$this->load->model('User_Model');
//	$result = $this->User_Model->getEmailCheck($json_data);
//	print_r($result);
//}