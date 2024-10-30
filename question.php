<?php
function question() {
	global $wpdb;
	global $options;
	global $capth_def_setting;
	if (isset($_REQUEST['show_in_setting'])) {
		$str_id = -1;
	} else {
		$hostname = $_SERVER['SERVER_NAME'];
		$user_ip = $_SERVER['REMOTE_ADDR'];
		// choose type_id
		$query_type = "SELECT type_id,info FROM ct_questiontype WHERE";
		$is_first_type = TRUE;
		if ('yes' == $options['question_type1']) {
			if ($is_first_type == FALSE) {
				$query_type .= " OR";
			}
			$query_type .= " type_id = 1 ";
			$is_first_type = FALSE;
		}

		if ('yes' == $options['question_type2']) {
			if ($is_first_type == FALSE) {
				$query_type .= " OR";
			}
			$query_type .= " type_id = 2 ";
			$is_first_type = FALSE;
		}

		if ('yes' == $options['question_type3']) {
			if ($is_first_type == FALSE) {
				$query_type .= " OR";
			}
			$query_type .= " type_id = 3 ";
			$is_first_type = FALSE;
		}
		if ('yes' == $options['question_type4']) {
			if ($is_first_type == FALSE) {
				$query_type .= " OR";
			}
			$query_type .= " type_id = 4 ";
			$is_first_type = FALSE;
		}
		if ('yes' == $options['question_type5']) {
			if ($is_first_type == FALSE) {
				$query_type .= " OR";
			}
			$query_type .= " type_id = 5 ";
			$is_first_type = FALSE;
		}
		
		$result = $wpdb -> get_results($query_type,ARRAY_A);
		$num_type = count($result);
		srand(make_seed($ctr.$key.($ctr+1)));
		// random type_id
		$rand_type =  rand(0,$num_type-1);
		
		// type_id = 5 call math()
		if ($result[$rand_type]['type_id'] == "5") {
			return array('info' =>$result[$rand_type]['info'], 'type_id'=>$result[$rand_type]['type_id']);
		}
		else {
	    $query = "SELECT * FROM ct_question WHERE type_id = {$result[$rand_type]['type_id']}";

		$row = $wpdb -> get_results($query, ARRAY_A);
		$num_ques = count($row);
		srand(make_seed($ctr.$key.($ctr+1)));
		$rand_ques =  rand(0,$num_ques-1);
		$str = $row[$rand_ques]['question'];
		/////เช็คว่าถ้าซ้ำให้ทับของเก่า

		$id = $row[$rand_ques]['question_id'];

		$query = "SELECT * FROM ct_user_question WHERE user_ip = '$user_ip' ";
		$check = $wpdb -> get_row($query, ARRAY_A);

		if ($wpdb -> num_rows) {
			$query = "DELETE FROM ct_user_question WHERE user_ip = '$user_ip'";
			$wpdb -> query($query);
		}

		$query = "INSERT INTO ct_user_question ( user_ip, question_id )
VALUES ( '$user_ip','$id' )";
		$wpdb -> query($query);

		$query = "SELECT * FROM ct_user_question WHERE user_ip = '$user_ip' ";
		$check = $wpdb -> get_row($query, ARRAY_A);
		$temp = $wpdb -> get_row($query, ARRAY_A);
		$str_id = $temp['id'];


		$query = "SELECT info FROM ct_questiontype WHERE type_id = {$row[$rand_ques]['type_id']}";
		$temp = $wpdb -> get_row($query, ARRAY_A);
		$info = $temp['info'];
		return array('info' => $info, 'str_id' => $str_id, 'type_id'=> $row[$rand_ques]['type_id']);
	}
	}
}

function make_seed($key)
{
  list($usec, $sec) = explode(' ', microtime());
  $m = ((float) $sec + ((float) $usec * 100000)).$key;
  return hexdec(substr(sha1($m), 3,13));
}
?>
