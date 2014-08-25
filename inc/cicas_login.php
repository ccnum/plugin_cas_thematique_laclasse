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


// lire la configuration du plugin
cicas_lire_meta();


// phpCAS::setDebug();

// D�terminer l'origine de l'appel (intranet, internet, ...)
// .i2 ou .ader.gouv.fr ou .gouv.fr ou .agri
$ciurlcas=cicas_url_serveur_cas();	

// initialise phpCAS
$cirep='';
$ciport=intval($GLOBALS['ciconfig']['cicasport']);
if (isset($GLOBALS['ciconfig']['cicasrepertoire'])) $cirep=$GLOBALS['ciconfig']['cicasrepertoire'];
phpCAS::client(CAS_VERSION_2_0,$ciurlcas,$ciport,$cirep);

// si url differente pour la validation du ticket  
if (isset($GLOBALS['ciconfig']['cicas_svu_url']) AND $GLOBALS['ciconfig']['cicas_svu_url']){
	$ci_svu = 'https://'.$GLOBALS['ciconfig']['cicas_svu_url'];

    if (isset($GLOBALS['ciconfig']['cicas_svu_port']) AND $GLOBALS['ciconfig']['cicas_svu_port']!=443)
        $ci_svu .= ':'.$GLOBALS['ciconfig']['cicas_svu_port'];

    if (isset($GLOBALS['ciconfig']['cicas_svu_repertoire']))  
	    $ci_svu .= $GLOBALS['ciconfig']['cicas_svu_repertoire'];

    $ci_svu .= '/serviceValidate';

	phpCAS::setServerServiceValidateURL($ci_svu);
}	

// langue
phpCAS::setLang(cicas_lang_phpcas($_GET['lang']));

// enlever le pied de page de CAS 
phpCAS::SetHTMLFooter('<hr>');

// Pour les versions r�centes de phpCAS
/*
if (method_exists('phpCAS','setNoCasServerValidation')) {
	phpCAS::setNoCasServerValidation();
}
*/
phpCAS::setNoCasServerValidation();

// forcer l'authentication CAS
phpCAS::forceAuthentication();

// A ce stade, l'utilisateur a ete authentifie par le serveur CAS
// et l'identifiant de l'utilisateur renvoye par CAS peut etre lu avec phpCAS::getUser().

$ci_cas_userid = '';
if ($ci_cas_userid=phpCAS::getUser()) {
	
	$auteur = array();
	$auteur = cicas_verifier_identifiant($ci_cas_userid);

	if (!isset($auteur['id_auteur'])) {
		
		// compatibilit� avec les anciennes adresses email	
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