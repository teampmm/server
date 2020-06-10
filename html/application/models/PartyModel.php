<?php

include_once 'OptionModel.php';


class PartyModel extends CI_Model
{
    public $option_model;


    public function __construct()
    {
        parent::__construct();
        $this->option = new Option();
    }

    // 정당 기본정보 반환
    public function getInfo($name)
    {
        // 클라이언트에게 응답 해줄 데이터
        $response_data = array();

        $sql = "SELECT idx,`name`,start_day,end_day,floor_leader,party_leader,homepage,logo,slogan FROM Party where name = ?";

        // 정당 정보 조회
        $party_s_result = $this->db->query($sql,array($name))->row();

        // 사용자의 sql 및 id 등 로그 기록하기
        $log_sql = "SELECT idx,`name`,start_day,end_day,floor_leader,party_leader,homepage,logo,slogan FROM Party where name = $name";
        $this->option_model->logRecode(
            array(
                'sql'=>$log_sql)
        );


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

    // 클라이언트가 받고싶은 카드의 날짜를 보낸다.
    // 요청한 날짜에 맞는 카드 데이터를 반환
    // 반환 데이터는 정당명, logo 이미지 이다. // 2020-06-10 : 세훈이형이 현재는 두개 데이터만 필요하다고함.
    public function getPartCard($date){

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

            // 클라이언트가 요청한 날짜보다 당 창당일이 낮은경우에만 클라이언트에게 응답해준다.
            if (($row->start_day <= $date and $date <= $row->end_day) or ($row->start_day >= $date and $row->end_day == null)){

                $card_data = array(
                    'idx'=>$row->idx,
                    'name'=>$row->name,
                    'logo'=>$row->logo,
                );
                array_push($total_card,$card_data);
            }

        }

        // 날짜 요청에 따라 반환할 카드가 없는 경우
        if(count($total_card) == 0){
            header("HTTP/1.1 404 ");
            $response_data['result'] = "요청한 날짜에 반환될 카드 데이터가 없습니다";
        }
        else{
            $response_data['total_card_num'] = count($total_card);
            $response_data['result'] = $total_card;
        }

        return json_encode($response_data);

    }

}

