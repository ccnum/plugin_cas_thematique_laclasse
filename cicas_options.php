<?php

/**
* Action de redirection si la requete vient d'un ENT référencé dans la configuration du plugin
*
**/	
/*
	$referer = $_SERVER['HTTP_REFERER'];
	$string = 'http://www.laclasse.com';
	if ((strpos($referer,$string) !== FALSE)&&(strpos($referer,'service') == FALSE))
	{
		//echo "serveur : ".$_SERVER['HTTP_REFERER'];
		$location = "http://".$_SERVER["HTTP_HOST"]."/?url=spip.php%3Fpage%3Dsommaire&cicas=oui&ent=1";
		header('Location: '.$location);
		exit;
	}
	elseif (strpos($referer,$string) !== FALSE)
	{
		//echo "serveur : ".$_SERVER['HTTP_REFERER'];
		//$location = $_SERVER["HTTP_HOST"]."/?url=spip.php%3Fpage%3Dsommaire&cicas=oui&ent=1";
		$location = "http://www.erasme.org/".$_SERVER["HTTP_REQUEST_URI"];
		header('Location: '.$location);
		exit;
	}
*/
?>