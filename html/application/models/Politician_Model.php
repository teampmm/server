<?php

class Politician_Model extends CI_Model
{

    private $total_card;
    private $total_card_idx;
    private $per_page_data;

    function __construct()//생성자, 제일 먼저 실행되는 일종의 초기화
    {
        parent::__construct();

        $this->total_card_idx = (array)$this->total_card_idx;

        // db에 있는 정치인 카드의 수
        $result = $this->db->query("select count(idx) as `count` from Politician")->row();
        $this->total_card = $result->count;

        // 정치인 인덱스를 저장
        for ($x = 0; $x < $this->total_card; $x++)
            array_push($this->total_card_idx, $x + 1);

    }


    // 정치인 모아보기 카드페이지에 따라서 카드정보 반환
    // input : 받은 카드의 인덱스 - 초기값 []
    // output : enable_scrool(스크롤 여부), 받은 카드의 인덱스, 카드정보(카드인덱스, 정치인이름, 정당인덱스
    public function getPoliticianCard($data)
    {
        // 최종 클라이언트에게 보낼 응답 데이터
        $response_data = array();

        $send_card_idx = array();

        // 페이지당 정치인 카드의 수
        $this->per_page_data = 8;

        // 클라이언트 사용한 카드의 인덱스
        $used_card_idx = $data['used_card_idx'];

        // 정치인 카드 인덱스에서 사용한 카드와 중복되지 않는 카드 리스트를 만든다..
        // array_diff는 값을 제외시키는 함수
        $temp_array = array_diff($this->total_card_idx, $used_card_idx);

        if (count($temp_array) == 0) {

            $response_data['enable_scroll'] = false;
            $response_data['card_list'] = "no data";
        } else {
            // 사용한 카드의 수 + 페이지에 보여줄 카드의 수가 총 카드의 수 보다 많은 경우
            if (count($temp_array) < $this->per_page_data) {

                $temp_array = $this->arraySort($temp_array);
                for ($x = 0; $x < (int)count($temp_array); $x++)
                    $send_card_idx[$x] = (string)((int)$temp_array[$x]);

                $response_data['enable_scroll'] = false;
            } // 사용한 카드의 수 + 페이지에 보여줄 카드의 수가 총 카드의 수 보다 적은 경우
            else {
                // 중복 되지 않은 카드 리스트에서 페이지에 표시될 카드의 수 만큼 뽑는다.
                $send_card_idx = array_rand($temp_array, $this->per_page_data);

                // 랜덤하게 뽑은 카드 한장을 사용한 카드 리스트로 넣는다.
                array_push($used_card_idx, $send_card_idx);

                // db에 idx가 1부터 시작이기에 모든 카드의 인덱스에 +1을 해줌
                for ($x = 0; $x < (int)count($send_card_idx); $x++)
                    $send_card_idx[$x] = (string)((int)$send_card_idx[$x] + 1);

                $response_data['enable_scroll'] = true;
            }



            // 클라이언트에게 보낼 카드리스트 인덱스로 카드 모아보기 정보를 검색한다.
            // 카드 모아보기에 필요한 데이터
            // 정치인 사진 경로
            // 정치인 이름
            // 정치인 정당 인덱스
            // 당선 지역
            // 카테고리
            $politician_pledge_result = $this->db->query("SELECT
                idx, kr_name, party_idx, profile_image_url, elect_area, category
                FROM Politician where idx IN (" . implode(',', $send_card_idx) . ")")->result();

            // 카드 모음 리스트
            $card_list = array();

            foreach ($politician_pledge_result as $row) {

                // 카드 하나에 들어있는 데이터
                $card_data = array();

                // 정당 인덱스로 정당의 이름을 조회
                $party_select_result = $this->db->query("SELECT
                    party_name
                    FROM Party where idx = '$row->party_idx'")->row();

                // 정치인 카드에 들어갈 정보
                $card_data['kr_name'] = $row->kr_name;
                $card_data['party_name'] = $party_select_result->party_name;
                $card_data['elect_area'] = $row->elect_area;
                $card_data['profile_image_url'] = $row->profile_image_url;
                $card_data['category'] = $row->category;

                // 정치인 카드 리스트에 추가
                array_push($card_list,$card_data);
            }


//            // 정당 인덱스로 정당 찾기
//            $asd_result = $this->db->query("SELECT
//                party_name
//                FROM Party where idx = '$result->party_idx'")->result();
//
//            return $asd_result;

            // 클라이언트에게 보낼 메세지 여기서 작성
            $response_data['send_card_idx'] = $send_card_idx;
            $response_data['card_list'] = $card_list;
        }


        return json_encode($response_data);

    }

    // 배열 재 정렬
    public function arraySort($array)
    {
        //배열 인덱스 재정렬
        $i = 0;
        foreach ($array as $key => $val) {
            unset($array[$key]);

            $new_key = $i;
            $array[$new_key] = $val;
            $i++;
        }

        return $array;
    }

    // 정치인 기본정보 요청
    // input : 정치인 이름
    // output : 정치인 사진경로, 공약 이행률, 카테고리, 이름, 정당이름, 약력
    public function getBaseInfo($data)
    {
        // 클라이언트에게 응답 해줄 데이터
        $response_data = array();

        // 정치인 이름
        $politician_name = $data['kr_name'];

        // 정치인 기본 정보 조회
        $politician_select_result = $this->db->query("SELECT
                idx, party_idx,history, category, profile_image_url
                FROM Politician where kr_name = '$politician_name'")->row();

        // 정치인 인덱스
        $politician_idx = $politician_select_result->party_idx;

        // 정치인 테이블에서 정당 인덱스로 정당 테이블에 있는 정당 이름 조회
        $party_select_result = $this->db->query("SELECT party_name FROM Party where idx = '$politician_idx'")->row();

        // 정당 이름
        $party_name = $party_select_result->party_name;

        $response_data['kr_name'] = $politician_name;
        $response_data['party_name'] = $party_name;
        $response_data['history'] = $politician_select_result->history;
        $response_data['category'] = $politician_select_result->category;
        $response_data['profile_image_url'] = $politician_select_result->profile_image_url;

        // 정치인 테이블에서 정치인 인덱스로 정치인 공약 모음 테이블에 있는 공약을 조회
        // 공약 이행률 계산
        // 공약 전체 갯수 : 공약 데이터중 정치인 테이블에서 정치인 인덱스가 포함된 데이터 찾기
        // 공약 이행 갯수 : 공약 이행 과정 - 완전이행 된것만 찾음
        // 공약 이행률 = (공약 이행 갯수 / 공약 전체 갯수) * 100
        $politician_pledge_select_result = $this->db->query("SELECT pledge_implement_process FROM PoliticianPledge where politician_idx = '$politician_idx'")->result();

        // 공약 전체 갯수
        $pledge_total = count($politician_pledge_select_result);

        $success_pledge = 0;    // 완전이행
        $part_success_pledge = 0; // 부분이행
        $retreat_pledge = 0; // 퇴각이행
        $not_execute_pledge = 0; // 미이행
        $impossible_judge = 0; // 판단불가

        foreach ($politician_pledge_select_result as $row) {
            $pledge_status = $row->pledge_implement_process;

            if ($pledge_status == "완전이행") {
                $success_pledge++;
            } else if ($pledge_status == "부분이행") {
                $part_success_pledge++;
            } else if ($pledge_status == "퇴각이행") {
                $retreat_pledge++;
            } else if ($pledge_status == "미이행") {
                $not_execute_pledge++;
            } else if ($pledge_status == "판단불가") {
                $impossible_judge++;
            }
        }


        // 공약 이행 데이터
        $pledge_data = array();

        $pledge_data['total_pledge_num'] = $pledge_total;
        $pledge_data['success_pledge_num'] = $success_pledge;
        $pledge_data['part_success_pledge_num'] = $part_success_pledge;
        $pledge_data['retreat_pledge_num'] = $retreat_pledge;
        $pledge_data['not_execute_pledge_num'] = $not_execute_pledge;
        $pledge_data['impossible_judge_num'] = $impossible_judge;

        $response_data['pledge_data'] = $pledge_data;


        // 공약 완전이행 갯수
//        $pledge_success_count =
        return json_encode($response_data);


    }

    // 정치인 관련 뉴스정보 요청
    // input : 정치인 이름
    // output : 뉴스제목, 뉴스날짜, 뉴스 링크 경로
    public function getNews($data)
    {
        // 정치인 이름
        $politician_name = $data['kr_name'];

        // 정치인 이름으로 정치인 인덱스 찾기
        $politician_select_result = $this->db->query("SELECT
                idx
                FROM Politician where kr_name = '$politician_name'")->row();

        // 정치인 인덱스
        $politician_idx = $politician_select_result->idx;

        // 정치인 관련 뉴스 조회
        $politician_select_result = $this->db->query("SELECT
                title, `date`, url
                FROM News where politician_idx = '$politician_idx'")->result();

        // 클라에게 보내줄 응답 데이터
        $response_data = array();
        $result_num = count($politician_select_result);
        $response_data['result_num'] = $result_num;
        $response_data['data'] = $politician_select_result;

        return json_encode($response_data);

    }


    // 정치인 공약 정보 요청
    // input : 정치인 이름
    // output : 공약내용, 공약대수, 공약 이행 상태
    public function getPledgeInfo($data){
        // 정치인 이름
        $politician_name = $data['kr_name'];

        // 정치인 이름으로 정치인 인덱스 찾기
        $politician_select_result = $this->db->query("SELECT
                idx
                FROM Politician where kr_name = '$politician_name'")->row();

        // 정치인 인덱스
        $politician_idx = $politician_select_result->idx;

        // 정치인 인덱스로 정치인 공약 모음 - 공약 내용, 공약 대수, 공약 이행 상태 조회하기
        $politician_pledge_select_result = $this->db->query("SELECT
                pledge_implement_process, content, generation
                FROM PoliticianPledge where politician_idx = '$politician_idx'")->result();

        // 클라에게 보내줄 응답 데이터
        $response_data = array();
        $result_num = count($politician_pledge_select_result);
        $response_data['result_num'] = $result_num;
        $response_data['data'] = $politician_pledge_select_result;


        return json_encode($response_data);
    }


    // 정치인 상세 정보 요청
    // input : 정치인 이름
    // output : 당선대수, 당선횟수, 당선지역, 소속위원회, 약력, 연락처
    public function getDetailInfo($data){

        // 정치인 이름
        $politician_name = $data['kr_name'];

        // 정치인 이름으로 상세정보 찾기
        $politician_select_result = $this->db->query("SELECT
                idx, affiliation_committee, history, elect_generation, elect_area, office_number
                FROM Politician where kr_name = '$politician_name'")->row();

        // 클라에게 보내줄 응답 데이터
        $response_data = array();
        $response_data['data'] = $politician_select_result;

        return json_encode($response_data);
    }

}

