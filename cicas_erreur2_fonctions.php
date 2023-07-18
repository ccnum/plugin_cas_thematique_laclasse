<?php
/**
 * Plugin Authentification CAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

/*-----------------------------------------------------------------
// Filtre pour le logout de CAS dans le cas de l'erreur 2
------------------------------------------------------------------*/

function cicas_logout_cas() {
	
	global $visiteur_session;

	include_spip('inc/cicas_commun');
	
	// import phpCAS lib
	include_spip('CAS');

	// Pour la solution hybride utilisation d'un cookie
	if(isset($_COOKIE['cicas_sso']))
		spip_setcookie('cicas_sso', '', time() - 3600);
	
	// Determiner l'origine de l'appel (intranet, internet, ...)
	// .i2 ou .ader.gouv.fr ou .gouv.fr ou .agri
	$ciurlcas=cicas_url_serveur_cas(0,false,true);	

	// Nombre de serveurs CAS additionnels
	$ci_nbre_serveurs_additionnels = cicas_nombre_serveurs_additionnels();
		
	// Lire la configuration du plugin pour la session
	$tableau_config = cicas_lire_meta(0,false,true);
	
	// initialize phpCAS
	$cirep='';
	$ciport=intval($tableau_config['cicasport']);
	if (isset($tableau_config['cicasrepertoire'])) $cirep=$tableau_config['cicasrepertoire'];
	
	phpCAS::client(CAS_VERSION_2_0,$ciurlcas,$ciport,$cirep);
	
	phpCAS::setLang(cicas_lang_phpcas());

	// Determiner l'url retour
	$ci_url_retour = cicas_url_retour('');
	

	// deconnexion de CAS uniquement si on n'est pas connecte dans SPIP
	if (!isset($visiteur_session['id_auteur'])) {
		// deconnexion de CAS avec l'url retour
/*		
		if (method_exists('phpCAS','logoutWithUrl')) {
			// Compatibilite avec les versions recentes de phpCAS
			phpCAS::logoutWithUrl(urlencode($ci_url_retour));
		} else {
			phpCAS::logout(urlencode($ci_url_retour));
		}
*/
//		phpCAS::logoutWithUrl(urlencode($ci_url_retour));
		phpCAS::logoutWithUrl($ci_url_retour);
	}
	
	return '';
      	
}	            	
?>