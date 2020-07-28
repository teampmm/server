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

    public function getKingInfo($page, $card_num, $king, $token_data){
       if ($king == "나이왕"){
           return $this->returnAgeKing($page, $card_num, $token_data);
       }
       elseif ($king == "발의왕"){
            return $this->returnProposeKing($page, $card_num, $token_data);
       }
       elseif ($king == "인기왕"){
           return $this->returnPopularKing($page, $card_num, $token_data);
       }
       elseif ($king == "법안폐기왕"){
           return $this->returnDisposalKing($page, $card_num, $token_data);
       }
       elseif ($king == "다선왕"){

       }
       elseif ($king == "정당이적왕"){

       }
       elseif ($king == "공포왕"){

       }
       elseif ($king == "정당 - 사회분야왕 등등"){

       }

    }

    // 나이왕 정보 반환
    /**
     * @param $card_num
     * @param $current_page
     * @param $total_page
     * @param $idx
     * @param $kr_name
     * @param $birthday
     * @param $image_url
     */
    public function returnAgeKing($page, $card_num, $token_data){

        $sql = "select idx, kr_name, birthday from Politician ORDER BY birthday ASC";
        $politician_info = $this->db->query($sql)->result();

        $sql = "select idx, kr_name, birthday from Politician ORDER BY birthday ASC limit 1";
        $politician_age_top_king = $this->db->query($sql)->row();

        $sql = "select idx, kr_name, birthday from Politician ORDER BY birthday DESC limit 1";
        $politician_age_last_king = $this->db->query($sql)->row();

        $response_data = array();

        $age_top_array = array();
        $age_top_array['ranking'] = "top";
        $age_top_array['idx'] = (int)$politician_age_top_king->idx;

        $sql = "select party_idx from PoliticianPartyHistory where politician_idx = ? and end_day is null";
        $politician_party_info = $this->db->query($sql, array((int)$politician_age_top_king->idx))->row();

        $sql = "select * from Party where idx = ?";
        $party_info = $this->db->query($sql, array($politician_party_info->party_idx))->row();

        $age_top_array['kr_name'] = $politician_age_top_king->kr_name;
        $age_top_array['party_idx'] = (int)$politician_party_info->party_idx;
        $age_top_array['party_name'] = $party_info->name;
        $age_top_array['birthday'] = (int)$politician_age_top_king->birthday;
        $age_top_array['image_url'] = "http://politicsking.com/files/images/politician_thumbnail/" . (string)$politician_age_top_king->idx . ".jpg";

        if($token_data != null){
            $sql = "select count(idx) as cnt from BookMark where user_idx = ? and politician_idx = ?";
            $top_king_bookmark_info = $this->db->query($sql, array((int)$token_data->idx, $politician_age_top_king->idx))->row()->cnt;
            if ($top_king_bookmark_info == 1) {$age_top_array['bookmark'] = true;}
            elseif ($top_king_bookmark_info == 0) {$age_top_array['bookmark'] = false;}
        }

        $age_last_array = array();
        $age_last_array['ranking'] = "last";
        $age_last_array['idx'] = (int)$politician_age_last_king->idx;

        $sql = "select party_idx from PoliticianPartyHistory where politician_idx = ? and end_day is null";
        $politician_party_info = $this->db->query($sql, array((int)$politician_age_last_king->idx))->row();

        $sql = "select * from Party where idx = ?";
        $party_info = $this->db->query($sql, array($politician_party_info->party_idx))->row();

        $age_last_array['kr_name'] = $politician_age_last_king->kr_name;
        $age_last_array['party_idx'] = (int)$politician_party_info->party_idx;
        $age_last_array['party_name'] = $party_info->name;
        $age_last_array['birthday'] = (int)$politician_age_last_king->birthday;
        $age_last_array['image_url'] = "http://politicsking.com/files/images/politician_thumbnail/" . (string)$politician_age_last_king->idx . ".jpg";

        if($token_data != null){
            $sql = "select count(idx) as cnt  from BookMark where user_idx = ? and politician_idx = ?";
            $last_king_bookmark_info = $this->db->query($sql, array((int)$token_data->idx, $politician_age_top_king->idx))->row()->cnt;
            if ($last_king_bookmark_info == 1){$age_last_array['bookmark'] = true;}
            elseif ($last_king_bookmark_info == 0) {$age_last_array['bookmark'] = false;}
        }

        $total_page = (int)ceil(count($politician_info) / $card_num);
        if ($total_page == 0) $total_page = 1;

        $card_list = array();
        $i = $card_num * ($page - 1);
        while (true) {
            $card_data = array();

            if ($i >= count($politician_info))
                break;

            $card_data['idx'] = (int)$politician_info[$i]->idx;

            $sql = "select party_idx from PoliticianPartyHistory where politician_idx = ? and end_day is null";
            $politician_party_info = $this->db->query($sql, array((int)$politician_info[$i]->idx))->row();

            $sql = "select * from Party where idx = ?";
            $party_info = $this->db->query($sql, array($politician_party_info->party_idx))->row();

            $card_data['kr_name'] = $politician_info[$i]->kr_name;
            $card_data['party_idx'] = (int)$politician_party_info->party_idx;
            $card_data['party_name'] = $party_info->name;
            $card_data['birthday'] = (int)$politician_info[$i]->birthday;
            $card_data['image_url'] = "http://politicsking.com/files/images/politician_thumbnail/" . (string)$politician_info[$i]->idx . ".jpg";

            if($token_data != null){
                $sql = "select count(idx) as cnt  from BookMark where user_idx = ? and politician_idx = ?";
                $all_king_bookmark_info = $this->db->query($sql, array((int)$token_data->idx, $politician_info[$i]->idx))->row()->cnt;
                if ($all_king_bookmark_info == 1){$card_data['bookmark'] = true;}
                elseif ($all_king_bookmark_info == 0) {$card_data['bookmark'] = false;}
            }

            $i = $i + 1;
            array_push($card_list, $card_data);
            if (count($card_list) == $card_num) {
                break;
            }
        }

        $propose_king_top_last = array();

        if($age_top_array != null){
            array_push($propose_king_top_last,$age_top_array);
            if ($age_last_array != null){
                array_push($propose_king_top_last,$age_last_array);
            }
        }
        else{
            header("HTTP/1.1 204 ");
            return;
        }

        $response_data['card_num'] = (int)$card_num;
        $response_data['current_page'] = (int)$page;
        $response_data['total_page'] = (int)$total_page;
//        $response_data['최고령'] = $age_top_array;
//        $response_data['최연소'] = $age_last_array;
        $response_data['age_king_top_last'] = $propose_king_top_last;
        $response_data['age_king_info'] = $card_list;

        return json_encode($response_data, JSON_UNESCAPED_UNICODE);

    }

    // 발의왕 정보 반환
    public function returnProposeKing($page, $card_num, $token_data){

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

        $response_data = array();

        $total_page = (int)ceil(count($politician_result) / $card_num);
        if ($total_page == 0) $total_page = 1;

        $propose_king_data = array();
        $i = $card_num * ($page - 1);
        while (true) {

            if ($i >= count($politician_result))
                break;

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

            $card_data = array(
                'politician_idx' => (int)$politician_idx,
                'politician_kr_name' => $politician_kr_name,
                'politician_image_url' => $politician_image_url,
                'party_idx' => (int)$party_idx,
                'party_name' => $party_name,
                'bill_count' => (int)$bill_count,
                'subscription_num' => (int)$subscription_num,
            );

            array_push($propose_king_data, $card_data);

            $i = $i + 1;

            if (count($propose_king_data) == $card_num) {
                break;
            }
        }
        $propose_king_1_2_3 = array();

        if($propose_king_data != null){
            if ($propose_king_data[0] != null) array_push($propose_king_1_2_3,$propose_king_data[0]);
            if($propose_king_data[1] != null) array_push($propose_king_1_2_3,$propose_king_data[1]);
            if($propose_king_data[2] != null) array_push($propose_king_1_2_3,$propose_king_data[2]);
        }
        else{
            header("HTTP/1.1 204 ");
            return;
        }

        $response_data['card_num'] = (int)($card_num);
        $response_data['current_page'] = (int)$page;
        $response_data['total_page'] = (int)$total_page;
        $response_data['popular_king_1_2_3'] = $propose_king_1_2_3;
        $response_data['propose_king_info'] = $propose_king_data;
        return json_encode($response_data);

    }

    // 인기왕 정보 반환 - 사용자의 북마크 정보에서 정치인 인덱스 데이터를 가져온다
    public function returnPopularKing($page, $card_num, $token_data){

        $response_data = array();
        $popular_king_data = array();

        $sql = "select politician_idx from BookMark";
        $politician_info = $this->db->query($sql)->result();

        $popular_politician_array = array();

        foreach ($politician_info as $row){
            if ($row->politician_idx != null){
                array_push($popular_politician_array, (int)$row->politician_idx);
            }
        }

        $popular_politician_count_array = array_count_values($popular_politician_array);

        // 배열 내림차순 정렬 / 오름차순 정렬은 - asort
        arsort($popular_politician_count_array);

        $total_page = (int)ceil(count($popular_politician_array) / $card_num);
        if ($total_page == 0) {$total_page = 1;}

        $min_i = $card_num * ($page - 1);
        $max_i = $card_num * ($page);
        $for_idx = 0;
        foreach ($popular_politician_count_array as $politician_idx => $count){

            if (($min_i <= $for_idx) and ($for_idx < $max_i)){

                if ($for_idx >= count($popular_politician_array))
                    break;

                $sql = "select * from Politician where idx = ?";
                $politician_info = $this->db->query($sql, array($politician_idx))->row();

                $sql = "select party_idx from PoliticianPartyHistory where politician_idx = ? and end_day is null";
                $politician_party_info = $this->db->query($sql, array($politician_idx))->row();

                $sql = "select * from Party where idx = ?";
                $party_info = $this->db->query($sql, array($politician_party_info->party_idx))->row();


                $temp = array(
                    "idx" => $politician_idx,
                    "kr_name" => $politician_info->kr_name,
                    "subscribe_num" => $count,
                    "party_idx" => $politician_party_info->party_idx,
                    "party_name" => $party_info->name,
                    "image_url" => "http://politicsking.com/files/images/politician_thumbnail/" . (string)$politician_idx . ".jpg"
                );

                array_push($popular_king_data, $temp);

                if (count($popular_king_data) == (int)$card_num) {
                    break;
                }

            }
            $for_idx = $for_idx + 1;
        }

        $popular_king_1_2_3 = array();

        if($popular_king_data != null){
            if ($popular_king_data[0] != null) array_push($popular_king_1_2_3, $popular_king_data[0]);
            if($popular_king_data[1] != null) array_push($popular_king_1_2_3,$popular_king_data[1]);
            if($popular_king_data[2] != null) array_push($popular_king_1_2_3,$popular_king_data[2]);
        }
        else{
            header("HTTP/1.1 204 ");
            return;
        }

        $response_data['card_num'] = (int)($card_num);
        $response_data['current_page'] = (int)$page;
        $response_data['total_page'] = (int)$total_page;
        $response_data['popular_king_1_2_3'] = $popular_king_1_2_3;
        $response_data['popular_king_info'] = $popular_king_data;

        return json_encode($response_data);

    }

    // 법안폐기왕
    public function returnDisposalKing($page, $card_num, $token_data){
        return "hi";
    }

}

