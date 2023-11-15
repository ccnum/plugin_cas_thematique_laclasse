<?php

/**
 * Plugin Authentification CAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/cicas_commun');


function exec_cicas_config() {

	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('cicas:titre_page_configuration'), "configuration", "configuration");

		echo debut_gauche('', true);
		$cinotif_navigation = charger_fonction('cicas_navigation', 'configuration');
		echo $cinotif_navigation();

		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
		echo '<h1 class="grostitre">' . _T('cicas:eq_titre') . '</h1>';

		$contexte = array();
		echo recuperer_fond("prive/editer/cicas_config", $contexte);

		echo fin_gauche(), fin_page();
	}
}
