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

// Nombre de serveurs CAS additionnels
$ci_nbre_serveurs_additionnels = cicas_nombre_serveurs_additionnels();
$ci_id_serveur_auth = '';

// Cas classique (sans serveurs CAS additionnels)
if ($ci_nbre_serveurs_additionnels<1){

	// configure phpCAS
	cicas_configure_phpCAS();
	
	// forcer l'authentication CAS
	phpCAS::forceAuthentication();

} else {
// Cas avec serveurs CAS additionnels
	$request_cicas = _request('cicas');

	// Memoriser, le cas echeant, le choix de l'utilisateur dans un cookie
	if (_request('memoriser') AND _request('memoriser')=='oui'){
		if ($request_cicas AND ($request_cicas=='oui' OR intval($request_cicas)>=1)){
			include_spip('inc/cookie');
			spip_setcookie('cicas_choix', $request_cicas, time() + 365 * 24 * 3600);
		}
	}

	// authentification CAS demandee par un clic sur le lien
	if ($request_cicas AND intval($request_cicas)>=1){
		$ci_id_serveur_auth = intval($request_cicas);
		cicas_configure_phpCAS($ci_id_serveur_auth);
		phpCAS::forceAuthentication();
	} else {
		cicas_configure_phpCAS();
		phpCAS::forceAuthentication();
	}
}

// A ce stade, l'utilisateur a ete authentifie par le serveur CAS
// et l'identifiant de l'utilisateur renvoye par CAS peut etre lu avec phpCAS::getUser().

$ci_cas_userid = '';
if ($ci_cas_userid=phpCAS::getUser()) {
	
	$auteur = array();
	$auteur = cicas_verifier_identifiant($ci_cas_userid);

	if (!isset($auteur['id_auteur'])) {
		
		// Lire la configuration pour cette session
		$tableau_config = cicas_lire_meta(0,false,true);
		
		// compatibilite avec les anciennes adresses email
		if (!isset($tableau_config['cicasuid']) 
			OR $tableau_config['cicasuid']==""  
			OR $tableau_config['cicasuid']=="email") {

			$ci_pos = strpos($ci_cas_userid, '@');
			if ($ci_pos AND $ci_pos > 0) {
				$ci_tableau_email = explode('@',$ci_cas_userid);
				$ci_nom_mail = strtolower($ci_tableau_email[0]);
				$ci_domaine_mail = strtolower($ci_tableau_email[1]);

				// compatibilite par defaut
				$cicasmailcompatible = array('equipement.gouv.fr' => 'developpement-durable.gouv.fr');
				
				// compatibilite figurant dans le fichier de parametrage config/_config_cas.php
				if (isset($tableau_config['cicasmailcompatible'])) {
					if (is_array($tableau_config['cicasmailcompatible'])) {
						$cicasmailcompatible = $tableau_config['cicasmailcompatible'];
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

	// Si l'authentification sur ce serveur CAS a reussi mais que l'auteur n'existe pas dans SPIP
	// le creer automatiquement si le parametrage l'autorise
	if (!isset($auteur['id_auteur']) AND isset($tableau_config['cicas_creer_auteur']) AND $tableau_config['cicas_creer_auteur']){
		$c = array();
		$c['login'] = '';
		$c['pass'] = '';
		$c['webmestre'] = 'non';
                $c['statut'] = $tableau_config['cicas_creer_auteur'];
                $c['source'] = 'cas';

		if ($tableau_config['cicasuid']=='' OR $tableau_config['cicasuid']=='email'){
			$c['email'] = strtolower($ci_cas_userid);
                        $ci_tableau_cicasuid = explode('@',$ci_cas_userid);
			$c['nom'] = strtolower($ci_tableau_cicasuid[0]);
                        $c['login'] = $c['nom'];
                } else {
			$c['nom'] = $ci_cas_userid;
                        $c['login'] = $ci_cas_userid;
                }

                // important (suite aux tests)
                $couples = $c;
                
                // inserer l'auteur
                $id_auteur = sql_insertq("spip_auteurs", $couples);
                
                // tracer le cas echeant
                if (defined('_DIR_PLUGIN_CITRACE')){
                        $commentaire = interdire_scripts(supprimer_numero($couples['nom']))
                        .' ('.interdire_scripts($couples['email']).')'.' - statut:'.$couples['statut'];
                        if ($citrace = charger_fonction('citrace', 'inc'))
                            $citrace('auteur', $id_auteur, "creation automatique de l'auteur", $commentaire);
                }	
		
		// seconde tentative
		$auteur = cicas_verifier_identifiant($ci_cas_userid);
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

                // memorise ci_id_serveur_auth a cause des redirections
                if ($ci_id_serveur_auth)
		    $auteur['cicas_id_serveur'] = $ci_id_serveur_auth;
		
		// on a ete authentifie, construire la session
		// en gerant la duree demandee pour son cookie 
//		if ($session_remember !== NULL)
//			$auteur['cookie'] = $session_remember;
		$session = charger_fonction('session', 'inc');
		$session($auteur);
	
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