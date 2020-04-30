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

    public function index(){
            $result = $this->page(NULL);
            print_r($result);

    }
    public function page($index){
        $this->load->model('Bill_Model');
        $result=$this->Bill_Model->billPageList($index);
        print_r($result);
    }
    public function billInfo($index){
        $this->load->model('Bill_Model');
        $result=$this->Bill_Model->billInfoData($index);
        print_r($result);
    }


}