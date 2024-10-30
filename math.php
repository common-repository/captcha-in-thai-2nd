<?php
session_start();
function math(){
	global $options;
    global $capth_def_setting;
	
	$num = array(); 
	$num[0] = "๐";
	$num[1] = "๑";
	$num[2] = "๒";
	$num[3] = "๓";
	$num[4] = "๔";
	$num[5] = "๕";
	$num[6] = "๖";
	$num[7] = "๗";
	$num[8] = "๘";
	$num[9] = "๙";
	
	$key = $options['app_key'];
	$ctr = $options['ctr']++;
	update_option( 'capth_options', $options, '', 'yes' );
	
	srand(make_seed($ctr.$key.($ctr+1)));
	$_SESSION["seed"] = make_seed(($ctr+1).$key.$ctr);
	
	$mode = rand(1, 3);
	$first_num;
	$second_num;
	$ans_num;
	$op;
	if( $mode == 1 ){
		$first_num = rand( 1, 99);
		$second_num = rand( 1, 99);
		$ans_num = $first_num + $second_num;
		$op = "บวก";
	}else if( $mode == 2 ){
		$first_num = rand( 1, 99);
		$second_num = rand( 1, 99);
		if( $first_num < $second_num ){
			$temp_num = $first_num;
			$first_num = $second_num;
			$second_num = $temp_num;	
		}
		$ans_num = $first_num - $second_num;
		$op = "ลบ";
	}else if( $mode == 3 ){
		$first_num = rand( 1, 9);
		$second_num = rand( 1, 9);
		$ans_num = $first_num * $second_num;
		$op = "คูณ";
	}
	
	$str = "";
	if ( $first_num >= 10 ){
		$str .= $num[ $first_num / 10 ];
	}
	$str .= $num[ $first_num % 10 ];
	
	$str .= " $op ";
	
	if ( $second_num >= 10 ){
		$str .= $num[ $second_num / 10 ];
	}
	$str .= $num[ $second_num % 10 ];
	
	$randnum = rand(0, 1000);

	$info = "หาค่าต่อไปนี้<br />&nbsp;เช่น ๑ บวก ๑ ให้ตอบ ๒ หรือตอบ 2";

	return array('info' => $info, 'str' => $str, 'ans_num' => $ans_num);
	
}



?>
