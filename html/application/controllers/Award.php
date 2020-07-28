<?php
defined('BASEPATH') or exit('No direct script access allowed');
include 'DTO/Option.php';
include 'DTO/PolicticsJwt.php';

class Award extends CI_Controller
{
    public $token_str;
    public $token_data;
    public $option;

    public function __construct()
    {
        parent::__construct();
        // 클라에서 요청한 (GET, POST, PATCH, DELETE) HTTP 메서드 확인
        $this->http_method = $_SERVER["REQUEST_METHOD"];
        $this->option = new Option();
    }

    // 왕 정보 조회
    public function getKingInfo(){

        // 정치인 카드 정보 요청 - 받았던 카드의 인덱스 정보를 가지고 온다.
        $request_data = $this->input->get(null, True);

        $error=$this->option->dataNullCheck($request_data,array('king','card_num', 'page'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        // 클라이언트가 요청한 페이지
        $page = $request_data['page'];
        $king = $request_data['king'];
        $card_num = $request_data['card_num'];

        if((int)$card_num < 3){header("HTTP/1.1 400 "); echo "card_num 값은 3이상 입력;";return;}

        $this->actionLogStack($king." api 조회 : ".$page."페이지");

        $this->load->model('AwardModel');
        $result = $this->AwardModel->getKingInfo($page, $card_num, $king, $this->token_data);
        echo $result;
    }


    public function requestData($client_data)
    {
        if ($this->http_method == "GET") {

            // 왕 시리즈 api 조회
            if ($client_data == "king") {
                $this->getKingInfo();
            }
        }
    }

    // 사용자의 행동 로그 쌓기
    public function actionLogStack($action)
    {
        $pmm_jwt = new PolicticsJwt();

        // 회원
        if(!empty($_COOKIE['jwt'])){
            $this->token_str = $_COOKIE['jwt'];
            $this->token_data = $pmm_jwt->tokenParsing($this->token_str);

            $log_data = array(
                "user_idx" => $this->token_data->idx,
                "user_action" => $action
            );
            $visit_log_data = $this->option->actionLogData($log_data);
            if ($visit_log_data == "favicon.ico") return;

            $this->load->model('OptionModel');
            $this->OptionModel->actionLogInsert($visit_log_data);
        }
        // 비회원
        else{
            $log_data = array(
                "user_idx" => 0,
                "user_action" => $action
            );
            $visit_log_data = $this->option->actionLogData($log_data);
            if ($visit_log_data == "favicon.ico") return;

            $this->load->model('OptionModel');
            $this->OptionModel->actionLogInsert($visit_log_data);
        }
    }
}
