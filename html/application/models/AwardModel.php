<?php

include_once 'OptionModel.php';

class AwardModel extends CI_Model
{
    public $option_model;


    public function __construct()
    {
        parent::__construct();
        $this->option_model = new OptionModel();
    }

    /**
     * 메인페이지에서 보여줄 모든 왕에 대한 정보를 응답해줘야한다.
     * 발의왕
     * 출석왕
     * 인기왕
     * 등등
     *
     * 발의왕에 대한 코드가 구현이 되어서 응답이 된다면, 아래 구현완료란에 발의왕이라고 작성해둔다.
     *
     * 구현 완료 :
     *
     */

    public function getAllKingInfo(){

        $sql = "select idx from Politician";
        $politician_result = $this->db->query($sql)->result();

        // 정치인 인덱스와 법안 발의 갯수가 들어감
        $total_array = array();

        foreach($politician_result as $politician_idx){


            $representative_sql = "select count(idx) as cnt from BillProposer where representative_idx = ?";
            $together_sql = "select count(idx) as cnt from BillProposer where together_idx = ?";

            $representative_count = $this->db->query($representative_sql, array((int)$politician_idx->idx))->row()->cnt;
            $together_count = $this->db->query($together_sql, array($politician_idx->idx))->row()->cnt;

            $bill_count = (int)$representative_count + (int)$together_count;

            $politician_bill_count = array('politician_idx' => (int)$politician_idx->idx, 'bill_count' => (int)$bill_count);
            array_push($total_array, $politician_bill_count);
        }

        // 법안을 많이 발의한 순으로 내림차순 정렬함
        $bill_sort_data = $this->arr_sort($total_array,'bill_count','desc');

        // top 3에 대한 정보를 가지고 찾아야 하는 것들
        // 정치인 인덱스, 정치인 이름, 정치인 정당인덱스, 정당이름
        // 구독자수, 법안 발의 갯수, 정치인 사진경로

        $response_data = array();

        for($i = 0; $i < 3; $i++){


            $politician_info_sql = "select * from Politician where idx = ?";
            $politician_party_sql = "select * from PoliticianPartyHistory where politician_idx = ? and end_day is null ";
            $bookmark_sql = "select count(*) as cnt from BookMark where idx = ?";

            $politician_info_result = $this->db->query($politician_info_sql, array((int)$bill_sort_data[$i]['politician_idx']))->row();
            $politician_party_result = $this->db->query($politician_party_sql, array((int)$bill_sort_data[$i]['politician_idx']))->row();
            $bookmark_result = $this->db->query($bookmark_sql, array((int)$bill_sort_data[$i]['politician_idx']))->row();

            $politician_idx = (int)$bill_sort_data[$i]['politician_idx']; # 정치인 인덱스
            $politician_image_url = 'http://politicsking.com/files/images/politician_thumbnail/'.$politician_idx.'.jpg'; # 정치인 사진 경로
            $politician_kr_name = $politician_info_result->kr_name; # 정치인 이름
            $party_idx = (int)$politician_party_result->party_idx; # 정당 인덱스

            $party_sql = "select name from Party where idx = ?";
            $party_result = $this->db->query($party_sql, array($party_idx))->row();
            $party_name = $party_result->name; # 정당이름

            $subscription_num = $bookmark_result->cnt; # 구독자수
            $bill_count = $bill_sort_data[$i]['bill_count']; # 법안 발의갯수

            $temp = array(
                'ranking' => (int)($i+1),
                'politician_idx' => (int)$politician_idx,
                'politician_kr_name' => $politician_kr_name,
                'politician_image_url' => $politician_image_url,
                'party_idx' => (int)$party_idx,
                'party_name' => $party_name,
                'bill_count' => (int)$bill_count,
                'subscription_num' => (int)$subscription_num,
            );

            array_push($response_data, $temp);
        }
        return json_encode($response_data);
    }

    // 2차원 배열 정렬 하기
    /** ex
     [
        {
        "bill_idx":1,
        "bill_count":463
        },
        {
        "bill_idx":2,
        "bill_count":1162
        },
     ]
     */
   function arr_sort($array, $key, $sort='asc') //정렬대상 array, 정렬 기준 key, 오름/내림차순
    {
        $keys = array();
        $vals = array();
        foreach ($array as $k=>$v)
        {
            $i = $v[$key].'.'.$k;
            $vals[$i] = $v;
            array_push($keys, $k);
        }
        unset($array);
        if ($sort=='asc') {
            ksort($vals);
        } else {
            krsort($vals);
        }
        $ret = array_combine($keys, $vals);
        unset($keys);
        unset($vals);
        return $ret;

    }

}

