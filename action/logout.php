<?php
/**
 * Plugin Authentification CAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/cookie');

// http://doc.spip.org/@action_logout_dist
function action_logout_dist()
{
	global $visiteur_session, $ignore_auth_http;
	$logout =_request('logout');
	$url = _request('url');
	// cas particulier, logout dans l'espace public
	if ($logout == 'public' AND !$url)
		$url = url_de_base();

//------- Debut ajout CI -----
	include_spip('inc/cicas_commun');
	
	// lire la configuration du plugin
	cicas_lire_meta();
	
	$ciauthcas= false;
	if ($GLOBALS['ciconfig']['cicas']=="oui" OR isset($_COOKIE['cicas_sso'])) {
		if ($GLOBALS['ciconfig']['cicasurldefaut'])
			$ciauthcas= true;
	}
//------- Fin ajout CI -----

	// seul le loge peut se deloger (mais id_auteur peut valoir 0 apres une restauration avortee)
	if (is_numeric($visiteur_session['id_auteur'])) {
		include_spip('inc/auth');
		auth_trace($visiteur_session, '0000-00-00 00:00:00');
	// le logout explicite vaut destruction de toutes les sessions
		if (isset($_COOKIE['spip_session'])) {
			$session = charger_fonction('session', 'inc');
			$session($visiteur_session['id_auteur']);
			spip_setcookie('spip_session', $_COOKIE['spip_session'], time()-3600);
		}
		// si authentification http, et que la personne est loge,
		// pour se deconnecter, il faut proposer un nouveau formulaire de connexion http
		if (isset($_SERVER['PHP_AUTH_USER']) AND !$ignore_auth_http AND $GLOBALS['auth_can_disconnect']) {
			  ask_php_auth(_T('login_deconnexion_ok'),
				       _T('login_verifiez_navigateur'),
				       _T('login_retour_public'),
				       	"redirect=". _DIR_RESTREINT_ABS, 
				       _T('login_test_navigateur'),
				       true);
			
		}
//------- Debut ajout CI -----
		if ($ciauthcas) {
	
			include_spip('inc/cicas_commun');
			// import phpCAS lib
			include_spip('CAS');

			// Pour la solution hybride utilisation d'un cookie
			if(isset($_COOKIE['cicas_sso']))
				spip_setcookie('cicas_sso', '', time() - 3600);
			
			// D�terminer l'origine de l'appel (intranet, internet, ...)
			// .i2 ou .ader.gouv.fr ou .gouv.fr ou .agri
			$ciurlcas=cicas_url_serveur_cas();	
		
			// initialize phpCAS
			$cirep='';
			$ciport=intval($GLOBALS['ciconfig']['cicasport']);
			if (isset($GLOBALS['ciconfig']['cicasrepertoire'])) $cirep=$GLOBALS['ciconfig']['cicasrepertoire'];
			
			phpCAS::client(CAS_VERSION_2_0,$ciurlcas,$ciport,$cirep);
			
			if (isset($_GET['lang'])) {
				$cilangphpcas=cicas_lang_phpcas($_GET['lang']);
			} else {
				$cilangphpcas='french';
			}		
			phpCAS::setLang($cilangphpcas);
	
			// D�terminer l'url retour
			$ci_url_retour = cicas_url_retour($url);
			
			// deconnexion de CAS avec l'url retour	
/*			
			if (method_exists('phpCAS','logoutWithUrl')) {
				// Compatibilit� avec les versions r�centes de phpCAS
				phpCAS::logoutWithUrl(urlencode($ci_url_retour));
			} else {
				phpCAS::logout(urlencode($ci_url_retour));
			}
*/
			phpCAS::logoutWithUrl($ci_url_retour);
			//phpCAS::logoutWithRedirectService($ci_url_retour);
			
		}
//------- Fin ajout CI -----
		
	}

//------- Debut ajout CI -----
	if (!$ciauthcas) {
//------- Fin ajout CI -----
		// Rediriger en contrant le cache navigateur (Safari3)
		include_spip('inc/headers');
		redirige_par_entete($url
			? parametre_url($url, 'var_hasard', uniqid(rand()), '&')
			: generer_url_public('login'));
//------- Debut ajout CI -----
	}
//------- Fin ajout CI -----
}

?>
