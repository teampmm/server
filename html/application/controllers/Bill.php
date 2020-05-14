<?php

defined('BASEPATH') or exit('No direct script access allowed');

include 'DTO/Option.php';
include 'DTO/PolicticsJwt.php';

class Bill extends CI_Controller
{
	public $http_method;

	public function __construct()
	{
		parent::__construct();

		// 클라에서 요청한 (GET, POST, PATCH, DELETE) HTTP 메서드 확인
		$this->http_method = $_SERVER["REQUEST_METHOD"];
	}

	public function index()
	{
		$result = $this->page(null);
		echo($result);
	}

	public function headerData(){
        $pmm_jwt = new PolicticsJwt();

        // 클라이언트가 header에 토큰정보를 담아 보낸걸 확인한다.
        $header_data = apache_request_headers();
        if(empty($header_data['Authorization'])){
            return (object)$result=array("idx"=>"토큰실패");
        }else{
            $token_data = $pmm_jwt->tokenParsing($header_data['Authorization']);
            return $token_data;

        }
    }


	public function requestData($data){
	    if ($this->http_method=="GET") {
            //법안 모아보기
            if ($data == 'card') {
                $input=$this->input->get(null,true);

                $error=jsonNullCheck($input,array('page'));
                if($error!=null){header("HTTP/1.1 400"); echo $error;return;}


                $page_idx = $input['page'];
                $result = $this->page($page_idx);
                echo $result;
            } //법안 상세정보
            else if ($data == 'bill_info') {
                $input = $this->input->get(null,true);

                $error=jsonNullCheck($input,array('bill_idx'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}


                $result = $this->billInfo($input['bill_idx']);
                echo $result;
            } //법안 자체의 좋아요 싫어요 수
            else if ($data == 'bill_user_evaluation') {

                // 클라이언트가 보낸 토큰 정보가 담겨있다.
                $token_data = $this->headerData();

                $opposition_comment = $this->input->get(null, true);

                $error=jsonNullCheck($opposition_comment,array('bill_idx'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}


                $result = $this->billUserEvaluation($opposition_comment['bill_idx'], $token_data);
                echo $result;
            }
            //법안의 찬성 댓글 and 대댓글 갯수
            //페이징
            else if ($data == 'bill_agreement_comment') {

                // 클라이언트가 보낸 토큰 정보가 담겨있다.
                $token_data = $this->headerData();

                $agreement_comment = $this->input->get(null, true);

                $error=jsonNullCheck($agreement_comment,array('bill_idx','comment_page'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}


                $result = $this->billComment($agreement_comment['bill_idx'],$agreement_comment['comment_page'], 'agreement',$token_data);
                echo $result;

            }
            //법안의 반대 댓글 and 대댓글 갯수
            //페이징
            else if ($data == 'bill_opposition_comment') {

                // 클라이언트가 보낸 토큰 정보가 담겨있다.
                $token_data = $this->headerData();

                $opposition_comment = $this->input->get(null, true);

                $error=jsonNullCheck($opposition_comment,array('bill_idx','comment_page'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

                $result = $this->billComment($opposition_comment['bill_idx'], $opposition_comment['comment_page'], 'opposition',$token_data);
                echo $result;
            }

            //대댓글 보기 클릭
            //댓글 idx , 대댓글 페이지
            //대댓글의 정보 and 페이징
            else if ($data == 'bill_sub_comment') {

                // 클라이언트가 보낸 토큰 정보가 담겨있다.
                $token_data = $this->headerData();

                $comment_idx = $this->input->get(null, true);

                $error=jsonNullCheck($comment_idx,array('comment_idx','sub_comment_page'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

                $result = $this->billSubComment($comment_idx['comment_idx'], $comment_idx['sub_comment_page'], $token_data);
                echo $result;
            }
        }else if ($this->http_method=="POST"){
	        //사용자가 법안에 대해 댓글 쓰기
	        if($data=='bill_comment_write'){

                // 클라이언트가 보낸 토큰 정보가 담겨있다.
                $token_data = $this->headerData();

	            $input=$this->input->post("comment_write",true);
	            $input_json=json_decode($input,true);

                $error=jsonNullCheck($input_json,array('bill_idx','content','status'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

                $result=$this->billCommentWrite($input_json['bill_idx'],$input_json['content'],$input_json['status'],$token_data);
	           echo $result;
            }
	        //사용자가 댓글에 대한 답글 쓰기
	        else if ($data=='bill_sub_comment_write'){

                // 클라이언트가 보낸 토큰 정보가 담겨있다.
                $token_data = $this->headerData();

	            $input=$this->input->post('sub_comment_write',true);
	            $input_json=json_decode($input,true);

                //댓글에 대한 답글일경우
                if(empty($input_json['parent_user_idx'])){
                    $error=jsonNullCheck($input_json,array('comment_idx','content'));
                    if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

                    $result= $this->subCommentWrite($input_json['comment_idx'],$input_json['content'],null,$token_data);
                   echo $result;
                }
                //답글에 대한 답글 일 경우
                else{
                    $error=jsonNullCheck($input_json,array('comment_idx','content','parent_user_idx'));
                    if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

                    $result= $this->subCommentWrite($input_json['comment_idx'],$input_json['content'],$input_json['parent_user_idx'],$token_data);
                    echo $result;

                }
            }
	        //법안에 대한 좋아요 싫어요
	        else if ($data=='bill_evaluation_write'){

                // 클라이언트가 보낸 토큰 정보가 담겨있다.
                $token_data = $this->headerData();

	            $input=$this->input->post("evaluation_write",true);
	            $input_json=json_decode($input,true);

                $error=jsonNullCheck($input_json,array('bill_idx','status'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

	            $result=$this->billEvaluationClick($input_json['bill_idx'],$input_json['status'],$token_data);
	            echo $result;
            }
	        //사용자가 댓글 , 대댓글에 대한 좋아요 싫어요 클릭
	        else if ($data=='comment_evaluation_write'){

                // 클라이언트가 보낸 토큰 정보가 담겨있다.
                $token_data = $this->headerData();


	            $input=$this->input->post("evaluation_write",true);
	            $input_json=json_decode($input,true);

                //댓글에 대해 좋아요 누른경우
                if (empty($input_json['sub_comment_idx'])){
                    $error=jsonNullCheck($input_json,array('status','comment_idx'));
                    if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}
                    $result=$this->commentEvaluationClick('comment_idx',$input_json['comment_idx'],$input_json['status'],$token_data);
                }
                //대댓글에 대해 좋아요 누른경우
                else{
                    $error=jsonNullCheck($input_json,array('status','sub_comment_idx'));
                    if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}
                    $result=$this->commentEvaluationClick('sub_comment_idx',$input_json['sub_comment_idx'],$input_json['status'],$token_data);
                }

                echo $result;
            }
        }
    }

	//법안 모아보기
	public function page($index)
	{
		$this->load->model('BillModel');
		$result = $this->BillModel->billPageList($index);
		return $result;
	}

	//법안 상세보기
	public function billInfo($index)
	{
		$this->load->model('BillModel');
		$result = $this->BillModel->billInfoData($index);
		return $result;
	}

	//법안에 대한 좋아요 싫어요
	public function billUserEvaluation($index, $token_data)
	{
		$this->load->model('BillModel');
		$result = $this->BillModel->billUserStatus($index, $token_data);
		return $result;
	}

	//법안에 대한 댓글가져오기
    public function billComment($index,$comment_page,$status,$token_data)
    {
        $this->load->model('BillModel');
        $result=$this->BillModel->billCommentList($index,$comment_page,$status,$token_data);
        echo($result);
    }

    //댓글에 대한 대댓글 가져오기
    public function billSubComment($index,$sub_comment_page, $token_data){
	    $this->load->model('BillModel');
	    $result=$this->BillModel->billSubCommentList($index,$sub_comment_page, $token_data);
	    return $result;
    }
    //법안 댓글 달기
    //좋아요 댓글인지 싫어요 댓글인지 표기
    public function billCommentWrite($bill_idx,$content,$status,$token_data){
        $this->load->model("BillModel");
        $result=$this->BillModel->billCommentWrite($bill_idx,$content,$status,$token_data);
        return $result;
    }
    //법안에 대해 좋아요 싫어요 클릭
    public function billEvaluationClick($bill_idx,$status,$token_data){
        $this->load->model('BillModel');
        $result=$this->BillModel->billEvaluationWrite($bill_idx,$status,$token_data);
        return$result;
    }
    //댓글에 대해 답글달기
    public function subCommentWrite($comment_idx,$content,$parent_idx,$token_data){
	    $this->load->model('BillModel');
        $result=$this->BillModel->billSubCommmentWrite($comment_idx,$content,$parent_idx,$token_data);
	    return$result;
    }
    //댓글, 대댓글에 대해 좋아요 싫어요 클릭
    public function commentEvaluationClick($comment_check,$comment_idx,$status,$token_data){
        $this->load->model('BillModel');
        $result=$this->BillModel->commentEvaluationWrite($comment_check,$comment_idx,$status,$token_data);
        return$result;
    }
}