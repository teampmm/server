<?php

class OptionModel extends CI_Model{

    function blackListTokenCheck($token_str){
        $query = $this->db->query("select count(idx) as 'count' from BlackList where 
                    token='$token_str'"
        )->row();

        if ($query->count >= 1){
            return '토큰 만료 - 재 로그인 바람';
        }
        else{
            return "유효한 토큰";
        }
    }


}