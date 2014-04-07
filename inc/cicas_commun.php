<?php
/**
 * Plugin Authentification CAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

include_spip('inc/cookie');

/**
 * D�termination du HOST
 *
 * @param : aucun
 * @return : host
 */
function cicas_url_host() {

	$ci_host = "";

	// lire la configuration du plugin
	cicas_lire_meta();
	
	// ordre de recherche par d�faut (celui de phpCAS)
	$cicashostordre = array('HTTP_X_FORWARDED_SERVER','SERVER_NAME','HTTP_HOST');
	
	// ordre de recherche personnalise dans le fichier de parametrage config/_config_cas.php
	if (isset($GLOBALS['ciconfig']['cicashostordre'])) {
		if (is_array($GLOBALS['ciconfig']['cicashostordre'])) {
			$cicashostordre = $GLOBALS['ciconfig']['cicashostordre'];
		}
	}
	
	foreach ($cicashostordre as $valeur) {
		if (isset($_SERVER[$valeur])) {
			if ($_SERVER[$valeur]) {
				$ci_host = $_SERVER[$valeur];
				break;
			}
		}
	}
	return $ci_host;
}

/**
 * D�termination de l'url de retour
 *
 * @param : demande de redirection (url)
 * @return : url de retour
 */
function cicas_url_retour($url) {
	
	$ci_url = "";
	
	// determination du HOST
	$ci_url = cicas_url_host();

	// cas d'un site en HTTPS
    if ( isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
		$ci_url = "https://".$ci_url;
    } else {
		$ci_url = "http://".$ci_url;
    }
	
	// cas particulier d'une adresse du type www.monserveur.com/repertoire_du_site/article.php3...
	/*
	if (isset($_SERVER['REQUEST_URI'])) {
		// le cas echeant, ne pas tenir compte du repertoire "ecrire"
		$ci_request_uri = str_replace("/ecrire/", "/", $_SERVER['REQUEST_URI']);
			
		$ci_pos = strrpos($ci_request_uri, "/");
		// ne pas tenir compte du premier "/" dans la recherche
		if ($ci_pos AND $ci_pos > 0) {
			$ci_url .= substr($ci_request_uri, 0, $ci_pos);
		}
	}
	
	// demande de redirection
	// (ne pas tenir compte du repertoire "ecrire" ni de "./")
	if ($url AND substr($url,0,6)!="ecrire" AND substr($url,0,1)!=".")
		$ci_url .= "/".$url;
	*/
	return $ci_url;
}


/**
 * D�terminer l'URL du serveur CAS (intranet, internet, ...)
 * correspondant � l'origine de l'appel (.i2 ou .ader.gouv.fr ou .gouv.fr ou .agri)
 * Les correspondances figurent dans le fichier de parametrage
 * sinon l'adresse par defaut est utilisee
 *
 * @param : aucun
 * @return :  URL du serveur CAS correspondant � l'origine de l'appel
 */
function cicas_url_serveur_cas() {

	$ciurlcas='';
	
	// lire la configuration du plugin
	cicas_lire_meta();
		
	// Pour la solution hybride utilisation d'un cookie
	if ($GLOBALS['ciconfig']['cicas']=='oui' OR $GLOBALS['ciconfig']['cicas']=='hybride') {

		$ci_host = cicas_url_host();

		// adresse par defaut du serveur CAS
		$ciurlcas=$GLOBALS['ciconfig']['cicasurldefaut'];
		
		// autre adresse du serveur CAS	selon le type de terminaison de l'adresse d'appel du site SPIP
		if (isset($GLOBALS['ciconfig']['cicasurls'])) {
			if (is_array($GLOBALS['ciconfig']['cicasurls'])) {
				while (list($terminaison, $valcasurl) = each($GLOBALS['ciconfig']['cicasurls'])) {
					if (substr($ci_host,-strlen($terminaison))==$terminaison) {
						$ciurlcas=$valcasurl;
						break;
					}
				}
			}
		}
	}

	return $ciurlcas;
}	


/**
 * D�termination du code de langue de phpCAS qui correspond au code de langue de SPIP
 *
 * @param : code de langue de SPIP
 * @return : code de langue de phpCAS
 */
function cicas_lang_phpcas($lang="") {
	
	$return='french';

	if ($lang) {
		switch ($lang) {
			case 'en':
				$return='english';
				break;
			case 'de':
				$return='german';
				break;
			case 'es':
				$return='spanish';
				break;
		}
	}

	return $return;
}	


/**
 * Cible de l'operation de connexion
 *
 * @param : $prive
 * @return : URL
 */
function cicas_url_cible($prive=null){

	$cible = "";
	
	// La cible de notre operation de connexion
	$url = _request('url');
	$cible = isset($url) ? $url : _DIR_RESTREINT;
	
	// Si on se connecte dans l'espace prive, 
	// ajouter "bonjour" (repere a peu pres les cookies desactives)
	if (is_null($prive) ? cicas_is_url_prive($cible) : $prive) {
		$cible = parametre_url($cible, 'bonjour', 'oui', '&');
	}
	if ($cible=='@page_auteur')
		$cible = generer_url_entite($GLOBALS['auteur_session']['id_auteur'],'auteur');

	if ($cible) {
		$cible = parametre_url($cible, 'var_login', '', '&');
	} 
	
	// transformer la cible absolue en cible relative
	// pour pas echouer quand la meta adresse_site est foireuse
	if (strncmp($cible,$u = url_de_base(),strlen($u))==0){
		$cible = "./".substr($cible,strlen($u));
	}

	return $cible;	
	
}


/**
 * L'URL correspond-t-elle � l'espace priv� de SPIP ?
 *
 * @param : URL
 * @return : true ou false
 */
function cicas_is_url_prive($cible){
	$parse = parse_url($cible);
	return strncmp(substr($parse['path'],-strlen(_DIR_RESTREINT_ABS)), _DIR_RESTREINT_ABS, strlen(_DIR_RESTREINT_ABS))==0;
}


/**
 * Verification de l'existence de l'identifiant dans la table des auteurs
 *
 * @param : identifiant de l'utilisateur renvoye par CAS
 * @return : tableau vide ou contenant la ligne de l'auteur dans spip_auteurs
 */
function cicas_verifier_identifiant($ci_cas_userid) {

	$return = array();
	
	// lire la configuration du plugin
	cicas_lire_meta();
	
	// Interdire identifiant vide
	if ($ci_cas_userid == '') { 
		$return = array();
	} else {
		// Eviter l'injection SQL
		$ci_cas_userid=addslashes($ci_cas_userid);

		$select = "*";
		$from = "spip_auteurs";
/*		
		$where = "(email='".$ci_cas_userid."' OR email='".addslashes(strtolower($ci_cas_userid))."') AND statut<>'5poubelle'";
		$groupby = "";
		$orderby = "nom";
		if ($GLOBALS['ciconfig']['cicasuid']=="LOGIN")
*/
		$where = "(login='".$ci_cas_userid."' OR login='".addslashes($ci_cas_userid)."') AND statut<>'5poubelle'";
		
		$cinumrows = sql_countsel($from, $where);
		
		if ($cinumrows==0) {
			$return = array();
		} else if ($cinumrows==1) {
			$result = sql_select($select, $from, $where,$groupby,$orderby);
			if ($row = sql_fetch($result)) {
				$return = $row;
			}
		} else if ($cinumrows>1) {
			$ci_statut="";
			$result = sql_select($select, $from, $where,$groupby,$orderby);
			while ($row = sql_fetch($result)) {
				$cistocker=true;
				if ($ci_statut) {
					switch ($row['statut']) {
					case '0minirezo':
						if ($ci_statut=='0minirezo') {
							// garder le pr�c�dent si le suivant est un admin restreint
							$cinewid=$row['id_auteur'];
							$cirestreint = sql_countsel("spip_auteurs_rubriques", "id_auteur=".$cinewid);
							if ($cirestreint > 0) $cistocker=false;
						}	
						break;
					case 'ciredval':
						if ($ci_statut=='0minirezo') $cistocker=false;
						break;
					case '1comite':
						if (preg_match("^(0minirezo|ciredval)$",$ci_statut)) $cistocker=false;
						break;
					case '6forum':
						if (preg_match("^(0minirezo|ciredval|1comite)$",$ci_statut)) $cistocker=false;
						break;
					}
				}
				
				if ($cistocker) {
					$ci_statut = $row['statut'];
					$return = $row;
				}
			}
		}
	}

	if (isset($return['id_auteur'])){
		// Pour la solution hybride
		if (_request('cicas')){
			if (_request('cicas')=="oui"){
				if(!isset($_COOKIE['cicas_sso'])) {
					$ci_id_random = mt_rand(1,999999);
					if (!$ci_id_random) $ci_id_random = rand(1,999999);
					spip_setcookie('cicas_sso', $ci_id_random);
				}
			}
		}
	}
	return $return;
}


/**
 * Lecture des parametres de configuration du plugin
 * et alimentation de variables globales
 * S'il existe, le parametrage par fichier est prioritaire
 *
 * @param : aucun
 * @return : false si parametrage par fichier, sinon true
 * Surcharge Erasme cicas_lire_meta() et backup cicas_lire_meta0()
 */
function cicas_lire_meta0() {
	
	$return = true;
	
	if (!isset($GLOBALS['ciconfig']['cicas'])) {

		$GLOBALS['ciconfig']['cicas'] = '';
		$GLOBALS['ciconfig']['cicasuid'] = '';
		$GLOBALS['ciconfig']['cicasurldefaut'] = '';
		$GLOBALS['ciconfig']['cicasrepertoire'] = '';
		$GLOBALS['ciconfig']['cicasport'] = '';
		$GLOBALS['ciconfig']['cicasurls'] = array();
		
		$f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . '_config_cas.php';
	
		if (@file_exists($f)) {
			// parametrage par fichier
			include_once($f);
			
			// compatibilite ascendante
			if ($GLOBALS['ciconfig']['cicasport']=='')
				$GLOBALS['ciconfig']['cicasport'] = '443';
			
			$return = false;
				
		} else {
			// configuration du plugin
			$tableau = array();
			$tableau = @unserialize($GLOBALS['meta']['cicas']);
	
			$GLOBALS['ciconfig']['cicas'] = $tableau['cicas'];
			$GLOBALS['ciconfig']['cicasuid'] = $tableau['cicasuid'];
			$GLOBALS['ciconfig']['cicasurldefaut'] = $tableau['cicasurldefaut'];
			$GLOBALS['ciconfig']['cicasrepertoire'] = $tableau['cicasrepertoire'];
			$GLOBALS['ciconfig']['cicasport'] = $tableau['cicasport'];
		}
	
		// valeur par d�faut
		if (!isset($GLOBALS['ciconfig']['cicas']))
			$GLOBALS['ciconfig']['cicas'] = 'non';
		elseif ($GLOBALS['ciconfig']['cicas']=='')
			$GLOBALS['ciconfig']['cicas'] = 'non';
	
	}
		
    return $return;
}

function cicas_lire_meta() {
	
	include_spip('inc/session');

	$return = true;
	
	//Fonction de l'ENT
	$ent ="laclasse";
	if (session_get('pgp')!='') $ent = session_get('pgp');
	if (isset($_GET['ent'])) $ent = $_GET['ent'];

	$tableau = array(
	'laclasse' => array(
	  'cicas' => 'hybride',
      'cas_version' => '2.0',
      'include_path' => '/var/www/html/CAS-1.2.0/CAS.php',
      'cicasurldefaut' => 'www.laclasse.com',
      'cicasport' => '443',
      'cicasrepertoire' => '/sso',
      'cicasuid' => 'LOGIN',
      'cicasstatutcrea' => '6forum'
     ),
    'cybercolleges42' => array(
	  'cicas' => 'hybride',
      'cas_version' => '2.0',
      'include_path' => '/var/www/html/CAS-1.2.0/CAS.php',
      'cicasurldefaut' => 'cas.cybercolleges42.fr',
      'cicasport' => '443',
      'cicasrepertoire' => '',
      'cicasuid' => 'uid',
      'cicasstatutcrea' => '6forum'
     )
    );
	$GLOBALS['ciconfig']['ent'] = $ent;
	$GLOBALS['ciconfig']['cicas'] = $tableau[$ent]['cicas'];
	$GLOBALS['ciconfig']['cicasuid'] = $tableau[$ent]['cicasuid'];
	$GLOBALS['ciconfig']['cicasurldefaut'] = $tableau[$ent]['cicasurldefaut'];
	$GLOBALS['ciconfig']['cicasrepertoire'] = $tableau[$ent]['cicasrepertoire'];
	$GLOBALS['ciconfig']['cicasport'] = $tableau[$ent]['cicasport'];
	$GLOBALS['ciconfig']['cicasstatutcrea'] = $tableau[$ent]['cicasstatutcrea'];
		
    return $return;
}

/**
 * Lecture des parametres de configuration du plugin
 * et alimentation de variables globales
 * S'il existe, le parametrage par fichier est prioritaire
 *
 * @param : aucun
 * @return : false si parametrage par fichier, sinon true
 */
function cicas_lire_meta_0() {
	
	$return = true;
	
	if (!isset($GLOBALS['ciconfig']['cicas'])) {

		$GLOBALS['ciconfig']['cicas'] = '';
		$GLOBALS['ciconfig']['cicasuid'] = '';
		$GLOBALS['ciconfig']['cicasurldefaut'] = '';
		$GLOBALS['ciconfig']['cicasrepertoire'] = '';
		$GLOBALS['ciconfig']['cicasport'] = '';
		$GLOBALS['ciconfig']['cicasurls'] = array();
		$GLOBALS['ciconfig']['cicasstatutcrea'] = '';

		
		$f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . '_config_cas.php';
	
		if (@file_exists($f)) {
			// parametrage par fichier
			include_once($f);
			
			// compatibilite ascendante
			if ($GLOBALS['ciconfig']['cicasport']=='')
				$GLOBALS['ciconfig']['cicasport'] = '443';

			$return = false;

		} else {
			// configuration du plugin
			$tableau = array();
			$tableau = @unserialize($GLOBALS['meta']['cicas']);

			$GLOBALS['ciconfig']['cicas'] = $tableau['cicas'];
			$GLOBALS['ciconfig']['cicasuid'] = $tableau['cicasuid'];
			$GLOBALS['ciconfig']['cicasurldefaut'] = $tableau['cicasurldefaut'];
			$GLOBALS['ciconfig']['cicasrepertoire'] = $tableau['cicasrepertoire'];
			$GLOBALS['ciconfig']['cicasport'] = $tableau['cicasport'];
			$GLOBALS['ciconfig']['cicasstatutcrea'] = $tableau['cicasstatutcrea'];

		}

		// valeur par d�faut
		if (!isset($GLOBALS['ciconfig']['cicas']))
			$GLOBALS['ciconfig']['cicas'] = 'non';
		elseif ($GLOBALS['ciconfig']['cicas']=='')
			$GLOBALS['ciconfig']['cicas'] = 'non';

	}
		
    return $return;
}

?>
