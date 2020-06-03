<?php
defined('BASEPATH') or exit('No direct script access allowed');
include 'DTO/Option.php';
include 'DTO/PolicticsJwt.php';

class Party extends CI_Controller
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

    // 정당 정보조회
    public function getPartyInfo(){
        $key = $this->input->get(null, True);

        $error=$this->option->jsonNullCheck($key,array('name'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        $this->load->model('PartyModel');
        $result = $this->PartyModel->getInfo($key['name']);

        echo $result;
    }

    // 클라이언트가 사용자에 대한 데이터를 요청할때
    // request url : {서버 ip}/politician/{data}
    public function requestData($client_data)
    {

        if ($this->http_method == "GET") {

            if($client_data == "info"){
                $this->getPartyInfo();
            }

        } else if ($this->http_method == "POST") {


        }else if ($this->http_method == "PATCH" or $this->http_method=='patch'){

        } else if ($this->http_method == "DELETE") {

        }
    }
}
