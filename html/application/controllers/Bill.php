<?php

defined('BASEPATH') or exit('No direct script access allowed');

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
                $page_idx = $this->input->get('page_request', true);
                $page_idx=json_decode($page_idx,true);
                $result = $this->page($page_idx['page']);
                echo $result;
            } //법안 상세정보
            else if ($data == 'bill_info') {
                $bill_idx = $this->input->get('bill_idx_request', true);
                $bill_idx=json_decode($bill_idx,true);
                $result = $this->billInfo($bill_idx['bill_idx']);
                echo $result;
            } //법안 자체의 좋아요 싫어요 수
            else if ($data == 'bill_user_evaluation') {
                $bill_idx = $this->input->get('bill_idx_request', true);
                $bill_idx=json_decode($bill_idx,true);
                $result = $this->billUserEvaluation($bill_idx['bill_idx']);
                echo $result;
            }
            //법안의 찬성 댓글 and 대댓글 갯수
            //페이징
            else if ($data == 'bill_agreement_comment') {
                $bill_idx = $this->input->get('bill_agreement_request', true);
                $bill_idx=json_decode($bill_idx,true);
                $result = $this->billComment($bill_idx['bill_idx'],$bill_idx['comment_page'], 'agreement');
                echo $result;

            }
            //법안의 반대 댓글 and 대댓글 갯수
            //페이징
            else if ($data == 'bill_opposition_comment') {
                $bill_idx = $this->input->get('bill_opposition_request', true);
                $bill_idx=json_decode($bill_idx,true);
                $result = $this->billComment($bill_idx['bill_idx'], $bill_idx['comment_page'], 'opposition');
                echo $result;
            }
            //대댓글 보기 클릭
            //댓글 idx , 대댓글 페이지
            //대댓글의 정보 and 페이징
            else if ($data == 'bill_sub_comment') {
                $comment_idx = $this->input->get('bill_sub_comment', true);
                $comment_idx=json_decode($comment_idx,true);

                $result = $this->billSubComment($comment_idx['comment_idx'], $comment_idx['sub_comment_page']);
                echo $result;
            }
        }else if ($this->http_method=="POST"){
	        //사용자가 법안에 대해 댓글 쓰기
	        if($data=='bill_comment_write'){
	           $input=$this->input->post('comment_write',true);
                $input_json=json_decode($input,true);
                $result=$this->billCommentWrite($input_json['bill_idx'],$input_json['content'],$input_json['status']);
	           echo $result;
            }else if ($data=='bill_evaluation_write'){
	            $input=$this->input->post("evaluation_write",true);
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
    public function billEvaluationWrite($input){
        echo "asd";
    }
}