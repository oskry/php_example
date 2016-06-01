<?php
session_start();
error_reporting(E_ALL && ~E_WARNING && ~E_STRICT);
$pages = array("index","magazyn","magazyn_add","magazyn_remove","magazyn_mass_remove","magazyn_mass_add","login","logout","generuj_fakture","generuj_wz");
$page = $_GET['x']; 
if(empty($page)) $page = "index"; 
require_once('var/config.php'); 
if(in_array($page,$pages)) 
{
if($page=="login")
{
$tpl->assign("thispage",$page); 
require_once("pages/".$page.".php"); 
$tpl->draw($page); 
}
else
{
	$tpl->assign("thispage",$page); 
	require_once("pages/header.php"); 
	require_once("pages/".$page.".php"); 
	require_once("pages/footer.php");
	$tpl->draw("header");
	$tpl->draw($page); 
	$tpl->draw("footer"); 
}
}
?>