<?php

/**
 * Plugin CICAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/cicas_commun');

function exec_cicas_serveur_dist() {

	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('cicas:serveur'), "naviguer", "mots");

		echo debut_gauche('', true);
		$cinotif_navigation = charger_fonction('cicas_navigation', 'configuration');
		echo $cinotif_navigation();

		echo creer_colonne_droite('', true);
		echo debut_droite('', true);

		if (spip_version() >= 3)
			$icone_retour = icone_verticale(_T('icone_retour'), generer_url_ecrire("cicas_serveurs"), "article-24.png", "rien.gif", $GLOBALS['spip_lang_left']);
		else
			$icone_retour = icone_inline(_T('icone_retour'), generer_url_ecrire("cicas_serveurs"), "article-24.gif", "rien.gif", $GLOBALS['spip_lang_left']);

		$contexte = array(
			'icone_retour' => $icone_retour,
			'titre' => '',
			'redirect' => generer_url_ecrire("cicas_serveurs", ""),
			'new' => _request('new') == "oui" ? "nouveau" : intval(_request('id_serveur')),
			'config_fonc' => 'cicas_serveur_edit_config'
		);

		echo recuperer_fond("prive/editer/cicas_serveur", $contexte);

		echo fin_gauche(),
		fin_page();
	}
}
