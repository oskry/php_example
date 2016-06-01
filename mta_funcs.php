<?php 
function isInAcl($login,$group)
{
	global $mtaServer;
	$resource = $mtaServer->getResource ( "resource" );
	$retn = $resource->call ( "isInACL",$login,$group); 
	if($retn[0] == "1") return true;
	else return false;
}
?>