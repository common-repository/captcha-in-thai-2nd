<?php
function backup_tables()
{
        global $wpdb;
		$table = 'ct_statistic';
        $query = 'SELECT * FROM ct_statistic';
		$result = $wpdb -> get_results($query,ARRAY_N);
		$num_row = $wpdb -> get_row('select count(*) from ct_statistic',ARRAY_N);
        $return = "";
        $row2 = $wpdb -> get_row('SHOW CREATE TABLE ct_statistic',ARRAY_N);
        $return.= "\n\n".$row2[1].";\n\n";   
            for($i=0;$i<=$num_row[0]-1;$i++)
            {
                $return.= 'INSERT INTO ct_statistic VALUES(';
                for($j=0; $j<7; $j++) 
                {
                	$result[$i][$j] = addslashes($result[$i][$j]);
                    if (isset($result[$i][$j])) { $return.= '"'.$result[$i][$j].'"' ; } else { $return.= '""'; }
                    if ($j<(7-1)) { $return.= ','; }
                }
                $return.= ");\n";
            }
        $return.="\n\n\n";

    return $return;
    }

function encode($string,$key) {
    $key = sha1($key);
    $strLen = strlen($string);
    $keyLen = strlen($key);
    for ($i = 0; $i < $strLen; $i++) {
        $ordStr = ord(substr($string,$i,1));
        if ($j == $keyLen) { $j = 0; }
        $ordKey = ord(substr($key,$j,1));
        $j++;
        $hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,36));
    }
    return $hash;
}


function statistics()
{
	global $wpdb;
	global $options;
	$num_question = 'select ct_answer.question_id, question, count(question) as num ,ct_question.type_id
			from ct_answer join ct_question on ct_question.question_id = ct_answer.question_id
			group by question';
	$result_question = $wpdb -> get_results($num_question,ARRAY_A);
	foreach ($result_question as $row) {
			$tmp[] = $row[question_id];
			$data[$row[question_id]]['id'] = $row[question_id];
			$data[$row[question_id]]['question'] = $row[Question];
			$data[$row[question_id]]['num_all'] = $row[num];
			$data[$row[question_id]]['type_id'] = $row[type_id];
		}

	$num_true = "select ct_answer.question_id, count(question) as num from ct_answer
			join ct_question on ct_answer.question_id = ct_question.question_id
			where ct_answer.istrue = 'y'
			group by question";
	$result_true = $wpdb -> get_results($num_true,ARRAY_A);
	foreach ($result_true as $row) {
            $data[$row[question_id]]['num_true'] = $row[num];
        }
	$num_false = "select ct_answer.question_id, count(question) as num from ct_answer
			join ct_question on ct_answer.question_id = ct_question.question_id
			where ct_answer.istrue = 'n'
			group by question";
	$result_false = $wpdb -> get_results($num_false,ARRAY_A);
	foreach ($result_false as $row) {
            $data[$row[question_id]]['num_false'] = $row[num];
        }
	$month = date('F');
	$year = date('Y');
	foreach ($data as $key => $value) {
		$insert_statistic = "INSERT INTO ct_statistic VALUES
		( '{$value['id']}' , '$month' , '$year', '{$value['type_id']}', '{$value['num_all']}' , '{$value['num_true']}' , '{$value['num_false']}')";
		$wpdb -> query($insert_statistic);
		}
	$data = backup_tables();
	$encrypt_data = encode($data, $options['app_key']);
	$data = "encrypt=".$encrypt_data;
	$data .= "&whois=".$_SERVER['REMOTE_ADDR'];
	$data .= "&host=".$_SERVER['SERVER_NAME'];
	$data .= "&k=".$options['app_key'];
	$host = 'www.captcha.in.th';
	$path = '/statistics/index.php';
    $port = 80;
    $statistics  = "POST $path HTTP/1.1\r\n";
    $statistics .= "Host: $host\r\n";
    $statistics .= "Content-Type: application/x-www-form-urlencoded;\r\n";
    $statistics .= "Content-Length: " . strlen($data) . "\r\n";
    $statistics .= "\r\n";
    $statistics .= $data;

    $response = '';
    if( false == ( $fs = @fsockopen( $host, $port, $errno, $errstr, 2) ) ) {
        die ('Could not open socket');
    }
	fwrite($fs, $statistics);
    fclose($fs);
    return true;
}
?>