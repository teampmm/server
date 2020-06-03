<?php

class PartyModel extends CI_Model
{
    // 정당 기본정보 반환
    public function getInfo($name)
    {
        // 클라이언트에게 응답 해줄 데이터
        $response_data = array();

        // 정당 정보 조회
        $party_s_result = $this->db->query("SELECT
                idx,`name`,start_day,end_day,floor_leader,party_leader,homepage,logo,slogan FROM Party where name = '$name'")->row();

        if ($party_s_result == null){
            header("HTTP/1.1 404 ");
            return '요청한 정당의 정보가 없습니다';
        }

        $response_data['idx'] = $party_s_result->idx;
        $response_data['name'] = $party_s_result->name;
        $response_data['start_day'] = $party_s_result->start_day;
        $response_data['end_day'] = $party_s_result->end_day;
        $response_data['floor_leader'] = $party_s_result->floor_leader;
        $response_data['party_leader'] = $party_s_result->party_leader;
        $response_data['homepage'] = $party_s_result->homepage;
        $response_data['logo'] = $party_s_result->logo;
        $response_data['slogan'] = $party_s_result->slogan;

        return json_encode($response_data);
    }

}

