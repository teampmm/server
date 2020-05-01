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

	public function page($index)
	{
		$this->load->model('BillModel');
		$result = $this->BillModel->billPageList($index);
		echo($result);
	}

	public function billInfo($index)
	{
		$this->load->model('BillModel');
		$result = $this->BillModel->billInfoData($index);
		echo($result);
	}

	public function billUserEvaluation($index)
	{
		$this->load->model('BillModel');
		$result = $this->BillModel->billUserStatus($index);
		echo($result);
	}

}