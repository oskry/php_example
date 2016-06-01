<?php 
function addLog($uid,$text)
{
	global $db;
	
	$query = $db->prepare("INSERT INTO sklep_logi(uid,data,ip,text) VALUES (:uid,:data,:ip,:text)");
	$query->execute(array(":uid"=>$uid,":data"=>time(),":ip"=>$_SERVER['REMOTE_ADDR'],":text"=>htmlspecialchars($text)));
}
function randomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>