<?php

class PoliticianModel extends CI_Model
{

	function __construct()//생성자, 제일 먼저 실행되는 일종의 초기화
	{
		parent::__construct();
	}

	// 정치인 카드 모아보기 정보
	public function getPoliticianCard($request_page, $random_card_idx, $token_data){

        /** 클라이언트가 요청한 대수 - 지금은 하드코딩 / 대수 : 20 으로 되었있다.*/
        $elect_generation = 20;

	    // 클라이언트가 첫 페이지 요청할때, 덱 번호가 정해져 있지 않아서 -1값으로 요청이 들어온다.
	    $RANDOM_CARD_INIT_DATA = -1;

		// 응답 데이터
		$response_data = array();

		// 한 페이지에 보여줄 카드의 갯수
		$per_page_data = 8;

		// 사용자가 가지고 있는 랜덤 카드 인덱스 - RandomCard 인덱스
		$current_rand_idx = $random_card_idx;

		// 첫페이지 요청
		if($current_rand_idx == $RANDOM_CARD_INIT_DATA){
			// 카드 인덱스, 카드 정보 가져오기 ( 정치인 인덱스가 들어있음 )
			$random_card_select_result = $this->db->query("select idx, card_number from RandomCard ORDER BY rand() limit 1")->row();
			$current_rand_idx = $random_card_select_result->idx;
		}

        // 첫페이지 이상 요청
        else{
			$random_card_select_result = $this->db->query("select idx, card_number from RandomCard where idx = '$current_rand_idx'")->row();
		}

		// 카드 정보 ( 정치인 인덱스가 들어있음 )
		$card_number = $random_card_select_result->card_number;

		// 문자열 제일 앞, 뒤에 있는 큰따옴표 제거
		$card_number = str_replace( "\"","", $card_number); // 쌍따옴표 제거

		// 카드 데이터 -> 카드 배열로 변환
		$card_number = explode(",", $card_number);

		// 카드 모음 리스트
		$card_list = array();

        // 북마크한 정치인의 인덱스를 담을 배열
        $book_mark_array = array();


        // 사용자가 로그인 했을때만 북마크 정보 저장
        if($token_data != null){
            // jwt 토큰에서 받은 아이디
                $user_idx = $token_data->idx;

                //사용자의 인덱스로 찾은 정치인 북마크 인덱스
                $book_mark_select_result = $this->db->query("select politician_idx from BookMark where user_idx = '$user_idx'")->result();

                // 배열에 정치인 북마크 인덱스 담기
                for ($i = 0 ;$i < count($book_mark_select_result); $i++ ){
                    $book_mark_array[$i] = $book_mark_select_result[$i]->politician_idx;
                }
        }


		for ($i = $per_page_data * ($request_page-1); $i < $per_page_data * ($request_page-1) + $per_page_data; $i++){

			// 정치인 카드 갯수가 모자란다면 반복을 종료
			if($i >= count($card_number)){
				break;
			}

			// 클라이언트에게 보낼 카드리스트 인덱스로 카드 모아보기 정보를 검색한다.
			// 카드 모아보기에 필요한 데이터
			// 정치인 사진 경로
			// 정치인 이름
			// 정치인 정당 인덱스
			// 당선 지역
			// 카테고리
			$politician_result = $this->db->query("SELECT
                * FROM Politician where idx = '$card_number[$i]'")->result();

			foreach ($politician_result as $row) {

				// 카드 하나에 들어있는 데이터
				$card_data = array();

                // 사용자가 로그인했을때만 북마크 여부를 보여준다.
                if($token_data != null){
                    if (in_array($row->idx, $book_mark_array)){
                        $card_data['bookmark'] = true;
                    }
                    else{
                        $card_data['bookmark'] = false;
                    }
                }

                // 정치인 인덱스로 정치인 상세정보 조회 - 20대 ( 최신 ) 카드정보만 준다.
                $politician_info_result=$this->db->query("select * from PoliticianInfo where politician_idx=$row->idx and elect_generation = $elect_generation")->row();

                // 정치인 인덱스로 정당이름 조회
                $party_name_select_result = $this->db->query("SELECT * FROM PartyName where idx = $politician_info_result->party_idx")->row();
                $party_name = $party_name_select_result->name;

                // 정치인 카드에 들어갈 정보
				$card_data['politician_idx'] = $row->idx;
				$card_data['kr_name'] = $row->kr_name;
				$card_data['profile_image_url'] = $row->profile_image_url;
                $card_data['committee_name'] = $politician_info_result->committee_idx;
                $card_data['party_name'] = $party_name;

				$elect_generation_result=$this->db->query("select elect_generation from PoliticianInfo where politician_idx=$row->idx order by elect_generation asc")->result();

				if(count($elect_generation_result)==1){
                    $card_data['category'] = '#초선';

                }else{
				    $data='';
				    foreach ($elect_generation_result as $elect_row){
                        $data =$data.'#'.$elect_row->elect_generation.'대 ';
                    }
				$card_data['category'] = $data;
                }

				// 정치인 카드 리스트에 추가
				array_push($card_list,$card_data);
			}
		}

		// 사용할 카드 덱의 번호
		$response_data['rand_card_idx'] = (int)$current_rand_idx;
		// 카드의 갯수
		$response_data['card_num'] = count($card_list);
		// 현재 보고있는 페이지
		$response_data['current_page'] = $request_page;
		// 총 페이지
		$response_data['total_page'] = ceil(count($card_number)/$per_page_data);
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
	public function getInfo($politician_idx)
	{

		// 클라이언트에게 응답 해줄 데이터
		$response_data = array();

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
		    // Politician 테이블에서 조회한 데이터
            // 한글, 한자, 영어 이름 및 약력, 생년월일, 프로필 이미지 경로
            $response_data['politician_idx'] = $politician_select_result->idx;
            $response_data['kr_name'] = $politician_select_result->kr_name;
            $response_data['ch_name'] = $politician_select_result->ch_name;
            $response_data['en_name'] = $politician_select_result->en_name;
            $response_data['history'] = $politician_select_result->history;
            $response_data['birthday'] = $politician_select_result->birthday;
            $response_data['profile_image_url'] = $politician_select_result->profile_image_url;

            // 정치인 인덱스로 PoliticianInfo에서 조회한 데이터
            // 정치인인덱스, 소속위원회인덱스, 정당인덱스,
            // 선거대수, 선거구, 사무실번호, 이메일 아이디, 이메일 주소
            // 보좌관, 비서, 카테고리, 표결점수, 홈페이지

            /** 여기 아래 부터 작업해야함. */

            $politician_info_select_result = $this->db->query("SELECT
                * FROM PoliticianInfo where politician_idx = '$politician_idx' ")->result();

            $politician_detail_info = array();
            foreach ($politician_info_select_result as $info_row){
                // 정치인 정보를 담을 배열
                $temp = array();
                $temp['elect_generation'] = (int)$info_row->elect_generation;
                $temp['elect_area'] = $info_row->elect_area;
                $temp['committee_name'] = $info_row->committee_idx;

                if($info_row->party_idx != null){
                    $party_name_select_result = $this->db->query("SELECT * FROM PartyName where idx = $info_row->party_idx")->row();
                    $party_name = $party_name_select_result->name;
                    $temp['party_name'] = $party_name;
                }
                $temp['office_number'] = $info_row->office_number;
                $temp['email_id'] = $info_row->email_id;
                $temp['email_address'] = $info_row->email_address;
                $temp['aide'] = $info_row->aide;
                $temp['secretary'] = $info_row->secretary;
                $temp['category'] = $info_row->category;
                $temp['vote_score'] = $info_row->vote_score;
                array_push($politician_detail_info,$temp);
            }
            $response_data['politician_detail_info'] = $politician_detail_info;

//			// 정치인 테이블에서 정치인 인덱스로 정치인 공약 모음 테이블에 있는 공약을 조회
//			// 공약 이행률 계산
//			// 공약 전체 갯수 : 공약 데이터중 정치인 테이블에서 정치인 인덱스가 포함된 데이터 찾기
//			// 공약 이행 갯수 : 공약 이행 과정 - 완전이행 된것만 찾음
//			// 공약 이행률 = (공약 이행 갯수 / 공약 전체 갯수) * 100
//			$politician_pledge_select_result = $this->db->query("SELECT pledge_implement_status FROM PoliticianPledge where politician_idx = '$politician_idx'")->result();
//
//			// 공약 전체 갯수
//			$pledge_total = count($politician_pledge_select_result);
//
//			$success_pledge = 0;    // 완전이행
//			$part_success_pledge = 0; // 부분이행
//			$retreat_pledge = 0; // 퇴각이행
//			$not_execute_pledge = 0; // 미이행
//			$impossible_judge = 0; // 판단불가
//
//			foreach ($politician_pledge_select_result as $row) {
//				$pledge_status = $row->pledge_implement_status;
//
//				if ($pledge_status == "완전이행") {
//					$success_pledge++;
//				} else if ($pledge_status == "부분이행") {
//					$part_success_pledge++;
//				} else if ($pledge_status == "퇴각이행") {
//					$retreat_pledge++;
//				} else if ($pledge_status == "미이행") {
//					$not_execute_pledge++;
//				} else if ($pledge_status == "판단불가") {
//					$impossible_judge++;
//				}
//			}
//
//			// 공약 이행 데이터
//			$pledge_data = array();
//
//			$pledge_data['total_pledge_num'] = $pledge_total;
//			$pledge_data['success_pledge_num'] = $success_pledge;
//			$pledge_data['part_success_pledge_num'] = $part_success_pledge;
//			$pledge_data['retreat_pledge_num'] = $retreat_pledge;
//			$pledge_data['not_execute_pledge_num'] = $not_execute_pledge;
//			$pledge_data['impossible_judge_num'] = $impossible_judge;
//
//			$response_data['politician_pledge_data'] = $pledge_data;

			return json_encode($response_data);
		}
	}

	// 정치인 관련 뉴스정보 요청
	// input : 정치인 인덱스
	// output : 뉴스제목, 뉴스날짜, 뉴스 링크 경로
	public function getNews($politician_idx)
	{
		// 클라에게 보내줄 응답 데이터
		$response_data = array();

		// 정치인 관련 뉴스 조회
		$politician_select_result = $this->db->query("SELECT
                title, `date`, url
                FROM News where politician_idx = '$politician_idx'")->result();

		$result_num = count($politician_select_result);
		$response_data['result_num'] = (int)$result_num;
		$response_data['data'] = $politician_select_result;

		return json_encode($response_data);

	}

	// 정치인 공약 정보 요청
	// input : 정치인 인덱스
	// output : 공약내용, 공약대수, 공약 이행 상태
	public function getPledgeInfo($politician_idx){

	    return "정치인 공약 데이터 없음 - 데이터 수집 못했음 ( 데이터 없어서 수작업 해야 할 수도 있음 )";

		// 클라에게 보내줄 응답 데이터
		$response_data = array();

		// 정치인 인덱스로 정치인 공약 모음 - 당선대수, 당선지역
		$politician_select_result = $this->db->query("SELECT
                * FROM Politician where idx = '$politician_idx'")->row();

		// 당선 대수
		$elect_generation = explode(',',$politician_select_result->elect_generation);

		// 당선 지역
		$elect_area = explode(',',$politician_select_result->elect_area);

		// 공약 정보를 담을 배열
		$pledge_data_array = array();

		for ($i = 0 ; $i < count($elect_generation); $i++){

			$pledge_data = array();

			// 정치인 공약 모음에서 대수, 공약이행상태, 내용을 가져온다.
			$politician_pledge_select_result = $this->db->query("SELECT
                pledge_implement_status, content
                FROM PoliticianPledge where 
                politician_idx = '$politician_idx' and 
                generation = '$elect_generation[$i]'")->result();

			$pledge_data['elect_generation'] = $elect_generation[$i];
			$pledge_data['elect_area'] = $elect_area[$i];
			$pledge_data['pledge_data'] = $politician_pledge_select_result;
			$response_data['kr_name'] = $politician_select_result->kr_name;
			array_push($pledge_data_array,$pledge_data);
		}
		$response_data['pledge_data'] = $pledge_data_array;

		return json_encode($response_data);
	}

	// 정치인 북마크 클릭/해제
	public function postBookmarkModify($politician_idx, $token_data){

        // 클라에게 보내줄 응답 데이터
        $response_data = array();

        // 유저 인덱스
        $user_idx = $token_data->idx;
        if($token_data->idx=="토큰실패"){$result_json=array();$result_json['result']='로그인 필요';header("HTTP/1.1 401 ");return json_encode($result_json);}


        // 사용자가 해당 정치인을 북마크 했는지 여부 보기
        $bookmark_select_result = $this->db->query("SELECT
                count(idx) as `count`  FROM BookMark where 
                user_idx = '$user_idx' and politician_idx = '$politician_idx'")->row();

        // 해당 정치인을 북마크를 하고있었는데, 북마크 삭제 요청이 들어옴
        if ($bookmark_select_result->count == 1){
            $this->db->query("DELETE FROM BookMark WHERE user_idx = '$user_idx' and politician_idx = '$politician_idx'" );
            $response_data['result'] = '북마크 삭제';
            return json_encode($response_data);
        }

        // 해당 정치인을 북마크를 안하고 있었는데, 북마크 추가 요청이 들어옴
        else{
            $this->db->query("INSERT INTO BookMark VALUES 
                (null, $user_idx, null, $politician_idx, NOW(),NOW(),NOW())" );
            $response_data['result'] = '북마크 추가';
            return json_encode($response_data);
        }
    }

    public function getBookmark($politician_idx, $token_data){
        // 클라에게 보내줄 응답 데이터
        $response_data = array();
        if($token_data->idx=="토큰실패"){$result_json=array();$result_json['result']='로그인 필요';header("HTTP/1.1 401 ");return json_encode($result_json);}

            // 사용자 인덱스
            $user_idx = $token_data->idx;

            // 좋아요 싫어요 정보조회
            $result = $this->db->query("SELECT * , count(*) as `count` FROM BookMark where 
                user_idx = $user_idx and politician_idx = $politician_idx")->row();

            if($result->count == 0){
                $response_data['status'] = '조회된 데이터가 없습니다';
            }
            else{
                $response_data['status'] = '현재 북마크중';
            }
            return json_encode($response_data);
    }

    // 정치인 좋아요 싫어요 정보수정
    public function postUserEvaluation($politician_idx, $like_status, $token_data){
            // 클라에게 보내줄 응답 데이터
        if($token_data->idx=="토큰실패"){$result_json=array();$result_json['result']='로그인 필요';header("HTTP/1.1 401 ");return json_encode($result_json);}

        $response_data = array();

            // 클라이언트가 좋아요를 요청한경우
            if($like_status == "like"){
                $status = 1;
            }

            // 클라이언트가 싫어요를 요청한경우
            else{
                $status = 0;
            }

            // 사용자 인덱스
            $user_idx = $token_data->idx;

            // 사용자의 정치인에 대한 좋아요 데이터가 있는지 확인
            $bookmark_select_result = $this->db->query("SELECT
                count(idx) as `count`  FROM UserEvaluationBill where 
                user_idx = '$user_idx' and politician_idx = '$politician_idx'")->row();

            // 좋아요 싫어요 데이터가 있는 경우
            if ($bookmark_select_result->count == 1){

                // 사용자의 정치인 좋아요에 대한 데이터가 있는가?
                $result = $this->db->query("select count(*) as `count` from UserEvaluationBill where 
                user_idx = '$user_idx' and politician_idx = '$politician_idx'")->row();

                // 데이터가 같은 경우 - 데이터 삭제
                if($result->count == 1){
                    if ($status==1){
                        $result=$this->db->query("update UserEvaluationBill set status=1 where user_idx='$user_idx' and politician_idx='$politician_idx'");
                    }else if ($status==0){
                        $result=$this->db->query("update UserEvaluationBill set status=0 where user_idx='$user_idx' and politician_idx='$politician_idx'");
                    }
                }
            }

            // 좋아요 싫어요 데이터가 없는 경우
            else{
                $result = $this->db->query("INSERT INTO UserEvaluationBill VALUES 
                (null, null ,$politician_idx,$user_idx,$status)" );
            }

            $response_data['result'] = (boolean)$result;

        return json_encode($response_data);
    }

    // 정치인 좋아요 싫어요 정보 조회
    public function getUserEvaluation($politician_idx, $token_data){
        // 클라에게 보내줄 응답 데이터
        $response_data = array();

        // 사용자 인덱스
        $user_idx = $token_data->idx;
        if($token_data->idx=="토큰실패"){$result_json=array();$result_json['result']='로그인 필요';header("HTTP/1.1 401 ");return json_encode($result_json);}

        // 좋아요 싫어요 정보조회
        $result = $this->db->query("SELECT * , count(*) as `count` FROM UserEvaluationBill where 
                user_idx = $user_idx and politician_idx = $politician_idx")->row();

        if($result->count == 0){
            $response_data['status'] = '조회된 데이터가 없습니다';
        }
        else{
            $response_data['status'] = $result->status;
        }
        return json_encode($response_data);

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
