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

        $error=$this->option->dataNullCheck($key,array('idx'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        $this->load->model('PartyModel');
        $result = $this->PartyModel->getInfo($key['idx']);

        echo $result;
    }

    // 정당 카드 정보 반환
    public function getPartyCard(){
        $key = $this->input->get(null, True);

        $error=$this->option->dataNullCheck($key,array('date'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        // 클라이언트가 보낸 날짜의 형식을
        $date_check_result = $this->date_check($key['date']);
        if($date_check_result == false){
            header("HTTP/1.1 400 ");
            echo "날짜형식이 올바르지 않습니다";
            return;
        }

        $this->load->model('PartyModel');
        $result = $this->PartyModel->getPartyCard($key['date']);

        echo $result;
    }

    // 날짜 형식 검사하는 메서드
    function date_check($date) {

        $date = (string)$date;

        // 날짜 형식은 20160405 와 같이 8자리이다.
        // 8자리가 아니면 날짜 형식에 맞지 않는걸로 함.
        if (strlen($date) != 8){
            return false;
        }

        // ex ) 20160413
        // $YY -> 2016
        // $MM -> 04
        // $DD -> 13
        $YY = substr($date,0,4);
        $MM = substr($date,4,2);
        $DD = substr($date,6,2);

        // 2016-04-13
        $date = $YY.'-'.$MM.'-'.$DD;

        // 날짜 형식에 맞는경우
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)) {
            return true;
        }

        // 날짜 형식에 맞지 않는 경우
        else {
            return false;
        }
    }

    // 클라이언트가 사용자에 대한 데이터를 요청할때
    // request url : {서버 ip}/politician/{data}
    public function requestData($client_data)
    {

        if ($this->http_method == "GET") {

            if($client_data == "info"){
                $this->getPartyInfo();
            }
            else if ($client_data == "card"){
                $this->getPartyCard();
            }

        } else if ($this->http_method == "POST") {


        }else if ($this->http_method == "PATCH" or $this->http_method=='patch'){

        } else if ($this->http_method == "DELETE") {

        }
    }
}
