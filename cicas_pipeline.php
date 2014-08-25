<?php
/**
 * Plugin Authentification CAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
/**
 * Pipeline d'aiguillage entre les modes d'authentification
 *
 * @param $flux
 * @return $flux
 */
function cicas_recuperer_fond($flux){
	
	if ($flux['args']['fond']=='formulaires/login'){
		
		include_spip('inc/cicas_commun');
	
		// lire la configuration du plugin
		cicas_lire_meta();

		// authentification CAS
		if ($GLOBALS['ciconfig']['cicas']=='oui' AND $GLOBALS['ciconfig']['cicasurldefaut']) {
			include_spip('inc/cicas_login');

		// authentification hybride CAS et SPIP
		} elseif ($GLOBALS['ciconfig']['cicas']=='hybride') {
			
			// authentification CAS demandee par un clic sur le lien
			if (_request('cicas') AND _request('cicas')=='oui') {
				include_spip('inc/cicas_login');

			} else {
				// ajout du lien vers l'authentification CAS
				include_spip("inc/utils");
				$lien = parametre_url(self(), 'cicas', 'oui');
				$lien = '<a href="'.$lien.'"><img alt="'._T('cicas:eq_lien_auth_hybride').'" src="'.find_in_path('cicas.gif').'" /></a>'
				.'&nbsp;<a href="'.$lien.'" style="vertical-align:top;">&#91;'._T('cicas:eq_lien_auth_hybride').'&#93;</a>';
				$flux['data']['texte'] = str_replace('</form>','</form>'.$lien,$flux['data']['texte']);
			}
		}
	}
	
	return $flux;
}


/**
 * Anti hack
 *
 * @param $tableau
 * @return $tableau
 */
function cicas_pre_edition($tableau){

	if (defined('_CICAS_ANTI_HACK') AND _CICAS_ANTI_HACK=='oui'){	
		// on ne doit pas pouvoir remplacer l'identifiant SSO (email ou login) d'un autre auteur avec son propre identifiant SSO (email ou login)
		if (isset($tableau['args']['action'])  
		    AND isset($tableau['args']['table']) AND $tableau['args']['table']=='spip_auteurs') {
	
	    	$id_auteur = intval($tableau['args']['id_objet']);
		    if ($id_auteur>0) {
				include_spip('inc/texte');
				$row = sql_fetsel('*', 'spip_auteurs', 'id_auteur='.$id_auteur);
				if ($row){
					
					if ($tableau['args']['action']=='modifier'){
						include_spip('inc/cicas_commun');
						cicas_lire_meta();
		    			$mon_id_auteur = (isset($GLOBALS['visiteur_session']['id_auteur']) ? $GLOBALS['visiteur_session']['id_auteur'] : '');
	
						// modifier un autre auteur
		    			if ($id_auteur!=$mon_id_auteur) {
		    				
							if ($GLOBALS['ciconfig']['cicasuid']=="login"){
								$old_login = $row['login'];
								$new_login = strtolower((isset($tableau['data']['login']) ? $tableau['data']['login'] : ''));
	
				    			// modifier l'identifiant SSO (login) d'un autre auteur
					    		if ($new_login!=$old_login){
					    			$mes_logins = array();
					    			if (isset($GLOBALS['visiteur_session']['login']) AND trim($GLOBALS['visiteur_session']['login']))
					    				$mes_logins[] = $GLOBALS['visiteur_session']['login'];
					    			
									// compatibilite avec mes anciens logins	
									$tableau_memo = cicas_sso_memo($mon_id_auteur,"login");
									foreach ($tableau_memo AS $valeur){
										if (trim($valeur))
											$mes_logins[] = $valeur;
									}
					    			
				    				if (in_array($new_login,$mes_logins))
				    					$tableau['data']['login'] = $old_login;
					    		}
							
							} else {
								$old_email = $row['email'];
								$new_email = strtolower((isset($tableau['data']['email']) ? $tableau['data']['email'] : ''));
				    			
				    			// modifier l'identifiant SSO (email) d'un autre auteur
					    		if ($new_email!=$old_email){
		
									$mes_emails = array();
									if (isset($GLOBALS['visiteur_session']['email']) AND trim($GLOBALS['visiteur_session']['email']))
										$mes_emails[] = $GLOBALS['visiteur_session']['email'];
										
									// compatibilite avec mes anciennes adresses email	
									$tableau_memo = cicas_sso_memo($mon_id_auteur,"email");
									foreach ($tableau_memo AS $valeur){
										if (trim($valeur))
											$mes_emails[] = $valeur;
									}

				    				if (in_array($new_email,$mes_emails)){
				    					$tableau['data']['email'] = $old_email;
	
					    			} else {				
										// compatibilite avec les anciennes adresses email	
										$new_tableau_email = explode('@',$new_email);
										$new_nom_mail = strtolower($new_tableau_email[0]);
										$new_domaine_mail = strtolower($new_tableau_email[1]);
	
										// compatibilite par defaut
										$cicasmailcompatible = array('equipement.gouv.fr' => 'developpement-durable.gouv.fr');
										
										// compatibilite figurant dans le fichier de parametrage config/_config_cas.php
										if (isset($GLOBALS['ciconfig']['cicasmailcompatible'])) {
											if (is_array($GLOBALS['ciconfig']['cicasmailcompatible'])) {
												$cicasmailcompatible = $GLOBALS['ciconfig']['cicasmailcompatible'];
											}
										}
										
										foreach ($mes_emails AS $mon_email){
											$mon_tableau_email = explode('@',$mon_email);
											$mon_nom_mail = strtolower($mon_tableau_email[0]);
											$mon_domaine_mail = strtolower($mon_tableau_email[1]);
			
											if ($new_nom_mail==$mon_nom_mail){											
												foreach ($cicasmailcompatible as $cle=>$valeur) {
													if ( ($mon_domaine_mail==$valeur AND $new_domaine_mail==$cle) OR ($mon_domaine_mail==$cle AND $new_domaine_mail==$valeur)) {
								    					$tableau['data']['email'] = $old_email;
														break;
													}
												}
											}
										}
										
				    				}
				    				
				    				// controle d'unicite de l'email
									if (!cicas_unicite_email($new_email,$id_auteur))
										$tableau['data']['email'] = $old_email;
					    		}
				    		}
			    		} else {
			    			// se modifier soi meme
			    			$memo = '';
							if ($GLOBALS['ciconfig']['cicasuid']=="login"){
								$old_login = $row['login'];
								$new_login = strtolower((isset($tableau['data']['login']) ? $tableau['data']['login'] : ''));
	
				    			// modifier son propre identifiant SSO (login)
					    		if ($old_login AND $new_login!=$old_login){
					    			// si administrateur du site
									if (autoriser('configurer'))
						    			cicas_ecrire_memo($mon_id_auteur,$old_login,'login');
					    			else
				    					$tableau['data']['login'] = $old_login;
					    		}
							} else {
								$old_email = $row['email'];
								$new_email = strtolower((isset($tableau['data']['email']) ? $tableau['data']['email'] : ''));
				    			// modifier son propre identifiant SSO (email)
					    		if ($old_email AND $new_email!=$old_email){
					    			// si administrateur du site
									if (autoriser('configurer')){
					    				// controle d'unicite de l'email
										if (!cicas_unicite_email($new_email,$id_auteur))
											$tableau['data']['email'] = $old_email;
										else
						    				cicas_ecrire_memo($mon_id_auteur,$old_email,'email');
					    			// sinon interdire de modifier son email	
									} else {
				    					$tableau['data']['email'] = $old_email;
									}
					    		}
		
							}
								
			    		}
					}
				}
			}
	    }	
	}

	return $tableau;
}

function cicas_sso_memo($id_auteur,$cicasuid='email'){
	$return = array();
	if ($id_auteur=intval($id_auteur)){
		$cherche = $id_auteur.'/'.$cicasuid.':';
		$longeur = strlen($cherche); 
		$tableau_memo = cicas_lire_memo();
		foreach ($tableau_memo AS $valeur){
			if (substr($valeur,0,$longeur)==$cherche)
				$return[] = substr($valeur,$longeur);
		}
	}
	
	return $return;
}

function cicas_ecrire_memo($id_auteur,$id_sso,$cicasuid='email'){
	
	if ($id_auteur AND $id_sso){
		$memo = $id_auteur.'/'.$cicasuid.':'.strtolower($id_sso).'|';
		
		$fichier = cicas_fichier_memo();
		$option_fopen = "ab";

		// supprimer le fichier s'il est trop gros
		$taille_max = ( (defined('_CICAS_TAILLE_MAX') AND intval(_CICAS_TAILLE_MAX)>0) ? intval(_CICAS_TAILLE_MAX) : 150);	// en Ko		
		if (@file_exists($fichier) AND @filesize($fichier)>($taille_max * 1024))
			$option_fopen = "w";

		$f = @fopen($fichier, $option_fopen);
		if ($f) {
			fputs($f, $memo);
			fclose($f);
		}
		
	}
}

function cicas_lire_memo(){
	$return = array();
	$fichier = cicas_fichier_memo();
	
	$handle = @fopen($fichier, "rb");
	if ($handle){
		$contenu = fread($handle, filesize($fichier));
		fclose($handle);
		if (substr($contenu,-1)=='|')
			$contenu = substr($contenu,0,-1);

		$return = explode('|',$contenu);		
	}
	return $return;
}


function cicas_fichier_memo(){

	$memo_nom = 'cicas_memo';
	$memo_suffix = '.txt';
	$memo_dir = ((defined('_DIR_LOG') AND !defined('_DIR_PLUGIN_CIMS')) ? _DIR_LOG : _DIR_RACINE._NOM_TEMPORAIRES_INACCESSIBLES);
	
	if (defined('_CICAS_REPERTOIRE') AND _CICAS_REPERTOIRE){
		$repertoire = _CICAS_REPERTOIRE;
		// securite
	    if ((strpos($repertoire,'../') === false)
			AND !(preg_match(',^\w+://,', $repertoire))) {
		
			if (substr($repertoire, 0, 1)=="/")
				$repertoire = substr($repertoire, 1);

			if (substr($repertoire, -1)=="/")
				$repertoire = substr($repertoire, 0, -1);
				
			if (is_dir(_DIR_RACINE.$repertoire))
				$memo_dir = _DIR_RACINE.$repertoire.'/';
		}
	}

	return $memo_dir.$memo_nom.$memo_suffix;
}

function cicas_unicite_email($new_email,$id_auteur){
	$return = true;

	if ($new_email AND $id_auteur=intval($id_auteur)){

		// controle d'unicite de l'email
		if (sql_countsel("spip_auteurs", "email='".$new_email."' AND id_auteur<>".$id_auteur) > 0)
			$return = false;

		if ($return){
			// compatibilite figurant dans le fichier de parametrage config/_config_cas.php
			if (isset($GLOBALS['ciconfig']['cicasmailcompatible'])) {
				if (is_array($GLOBALS['ciconfig']['cicasmailcompatible'])) {
					$cicasmailcompatible = $GLOBALS['ciconfig']['cicasmailcompatible'];
				}
			}
			
			$mon_tableau_email = explode('@',$new_email);
			$mon_nom_mail = strtolower($mon_tableau_email[0]);
			$mon_domaine_mail = strtolower($mon_tableau_email[1]);
	
			foreach ($cicasmailcompatible as $cle=>$valeur) {
				if (sql_countsel("spip_auteurs", "(email='".$mon_nom_mail."@".$cle."' OR email='".$mon_nom_mail."@".$valeur."') AND id_auteur<>".$id_auteur) > 0){
					$return = false;
					break;
				}
			}
		}
	}
	return $return;
}

?>