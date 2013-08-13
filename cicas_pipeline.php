<?php
/**
 * Plugin Authentification CAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
/**
 * Pipeline d'aiguillage entre les modes d'authentification
 *
 * @param $flux, $contexte
 * @return $flux
 */
function cicas_recuperer_fond($flux){

	if (($flux['args']['fond']=='formulaires/login')||($flux['args']['fond']=='noisettes/inc/authentification')||($flux['args']['fond']=='inclure/authentification')||($flux['args']['fond']=='footer'))
	{
		include_spip('inc/cicas_commun');
	
		// lire la configuration du plugin
		cicas_lire_meta();

		// authentification CAS
		if ($GLOBALS['ciconfig']['cicas']=='oui' AND $GLOBALS['ciconfig']['cicasurldefaut']) 
		{
			include_spip('inc/cicas_login');
		// authentification hybride CAS et SPIP
		} elseif ($GLOBALS['ciconfig']['cicas']=='hybride') 
		{
			// authentification CAS demandee par un clic sur le lien
			if (_request('cicas') AND _request('cicas')=='oui') {
				include_spip('inc/cicas_login');

			} else {
				// ajout du lien vers l'authentification CAS
				include_spip("inc/utils");
				//$url = $GLOBALS['meta']['adresse_site'];
				$lien = parametre_url($url, 'url', 'spip.php?page=sommaire');
				$lien = parametre_url($lien, 'cicas', 'oui');
				//$lien = "<a href='$lien'>CAS</a>";
				//$lien = '<a href="'.$lien.'"><img alt="'._T('cicas:eq_lien_auth_hybride').'" src="'.chemin('cicas.gif').'" /></a>';
				//$lien .= '<a href="'.$lien.'" style="vertical-align:top;">&#91;'._T('cicas:eq_lien_auth_hybride').'&#93;</a>';
			}
		}
	}
	
		if ($flux['args']['fond']=='formulaires/login')	
			$flux['data']['texte'] = str_replace('</form>','</form><a href="'.$lien.'">CAS</a>',$flux['data']['texte']);
		if ($flux['args']['fond']=='noisettes/inc/authentification')
			$flux['data']['texte'] = str_replace('<a href="#" onClick="show_connexion();">','<a href="'.$lien.'">',$flux['data']['texte']);	
		if (($flux['args']['fond']=='inclure/authentification')||($flux['args']['fond']=='footer'))
			$flux['data']['texte'] = str_replace('href="page=popup-login" class="mediabox','href="'.$lien.'" class="',$flux['data']['texte']);
	
	return $flux;
}

?>
