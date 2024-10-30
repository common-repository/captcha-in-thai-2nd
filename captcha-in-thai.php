<?php
extract($_REQUEST);
/*
Plugin Name: CAPTCHA In Thai 2nd
Plugin URI: http://www.captcha.in.th
Description: CAPTCHA in Thai language.
Version: 1.1
Author: ENGR TU
Author URI: http://www.captcha.in.th
License: GPLv2 or later
*/

/*  Copyright 2013  Nattapon, Thanate, Kunanon And Kittiporn  (email : captcha.in.thai@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//add admin menu

require_once('menupage.php');
require_once('check.php');
require_once('question.php');
require_once('image.php');
require_once('statistics.php');
require_once('math.php');
global $wpdb;
$capth_def_setting = array(
        'font_size' => 30,
        'width' => 250,
        'height' => 100,
        'font_color' => "#000000",
        'bg_color' => "#64dcdc",
    );

add_action( 'admin_menu', 'add_menu' );

function add_menu()
{
    add_menu_page( 'CAPTCHA in Thai', 'CAPTCHA in Thai', 'manage_options', 'capthsetting', 'menu_page', plugins_url('captcha-in-thai-2nd/images/icon.png'));
    add_action( 'admin_init', 'setting' );
}

function setting()
{
    global $options;
    global $capth_def_setting;
    $option_defaults = array(
        'login_font_color' => $capth_def_setting['font_color'],
        'login_bg_color' => $capth_def_setting['bg_color'],
        'comment_font_color' => $capth_def_setting['font_color'],
        'comment_bg_color' => $capth_def_setting['bg_color'],
        'register_font_color' => $capth_def_setting['font_color'],
        'register_bg_color' => $capth_def_setting['bg_color'],
        'lostpassword_font_color' => $capth_def_setting['font_color'],
        'lostpassword_bg_color' => $capth_def_setting['bg_color'],
		'font_bold' => 'no',
        'font_random' => 'yes',
        'font_name' => 'TH Sarabun PSK',
        'width_frame' => '250',
        'height_frame' => '250',
        'random_color' => 'no',
        'line' => 'straight',
        'question_type1' => 'yes',
        'question_type2' => 'yes',
        'question_type3' => 'yes',
        'question_type4' => 'yes',
        'question_type5' => 'yes',
        'com_color' => 'no',
        'lost_color' => 'no',
        'reg_color' => 'no',
        'login_color' => 'no',
        'statistics' =>'no',
		'install_db' => 'no',
        'captcha_login' => 'no',
        'captcha_comments' => 'no',
        'captcha_register' => 'no',
        'captcha_lostpassword' => 'no',
        'hide_register_user' => 'yes',
		'distorted' => 'med',
		'noise' => 'med',
		'user_agreement' => 'no',
		'app_key' => 'none',
		'ctr' => 0,
    );
    
    
    if( !get_option( 'capth_options' ) )
    {
        add_option( 'capth_options', $option_defaults, '', 'yes' );
    }
	
    $options = get_option( 'capth_options' );
    $options = array_merge( $option_defaults, $options);
	
	if($options != get_option( 'capth_options' ) ){
		delete_option( 'capth_options' );
		add_option( 'capth_options', $options, '', 'yes' );
	}
	
} // end function setting

//display captcha in login form
$options = get_option( 'capth_options' );




if( 'yes' == $options['captcha_login']){
    add_action( 'login_form', 'login' );
    add_filter( 'login_errors', 'login_post' );
    add_filter( 'login_redirect', 'login_check'); 
}

if( 'yes' == $options['captcha_comments'] ){
	add_action( 'comment_form_after_fields', 'comment', 1 );
	add_action( 'comment_form_logged_in_after', 'comment', 1 );
    //add_action( 'comment_form', 'comment' );
    add_filter( 'preprocess_comment', 'comment_post' );
}

if( 'yes' == $options['captcha_register'] ){
    add_action( 'register_form', 'register' );
    add_action( 'register_post', 'register_post', 10, 3 );
}

if( 'yes' == $options['captcha_lostpassword'] ){
    add_action( 'lostpassword_form', 'lostpassword' );
    add_action( 'lostpassword_post', 'lostpassword_post', 10, 3 );
}


//function for add settings menu
function menu_link($links, $file)
{
    $this_plugin = plugin_basename(__FILE__);

    if ( $file == $this_plugin){
        $settings_link = '<a href="admin.php?page=capthsetting">Settings</a>';
        array_unshift( $links, $settings_link );
    }
    return $links;
} // end function menu_link

function login(){
    if( session_id() == "" ){
        session_start();
    }
	
    if( ! $_SESSION["login_failed"] ){
        // don't do anyting
    }else{
    	global $captcha_host;
		if( isset( $_SESSION["login"] ) ){
            unset( $_SESSION["login"]);
        }
        
        if( isset( $_SESSION['error'] ) ) {
            echo "<br /><span style='color:red'>". $_SESSION['error'] ."</span><br />";
            unset( $_SESSION['error'] );
        }
	    
	    display( "login" );

    }

    return true;
}

function register(){
	display( "register" );
    return true;
}

function lostpassword(){
	display( "lostpassword" );
    return true;
}

function comment(){
    global $options;
	global $captcha_host;

    if ( is_user_logged_in() && 'yes' == $options['hide_register_user'] ) {
        return true;
    }
    display( "comment" );
    return true;
}
function display( $part ){
	global $capth_def_setting;
	global $options;
	$stats_option = get_option( 'capth_statistics' );
	if( !get_option( 'capth_statistics' ) )
    {
        add_option( 'capth_statistics', 'no', '', 'yes' );
    }
	if(date('d')== 1|| date('d')== 19 || date('d') == 18){
		if($stats_option=='no'){
			statistics();
			update_option( 'capth_statistics', 'yes' );
		}
		if(date('d') == 30  || date('d')== 18 ){
			update_option( 'capth_statistics', 'no');
		}
	}
 	$question = question();
 	if($question['type_id'] == '5'){
 	  $math_str = math();
      $url =  create_image($part,'',$math_str['str'],$question['type_id']);
 	}
 	else {
	  $url = create_image($part,$question['str_id'],'',$question['type_id']);
	}
	if( absint($options['width_frame']) > 465){
		$options['width_frame'] = 250;
	}
	if( absint($options['height_frame']) > 400){
		$options['height_frame'] = 125;
	}
 ?>
<br />
<iframe id="capth_iframe" srcdoc='
<table align="left" cellpadding="0" cellspacing="0" width="<?= $options['width_frame'] ?>" height="<?=  $options['height_frame']*1.3 ?>" rules="all" border="1" style="border: solid; border-color: #696969;" >
        <tr style="border:none;background-color: #BEBEBE;">
        	<td width="200" align="center"><b><center><?= $question['info'] ?></center></b></td>
        </tr>
        <tr>
            <td bgcolor="#00AfAf" align="center" ><img width="100%" height="100%" src="<?= $url ?>" /></td>
        </tr>
	</table>
' style="border:none"  width="<?= $options['width_frame']*1.2 ?>" height="<?=  $options['height_frame']*1.5?>" scrolling="no"><br />
</iframe>
 <td align="center"><input type="text" name="answer"/></td>
<input type='hidden' name='ans_num' id="ans_num" value="<?=$math_str['ans_num']?>" />
<?php
    return true;
} // end function display

function login_post($errors) {
    $_SESSION["login_failed"] = true;
    // Delete errors, if they set
    if( isset( $_SESSION['error'] ) )
        unset( $_SESSION['error'] );
	$answer = ereg_replace('[[:space:]]+', '', trim(wp_filter_nohtml_kses( $_REQUEST['answer'])));
    // If captcha not complete, return error
    if ( isset( $_REQUEST['answer'] ) && "" == $answer ) {
        return $errors;//.'<strong>ERROR</strong>: กรุณากรอก CAPTCHA';
    }
	if(isset($_REQUEST['ans_num']) && $_REQUEST['ans_num'] != ""){
				if( ( 0 == strcmp( $_REQUEST['ans_num']  ,$answer) ) || ( 0 == strcmp( tran_num($_REQUEST['ans_num'])  , $answer ) ) ){
				// captcha was matched
	    	}
	    	else {
	            return $errors;//."<strong>ERROR</strong>: คุณกรอก CAPTCHA ไม่ถูกต้อง<br />";
	    	}
	}else{
    	$res = check_answer($_SERVER['SERVER_NAME'], $_SERVER['REMOTE_ADDR'], $answer );
    	if ( 0 == strcmp($res[0], 'true') ) {
        	// captcha was matched
    	} else {
        	return $errors;//."<strong>ERROR</strong>:NO คุณกรอก CAPTCHA ไม่ถูกต้อง<br />";
    	}
	}
	
  return($errors);
} // end function cptch_login_post

function login_check($url) {
    $_SESSION["login_failed"] = true;
    if( session_id() == "" ){
        session_start();
    }
	if ( isset( $_REQUEST['answer'] ) && "" ==  $_REQUEST['answer'] ) {
        $_SESSION['error'] =  'กรุณากรอก CAPTCHA.';
		wp_clear_auth_cookie();
		return $_SERVER["REQUEST_URI"];
	}
    if ( isset( $_REQUEST['answer'] ) ){
    		$answer = ereg_replace('[[:space:]]+', '', trim(wp_filter_nohtml_kses( $_REQUEST['answer'])));
		    if(isset($_REQUEST['ans_num']) && $_REQUEST['ans_num'] != ""){
				if( ( 0 == strcmp( $_REQUEST['ans_num']  ,$answer) ) || ( 0 == strcmp( tran_num($_REQUEST['ans_num'])  , $answer ) ) ){
            	$_SESSION['login'] = 'true';
            	unset( $_SESSION["login_failed"] );
            	return $url;
	    	}
	    	else {
	        	$_SESSION['error'] = 'คุณกรอก CAPTCHA ไม่ถูกต้อง';
	            wp_clear_auth_cookie();
	            return $_SERVER["REQUEST_URI"];
	    	}
			}else{
        	$res = check_answer($_SERVER['SERVER_NAME'], $_SERVER['REMOTE_ADDR'], $answer );
        	if( 0 == strcmp($res[0], 'true') ){
           	 	$_SESSION['login'] = 'true';
            	unset( $_SESSION["login_failed"] );
            	return $url;
        	}
	        else {
	            $_SESSION['error'] = "คุณกรอก CAPTCHA ไม่ถูกต้อง <br />";
	            wp_clear_auth_cookie();
	            return $_SERVER["REQUEST_URI"];
        	}
			}
    }else{
        return $url;
    }
         
} // end function cptch_login_post

function register_post($login,$email,$errors) {

    // If captcha is blank - add error
    if ( isset( $_REQUEST['answer'] ) && "" ==  $_REQUEST['answer'] ) {
        $errors->add('captcha_blank', '<strong>ERROR</strong>: กรุณากรอก CAPTCHA');
        return $errors;
    }
    if ( isset( $_REQUEST['answer'] ) ){
    		$answer = ereg_replace('[[:space:]]+', '', trim(wp_filter_nohtml_kses( $_REQUEST['answer'])));
			if(isset($_REQUEST['ans_num']) && $_REQUEST['ans_num'] != ""){
				if( ( 0 == strcmp( $_REQUEST['ans_num']  ,$answer) ) || ( 0 == strcmp( tran_num($_REQUEST['ans_num'])  , $answer ) ) ){		
	    		}
	    		else {
					$errors->add('captcha_wrong', "<strong>ERROR</strong>: คุณกรอก CAPTCHA ไม่ถูกต้อง<br />");
	    		}
		    }else{
	        $res = check_answer($_SERVER['SERVER_NAME'], $_SERVER['REMOTE_ADDR'], $answer );
	        if( 0 == strcmp($res[0], 'true') ){

	        }
	        else {
	            $errors->add( 'captcha_wrong' , "<strong>ERROR</strong>: คุณกรอก CAPTCHA ไม่ถูกต้อง<br />" );
	        }
			}
    }
	return $errors;
} // end function cptch_register_post

function lostpassword_post() {
	global $captcha_host;

	if ( isset( $_REQUEST['answer'] ) && "" ==  $_REQUEST['answer'] ) {
        wp_die( "<strong>ERROR</strong>: กรุณากรอก CAPTCHA. <br /> <a href=".wp_lostpassword_url().">กลับ</a>" );
    }
		$answer = ereg_replace('[[:space:]]+', '', trim(wp_filter_nohtml_kses( $_REQUEST['answer'])));
		 if(isset($_REQUEST['ans_num']) && $_REQUEST['ans_num'] != ""){
				if( ( 0 == strcmp( $_REQUEST['ans_num']  ,$answer) ) || ( 0 == strcmp( tran_num($_REQUEST['ans_num'])  , $answer ) ) ){
					return;
	    	}
	    	else {
	        	wp_die( "<strong>ERROR</strong>: คุณกรอก CAPTCHA ไม่ถูกต้อง <br /> <a href=".wp_lostpassword_url().">กลับ</a>");
	    	}
		}else{
        $res = check_answer($_SERVER['SERVER_NAME'], $_SERVER['REMOTE_ADDR'], $answer );
        if( 0 == strcmp($res[0], 'true') ){
            return;
        }
        else {
            wp_die( "<strong>ERROR</strong>: คุณกรอก CAPTCHA ไม่ถูกต้อง<br /> <a href=".wp_lostpassword_url().">กลับ</a>");
        }
		}
	
} // function cptch_lostpassword_post

function comment_post($comment) { 
    global $options;
	global $captcha_host;

    if ( is_user_logged_in() && 'yes' == $options['hide_register_user'] ) {
        return $comment;
    }
    
    // If captcha is empty
    if ( isset( $_REQUEST['answer'] ) && "" ==  $_REQUEST['answer'] ) {
        wp_die( "<strong>ERROR</strong>: กรุณากรอก CAPTCHA. <br /> <a href=".get_comments_link().">กลับ</a>" );
	}
	if ( isset($_REQUEST['ans_num']) && "" ==  $_REQUEST['answer'] ){
		if( ( 0 == strcmp( $_REQUEST['ans_num']  , $_REQUEST['answer']) ) || ( 0 == strcmp( tran_num($_REQUEST['ans_num'])  , $_REQUEST['answer'] ) ) ){
	        return $comment;
	    }
	    else {
	        wp_die( "<strong>ERROR</strong>: คุณกรอก CAPTCHA ไม่ถูกต้อง <br /> <a href=".get_comments_link().">กลับ</a>");
	    }
	}
		$answer = ereg_replace('[[:space:]]+', '', trim(wp_filter_nohtml_kses( $_REQUEST['answer'])));
	    $res = check_answer($_SERVER['SERVER_NAME'], $_SERVER['REMOTE_ADDR'], $answer );
	    if( 0 == strcmp($res[0], 'true') ){
	        return $comment;
	    }
	    else {
	        wp_die( "<strong>ERROR</strong>: คุณกรอก CAPTCHA ไม่ถูกต้อง <br /> <a href=".get_comments_link().">กลับ</a>" );
	    }
} // end function cptch_comment_post

function tran_num( $old ){
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
	
	$new = "";
	if ( $old >= 100 ){
		$new .= $num[ $old / 100 ];
		$old %= 100;
	}
	
	if ( $old >= 10 ){
		$new .= $num[ $old / 10 ];
		$old %= 10;
	}
	
	$new .= $num[ $old ];
	return $new;
	
}

//สร้าง link ไปหน้า setting
add_filter( 'plugin_action_links', 'menu_link', 10, 2 );
?>
