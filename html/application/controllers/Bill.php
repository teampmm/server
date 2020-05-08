<?php

defined('BASEPATH') or exit('No direct script access allowed');

include 'DTO/Option.php';

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
                $opposition_comment = $this->input->get(null, true);

                $error=jsonNullCheck($opposition_comment,array('bill_idx'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}


                $result = $this->billUserEvaluation($opposition_comment['bill_idx']);
                echo $result;
            }
            //법안의 찬성 댓글 and 대댓글 갯수
            //페이징
            else if ($data == 'bill_agreement_comment') {
                $agreement_comment = $this->input->get(null, true);

                $error=jsonNullCheck($agreement_comment,array('bill_idx','comment_page'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}


                $result = $this->billComment($agreement_comment['bill_idx'],$agreement_comment['comment_page'], 'agreement');
                echo $result;

            }
            //법안의 반대 댓글 and 대댓글 갯수
            //페이징
            else if ($data == 'bill_opposition_comment') {
                $opposition_comment = $this->input->get(null, true);

                $error=jsonNullCheck($opposition_comment,array('bill_idx','comment_page'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

                $result = $this->billComment($opposition_comment['bill_idx'], $opposition_comment['comment_page'], 'opposition');
                echo $result;
            }

            //대댓글 보기 클릭
            //댓글 idx , 대댓글 페이지
            //대댓글의 정보 and 페이징
            else if ($data == 'bill_sub_comment') {
                $comment_idx = $this->input->get(null, true);

                $error=jsonNullCheck($comment_idx,array('comment_idx','sub_comment_page'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

                $result = $this->billSubComment($comment_idx['comment_idx'], $comment_idx['sub_comment_page']);
                echo $result;
            }
        }else if ($this->http_method=="POST"){
	        //사용자가 법안에 대해 댓글 쓰기
	        if($data=='bill_comment_write'){
	           $input=$this->input->post("comment_write",true);
	           $input_json=json_decode($input,true);

                $error=jsonNullCheck($input_json,array('bill_idx','content','status'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

                $result=$this->billCommentWrite($input_json['bill_idx'],$input_json['content'],$input_json['status']);
	           echo $result;
            }
	        //사용자가 댓글에 대한 답글 쓰기
	        else if ($data=='bill_sub_comment_write'){
	            $input=$this->input->post('sub_comment_write',true);
	            $input_json=json_decode($input,true);

                $error=jsonNullCheck($input_json,array('comment_idx','content'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

                //댓글에 대한 답글일경우
                if(empty($input_json['parent_user_idx'])){
                   $result= $this->subCommentWrite($input_json['comment_idx'],$input_json['content'],null);
                   echo $result;
                }
                //답글에 대한 답글 일 경우
                else{
                    $result= $this->subCommentWrite($input_json['comment_idx'],$input_json['content'],$input_json['parent_user_idx']);
                    echo $result;

                }
            }
	        else if ($data=='bill_evaluation_write'){
	            $input=$this->input->post("evaluation_write",true);
	            $input_json=json_decode($input,true);

                $error=jsonNullCheck($input_json,array('bill_idx','status'));
                if($error!=null){header("HTTP/1.1 400 "); echo $error;return;}

	            $result=$this->billEvaluationClick($input_json['bill_idx'],$input_json['status']);
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
	public function billUserEvaluation($index)
	{
		$this->load->model('BillModel');
		$result = $this->BillModel->billUserStatus($index);
		return $result;
	}

	//법안에 대한 댓글가져오기
    public function billComment($index,$comment_page,$status)
    {
        $this->load->model('BillModel');
        $result=$this->BillModel->billCommentList($index,$comment_page,$status);
        echo($result);
    }

    //댓글에 대한 대댓글 가져오기
    public function billSubComment($index,$sub_comment_page){
	    $this->load->model('BillModel');
	    $result=$this->BillModel->billSubCommentList($index,$sub_comment_page);
	    return $result;
    }
    //법안 댓글 달기
    //좋아요 댓글인지 싫어요 댓글인지 표기
    public function billCommentWrite($bill_idx,$content,$status){
        $this->load->model("BillModel");
        $result=$this->BillModel->billCommentWrite($bill_idx,$content,$status);
        return $result;
    }
    //법안에 대해 좋아요 싫어요 클릭
    public function billEvaluationClick($bill_idx,$status){
        $this->load->model('BillModel');
        $result=$this->BillModel->billEvaluationWrite($bill_idx,$status);
        return$result;
    }
    //댓글에 대해 답글달기
    public function subCommentWrite($comment_idx,$content,$parent_idx){
	    $this->load->model('BillModel');
        $result=$this->BillModel->billSubCommmentWrite($comment_idx,$content,$parent_idx);
	    return$result;
    }
}