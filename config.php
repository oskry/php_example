<?php 
session_start();
date_default_timezone_set("Europe/Warsaw");
include("db.config.php");
include("inc/mta_funcs.php");
include("custom.funcs.php");

//Konfiguracja servera MTA
	include( "inc/mta_sdk.php" );
	$mtaServer = new mta("host", port, "user", "pass");
//

$weathers=array(
	1 => array("Słonecznie","1.png"),
	2 => array("Pogodnie, lekkie zachmurzenie","12.png"),
	3 => array("Pogodnie, lekkie zachmurzenie","12.png"),
	4 => array("Pochmurnie, lekki wiatr","1.png"),
	5 => array("Lekkie zachmurzenie","9.png"),
	6 => array("Ciepło i pogodnie","1.png"),
	7 => array("Pochmurno, lekki wiatr","9.png"),
	9 => array("Mgliście","9.png"),
	10 => array("Bezchmurnie, pogodnie","1.png"),
	12 => array("Ciepło, lekkie zachmurzenie","12.png"),
	13 => array("Ciepło, lekkie zachmurzenie","12.png")
);

//Konfiguracja rainTPL
	include "inc/rain.tpl.class.php";
	raintpl::configure("base_url", null );
	raintpl::configure("tpl_dir", "tpl/" );
	raintpl::configure("cache_dir", "tmp/" );
	$tpl = new RainTPL;
	if(isset($_SESSION['tpl_tmp'])) {$tpl->assign($_SESSION['tpl_tmp']); unset($_SESSION['tpl_tmp']);}
	function tplTmp($tmp)
	{
		$_SESSION['tpl_tmp'] = $tmp;
	}
//
function getUserData($user_id = -1) {
	global $db;
    if($user_id == -1) {
        $user_id = $_SESSION['user_id'];
    }
    $prepare = $db->prepare("SELECT * FROM players WHERE id = ".(int)$user_id);
	$exec = $prepare->execute();
    if($exec == 0) {
        return false;
    }
    return $prepare->fetch(PDO::FETCH_ASSOC);
}

function checklogin()
{
	if(!$_SESSION['logged'])
	{
		header("Location: ?page=login");
		die;
	}
}


if(!isset($_SESSION['logged'])) {
    $_SESSION['logged'] = false;
    $_SESSION['user_id'] = -1;
}
else 
{
	$user = getUserData();
	$tpl->assign("user",$user);
	if(isInAcl($user['login'],"Admin") || isInAcl($user['login'],"SuperModerator")) $jestadmin=true;
	else $jestadmin=false;
	$tpl->assign("isadmin",$jestadmin);
	
	$menu = [
		["Dashboard","index","fa-home"],
		["Sklep (".$user['pktpr']." pkt)","shop","fa-flask"],
		["Wspomóż nas","sponsoring","fa-credit-card"],
		"Serwer|fa-flask" => [
			["Logi banku","bank"],
			["Lista vip","vips"],
			["Lista online","online"],
			["Rankingi","rankingi"],
			["Statystyki wezwań","frakcje"], 
			["Historia pojazdów","carhist"],
		],
		["Panel Administratora","admin","fa-flask",true],
	];
	$htmlmenu="";
	foreach($menu as $key => $val)
	{
		if(is_string($key))
		{
			
			$tmp='';
			$active=false;
			$display=true;
			$icn=explode("|",$key);
				if(!empty($icn[1])) $icon = '<i class="fa '.$icn[1].'"></i> ';
				foreach($val as $v)
				{
					if($_GET['page'] == $v[1]) $active=true;
					$tmp.='<li '.($_GET['page'] == $v[1] ? 'class=\'active\'' : '').'><a href="?page='.$v[1].'">'.$v[0].'</a></li>';
				}
				$htmlmenu.= '<li class="submenu '.($active ? 'active open' : '').'"><a href="#"><i class="fa fa-flask"></i> <span>'.$icn[0].'</span> <i class="arrow fa fa-chevron-right"></i></a><ul>'.$tmp."</ul>";
		}
		else 
		{
			$display=true;
			if($val[3] == true	&& !isInAcl($user['login'],"Admin") && !isInAcl($user['login'],"SuperModerator"))
			{
				$display=false;
			}
			if($display)
			{
				if(!empty($val[2])) $icon = '<i class="fa '.$val[2].'"></i> ';
				$htmlmenu.='<li '.($_GET['page'] == $val[1] ? 'class=\'active\'' : '').'><a href="?page='.$val[1].'">'.$icon.'<span>'.$val[0].'</span></a></li>';
			}
		}
	}
		
	$tpl->assign("htmlmenu",$htmlmenu);
}

?>