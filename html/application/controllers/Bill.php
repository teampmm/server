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
	    //법안 모아보기
	    if ($data=='card'){
	        $page_idx=$this->input->get('page',true);
	        $result=$this->page($page_idx);
	        echo $result;
        }
        //법안 상세정보
	    else if ($data=='bill_info'){
            $bill_idx=$this->input->get('bill_idx',true);
            $result=$this->billInfo($bill_idx);
            echo $result;
        }
        //법안 자체의 좋아요 싫어요 수
	    else if ($data == 'bill_user_evaluation') {
            $bill_idx = $this->input->get('bill_idx', true);
            $result = $this->billUserEvaluation($bill_idx);
            echo $result;
        }
	    //법안의 찬성 댓글 and 대댓글 갯수
        //페이징
	    else if ($data=='bill_agreement_comment'){
            $bill_idx=$this->input->get('bill_idx',true);
            $comment_page=$this->input->get('comment_page',true);
            $result=$this->billComment($bill_idx,$comment_page,'agreement');
            echo $result;

        }
	    //법안의 반대 댓글 and 대댓글 갯수
        //페이징
        else if ($data=='bill_opposition_comment'){
            $bill_idx=$this->input->get('bill_idx',true);
            $comment_page=$this->input->get('comment_page',true);
            $result=$this->billComment($bill_idx,$comment_page,'opposition');
            echo $result;
        }
        //대댓글 보기 클릭
        //댓글 idx , 대댓글 페이지
        //대댓글의 정보 and 페이징
	    else if ($data=='bill_sub_comment'){
	        $comment_idx=$this->input->get('comment_idx',true);
            $sub_comment_page=$this->input->get('sub_comment_page',true);
            $result=$this->billSubComment($comment_idx,$sub_comment_page);
            echo $result;
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
}