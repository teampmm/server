
<?php

    function JsonNullCheck($json,$check_array){
        foreach ($check_array as $check_data){

            if (empty($json[$check_data])){
                return 'invaild_data_'.$check_data;
            }
        }
        return null;
    }



