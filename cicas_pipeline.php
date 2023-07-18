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
        
        // ajout d'une securite anti BOT (via la constante _CICAS_ANTI_BOT)
       	if (defined('_CICAS_ANTI_BOT') AND _CICAS_ANTI_BOT=='oui' AND _IS_BOT){
            include_spip('inc/headers');
            redirige_par_entete(generer_url_public('403'));
            
        } else {    
            include_spip('inc/cicas_commun');

            // lire la configuration du plugin
            $tableau_config = cicas_lire_meta();

            // authentification CAS
            if ($tableau_config['cicas']=='oui' AND $tableau_config['cicasurldefaut']) {

                    if (cicas_nombre_serveurs_additionnels()>=1 AND !_request('cicas')){

                            // l'utilisateur a memorise son choix
                            if (isset($_COOKIE['cicas_choix']) AND $_COOKIE['cicas_choix'] 
                                    AND ($_COOKIE['cicas_choix']=='oui' OR intval($_COOKIE['cicas_choix'])>=1)){

                                    $_GET['cicas'] = $_COOKIE['cicas_choix'];
                                    include_spip('inc/cicas_login');

                            } else {						
                                    include_spip('inc/headers');
                                    $arg = array();
                                    if (_request('url'))
                                            $arg['url'] = _request('url');
                                    $ciredirect = generer_url_public('cicas_multi',$arg);
                                    $ciredirect = str_replace('&amp;','&',$ciredirect); // les redirections se font en &, pas en en &amp;
                                    redirige_par_entete($ciredirect);
                            }
                    } else {
                            include_spip('inc/cicas_login');
                    }

            // authentification hybride CAS et SPIP
            } elseif ($tableau_config['cicas']=='hybride') {

                    // authentification CAS demandee par un clic sur le lien
                    if (_request('cicas')) {
                            include_spip('inc/cicas_login');

                    } else {			
                            // ajout du lien vers l'authentification CAS
                            include_spip("inc/utils");
                            $return	= '';
                            $self = self();
                            $cistyle = "vertical-align:top;";
                            if (spip_version()>=4){
                                $cistyle .= "color:#000;";
                            }

                            if (cicas_nombre_serveurs_additionnels()>=1){

                                    // l'utilisateur a memorise son choix
                                    if (isset($_COOKIE['cicas_choix']) AND $_COOKIE['cicas_choix'] 
                                            AND ($_COOKIE['cicas_choix']=='oui' OR intval($_COOKIE['cicas_choix'])>=1)){

                                            $lien = parametre_url($self, 'cicas', $_COOKIE['cicas_choix']);
                                            $return = '<a href="'.$lien.'"><img alt="'._T('cicas:eq_lien_auth_hybride').'" src="'.find_in_path('cicas.gif').'" /></a>'
                                            .'&nbsp;<a href="'.$lien.'" style="'.$cistyle.'">&#91;'._T('cicas:eq_lien_auth_hybride').'&#93;</a>';

                                    } else {
                                            $titre = '<div>'._T('cicas:choix_serveur').'</div>';

                                            $lien = parametre_url($self, 'cicas', 'oui');
                                            $return .= '<li><a href="'.$lien.'"><img alt="'._T('cicas:eq_lien_auth_hybride').'" src="'.find_in_path('cicas.gif').'" /></a>'
                                            .'&nbsp;<a href="'.$lien.'" style="'.$cistyle.'">&#91;'.$tableau_config['cicasurldefaut'].'&#93;</a>
                                            &nbsp;<a href="'.$lien.'&memoriser=oui" style="'.$cistyle.'">'._T('cicas:memoriser_choix').'</a></li>';

                                            // serveurs additionnels
                                            $serveurs = cicas_lire_serveurs_additionnels();	
                                            foreach ($serveurs as $id_serveur=>$serveur){
                                                    $lien = parametre_url($self, 'cicas', intval($id_serveur));		
                                                    $return .= '<li><a href="'.$lien.'"><img alt="'._T('cicas:eq_lien_auth_hybride').'" src="'.find_in_path('cicas.gif').'" /></a>'
                                                    .'&nbsp;<a href="'.$lien.'" style="'.$cistyle.'">&#91;'.$serveur['cicasurldefaut'].'&#93;</a>		
                                                    &nbsp;<a href="'.$lien.'&memoriser=oui" style="'.$cistyle.'">'._T('cicas:memoriser_choix').'</a></li>';
                                            }

                                            $return = $titre.'<ul style="text-align:left;padding:0;">'.$return.'</ul>';
                                    }

                            } else {
                                    $lien = parametre_url($self, 'cicas', 'oui');
                                    $return = '<a href="'.$lien.'"><img alt="'._T('cicas:eq_lien_auth_hybride').'" src="'.find_in_path('cicas.gif').'" /></a>'
                                    .'&nbsp;<a href="'.$lien.'" style="'.$cistyle.'">&#91;'._T('cicas:eq_lien_auth_hybride').'&#93;</a>';
                            }
                            $flux['data']['texte'] = str_replace('</form>','</form>'.$return,$flux['data']['texte']);
                    }
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
						
						// Lire la configuration du plugin pour la session
						$tableau_config = cicas_lire_meta(0,false,true);

		    			$mon_id_auteur = (isset($GLOBALS['visiteur_session']['id_auteur']) ? $GLOBALS['visiteur_session']['id_auteur'] : '');
	
						// modifier un autre auteur
		    			if ($id_auteur!=$mon_id_auteur) {
		    				
                                                if ($tableau_config['cicasuid']=="login"){
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
					    		if ($new_email AND $new_email!=$old_email){
		
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
										if (isset($tableau_config['cicasmailcompatible'])) {
											if (is_array($tableau_config['cicasmailcompatible'])) {
												$cicasmailcompatible = $tableau_config['cicasmailcompatible'];
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
							if ($tableau_config['cicasuid']=="login"){
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
			// compatibilite par defaut
			$cicasmailcompatible = array('equipement.gouv.fr' => 'developpement-durable.gouv.fr');
                        
			// Lire la configuration du plugin pour la session
			$tableau_config = cicas_lire_meta(0,false,true);
			
			// compatibilite figurant dans le fichier de parametrage config/_config_cas.php
			if (isset($tableau_config['cicasmailcompatible'])) {
				if (is_array($tableau_config['cicasmailcompatible'])) {
					$cicasmailcompatible = $tableau_config['cicasmailcompatible'];
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


function cicas_formulaire_verifier($flux) {
        // Un rédacteur ou un admin restreint ne doit pas pouvoir modifier son propre email
        // si CICAS utilise l'email comme identifiant
        // or SPIP 4.0 le permet
        // Remarque : id_auteur figure dans $flux['args']['args'][0]
        if ($GLOBALS['spip_version_branche']>=4) {
                if (isset($flux['args']['form']) 
                        AND $flux['args']['form']=='editer_auteur' 
                        AND isset($flux['args']['args'][0]) 
                        AND $flux['args']['args'][0]){

                        if (isset($GLOBALS['visiteur_session']['id_auteur']) 
                                AND $GLOBALS['visiteur_session']['id_auteur']==$flux['args']['args'][0] 
                                AND isset($GLOBALS['visiteur_session']['statut'])){

                                if ($GLOBALS['visiteur_session']['statut'] == '1comite' 
                                        OR ($GLOBALS['visiteur_session']['statut'] == '0minirezo' AND liste_rubriques_auteur($GLOBALS['visiteur_session']['id_auteur'])) ){

                                        // email modifié ?
                                        $old_email = '';
                                        $new_email = _request('email');
                                        $row = sql_fetsel("email", "spip_auteurs", "id_auteur=".intval($flux['args']['args'][0]));
                                        if ($row) {
                                                $old_email = $row['email'];
                                        }

                                        if ($new_email!=$old_email){
                                                // Si CICAS utilise l'email comme identifiant
                                                include_spip('inc/cicas_commun');
                                                $tableau_config = cicas_lire_meta(0,false,true);

                                                if (!isset($tableau_config['cicasuid']) 
                                                        OR $tableau_config['cicasuid']==""  
                                                        OR $tableau_config['cicasuid']=="email") {

                                                        $flux['data'] = array('email'=>_T('cicas:email_non_modifiable'));
                                                }
                                        }
                                }
                        }
                }
        }
        
        return $flux;
}

?>