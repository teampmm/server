<?php

class PoliticianModel extends CI_Model
{

    function __construct()//생성자, 제일 먼저 실행되는 일종의 초기화
    {
        parent::__construct();
    }

    // 정치인 카드 모아보기 정보
    public function getPoliticianCard($data){

    	// 응답 데이터
	    $response_data = array();

	    // 한 페이지에 보여줄 카드의 갯수
	    $per_page_data = 8;

	    // 총 카드의 수
	    $total_card = $this->db->query("select count(idx) as `count` from Politician")->row();
		$total_card = $total_card->count;

	    // 사용자가 가지고 있는 랜덤 카드 인덱스 - RandomCard 인덱스
    	$current_rand_idx = $data['idx'];

	    // 사용자가 요청한 페이지 - RandomCard 인덱스
	    $request_page_idx = $data['page'];

	    if ($current_rand_idx == null){
		    // rand_idx 할당
		    $current_rand_idx = mt_rand(1, $total_card);
	    }
	    // 카드 데이터 가져오기 - 1,4,2,56,7,4 ...
	    $card_data = $this->db->query("select card_number from RandomCard where idx = '$current_rand_idx'")->row();

	    // 문자열 제일 앞, 뒤에 있는 큰따옴표 제거
	    $card_data = str_replace( "\"","", $card_data->card_number); // 쌍따옴표 제거

	    // 카드 데이터 -> 카드 배열로 변환
	    $card_array = explode(",", $card_data);

	    // 카드 모음 리스트
	    $card_list = array();

	    // 첫 페이지 요청
	    if($request_page_idx == 1){
	    	// rand_idx 할당 및 rand_idx에 해당하는 카드를 per_page_data 만큼 반환

		    for ($i = 0; $i < $per_page_data; $i++){

			    // 클라이언트에게 보낼 카드리스트 인덱스로 카드 모아보기 정보를 검색한다.
			    // 카드 모아보기에 필요한 데이터
			    // 정치인 사진 경로
			    // 정치인 이름
			    // 정치인 정당 인덱스
			    // 당선 지역
			    // 카테고리
			    $politician_pledge_result = $this->db->query("SELECT
                idx, kr_name, party_idx, profile_image_url, elect_area, category
                FROM Politician where idx = '$card_array[$i]'")->result();

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

		    }
	    }

	    // 요청한 페이지가 2페이지 이상이고, 현재 보고있는 idx를 받아야함.
	    else{

		    for ($i = $per_page_data * ($request_page_idx-1); $i < $per_page_data * ($request_page_idx-1) + $per_page_data; $i++){

		    	// 정치인 카드 갯수가 모자란다면 반복을 종료
		    	if($i >= count($card_array)){
			    	break;
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
                FROM Politician where idx = '$card_array[$i]'")->result();

			    foreach ($politician_pledge_result as $row) {

				    // 카드 하나에 들어있는 데이터
				    $card_data = array();

				    // 정당 인덱스로 정당의 이름을 조회
				    $party_select_result = $this->db->query("SELECT
                    party_name
                    FROM Party where idx = '$row->party_idx'")->row();

				    // 정치인 카드에 들어갈 정보
				    $card_data['politician_idx'] = $row->idx;
				    $card_data['kr_name'] = $row->kr_name;
				    $card_data['party_name'] = $party_select_result->party_name;
				    $card_data['elect_area'] = $row->elect_area;
				    $card_data['profile_image_url'] = $row->profile_image_url;
				    $card_data['category'] = $row->category;

				    // 정치인 카드 리스트에 추가
				    array_push($card_list,$card_data);
			    }
		    }
	    }
	    // 사용할 카드 덱의 번호
	    $response_data['rand_card_idx'] = $current_rand_idx;
	    // 카드의 갯수
	    $response_data['card_num'] = count($card_list);
	    // 현재 보고있는 페이지
	    $response_data['current_page'] = $request_page_idx;
	    // 총 페이지
	    $response_data['total_page'] = ceil($total_card/$per_page_data);
	    // 카드 정보
	    $response_data['card_list'] = $card_list;
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
    // input : 정치인 인덱스
    // output : 정치인 사진경로, 공약 이행률, 카테고리, 이름, 정당이름, 약력
    public function getInfo($data)
    {
        // 클라이언트에게 응답 해줄 데이터
        $response_data = array();

        // 정치인 인덱스
        $politician_idx = $data['idx'];
		if($politician_idx==null) return 'invalid_data_[idx]';

        // 정치인 기본 정보 조회 - 인덱스, 정당인덱스, 약력, 카테고리, 정치인 사진 경로
        $politician_select_result = $this->db->query("SELECT
                * FROM Politician where idx = '$politician_idx'")->row();

        // 정치인 조회 결과가 없음
        if($politician_select_result == null){
	        $response_data['result'] = "정치인 정보가 없습니다.";
        	return json_encode($response_data);
        }

        // 정치인 조회 결과가 있음
		else{
			// 정치인 인덱스로 정당 테이블에 있는 정당 이름 조회
			$party_select_result = $this->db->query("SELECT party_name FROM Party where idx = '$politician_idx'")->row();

			$response_data['kr_name'] = $politician_select_result->kr_name;
			$response_data['ch_name'] = $politician_select_result->ch_name;
			$response_data['en_name'] = $politician_select_result->en_name;
			$response_data['party_name'] = $party_select_result->party_name;
			$response_data['office_number'] = $politician_select_result->office_number;
			$response_data['history'] = $politician_select_result->history;
			$response_data['profile_image_url'] = $politician_select_result->profile_image_url;
			$response_data['affiliation_committee'] = $politician_select_result->affiliation_committee;
			$response_data['email_id'] = $politician_select_result->email_id;
			$response_data['email_address'] = $politician_select_result->email_address;
			$response_data['aide'] = $politician_select_result->aide;
			$response_data['secretary'] = $politician_select_result->secretary;
			$response_data['category'] = $politician_select_result->category;
			$response_data['elect_generation'] = $politician_select_result->elect_generation;
			$response_data['elect_area'] = $politician_select_result->elect_area;

			// 정치인 테이블에서 정치인 인덱스로 정치인 공약 모음 테이블에 있는 공약을 조회
			// 공약 이행률 계산
			// 공약 전체 갯수 : 공약 데이터중 정치인 테이블에서 정치인 인덱스가 포함된 데이터 찾기
			// 공약 이행 갯수 : 공약 이행 과정 - 완전이행 된것만 찾음
			// 공약 이행률 = (공약 이행 갯수 / 공약 전체 갯수) * 100
			$politician_pledge_select_result = $this->db->query("SELECT pledge_implement_status FROM PoliticianPledge where politician_idx = '$politician_idx'")->result();

			// 공약 전체 갯수
			$pledge_total = count($politician_pledge_select_result);

			$success_pledge = 0;    // 완전이행
			$part_success_pledge = 0; // 부분이행
			$retreat_pledge = 0; // 퇴각이행
			$not_execute_pledge = 0; // 미이행
			$impossible_judge = 0; // 판단불가

			foreach ($politician_pledge_select_result as $row) {
				$pledge_status = $row->pledge_implement_status;

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

			return json_encode($response_data);
		}
    }

    // 정치인 관련 뉴스정보 요청
    // input : 정치인 인덱스
    // output : 뉴스제목, 뉴스날짜, 뉴스 링크 경로
    public function getNews($data)
    {
	    // 클라에게 보내줄 응답 데이터
	    $response_data = array();

        // 정치인 이름
        $politician_idx = $data['idx'];

	    // 정치인 조회 결과가 없음
	    if($politician_idx == null) return 'invalid_data_[idx]';

        // 정치인 관련 뉴스 조회
        $politician_select_result = $this->db->query("SELECT
                title, `date`, url
                FROM News where politician_idx = '$politician_idx'")->result();

        $result_num = count($politician_select_result);
        $response_data['result_num'] = $result_num;
        $response_data['data'] = $politician_select_result;

        return json_encode($response_data);

    }


    // 정치인 공약 정보 요청
    // input : 정치인 인덱스
    // output : 공약내용, 공약대수, 공약 이행 상태
    public function getPledgeInfo($data){

	    // 클라에게 보내줄 응답 데이터
	    $response_data = array();

        // 정치인 인덱스
        $politician_idx = $data['idx'];

	    // 정치인 조회 결과가 없음
	    if($politician_idx == null) return 'invalid_data_[idx]';

        // 정치인 인덱스로 정치인 공약 모음 - 당선대수, 당선지역
        $politician_select_result = $this->db->query("SELECT
                elect_generation, elect_area, kr_name
                FROM Politician where idx = '$politician_idx'")->row();

        // 당선 대수
	    $elect_generation = explode(',',$politician_select_result->elect_generation);

	    // 당선 지역
	    $elect_area = explode(',',$politician_select_result->elect_area);

        for ($i = 0 ; $i < count($elect_generation); $i++){

        	$pledge_data = array();

	        // 정치인 공약 모음에서 대수, 공약이행상태, 내용을 가져온다.
	        $politician_pledge_select_result = $this->db->query("SELECT
                pledge_implement_status, content, create_at, update_at
                FROM PoliticianPledge where 
                politician_idx = '$politician_idx' and 
                generation = '$elect_generation[$i]'")->result();


	        $pledge_data['elect_generation'] = $elect_generation[$i];
	        $pledge_data['elect_area'] = $elect_area[$i];
	        $pledge_data['pledge_data'] = $politician_pledge_select_result;
	        $response_data['kr_name'] = $politician_select_result->kr_name;
	        $response_data[''.$i] = $pledge_data;
        }

        return json_encode($response_data);
    }


    // 정치인 응원하기 댓글
	// input : 정치인 이름
	// output : 정치인 사진 경로, 댓글
    public function getComments($data){

	    // 정치인 이름
	    $politician_name = $data['kr_name'];

	    // 정치인 이름으로 정치인 인덱스 찾기
	    $politician_select_result = $this->db->query("SELECT
                idx, profile_image_url
                FROM Politician where kr_name = '$politician_name'")->row();

	    // 정치인 인덱스
	    $politician_idx = $politician_select_result->idx;


    }

}


// 사용하지 않는 메서드

// 정치인 상세 정보 요청
// input : 정치인 이름
// output : 당선대수, 당선횟수, 당선지역, 소속위원회, 약력, 연락처
//public function getDetailInfo($data){
//
//	// 정치인 이름
//	$politician_idx = $data['idx'];
//
//	// 정치인 조회 결과가 없음
//	if($politician_idx == null) return 'invalid_data_[idx]';
//
//	// 정치인 이름으로 상세정보 찾기
//	$politician_select_result = $this->db->query("SELECT
//                kr_name, ch_name, en_name, office_number, history, profile_image_url, affiliation_committee,
//                email_id, email_address, aide, secretary, elect_generation, elect_area
//                FROM Politician where idx = '$politician_idx'")->row();
//
//	// 클라에게 보내줄 응답 데이터
//	$response_data = array();
//
//	$response_data['result'] = $politician_select_result;
//
//	return json_encode($response_data);
//
//}
