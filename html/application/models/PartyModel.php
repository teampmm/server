<?php

include_once 'OptionModel.php';


class PartyModel extends CI_Model
{
    public $option_model;


    public function __construct()
    {
        parent::__construct();
        $this->option_model = new OptionModel();
    }

    // 정당 기본정보 반환
    public function getInfo($idx)
    {
        // 클라이언트에게 응답 해줄 데이터
        $response_data = array();

        $sql = "SELECT * FROM Party where idx = ?";

        // 정당 정보 조회
        $party_s_result = $this->db->query($sql,array((int)$idx))->row();

        // 사용자의 sql 및 id 등 로그 기록하기
        $log_sql = "SELECT * FROM Party where idx = $idx";
        $this->option_model->logRecode(
            array(
                'sql'=>$log_sql)
        );

        if ($party_s_result == null){
            header("HTTP/1.1 204 ");
            return;
        }

        $sql = "SELECT politician_idx FROM PoliticianPartyHistory where party_idx = ? and end_day is ?";
        $party_politician_result = $this->db->query($sql, array($party_s_result->idx, null))->result();

        $politician_idx_array = array();
        foreach ($party_politician_result as $row){
            array_push($politician_idx_array, $row->politician_idx);
        }

        $sql = "SELECT kr_name FROM Politician where idx = ?";
        $politician_name_array = array();
        for($i = 0; $i < count($politician_idx_array); $i++){
            $politician_kr_name = $this->db->query($sql, array($politician_idx_array[$i]))->row()->kr_name;
            array_push($politician_name_array, $politician_kr_name);
        }

        $response_data['idx'] = $party_s_result->idx;
        $response_data['name'] = $party_s_result->name;
        $response_data['start_day'] = $party_s_result->start_day;
        $response_data['end_day'] = $party_s_result->end_day;
        $response_data['floor_leader'] = $party_s_result->floor_leader;
        $response_data['party_leader'] = $party_s_result->party_leader;
        $response_data['homepage'] = $party_s_result->homepage;
        $response_data['logo'] = 'http://52.78.106.225/files/images/party_logo/'.$party_s_result->idx.'.jpg';
        $response_data['slogan'] = $party_s_result->slogan;
        $response_data['politician_num'] = count($party_politician_result);
        $response_data['party_politician'] = $politician_name_array;

        return json_encode($response_data,JSON_UNESCAPED_UNICODE);
    }

    // 클라이언트가 받고싶은 카드의 날짜를 보낸다.
    // 요청한 날짜에 맞는 카드 데이터를 반환
    // 반환 데이터는 정당명, logo 이미지 이다. // 2020-06-10 : 세훈이형이 현재는 두개 데이터만 필요하다고함.
    public function getPartyCard($date){

        // 클라이언트에게 응답 해줄 데이터
        $response_data = array();

        $sql = "SELECT * FROM Party";

        // 정당 정보 조회
        $party_s_result = $this->db->query($sql)->result();

        // 사용자의 sql 및 id 등 로그 기록하기
        $log_sql = "SELECT * FROM Party";
        $this->option_model->logRecode(
            array(
                'sql'=>$log_sql)
        );

        $total_card = array();

        foreach ($party_s_result as $row){

            if ($row->name == "무소속")
                continue;

            // 클라이언트가 요청한 날짜보다 당 창당일이 낮은경우에만 클라이언트에게 응답해준다.
            if (($row->start_day <= $date and ($date <= $row->end_day or $row->end_day == null))){

                $sql = "SELECT count(idx) as cnt FROM PoliticianPartyHistory 
                where party_idx = ? and 
                (start_day <= ? and (? <= end_day or end_day is null))";

                $party_politician_count = $this->db->query($sql, array($row->idx, $date, $date))->row()->cnt;

                $card_data = array(
                    'idx'=>$row->idx,
                    'name'=>$row->name,
                    'politician_num'=>(int)$party_politician_count,
                    'logo'=>'http://52.78.106.225/files/images/party_logo/'.$row->idx.'.jpg'

            );
                array_push($total_card,$card_data);
            }

        }

        // 날짜 요청에 따라 반환할 카드가 없는 경우
        if(count($total_card) == 0){
            header("HTTP/1.1 204 ");
            return;
        }
        else{
            $response_data['total_card_num'] = count($total_card);
            $response_data['result'] = $total_card;
        }

        return json_encode($response_data,JSON_UNESCAPED_UNICODE);

    }

}

