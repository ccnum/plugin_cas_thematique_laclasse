<?php
/**
 * Plugin Authentification CAS
 * Copyright (c) Christophe IMBERTI
 */

if (!defined("_ECRIRE_INC_VERSION")) return;    #securite

/**
 * Declenchement de l'authentification CAS puis redirection
 */

include_spip('inc/headers');
include_spip('inc/session');
include_spip('inc/cookie');
include_spip('inc/texte');
include_spip('base/abstract_sql');
include_spip('inc/headers');
include_spip('inc/cicas_commun');

// import phpCAS lib
include_spip('CAS');

// redirection par defaut
$ciredirect = generer_url_public('');

session_start();

if ( !isset($_SESSION['cicas']) || !is_array($_SESSION['cicas']) ) {
     $_SESSION['cicas'] = array('config_id' => 1);
}

if (empty($_SESSION['cicas']['config_id']) || ($_SESSION['cicas']['config_id'] > lire_config('cicas/server_nb',1)))
    $_SESSION['cicas']['config_id'] = 1;

$auth = false;
$id_ent = 0;

//Calcul de l'ent si passage de paramètres
	//Soit par nom de domaine
	if (isset($_GET['domaine']))
	{
		$tableau = array();
		$tableau = @unserialize($GLOBALS['meta']['cicas']);

		for ($j = 1; $j <= lire_config('cicas/server_nb',1); $j++) 
		{			
			// test
			if ($j > 1) $domaine = $tableau['config'.$j]['cicasurldefaut']; else $domaine = $tableau['cicasurldefaut'];
			
			//S'agit il du même domaine
			if 	($_GET['domaine'] == $domaine) 
			{
				$id_ent = $j;
				break;
			}
		}
	}

	//Soit par index de l'ent
	//if (isset($_GET['ent']))	
	if (isset($_GET['ent'])&&(is_numeric($_GET['ent']))&&($_GET['ent']>0))
	{
		$id_ent=$_GET['ent'];
	}


//On force l'authentification sur un CAS si l'ent existe
	if ($id_ent !== 0)
	{
		$_SESSION['cicas']['config_id'] = $id_ent;
		cicas_init_phpCAS($id_ent);
	   	$auth = true;
	   	//error_log($id_ent."\n", 3, LOG_PATH);
	}
	else
	{
		//Sinon on les vérifie séquentiellement
		for ($j = $_SESSION['cicas']['config_id']; $j <= lire_config('cicas/server_nb',1); $j++) {

		    cicas_init_phpCAS($j);

		    if ($auth = phpCAS::checkAuthentication()) {
			    $_SESSION['cicas']['config_id'] = $j;
		        break;
		    }
		    else
		    {
			    session_regenerate_id();
			    unset($_SESSION['phpCAS']);
			}
		}

		if ($auth == false) {
		    //session_regenerate_id();
		    //unset($_SESSION['phpCAS']);
		    $_SESSION['cicas']['config_id'] = 1;
		    cicas_init_phpCAS($_SESSION['cicas']['config_id']);
		}
	}

// forcer l'authentication CAS
phpCAS::forceAuthentication();

// A ce stade, l'utilisateur a ete authentifie par le serveur CAS
// et l'identifiant de l'utilisateur renvoye par CAS peut etre lu avec phpCAS::getUser().

$ci_cas_userid = '';
if ($ci_cas_userid=phpCAS::getUser()) {
	//foreach (phpCAS::getAttributes() as $key => $value) { $tt_att .= $key.':'.$value.'\n'; }
	//error_log($tt_att."\n", 3, LOG_PATH);

	$auteur = array();
	$auteur = cicas_verifier_identifiant($ci_cas_userid);

	if (!isset($auteur['id_auteur'])) {
		
		// compatibilité avec les anciennes adresses email	
		if (!isset($GLOBALS['ciconfig']['cicasuid']) 
			OR $GLOBALS['ciconfig']['cicasuid']==""  
			OR $GLOBALS['ciconfig']['cicasuid']=="email") {

			$ci_pos = strpos($ci_cas_userid, '@');
			if ($ci_pos AND $ci_pos > 0) {
				$ci_tableau_email = explode('@',$ci_cas_userid);
				$ci_nom_mail = strtolower($ci_tableau_email[0]);
				$ci_domaine_mail = strtolower($ci_tableau_email[1]);

				// compatibilite par defaut
				$cicasmailcompatible = array('equipement.gouv.fr' => 'developpement-durable.gouv.fr');
				
				// compatibilite figurant dans le fichier de parametrage config/_config_cas.php
				if (isset($GLOBALS['ciconfig']['cicasmailcompatible'])) {
					if (is_array($GLOBALS['ciconfig']['cicasmailcompatible'])) {
						$cicasmailcompatible = $GLOBALS['ciconfig']['cicasmailcompatible'];
					}
				}
				
				foreach ($cicasmailcompatible as $cle=>$valeur) {
					if ($ci_domaine_mail==$valeur) {
						$auteur = cicas_verifier_identifiant($ci_nom_mail.'@'.$cle);
						if (isset($auteur['id_auteur']))
							break;
					}
				}
			}
		}
		
	}

	if (!isset($auteur['id_auteur'])) {
		// Envoyer au pipeline
		$cipipeline = true;
		if (@is_readable($charger = _CACHE_PIPELINES)){
			include_once($charger);
			if (!function_exists('execute_pipeline_cicas'))
				$cipipeline = false;
		}
		
		if ($cipipeline)
			$auteur = pipeline('cicas',
				array(
					'args' => $ci_cas_userid,
					'data' => array()
				)
			);
	}	

	// Si l'auteur a un compte CAS qui n'existe pas dans la base SPIP
	// On lui crèe un compte à la volée si c'est possible
    $auteur['statut_forced'] = ($_SESSION['cicas']['config_id'] == 1) ? lire_config('cicas/cicasstatutcrea') : lire_config('cicas/config'.$_SESSION['cicas']['config_id'].'/cicasstatutcrea');
	if (!isset($auteur['id_auteur']) && $auteur['statut_forced']) {
    	$auteur['source'] = 'cas';
    	$auteur['pass'] = '';

        $cicasuid = ($_SESSION['cicas']['config_id'] == 1) ? lire_config('cicas/cicasuid') : lire_config('cicas/config'.$_SESSION['cicas']['config_id'].'/cicasuid');

    	if ($cicasuid == 'email') {
		    $auteur['email'] = strtolower($ci_cas_userid);
		    $auteur['login'] = '';    	
    	}

    	if ($cicasuid == 'login') {
		    $auteur['email'] = '';
		    $auteur['login'] = strtolower($ci_cas_userid);
    	}
        
        $auteur['statut'] = $auteur['statut_forced'];
        unset($auteur['statut_forced']);
	    // rajouter le statut indiqué à l'install
		$r = sql_insertq('spip_auteurs', $auteur);

		// On recharge le profil utilisateur créé
		$auteur = cicas_verifier_identifiant($ci_cas_userid);
	}

    //Mettre à jour le profil auteur si demandé
    if (isset($auteur['id_auteur']) && (lire_config('cicas/update_auteur_all') || lire_config('cicas/update_auteur_vide'))) {
        //Provisionner les information CAS dans les informations auteurs
        if ($_SESSION['cicas']['config_id'] == 1)
            $attributes = lire_config('cicas/attributes');
        else
            $attributes = lire_config('cicas/config'.$_SESSION['cicas']['config_id'].'/attributes');

        $trouver_table = charger_fonction('trouver_table', 'base');
        $auteur_desc = $trouver_table('spip_auteurs');

        //Lister les champs à actualiser
        $auteur_update = array();
        foreach($attributes as $attribute => $champ) {
            if (isset($auteur_desc['field'][$champ]) && lire_config('cicas/update_auteur_all'))
                $auteur_update[$champ] = '';

            if (isset($auteur_desc['field'][$champ]) && empty($auteur[$champ]) && lire_config('cicas/update_auteur_vide'))
                $auteur_update[$champ] = '';
        }

        //Affecter les données
        foreach($attributes as $attribute => $champ) {
            //Ne pas traiter si le champ auteur n'existe pas ou n'est pas à mettre à jour
            if (!isset($auteur_desc['field'][$champ]) || !isset($auteur_update[$champ]))
                continue;

            if (phpCAS::hasAttribute($attribute)) {
                $auteur_update[$champ] .= " ".phpCAS::getAttribute($attribute);
            } else {
                $auteur_update[$champ] .= " ".$attribute;
            }
            $auteur_update[$champ] = trim($auteur_update[$champ]);
        }

        //Pousser les attributs CAS dans le profil auteur
		$r = sql_updateq('spip_auteurs', $auteur_update, 'id_auteur ='.$auteur['id_auteur']);
    }
	
	if (isset($auteur['id_auteur'])) {

		// URL cible de l'operation de connexion
		$cible = cicas_url_cible();

		//  bloquer ici le visiteur qui tente d'abuser de ses droits
		if (isset($auteur['statut'])) {
			if (cicas_is_url_prive($cible)) {
				if ($auteur['statut']=='6forum'){
					$ciredirect = generer_url_public("cicas_erreur3");
					// redirection immediate
					redirige_par_entete($ciredirect);
				}
			}
		}
		
		// on a ete authentifie, construire la session
		// en gerant la duree demandee pour son cookie 
		if ($session_remember !== NULL)
			$auteur['cookie'] = $session_remember;
		$session = charger_fonction('session', 'inc');
		$session($auteur);
/*		
		$p = ($auteur['prefs']) ? unserialize($auteur['prefs']) : array();
		$p['cnx'] = ($session_remember == 'oui') ? 'perma' : '';
		$p = array('prefs' => serialize($p));
		sql_updateq('spip_auteurs', $p, "id_auteur=" . $auteur['id_auteur']);
*/
	
		// Si on est admin, poser le cookie de correspondance
		if (isset($auteur['statut'])) {
			if ($auteur['statut'] == '0minirezo') {
				include_spip('inc/cookie');
				spip_setcookie('spip_admin', '@'.$auteur['login'],time() + 7 * 24 * 3600);
			}
		}
	
		// Si on est connecte, envoyer vers la destination
		if ($cible)
			$ciredirect = $cible;

	} else {
		// Si l'auteur a un compte CAS qui n'existe pas dans la base SPIP
		$ciredirect = generer_url_public("cicas_erreur2");
	}
		
} else {
	$ciredirect = generer_url_public("cicas_erreur1");
}

if (!headers_sent($filename, $linenum)){
    redirige_par_entete($ciredirect);
}else{
	// si les entetes ont deja ete envoyee, redirection par une page  
	echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">',"\n",
	  html_lang_attributes(),'
<head><title>Error</title>
</head>
<body>
<h1>Error : headers already sent by file '.$filename. ', line '.$linenum.'</h1>
<a href="',
	  quote_amp(htmlentities($ciredirect)),
	  '">',
	  _T('navigateur_pas_redirige'),
	  '</a></body></html>';

	exit;

}

?>