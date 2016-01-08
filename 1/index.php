<?php
session_start();

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

//从POST过来的signed_request中提取oauth2信息
if(!empty($_REQUEST["signed_request"])){
	$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY  );
	$data=$o->parseSignedRequest($_REQUEST["signed_request"]);
	if($data=='-2'){
		 die('签名错误!');
	}else{
		$_SESSION['oauth2']=$data;
	}
}
//判断用户是否授权
if (empty($_SESSION['oauth2']["user_id"])) {
		include "auth.php";
		exit;
} else {
		$c = new SaeTClientV2( WB_AKEY , WB_SKEY ,$_SESSION['oauth2']['oauth_token'] ,'' );
} 

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>备份我的微博</title>
</head>

<body>

<?php
//$ms  = $c->home_timeline(); // done

?>
<table align="center">
<tr><td align="center">
<h2>请输入备份微博的邮箱：</h2>
</td></tr>
<tr><td align="center">
<form action="" >
<input type="text" name="text" style="width:300px" />
&nbsp;<input type="submit" value="发送"/>
</form>
<?php
$max_item_cnt = 2000;

if( isset($_REQUEST['text']) )
{
   $mail = new SaeMail();
   $to = $_REQUEST['text'];
   $subject = '您的微博备份邮件';
   $content = '<table>';
   $page_no = 1;
   $item_cnt = 0;
   
   $ms  = $c->user_timeline_by_id($c->client_id, $page_no, 200); 
   
   while( is_array( $ms['statuses'] ) ):
       foreach( $ms['statuses'] as $item ):
		   $item_cnt ++;
		   $content = $content . '<p><tr><td>'.$item_cnt.'</td><td>'.$item['created_at'].'</td><td>'.$item['text'].'</td></tr>';
       endforeach;
	   $page_no++;
	   if ($item_cnt >= $max_item_cnt):
	       break;
	   endif;
	   $ms  = $c->user_timeline_by_id($c->client_id, $page_no, 200);
   endwhile; 
   $content = $content.'</table>';
   
   $mail->setOpt( 
		array( 	'from' => 'fetchmyweibo@gmail.com', 
				'to' => $to, 
				'smtp_host' => 'smtp.gmail.com', 
				'smtp_port' => 587, 
				'smtp_username' => 'fetchmyweibo', 
				'smtp_password' => 'ilovelj1', 
				'subject' => $subject, 
				'content' => $content, 
				'content_type' => "HTML", 
				'tls' => true 
			) 
		);   
   
   $ret = $mail->send();
   echo '<p>已将'.$item_cnt.'条微博发至您的邮箱：'.$to;
} else {
   echo '<p>*由于API调用限制，目前仅能备份您最新'.$max_item_cnt.'条微博。';
}
?>

</tr></td>
</table>
</body>
</html>
