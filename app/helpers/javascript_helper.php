<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 자바스크립트 관련 helper 모음.
 * @author gabia
 * @since version 1.0 - 2009. 7. 7.
 */

function js($content) {
	return "
	<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">\n
	<script type=\"text/javascript\" charset=\"utf-8\">".$content."</script>
	";
}

function alert($msg) {
	$msg = str_replace('<br />','\n',$msg);
	echo js("alert('$msg')");
}

function pageRedirect($url, $msg = '', $target = 'self') {
	if ($msg) {
		alert($msg);
	}
	echo js($target . ".document.location.replace('$url')");
}

function pageLocation($url, $msg = '', $target = 'self') {
	if ($msg) {
		alert($msg);
	}
	echo js($target . ".document.location.href='$url'");
}

function pageBack($msg = '') {
	if ($msg) {
		alert($msg);
	}
	echo js("history.back();");
	exit;
}

function pageReload($msg = '', $target = 'self') {
	if ($msg) {
		alert($msg);
	}
	echo js($target . ".document.location.reload();");
	if($target=='parent' || $target=='top') echo js("document.location.href='about:blank';");
}

function pageClose($msg = '') {
	if ($msg) {
		alert($msg);
	}
	echo js("self.close();");
}

function openerRedirect($url, $msg = '') {
	if ($msg) {
		alert($msg);
	}
	echo js("opener.document.location.replace('$url')");
}

function openDialog($title, $layerId, $customOptions=array(), $target = 'self', $callback='') {
	$CI =& get_instance();

	if	(strpos($_SERVER['HTTP_USER_AGENT'], "Firefox") !== false) {
		if (strpos($callback, "location.reload()") !== false) $callback = str_replace("location.reload()","location.reload(true)",$callback);
	}

	echo("<script type='text/javascript'>");
	echo("{$target}.loadingStop('body',true);");
	echo("{$target}.loadingStop();");
	echo("{$target}.openDialog('{$title}', '{$layerId}', ".json_encode($customOptions).", function(){{$callback}});");
	echo("</script>");
}

function openDialogAlert($msg,$width,$height,$target = 'self',$callback='',$options=array()) {
	$CI =& get_instance();
	if($CI->mobileMode){
		$msg = str_replace(array("<br />","<br/>","<br>"),"",$msg);
		$msg = strip_tags($msg);
	}

	if (strpos($_SERVER['HTTP_USER_AGENT'], "Firefox") !== false) {
		if (strpos($callback, "location.reload()") !== false) $callback = str_replace("location.reload()","location.reload(true)",$callback);
	}

	echo("<script type='text/javascript'>");

	echo("{$target}.loadingStop('body',true);");
	echo("{$target}.loadingStop();");
	echo("{$target}.openDialogAlert('{$msg}','{$width}','{$height}',function(){{$callback}},".json_encode($options).");");
	echo("</script>");
}

function openDialogConfirm($msg,$width,$height,$target = 'self',$yesCallback='',$noCallback='') {
	$CI =& get_instance();
	if($CI->mobileMode){
		$msg = str_replace(array("<br />","<br/>","<br>"),"",$msg);
		$msg = strip_tags($msg);
	}
	echo("<script type='text/javascript'>");
	echo("{$target}.loadingStop();");
	echo("{$target}.openDialogConfirm('{$msg}','{$width}','{$height}',function(){{$yesCallback}},function(){{$noCallback}});");
	echo("</script>");
}

// 배열로 폼을 만들어서 submit해 주는 함수
function arrayToFormSubmit($formName, $formAction, $params, $formTarget = '', $noSubmit = ''){
	if	(is_array($params) && count($params) > 0){
		echo '<form name="' . $formName . '" method="post" action="' . $formAction . '"';
		if	($formTarget)	echo ' target="' . $formTarget . '"';
		echo '>';
		foreach($params as $name => $value){
			if	(strlen($value) > 255){
				echo '<textarea name="' . $name . '" style="display:none;">' . $value . '</textarea>';
			}else{
				echo '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
			}
		}
		echo '</form>';

		if	($noSubmit != 'y'){
			echo '<script>' . $formName . '.submit();</script>';
		}
	}
}


// END
/* End of file helper.php */
/* Location: ./app/helper/javascript.php */
