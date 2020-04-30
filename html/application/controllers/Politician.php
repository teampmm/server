<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Politician extends CI_Controller
{
    public $http_method;

    public function __construct()
    {
        parent::__construct();

        // 클라에서 요청한 (GET, POST, PATCH, DELETE) HTTP 메서드 확인
        $this->http_method = $_SERVER["REQUEST_METHOD"];

        $this->load->model('Politician_Model');
    }

    // request url : {서버 ip}/politician
    public function index()
    {

    }

    public function headerData($jwtToken)
    {

    }

    // 정치인 카드 모아 보기 정보 가져오기
    public function getPoliticianCard(){
        // 정치인 카드 정보 요청 - 받았던 카드의 인덱스 정보를 가지고 온다.
        $json_data = $this->input->get('card_request', True);
        $json_data = json_decode($json_data, True);

        // db에 사용자가 보낸 이메일이 있는지 확인한다. ( 중복체크 과정 ).
        $this->load->model('Politician_Model');
        $result = $this->Politician_Model->getPoliticianCard($json_data);
        print_r($result);
    }

    // 정치인 기본 정보 가져오기
    public function getBaseInfo(){
        // 정치인 기본정보 요청 - 정치인의 이름을 가지고 들어옴. ( kr_name )
        $json_data = $this->input->get('base_info_request', True);
        $json_data = json_decode($json_data, True);

        // db에 사용자가 보낸 이메일이 있는지 확인한다. ( 중복체크 과정 ).
        $this->load->model('Politician_Model');
        $result = $this->Politician_Model->getBaseInfo($json_data);
        print_r($result);
    }

    // 정치인 관련 뉴스 가져오기
    public function getNews(){
        // 정치인 관련 뉴스 정보 요청
        $json_data = $this->input->get('news_request', True);
        $json_data = json_decode($json_data, True);

        // db에 사용자가 보낸 이메일이 있는지 확인한다. ( 중복체크 과정 ).
        $this->load->model('Politician_Model');
        $result = $this->Politician_Model->getNews($json_data);
        print_r($result);
    }

    // 정치인 공약정보 가져오기
    public function getPledgeInfo(){
        // 정치인 공약 정보 요청
        $json_data = $this->input->get('pledge_request', True);
        $json_data = json_decode($json_data, True);

        // db에 사용자가 보낸 이메일이 있는지 확인한다. ( 중복체크 과정 ).
        $this->load->model('Politician_Model');
        $result = $this->Politician_Model->getPledgeInfo($json_data);
        print_r($result);
    }

    // 정치인 상세정보 가져오기
    public function getDetailInfo(){
        // 정치인 상세 정보 요청
        $json_data = $this->input->get('detail_info_request', True);
        $json_data = json_decode($json_data, True);

        // db에 사용자가 보낸 이메일이 있는지 확인한다. ( 중복체크 과정 ).
        $this->load->model('Politician_Model');
        $result = $this->Politician_Model->getDetailInfo($json_data);
        print_r($result);
    }

    // 클라이언트가 사용자에 대한 데이터를 요청할때
    // request url : {서버 ip}/politician/{data}
    public function requestData($client_data)
    {

        if ($this->http_method == "GET") {

            // 클라이언트가 정치인 카드 모아보기 정보를 요청함.
            if ($client_data == "card"){
                $this->getPoliticianCard();
            }

            // 클라이언트가 정치인 기본정보를 요청함.
            else if($client_data == "info"){
                $this->getBaseInfo();
            }

            // 클라이언트가 정치인 관련 뉴스 정보를 요청함.
            else if($client_data == "news"){
                $this->getNews();
            }

            // 클라이언트가 정치인 공약 정보 요청
            else if($client_data == "pledge_info"){
                $this->getPledgeInfo();
            }

            // 클라이언트가 정치인 상세 정보를 요청
            else if($client_data == "detail_info"){
                $this->getDetailInfo();
            }

        } else if ($this->http_method == "POST") {

        } else if ($this->http_method == "PATCH") {

        } else if ($this->http_method == "DELETE") {

        }
    }
}
