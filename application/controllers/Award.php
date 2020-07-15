<?php
defined('BASEPATH') or exit('No direct script access allowed');
include 'DTO/Option.php';
include 'DTO/PolicticsJwt.php';

class Award extends CI_Controller
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

    // 정당 정보조회
    public function getAllKingInfo(){
        $this->load->model('AwardModel');
        $result = $this->AwardModel->getAllKingInfo();
        echo $result;
    }


    // 클라이언트가 사용자에 대한 데이터를 요청할때
    // request url : {서버 ip}/politician/{data}
    public function requestData($client_data)
    {

        if ($this->http_method == "GET") {

            if ($client_data == "all") {
                $this->getAllKingInfo();
            }
        }
    }
}
