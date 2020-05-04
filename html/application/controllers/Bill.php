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
	    if ($data=='page'){
	        $parm=$this->input->get('page',true);
	        $result=$this->page($parm);
	        echo $result;
        }else if ($data=='bill_info'){
            $parm=$this->input->get('bill_info',true);
            $result=$this->billInfo($parm);
            echo $result;
        }else if ($data == 'bill_user_evaluation'){
            $parm=$this->input->get('bill_info',true);
            $result=$this->billUserEvaluation($parm);
            echo $result;
        }else if ($data=='bill_comment'){
            $parm=$this->input->get('bill_info',true);
            $result=$this->billComment($parm);
            echo $result;
        }
//	    $data=$this->input->get('bbb',true);
//        echo $data;

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
    public function billComment($index)
    {

        $this->load->model('BillModel');
        $result=$this->BillModel->billCommentList($index);
        echo($result);
    }

}