<?php
/**
 * Plugin Authentification CAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/cicas_commun');

function cicas_appliquer_modifs_config() {
	
	$tableau = array();

	$tableau['cicas'] = _request('cicas');
	$tableau['cicasuid'] = _request('cicasuid');
	$tableau['cicasurldefaut'] = _request('cicasurldefaut');
	if (!_request('cicasrepertoire') OR _request('cicasrepertoire')=="/")
		$tableau['cicasrepertoire'] = "";
	elseif (substr(_request('cicasrepertoire'),0,1)=="/")
		$tableau['cicasrepertoire'] = _request('cicasrepertoire');
	else
		$tableau['cicasrepertoire'] = "/"._request('cicasrepertoire');

	$tableau['cicasport'] = _request('cicasport');

	$GLOBALS['ciconfig']['cicas'] = $tableau['cicas'];
	$GLOBALS['ciconfig']['cicasuid'] = $tableau['cicasuid'];
	$GLOBALS['ciconfig']['cicasurldefaut'] = $tableau['cicasurldefaut'];
	$GLOBALS['ciconfig']['cicasrepertoire'] = $tableau['cicasrepertoire'];
	$GLOBALS['ciconfig']['cicasport'] = $tableau['cicasport'];
	
	include_spip('inc/meta');
	ecrire_meta('cicas', @serialize($tableau));

}


function exec_cicas_config(){

	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {

		// ne pas stocker en meta si configuration par fichier
		if (_request('changer_config') == 'oui' AND cicas_lire_meta()) {
			cicas_appliquer_modifs_config();
		}
		
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('cicas:titre_page_configuration'), "configuration", "configuration");
		
		echo "<br /><br /><br />\n";
		echo gros_titre(_T('cicas:eq_titre'),'', false);
		
		echo debut_gauche('', true);	
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
	
		$cicas_configuration = charger_fonction('cicas_configuration', 'configuration');
		echo  $cicas_configuration(), "<br />\n";

		echo fin_gauche(), fin_page();
	}
}
?>