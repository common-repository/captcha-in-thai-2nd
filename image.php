<?php
putenv('GDFONTPATH=' . realpath('.'));
function  create_image($part, $str_id, $str_math ,$type_id){
	session_start();
	list($usec, $sec) = explode(' ', microtime());
	$seed = (float)$sec + ((float)$usec * 100000);
	srand($seed);
	
	global $wpdb;
	global $options;
	global $capth_def_setting;
	$font_size = $capth_def_setting['font_size']*0.85;
	$width = 250;
	$height = 125;
	
	//calculate oppositecolor
    function OppositeHex($color) {
		$r = dechex(255 - hexdec(substr($color, 0, 2)));
		$r = (strlen($r) > 1) ? $r : '0' . $r;
		$g = dechex(255 - hexdec(substr($color, 2, 2)));
		$g = (strlen($g) > 1) ? $g : '0' . $g;
		$b = dechex(255 - hexdec(substr($color, 4, 2)));
		$b = (strlen($b) > 1) ? $b : '0' . $b;
		return $r . $g . $b;
	}
	// กำหนดสีให้แต่ละส่วน
	if ("login" == $part) {
		if($options['login_color'] == 'yes') {
		  $r = rand(0, 15);
		  $g = rand(0, 15);
		  $b = rand(0, 15);
		  $bg_color = dechex($r) . dechex($r) . dechex($g) . dechex($g) . dechex($b) . dechex($b);
		  $font_color = OppositeHex($bg_color);
		}
		else {
		  $font_color = substr($options['login_font_color'], 1);
		  $bg_color = substr($options['login_bg_color'], 1);
		}
	} elseif ("register" == $part) {
		if($options['reg_color'] == 'yes') {
		  $r = rand(0, 15);
		  $g = rand(0, 15);
		  $b = rand(0, 15);
		  $bg_color = dechex($r) . dechex($r) . dechex($g) . dechex($g) . dechex($b) . dechex($b);
		  $font_color = OppositeHex($bg_color);
		}
		else {
		$font_color = substr($options['register_font_color'], 1);
		$bg_color = substr($options['register_bg_color'], 1);
		}
	} elseif ("comment" == $part) {
		if($options['com_color'] == 'yes') {
		  $r = rand(0, 15);
		  $g = rand(0, 15);
		  $b = rand(0, 15);
		  $bg_color = dechex($r) . dechex($r) . dechex($g) . dechex($g) . dechex($b) . dechex($b);
		  $font_color = OppositeHex($bg_color);
		}
		else {
		$font_color = substr($options['comment_font_color'], 1);
		$bg_color = substr($options['comment_bg_color'], 1);
		}
	} elseif ("lostpassword" == $part) {
		if($options['lost_color'] == 'yes') {
		  $r = rand(0, 15);
		  $g = rand(0, 15);
		  $b = rand(0, 15);
		  $bg_color = dechex($r) . dechex($r) . dechex($g) . dechex($g) . dechex($b) . dechex($b);
		  $font_color = OppositeHex($bg_color);
		}
		else {
		$font_color = substr($options['lostpassword_font_color'], 1);
		$bg_color = substr($options['lostpassword_bg_color'], 1);
		}
	}
	
	// เส้นโค้ง
	function _captcha_in_thai_distorted_curve($width, $height, &$image, $status) {
  		$val['distorted']['high'] = array('period' => rand(8, 10), 'amp' => rand(7, 9));
  		$val['distorted']['med'] = array('period' => rand(5, 7), 'amp' => rand(5, 6));
  		$val['distorted']['low'] = array('period' => rand(2, 4), 'amp' => rand(2, 4));
  		$width2 = $width * 2;
  		$height2 = $height * 2;
  		$period = $val['distorted'][$status]['period'];
  		$amp = $val['distorted'][$status]['amp'];
  		$image2 = imagecreatetruecolor($width2, $height2);
  		imagecopyresampled($image2, $image, 0, 0, 0, 0, $width2, $height2, $width, $height);
  		for ($i = 0; $i < $width2; $i += 2) {
  		  imagecopy($image2, $image2, $i - 2, sin($i / $period * 0.5) * $amp *1.5, $i, 0, 2, $height2);
  		}
  		imagecopyresampled($image, $image2, 0, 0, 0, 0, $width, $height, $width2, $height2);
  		imagedestroy($image2);
	}
	
	// เส้นหยัก
	function _captcha_in_thai_distorted_wavy($width, $height, &$image, $status) {
  		$val['distorted']['high'] = array('period' => rand(8, 10), 'amp' => rand(7, 9));
  		$val['distorted']['med'] = array('period' => rand(5, 7), 'amp' => rand(5, 6));
  		$val['distorted']['low'] = array('period' => rand(2, 4), 'amp' => rand(2, 4));
  		$width2 = $width * 2;
  		$height2 = $height * 2;
  		$period = $val['distorted'][$status]['period'];
  		$amp = $val['distorted'][$status]['amp'];
  		$image2 = imagecreatetruecolor($width2, $height2);
  		imagecopyresampled($image2, $image, 0, 0, 0, 0, $width2, $height2, $width, $height);
  		for ($i = 0; $i < $width2; $i += 2) {
  		  imagecopy($image2, $image2, $i - 2, sin($i / $period) * $amp, $i, 0, 2, $height2);
  		}
  		imagecopyresampled($image, $image2, 0, 0, 0, 0, $width, $height, $width2, $height2);
  		imagedestroy($image2);
	}
	
	// สร้างจุดและเส้น
	function _captcha_in_thai_noise(&$image, $status, $width, $height, $font_line_color, $bg) {
		$val['noise']['high'] = array('dot' => $width * $height / 10, 'line' => 20, 'bgline' => 10);
		$val['noise']['med'] = array('dot' => $width * $height / 20, 'line' => 10, 'bgline' => 5);
		$val['noise']['low'] = array('dot' => $width * $height / 40, 'line' => 5, 'bgline' => 5);
		for ($i = 0; $i < $val['noise'][$status]['dot']; $i++)//สร้างจุด
		{
			$cx = rand(0, $width);
			$cy = rand(0, $height);
			imageellipse($image, $cx, $cy, 1, 1, $font_line_color);
		}
		for ($i = 0; $i < $val['noise'][$status]['line']; $i++)//สร้างเส้นสีดำ
		{
			$x1 = rand(0, $width);
			$x2 = rand(0, $width);
			$y1 = rand(0, $height);
			$y2 = rand(0, $height);
			imageline($image, $x1, $y1, $x2, $y2, $font_line_color);
		}
		for ($i = 0; $i < $val['noise'][$status]['bgline']; $i++)//สร้างเส้นสีเเขียว
		{
			$x_start = $xfont;
			$y_start = ($yfont - 30) >= 0 ? $yfont - 30 : 0;
			$y_end = $yfont;
			$x1 = rand($x_start, $width - 10);
			$x2 = rand($x_start, $width - 10);
			$y1 = rand($y_start, $y_end);
			$y2 = rand($y_start - 10, $y_end + 10);
			imageline($image, $x1, $y1, $x2, $y2, $bg);
		}		
	}

	//coordinates
	$xfont = rand(10, 100);
	$yfont = rand(40, 80);
	$angle = rand(-10, 10);
	//change frame size
	switch ($options['width_frame']) {
		case "300x120" :
			$width = $width * 1.2;
			$height = $height * 1.2;
			$font_size = $font_size * 1.2;
			break;
		case "250x100" :
			$width = $width * 1.0;
			$height = $height * 1.0;
			$font_size = $font_size * 1.0;
			break;
		case "200x80" :
			$width = $width * 0.9;
			$height = $height * 0.8;
			$font_size = $font_size * 0.6;
			$xfont = 40 * 0.8;
			$yfont = 60 * 0.8;
			break;
	}
	
	//font options
	if ('yes' == $options['font_random']) {
		$query = " SELECT *
		   FROM ct_fonts			
		   ORDER BY RAND() LIMIT 1
		   ";
		$row = $wpdb -> get_row($query, ARRAY_A);
		$font_name = $row['fontname'];
	} else {
		$font_name = $options['font_name'];
	}// change font normal => bold
	if ('yes' == $options['font_bold'] && 'no' == $options['font_random'] && 'TH Charm of AU' != $options['font_name']) {
		$font_name .= " Bold";
	}
	$fonts = dirname(__FILE__) . '/font/'.$font_name.'.ttf';  
	
	if($type_id == '5') {
		$str = $str_math;
	}
	else{
		// select question from db
		$query = "  SELECT question
                FROM ct_question 
                WHERE question_id 
                IN  (   SELECT question_id 
                        FROM ct_user_question 
                        WHERE id = $str_id
                    )";
		$row = $wpdb -> get_row($query, ARRAY_A);
		$str = $row['question'];
	}
	
	// สร้างรูปภาพ
	$image = imagecreate($width, $height);
	// สีพื้นหลัง
	$bg = imagecolorallocate($image, hexdec(substr($bg_color, 0, 2)), hexdec(substr($bg_color, 2, 2)), hexdec(substr($bg_color, 4, 2)));
	// สีตัวอักษร
	$font_line_color = imagecolorallocate($image, hexdec(substr($font_color, 0, 2)), hexdec(substr($font_color, 2, 2)), hexdec(substr($font_color, 4, 2)));


	//เส้นตรง
	if ('straight' == $options['line']) {
		imagettftext($image, $font_size, $angle, $xfont, $yfont, $font_line_color, $fonts, $str);
		if ($options['distorted'] != 'no') {
			_captcha_in_thai_distorted_wavy($width, $height, $image, $options['distorted']);
		}
		if ($options['noise'] != 'no') {
			_captcha_in_thai_noise($image, $options['noise'], $width, $height, $font_line_color, $bg);
		}
	}
	
	//เส้นโค้ง
	if ('curve' == $options['line']) {
		imagettftext($image, $font_size, $angle, $xfont, $yfont, $font_line_color, $fonts, $str);
		if ($options['noise'] != 'no') {
			_captcha_in_thai_noise($image, $options['noise'], $width, $height, $font_line_color, $bg);
		}
		if ($options['distorted'] != 'no') {
			_captcha_in_thai_distorted_curve($width, $height, $image, $options['distorted']);
		}
	}
	
	//เส้นหยัก
	if ('wavy' == $options['line']) {
		if ($options['noise'] != 'no') {
			_captcha_in_thai_noise($image, $options['noise'], $width, $height, $font_line_color, $bg);
		}
		imagettftext($image, $font_size, $angle, $xfont, $yfont, $font_line_color, $fonts, $str);
		if ($options['distorted'] != 'no') {
			_captcha_in_thai_distorted_wavy($width, $height, $image, $options['distorted']);
		}
	}

	ob_start();
    imagepng($image);
	$rawImageBytes = ob_get_clean();
    imagedestroy($image);
	ob_end_flush();
	return "data:image/jpeg;base64," . base64_encode( $rawImageBytes )."";
	}
?>
