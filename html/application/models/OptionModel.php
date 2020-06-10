<?php

class OptionModel extends CI_Model{

    function blackListTokenCheck($token_str){
        $query = $this->db->query("select count(idx) as 'count' from BlackList where 
                    token='$token_str'"
        )->row();

        if ($query->count >= 1){
            header("HTTP/1.1 401 ");
            return '토큰 만료 - 재 로그인 바람';
        }
        else{
            return "유효한 토큰";
        }
    }

    function logRecode($log_data_array){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $message = "ip : ". $ip. ', ';

        foreach ($log_data_array as $key=>$value){
            $message = $message.$key.' : '.$value.', ';
        }
        $message = substr($message,0,-2);

        log_message("error", $message);
    }


}