<?php
/*
 * 법안 모아보기 법안 상세보기 등
 * 법안에 관련된 DB를 참조 하는데 사용하는 모델
 * */

class Bill_Model extends CI_Model
{
    function __construct()//생성자, 제일 먼저 실행되는 일종의 초기화
    {
        parent::__construct();
    }

    //법안 모아보기 페이지 데이터

    /* 출력데이터
{
    "total": 10,                                                            //총 페이지수 페이징을 위함
    "info": [
        {
            "bill_idx": "14",                                               //법안의 인덱스
            "name": "name 014",                                               //법안명
            "progress_status": "progress_status 014",                       //법안 진행 상태
            "proposal_date": "2020-04-29 01:49:06",                         //제안 날짜
            "committee_idx": "idx14",                                       //위원회 idx
            "committee_name": "name 014",                                   //위원회 이름
            "proclamation_number":"123123",                                 //공포번호
            "proposer": [                                                   //발의에 참여한 사람들 리스트
                {
                    "idx": "14",                                            //정치인 idx
                    "kr_name": "kr_name 014",                               //정치인 이름
                    "representative": "14",                                 //1이면 대표발의 0이면 공동발의
                    "party_idx": "14",                                      //정당idx
                    "party_name": "party_name 014"                          //정당이름
               }
            ]
        }, ...........
    }
 }
     * */


    function billPageList($index)
    {
        //모아보기 메인 == index로 들어왔을때
        if ($index == NULL) {
            $index = 0;
        } else {
            $index = ($index - 1) * 10;
        }
        $result = array();
        //법안 모아보기 정보
        $bill_info = $this->db->query("select  idx,name,committee_idx,progress_status,proposal_date,proclamation_number from Bill order by proposal_date desc limit $index, 10")->result();
        //페이징을 위한 총 페이지수
        $bill_total_rows = $this->db->query("select count(idx) as total from Bill ")->row();
        $result['total_page'] = ceil($bill_total_rows->total / 10);
        $bill_array = array();
        foreach ($bill_info as $row) {

            $bill_data = array();
            $bill_data['bill_idx'] = $row->idx;
            $bill_data['name'] = '법안명' . $row->name;
            $bill_data['progress_status'] = '법안진행상태' . $row->progress_status;
            $bill_data['proposal_date'] = '날짜' . $row->proposal_date;
            $bill_data['proclamation_number'] = '공포번호' . $row->proclamation_number;
            //법안을 발의한 위원회
            $bill_data['committee_idx'] = '위원회idx' . $row->committee_idx;
            //위원회 idx로 위원회 이름 얻기
            $committee = $this->db->query("select name from Committee where idx =$row->committee_idx")->row();
            $bill_data['committee_name'] = '위원회이름' . $committee->name;


            //법안인덱스로 -> 법안을 발의한 정치인들의 정보를 반한다  //정치인 idx , 정치인 이름 , 대표발의여부 , 정당인덱스, 정당이름
            $bill_data['proposers'] = $this->billIndexToPoliticians($row->idx);


            array_push($bill_array, $bill_data);

        }
        $result['info'] = $bill_array;
        return json_encode($result);
    }

    function billInfoData($index)
    {
        $result = array();
        //데이터 1개만 가져옴 나중에 row로 바꿔야할듯
        $bill_rows = $this->db->query("select * from Bill where idx=$index")->result();
        foreach ($bill_rows as $row) {
            $bill_data = array();
            $bill_data['idx'] = $row->idx;
            $bill_data['bill_name'] = $row->name;
            //위원회 idx
            $bill_data['committee_idx'] = $row->committee_idx;
            //위원회 idx로 위원회 이름 얻기
            $committee = $this->db->query("select name from Committee where idx =$row->committee_idx")->row();
            $bill_data['committee_name'] = $committee->name;

            //법안인덱스로 -> 법안을 발의한 정치인들의 정보를 반한다  //정치인 idx , 정치인 이름 , 대표발의여부 , 정당인덱스, 정당이름
            $bill_data['proposers'] = $this->billIndexToPoliticians($row->idx);
            //법안인덱스로 해당 법안에 참여한 인원은 몇명이고 찬성,반대,기권,불참한 정치인들의 idx를 가져옴
            $result['bill_Vote']=$this->billIndexToBillVote($row->idx);
            //예고기간 시작,종료
            $bill_data['notice_period_start'] = $row->notice_period_start;
            $bill_data['notice_period_end'] = $row->notice_period_end;
            //진행상태
            $bill_data['progress_status'] = $row->progress_status;
            //회부일
            $bill_data['reject_day'] = $row->reject_day;
            //상정일
            $bill_data['pass_day'] = $row->pass_day;
            //처리일
            $bill_data['disposal_day'] = $row->disposal_day;
            //공포일
            $bill_data['proclamation_day'] = $row->proclamation_day;
            //원문 hwp링크주소
            $bill_data['hwp_url'] = $row->hwp_url;
            //원문 pdf링크주소
            $bill_data['pdf_url'] = $row->pdf_url;
            //공포 법률 링크주소
            $bill_data['proclamation_law_url'] = $row->proclamation_law_url;
            //요약내용
            $bill_data['summary_content'] = $row->summary_content;
            //공포번호
            $bill_data['proclamation_number'] = $row->proclamation_number;
            //제안날짜
            $bill_data['proposal_date'] = $row->proposal_date;
            //법안번호
            $bill_data['bill_number'] = $row->proposal_date;

            $result['bill_info'] = $bill_data;


        }
        return json_encode($result);

    }

    //법안인덱스로 -> 법안을 발의한 정치인들의 정보를 반한다  //정치인 idx , 정치인 이름 , 대표발의여부 , 정당인덱스, 정당이름
    private  function billIndexToPoliticians($bill_idx)
    {
        $proposer = $this->db->query("select politician_idx,representative from Proposer where bill_idx=$bill_idx")->result();
        $proposer_array = array();
        foreach ($proposer as $proposerRow) {
            $proposer_data = array();
            $politicians = $this->db->query("select idx,party_idx,kr_name from Politician where idx=$proposerRow->politician_idx")->row();
            $proposer_data['idx'] = $politicians->idx;
            $proposer_data['kr_name'] = $politicians->kr_name;
            $proposer_data['representative'] = $proposerRow->representative;
            //정당인덱스로 정당 이름 찾기
            $party = $this->db->query("select idx,party_name from Party where idx=$politicians->party_idx")->row();
            $proposer_data['party_idx'] = $party->idx;
            $proposer_data['party_name'] = $party->party_name;

            array_push($proposer_array, $proposer_data);
        }
        return $proposer_array;
    }


    //법안인덱스로 해당 법안에 참여한 인원은 몇명이고 찬성,반대,기권,불참한 정치인들의 idx를 가져옴
    private function billIndexToBillVote($index){
        $result=array();
//        $total_array=array();
        $vote_row=$this->db->query("select * from BillVote where bill_idx=$index")->row();
        $result['total']=$vote_row->total.'투표참여한 총 인원';

        //찬성한사람
        $agreement=explode("|",$vote_row->agreement);
        $result['찬']=$this->votePerson($agreement);
        //반대한사람
        $opposition=explode("|",$vote_row->opposition);
        $result['반']=$this->votePerson($opposition);
        //기권한사람
        $abstention=explode("|",$vote_row->abstention);
        $result['기']=$this->votePerson($abstention);
        //불참한사람
        $absence=explode("|",$vote_row->absence);
        $result['불']=$this->votePerson($absence);



        return $result;


    }
    //input으로 찬성,반대,기권,불참 한 사람들의 배열이 들어옴
    //해당 인덱스를 가지고 상세한 정보 반환
    private  function votePerson($input){
        $result_array=array();
        foreach ($input as $key => $value) {
            if ($key==count($input)-1){
                break;
            }
            else{
                $politician_data=array();
                $value=(int)$value;

                $politician_info=$this->db->query("select idx,party_idx,kr_name from Politician where idx=$value")->row();
                $politician_data['idx']=$politician_info->idx;
                $politician_data['party_idx']=$politician_info->party_idx;
                $politician_data['kr_name']=$politician_info->kr_name;
                array_push($result_array,$politician_data);
            }
        }
        return $result_array;
    }
}