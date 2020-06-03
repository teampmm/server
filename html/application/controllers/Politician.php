<?php
defined('BASEPATH') or exit('No direct script access allowed');
include 'DTO/Option.php';
include 'DTO/PolicticsJwt.php';

class Politician extends CI_Controller
{
    public $token_str;
    public $option;

    public function __construct()
    {
        parent::__construct();

        // 클라에서 요청한 (GET, POST, PATCH, DELETE) HTTP 메서드 확인
        $this->http_method = $_SERVER["REQUEST_METHOD"];
        $this->option = new Option();

//        $this->load->model('PoliticianModel');
    }

    // request url : {서버 ip}/politician
    public function index()
    {

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

    // 정치인 카드 모아 보기 정보 가져오기
    public function getPoliticianCard(){

        // 클라이언트가 보낸 토큰 정보가 담겨있다.
        $token_data = $this->headerData();

        // 토큰이 유효한지 검사
        $this->load->model('OptionModel');
        $token_result = $this->OptionModel->blackListTokenCheck($this->token_str);
        if($token_result != "유효한 토큰") {
            $request_data['result'] = $token_result;
            echo json_encode($request_data);
            return;
        }

        // 정치인 카드 정보 요청 - 받았던 카드의 인덱스 정보를 가지고 온다.
        $request_data = $this->input->get(null, True);

        $error=$this->option->jsonNullCheck($request_data,array('page','random_card_idx'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        // 클라이언트가 요청한 페이지
        $request_page = $request_data['page'];

        // 클라이언트가 요청한 덱 번호
        $random_card_idx = $request_data['random_card_idx'];

        // 클라이언트가 요청한 카드의 갯수
        $card_num = $request_data['card_num'];
        if($card_num<1){header("HTTP/1.1 400 "); echo "요청할 카드의 개수가 1이상이어야 합니다";return;}

        // 클라이언트가 요청한 국회의원 대수
        $generation = $request_data['generation'];

        $this->load->model('PoliticianModel');
        $result = $this->PoliticianModel->getPoliticianCard($request_page, $random_card_idx, $token_data, $card_num, $generation);
        echo $result;
    }

    // 정치인 기본 정보 가져오기
    public function getInfo(){
        // 정치인 기본정보 요청 - 정치인의 이름을 가지고 들어옴. ( kr_name )
        $politician_idx = $this->input->get(null, True);

        $error=$this->option->jsonNullCheck($politician_idx,array('politician_idx'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        $this->load->model('PoliticianModel');
        $result = $this->PoliticianModel->getInfo($politician_idx['politician_idx']);
        echo $result;
    }

    // 정치인 관련 뉴스 가져오기
    public function getNews(){
        // 정치인 관련 뉴스 정보 요청
        $politician_idx = $this->input->get(null, True);

        $error=$this->option->jsonNullCheck($politician_idx,array('politician_idx'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        $this->load->model('PoliticianModel');
        $result = $this->PoliticianModel->getNews($politician_idx['politician_idx']);
        echo $result;
    }

    // 정치인 공약정보 가져오기
    public function getPledgeInfo(){
        // 정치인 공약 정보 요청
        $politician_data = $this->input->get(null, True);

        $error=$this->option->jsonNullCheck($politician_data,array('politician_idx'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        $this->load->model('PoliticianModel');
        $result = $this->PoliticianModel->getPledgeInfo($politician_data['politician_idx'], $politician_data['generation']);
        echo $result;
    }

    // 정치인 북마크 수정하기
    public function postBookmarkModify(){

        // 클라이언트가 보낸 토큰 정보가 담겨있다.
        $token_data = $this->headerData();

        // 토큰이 유효한지 검사
        $this->load->model('OptionModel');
        $token_result = $this->OptionModel->blackListTokenCheck($this->token_str);
        if($token_result != "유효한 토큰") {
            $request_data['result'] = $token_result;
            echo json_encode($request_data);
            return;
        }
        $politician_idx = $this->input->post('politician_idx');

        // 클라가 정치인 인덱스값을 서버어 안보냈을때
        if ( $politician_idx == null) return "invaild_data_politician_idx";

        // 클라가 요청한 정처인 인덱스가 1이하인 경우엔 정치인 데이터가 없음
        if($politician_idx < 1) return "invaild_data_politician_idx";

        $this->load->model('PoliticianModel');
        $result = $this->PoliticianModel->postBookmarkModify($politician_idx, $token_data);
        echo $result;
    }
    // 정치인 북마크 조회하기
    public function getBookmark(){

        // 클라이언트가 보낸 토큰 정보가 담겨있다.
        $token_data = $this->headerData();

        // 토큰이 유효한지 검사
        $this->load->model('OptionModel');
        $token_result = $this->OptionModel->blackListTokenCheck($this->token_str);
        if($token_result != "유효한 토큰") {
            $request_data['result'] = $token_result;
            echo json_encode($request_data);
            return;
        }
        $politician_idx = $this->input->get(null, True);

        $error=$this->option->jsonNullCheck($politician_idx,array('politician_idx'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        $this->load->model('PoliticianModel');
        $result = $this->PoliticianModel->getBookmark($politician_idx['politician_idx'], $token_data);
        echo $result;
    }

    // 정치인 좋아요 싫어요 수정하기
    public function postUserEvaluation(){

        // 클라이언트가 보낸 토큰 정보가 담겨있다.
        $token_data = $this->headerData();

        // 토큰이 유효한지 검사
        $this->load->model('OptionModel');
        $token_result = $this->OptionModel->blackListTokenCheck($this->token_str);
        if($token_result != "유효한 토큰") {
            $request_data['result'] = $token_result;
            echo json_encode($request_data);
            return;
        }
        $input=$this->input->post("evaluation_write",true);
        $input_json=json_decode($input,true);

        $error=$this->option->jsonNullCheck($input_json,array('politician_idx','status'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        $this->load->model('PoliticianModel');
        $result = $this->PoliticianModel->postUserEvaluation($input_json['politician_idx'], $input_json['status'], $token_data);
        echo $result;
    }

    // 정치인 좋아요 싫어요 정보 조회
    public function getUserEvaluation(){

        // 클라이언트가 보낸 토큰 정보가 담겨있다.
        $token_data = $this->headerData();

        // 토큰이 유효한지 검사
        $this->load->model('OptionModel');
        $token_result = $this->OptionModel->blackListTokenCheck($this->token_str);
        if($token_result != "유효한 토큰") {
            $request_data['result'] = $token_result;
            header("HTTP/1.1 401");
            echo json_encode($request_data);
            return;
        }
        $politician_idx = $this->input->get(null, True);

        $error=$this->option->jsonNullCheck($politician_idx,array('politician_idx'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        $this->load->model('PoliticianModel');
        $result = $this->PoliticianModel->getUserEvaluation($politician_idx['politician_idx'], $token_data);
        echo $result;
    }

    public function postMakeRandomCard(){
        // db에 사용자가 보낸 이메일이 있는지 확인한다. ( 중복체크 과정 ).
        $this->load->model('PoliticianModel');
        $result = $this->PoliticianModel->postMakeRandomCard();
        echo $result;
    }

    public function getPDF(){
        $politician_idx = $this->input->get(null, True);

        $error=$this->option->jsonNullCheck($politician_idx,array('politician_idx'));
        if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

        $this->load->model('PoliticianModel');
        $result = $this->PoliticianModel->getPDF($politician_idx['politician_idx']);
        echo $result;
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
                $this->getInfo();
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

            // 정치인 좋아요 싫어요 정보 조회
            else if($client_data == "politician_user_evaluation"){
                $this->getUserEvaluation();
            }
            // 정치인 북마크 정보 조회
            else if($client_data == "bookmark"){
                $this->getBookmark();
            }
            // 정치인 pdf 자료 조회
            else if($client_data == "pdf"){
                $this->getPDF();
            }

        } else if ($this->http_method == "POST") {
            // 북마크 선택 / 해제
            if($client_data == "bookmark"){
                $this->postBookmarkModify();
            }
            // 정치인 좋아요 / 싫어요
            else if($client_data == "politician_user_evaluation"){
                $this->postUserEvaluation();
            }
            // 랜덤카드 만들기
            else if($client_data == "make_card"){
                $this->postMakeRandomCard();
            }

        }else if ($this->http_method == "PATCH" or $this->http_method=='patch'){

        } else if ($this->http_method == "DELETE") {

        }
    }
}

// 사용안함
// 정치인 상세정보 가져오기
//public function getDetailInfo(){
//	// 정치인 상세 정보 요청
//	$json_data = $this->input->get('detail_info_request', True);
//	$json_data = json_decode($json_data, True);
//
//	// db에 사용자가 보낸 이메일이 있는지 확인한다. ( 중복체크 과정 ).
//	$this->load->model('PoliticianModel');
//	$result = $this->PoliticianModel->getDetailInfo($json_data);
//	print_r($result);
//}