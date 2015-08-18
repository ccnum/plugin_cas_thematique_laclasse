<?php

/**
* Action de redirection si la requete vient d'un ENT référencé dans la configuration du plugin
*
**/
	//echo "serveur".$_SERVER['HTTP_REFERER'];
	if ((strpos($_SERVER['HTTP_REFERER'],'www.laclasse.com') !== false)&&(strpos($_SERVER['HTTP_REFERER'],'ticket') == false))
	{
		$location = "http://".$_SERVER["HTTP_HOST"]."/?url=spip.php%3Fpage%3Dsommaire&cicas=oui&ent=1";
		header('Location: '.$location);
		exit;
	}
?>