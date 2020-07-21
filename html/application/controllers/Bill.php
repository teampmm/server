<?php

defined('BASEPATH') or exit('No direct script access allowed');

include 'DTO/Option.php';
include 'DTO/PolicticsJwt.php';

class Bill extends CI_Controller
{
	public $http_method;
    public $token_str;

	public function __construct()
	{
		parent::__construct();

		// 클라에서 요청한 (GET, POST, PATCH, DELETE) HTTP 메서드 확인
		$this->http_method = $_SERVER["REQUEST_METHOD"];
        $this->option = new Option();
    }

	public function index()
	{
		$result = $this->page(null);
		echo($result);
	}

	public function headerData(){
        // Jwt 객체 생성 - PoliticsJwt 클래스는 3가지 기능을 한다.
        // 1. 토큰을 생성(발급)하는 기능 - createToken() - 로그인 시에 토큰이 발급된다.
        // 2. 토큰을 파싱하는 기능 - tokenParsing()
        // controller - Politician 클래스 에서는 토큰을 파싱하는 기능만 사용한다.
        $pmm_jwt = new PolicticsJwt();

        // 클라이언트의 Header 정보를 확인한다.
        $header_data = apache_request_headers();

        // 클라이언트에서 보낸 토큰이 있는지 없는지 확인한다.
        // 클라이언트는 key - value 형태로 인증키를 보낸다. key 값은 Authorization 이다.
        // 클라이언트에서 보낸 토큰이 없는 경우 -> 로그인 안한 경우이다.
        if(empty($header_data['Authorization'])){
            return (object)$result=array("idx"=>"토큰실패");
        }
        // 클라이언트에서 보낸 토큰이 있는 경우 -> 로그인 한 경우이다.
        else{
            // 토큰 문자열을 token_str에 저장한다.
            // 토큰 문자열을 저장하는 이유는 모든 API요청시에 클라이언트가 보낸 토큰이 만료되었는지 확인하기 때문이다.
            $this->token_str = $header_data['Authorization'];

            // 클라이언트가 보낸 토큰을 해독한다.
            // token_data에는 jwt의 payload정보가 들어가는데,
            // payload 정보에는
            // 사용자의 idx, id, nick_name, token issue time(토큰발급시간), token expiration time(토큰만료시간) 정보가 포함된다.
            // 해당 데이터를 사용 할때는 $token_data->idx , $token_data->id 와 같이 사용하면된다.
            $token_data = $pmm_jwt->tokenParsing($this->token_str);
            return $token_data;
        }
    }

	public function requestData($data){
        if ($this->http_method=="GET") {

	        $token_data=$this->headerData();
            //법안 모아보기
            if ($data == 'card') {
                $input = $this->input->get(null, true);
                $error = $this->option->dataNullCheck($input, array('page'));
                if ($error != null) {
                    header("HTTP/1.1 400");
                    echo $error;
                    return;
                }
//                var_dump($token_data);
//                return;
                $this->getBillCard($input,$token_data);

            } //법안 상세정보
            else if ($data == 'bill_info') {
                $token_data=$this->headerData();
                $input = $this->input->get(null, true);
                $error = $this->option->dataNullCheck($input, array('bill_idx'));
                if ($error != null) {
                    header("HTTP/1.1 400 ");
                    echo $error;
                    return;
                }
                $this->billInfo($input,$token_data);
            }
            else if ($data=='get_bill_evaluation'){
                $token_data=$this->headerData();
                $input = $this->input->get(null, true);
                $error = $this->option->dataNullCheck($input, array('bill_idx'));
                if ($error != null) {
                    header("HTTP/1.1 400 ");
                    echo $error;
                    return;
                }
                $this->getBillEvaluation($input,$token_data);
            }
            else if ($data=='process'){
//                $token_data=$this->headerData();
                $input = $this->input->get(null, true);
                $error = $this->option->dataNullCheck($input, array('bill_idx','status'));
                if ($error != null) {
                    header("HTTP/1.1 400 ");
                    echo $error;
                    return;
                }
                $this->getBillProcessDetail($input);
            }
            else if ($data=='subscribe'){

                if ($token_data->idx != "토큰실패"){
                    // 토큰이 유효한지 검사
                    $this->load->model('OptionModel');
                    $token_result = $this->OptionModel->blackListTokenCheck($this->token_str);
                    if($token_result != "유효한 토큰") {
                        $request_data['result'] = $token_result;
                        echo json_encode($request_data);
                        return;
                    }
                    else{
                        $this->getSubscribe($token_data);
                    }

                }
                else{
                    $response_data = array();
                    $response_data['result'] = "로그인 필요";
                    echo json_encode($response_data, JSON_UNESCAPED_UNICODE);
                    return;
                }
        }

            //커뮤니티 기능 일단 비활성화
//            //법안 자체의 좋아요 싫어요 수
//            else if ($data == 'bill_user_evaluation') {
//
//                // 클라이언트가 보낸 토큰 정보가 담겨있다.
//                $token_data = $this->headerData();
//
//                $opposition_comment = $this->input->get(null, true);
//
//                $error=$this->option->dataNullCheck($opposition_comment,array('bill_idx'));
//                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}
//
//
//                $result = $this->billUserEvaluation($opposition_comment['bill_idx'], $token_data);
//                echo $result;
//            }
//            //법안의 찬성 댓글 and 대댓글 갯수
//            //페이징
//            else if ($data == 'bill_agreement_comment') {
//
//                // 클라이언트가 보낸 토큰 정보가 담겨있다.
//                $token_data = $this->headerData();
//
//                $agreement_comment = $this->input->get(null, true);
//
//                $error=dataNullCheck($agreement_comment,array('bill_idx','comment_page'));
//                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}
//
//
//                $result = $this->billComment($agreement_comment['bill_idx'],$agreement_comment['comment_page'], 'agreement',$token_data);
//                echo $result;
//
//            }
//            //법안의 반대 댓글 and 대댓글 갯수
//            //페이징
//            else if ($data == 'bill_opposition_comment') {
//
//                // 클라이언트가 보낸 토큰 정보가 담겨있다.
//                $token_data = $this->headerData();
//
//                $opposition_comment = $this->input->get(null, true);
//
//                $error=dataNullCheck($opposition_comment,array('bill_idx','comment_page'));
//                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}
//
//                $result = $this->billComment($opposition_comment['bill_idx'], $opposition_comment['comment_page'], 'opposition',$token_data);
//                echo $result;
//            }
//
//            //대댓글 보기 클릭
//            //댓글 idx , 대댓글 페이지
//            //대댓글의 정보 and 페이징
//            else if ($data == 'bill_sub_comment') {
//
//                // 클라이언트가 보낸 토큰 정보가 담겨있다.
//                $token_data = $this->headerData();
//
//                $comment_idx = $this->input->get(null, true);
//
//                $error=dataNullCheck($comment_idx,array('comment_idx','sub_comment_page'));
//                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}
//
//                $result = $this->billSubComment($comment_idx['comment_idx'], $comment_idx['sub_comment_page'], $token_data);
//                echo $result;
//            }
//        }else if ($this->http_method=="POST"){
//
//	        //사용자가 법안에 대해 댓글 쓰기
//	        if($data=='bill_comment_write'){
//
//                // 클라이언트가 보낸 토큰 정보가 담겨있다.
//                $token_data = $this->headerData();
//
//	            $input=$this->input->post("comment_write",true);
//	            $input_json=json_decode($input,true);
//
//                $error=dataNullCheck($input_json,array('bill_idx','content','status'));
//                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}
//
//                $result=$this->billCommentWrite($input_json['bill_idx'],$input_json['content'],$input_json['status'],$token_data);
//	           echo $result;
//            }
//	        //사용자가 댓글에 대한 답글 쓰기
//	        else if ($data=='bill_sub_comment_write'){
//
//                // 클라이언트가 보낸 토큰 정보가 담겨있다.
//                $token_data = $this->headerData();
//
//	            $input=$this->input->post('sub_comment_write',true);
//	            $input_json=json_decode($input,true);
//
//                //댓글에 대한 답글일경우
//                if(empty($input_json['parent_user_idx'])){
//                    $error=dataNullCheck($input_json,array('comment_idx','content'));
//                    if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}
//
//                    $result= $this->subCommentWrite($input_json['comment_idx'],$input_json['content'],null,$token_data);
//                   echo $result;
//                }
//                //답글에 대한 답글 일 경우
//                else{
//                    $error=dataNullCheck($input_json,array('comment_idx','content','parent_user_idx'));
//                    if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}
//
//                    $result= $this->subCommentWrite($input_json['comment_idx'],$input_json['content'],$input_json['parent_user_idx'],$token_data);
//                    echo $result;
//
//                }
//            }
//	        //사용자가 댓글 , 대댓글에 대한 좋아요 싫어요 클릭
//	        else if ($data=='comment_evaluation_write'){
//
//                // 클라이언트가 보낸 토큰 정보가 담겨있다.
//                $token_data = $this->headerData();
//
//
//	            $input=$this->input->post("evaluation_write",true);
//	            $input_json=json_decode($input,true);
//
//                //댓글에 대해 좋아요 누른경우
//                if (empty($input_json['sub_comment_idx'])){
//                    $error=dataNullCheck($input_json,array('status','comment_idx'));
//                    if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}
//                    $result=$this->commentEvaluationClick('comment_idx',$input_json['comment_idx'],$input_json['status'],$token_data);
//                }
//                //대댓글에 대해 좋아요 누른경우
//                else{
//                    $error=dataNullCheck($input_json,array('status','sub_comment_idx'));
//                    if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}
//                    $result=$this->commentEvaluationClick('sub_comment_idx',$input_json['sub_comment_idx'],$input_json['status'],$token_data);
//                }
//
//                echo $result;
//            }
        }
        else if ($this->http_method=="POST"){
                //북마크 추가,삭제 (북마크 쓰기)
                if ($data =='put_bookmark'){
                    $token_data=$this->headerData();
                    if($token_data->idx=="토큰실패"){$result_json=array();$result_json['result']='로그인 필요';header("HTTP/1.1 401 ");echo json_encode($result_json); return;}
                    else{
                        $input=$this->input->post(null,null);
                        $error = $this->option->dataNullCheck($input, array('bill_idx'));
                        if ($error != null) {
                            header("HTTP/1.1 400 ");
                            echo $error;
                            return;
                        }
                        $this->putBookmark($input,$token_data->idx);
                        return;
                    }
                }else if ($data=='put_bill_evaluation'){
                    $token_data=$this->headerData();
                    if($token_data->idx=="토큰실패"){$result_json=array();$result_json['result']='로그인 필요';header("HTTP/1.1 401 ");echo json_encode($result_json); return;}
                    else{
                        $input=$this->input->post('evaluation_write',true);
                        $input=json_decode($input,true);
                        $error = $this->option->dataNullCheck($input, array('bill_idx','status'));
                        if ($error != null) {
                            header("HTTP/1.1 400 ");
                            echo $error;
                            return;
                        }
                        $this->putBillEvaluation($input,$token_data->idx);
                        return;
                    }
                }
            }
        }
    //북마크 추가,삭제 (북마크 쓰기)
    public function putBookmark($input,$user_idx){
	    $this->load->model("BillModel");
	    $result=$this->BillModel->putBookmark($input['bill_idx'],$user_idx);
	    echo$result;
}

    //사용자가 법안에 대해서 좋아요 혹은 싫어요 클릭
    public function putBillEvaluation($input,$user_idx){
        $this->load->model("BillModel");
        $result=$this->BillModel->putBillEvaluation($input['bill_idx'],$user_idx,$input['status']);
        echo$result;
    }
	//법안 모아보기
	public function getBillCard($index,$token_data)
	{

		$this->load->model('BillModel');
		if(empty($index['generation'])){$index['generation']=null;}
        if(empty($index['status'])){$index['status']=null;}
		$result = $this->BillModel->getBillCard($index['page'],$token_data,$index['generation'],$index['status']);
		echo $result;
	}

	//법안 상세보기
	public function billInfo($index,$token_data)
	{
		$this->load->model('BillModel');
		$result = $this->BillModel->billInfo($index['bill_idx'],$token_data);
		echo  $result;
	}

	//법안 좋아요 싫어요 갯수 , 해당 사용자의 클릭 상태 ? (현재 사용자는 어떤것을 클릭 했는지)
	public function getBillEvaluation($input,$token_data){
        $this->load->model('BillModel');
		$result = $this->BillModel->getBillEvaluation($token_data,$input['bill_idx']);
		echo  $result;
    }

    // 법안의 상세 과정에 대한 데이터를 가져오는 메서드 : 접수, 공포 등에 대한 데이터
    public function getBillProcessDetail($input){
        $this->load->model('BillModel');
        $result = $this->BillModel->getBillProcessDetail($input['bill_idx'],$input['status']);
        echo  $result;
    }

    // 구독한 정치인 조회
    public function getSubscribe($token_data){
        $this->load->model('BillModel');
        $result = $this->BillModel->getSubscribe($token_data);
        echo $result;
    }
//	//법안에 대한 좋아요 싫어요
//	public function billUserEvaluation($index, $token_data)
//	{
//		$this->load->model('BillModel');
//		$result = $this->BillModel->billUserStatus($index, $token_data);
//		return $result;
//	}
//
//	//법안에 대한 댓글가져오기
//    public function billComment($index,$comment_page,$status,$token_data)
//    {
//        $this->load->model('BillModel');
//        $result=$this->BillModel->billCommentList($index,$comment_page,$status,$token_data);
//        echo($result);
//    }
//
//    //댓글에 대한 대댓글 가져오기
//    public function billSubComment($index,$sub_comment_page, $token_data){
//	    $this->load->model('BillModel');
//	    $result=$this->BillModel->billSubCommentList($index,$sub_comment_page, $token_data);
//	    return $result;
//    }
//    //법안 댓글 달기
//    //좋아요 댓글인지 싫어요 댓글인지 표기
//    public function billCommentWrite($bill_idx,$content,$status,$token_data){
//        $this->load->model("BillModel");
//        $result=$this->BillModel->billCommentWrite($bill_idx,$content,$status,$token_data);
//        return $result;
//    }
//    //법안에 대해 좋아요 싫어요 클릭
//    public function billEvaluationClick($bill_idx,$status,$token_data){
//        $this->load->model('BillModel');
//        $result=$this->BillModel->billEvaluationWrite($bill_idx,$status,$token_data);
//        return$result;
//    }
//    //댓글에 대해 답글달기
//    public function subCommentWrite($comment_idx,$content,$parent_idx,$token_data){
//	    $this->load->model('BillModel');
//        $result=$this->BillModel->billSubCommmentWrite($comment_idx,$content,$parent_idx,$token_data);
//	    return$result;
//    }
//    //댓글, 대댓글에 대해 좋아요 싫어요 클릭
//    public function commentEvaluationClick($comment_check,$comment_idx,$status,$token_data){
//        $this->load->model('BillModel');
//        $result=$this->BillModel->commentEvaluationWrite($comment_check,$comment_idx,$status,$token_data);
//        return$result;
//    }
}