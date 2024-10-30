<?php
session_start();

putenv('GDFONTPATH=' . realpath('.'));
$font_name = $_GET['font_name'];
$font = "font/{$font_name}.ttf";
$font_size = $_GET['font_size'];
$width = 250;
$height = 100;

function _captcha_noise(&$image,$status,$font_line_color,$width,$height,$xfont,$yfont,$bg){
	$val['noise']['high'] = array( 'dot' => $width*$height/10 , 'line' => 20 , 'bgline' => 10);
    $val['noise']['med'] = array( 'dot' => $width*$height/20 , 'line' => 10 , 'bgline' => 5);
    $val['noise']['low'] = array( 'dot' => $width*$height/40 , 'line' => 5 , 'bgline' => 5);
	//สร้างจุด
	for ($i=0; $i < $val['noise'][$status]['dot'] ; $i++)
	{
	    $cx = rand(0, $width);
	    $cy = rand(0, $height);
		imageellipse($image, $cx, $cy, 1, 1, $font_line_color);
	}
	//สร้างเส้นเดียวกับตัวอักษร ทับบนตัวอักษร
	for ($i=0; $i <= $val['noise'][$status]['line']/2 ; $i++) 
	{
		$x_start = ($xfont-20)>=0?$xfont-20:0;
		$y_start = ($yfont-30)>=0?$yfont-30:0;
		$y_end = $yfont+20;
	    $x1 = rand($x_start, $width-10);
	    $x2 = rand($x_start, $width-10);
	    $y1 = rand($y_start, $y_end);
	    $y2 = rand($y_start-10, $y_end+10);
	    imageline($image, $x1, $y1, $x2, $y2, $font_line_color);
	}
	//สร้างเส้นสีเดียวกับตัวอักษร กระจายบนภาพ
	for ($i=0; $i <= $val['noise'][$status]['line']/2 ; $i++) 
	{
	    $x1 = rand(0, $width);
	    $x2 = rand(0, $width);
	    $y1 = rand(0, $height);
	    $y2 = rand(0, $height);
	    imageline($image, $x1, $y1, $x2, $y2, $font_line_color);
	}
	//สร้างเส้นสีเดียวกับพื้นหลัง เพื่อทำให้ตัวอักษรขาดออกจากกัน
	for ($i=0; $i < $val['noise'][$_GET['noise']]['bgline'] ; $i++) 
	{
	    $x_start = $xfont;
		$y_start = ($yfont-30)>=0?$yfont-30:0;
		$y_end = $yfont;
	    $x1 = rand($x_start, $width-10);
	    $x2 = rand($x_start, $width-10);
	    $y1 = rand($y_start, $y_end);
	    $y2 = rand($y_start-10, $y_end+10);
	    imageline($image, $x1, $y1, $x2, $y2, $bg);
	}	
}

function _captcha_distorted_curve(&$image,$status,$width,$height){
	$val['distorted']['high'] = array( 'period' => rand(8,10) , 'amp' => rand(7, 9) );
	$val['distorted']['med'] = array( 'period' => rand(5,7) , 'amp' => rand(5, 6) );
	$val['distorted']['low'] = array( 'period' => rand(2,4) , 'amp' => rand(2, 4) );
	
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


function _captcha_distorted_wavy(&$image,$status,$width,$height){
	$val['distorted']['high'] = array( 'period' => rand(8,10) , 'amp' => rand(7, 9) );
	$val['distorted']['med'] = array( 'period' => rand(5,7) , 'amp' => rand(5, 6) );
	$val['distorted']['low'] = array( 'period' => rand(2,4) , 'amp' => rand(2, 4) );
	
	$width2 = $width * 2;
  	$height2 = $height * 2;
  	$period = $val['distorted'][$status]['period'];
  	$amp = $val['distorted'][$status]['amp'];
  	$image2 = imagecreatetruecolor($width2, $height2);
  	imagecopyresampled($image2, $image, 0, 0, 0, 0, $width2, $height2, $width, $height);
  	for ($i = 0; $i < $width2; $i += 2) {
  	  imagecopy($image2, $image2, $i - 2, sin($i / $period*1.0) * $amp*1.5, $i, 0, 2, $height2);
  	}
  	imagecopyresampled($image, $image2, 0, 0, 0, 0, $width, $height, $width2, $height2);
  	imagedestroy($image2);
}

$font_color = $_GET['font_color'];
$bg_color = $_GET['bg_color'];

if(isset( $_SESSION['capth_str'] )){
	$str = $_SESSION['capth_str'];
	$seed = $_SESSION['seed'];
}else{
	$str = "ทดสอบ";
	list($usec, $sec) = explode(' ', microtime());
  	$seed = (float) $sec + ((float) $usec * 100000);
}
srand($seed);
session_destroy();
$xfont = rand(10, 100);
$yfont = rand(40, 80);
$image = imagecreate($width, $height); 
$bg = imagecolorallocate($image, hexdec (substr($bg_color, 0 , 2)) ,hexdec (substr($bg_color, 2 , 2)),hexdec (substr($bg_color, 4 , 2)) ); 
$font_line_color = imagecolorallocate($image, hexdec (substr($font_color, 0 , 2)) ,hexdec (substr($font_color, 2 , 2)),hexdec (substr($font_color, 4 , 2)) );

$angle = rand(-10, 10);

$val['noise']['high'] = array( 'dot' => $width*$height/10 , 'line' => 20 , 'bgline' => 10);
$val['noise']['med'] = array( 'dot' => $width*$height/20 , 'line' => 10 , 'bgline' => 5);
$val['noise']['low'] = array( 'dot' => $width*$height/40 , 'line' => 5 , 'bgline' => 5);

$val['distorted']['high'] = array( 'period' => rand(8,10) , 'amp' => rand(7, 9) );
$val['distorted']['med'] = array( 'period' => rand(5,7) , 'amp' => rand(5, 6) );
$val['distorted']['low'] = array( 'period' => rand(2,4) , 'amp' => rand(2, 4) );

if($_GET['line']=="wavy"){
	if( $_GET['noise'] != 'no' ){
		_captcha_noise($image, $_GET['noise'], $font_line_color, $width, $height,$xfont,$yfont,$bg);
	}
	imagettftext($image,$font_size,$angle,$xfont,$yfont,$font_line_color,$font,$str);
	if( $_GET['distorted'] != 'no' ){
		_captcha_distorted_wavy($image, $_GET['distorted'], $width, $height);
	}
}

if($_GET['line']=="curve"){
	imagettftext($image,$font_size,$angle,$xfont,$yfont,$font_line_color,$font,$str);
	if( $_GET['noise'] != 'no' ){
		_captcha_noise($image, $_GET['noise'], $font_line_color, $width, $height,$xfont,$yfont,$bg);
	}
	if( $_GET['distorted'] != 'no' ){
		_captcha_distorted_curve($image, $_GET['distorted'], $width, $height);
	}
}

if($_GET['line']=="straight"){
	imagettftext($image,$font_size,$angle,$xfont,$yfont,$font_line_color,$font,$str);
	if( $_GET['distorted'] != 'no' ){
		_captcha_distorted_curve($image, $_GET['distorted'], $width, $height);
	}
	if( $_GET['noise'] != 'no' ){
		_captcha_noise($image, $_GET['noise'], $font_line_color, $width, $height,$xfont,$yfont,$bg);
	}
}





header("Content-type:image/png"); 
imagepng($image); 
imagedestroy($image); 

?>