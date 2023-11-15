<?php

/**
 * Plugin CICAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/filtres');
include_spip('inc/cicas_commun');


function exec_cicas_serveurs() {

	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {

		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('cicas:info_serveurs'), "configuration", "configuration");


		echo debut_gauche('', true);
		$cicas_navigation = charger_fonction('cicas_navigation', 'configuration');
		echo $cicas_navigation();


		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
		echo '<h1 class="grostitre">' . _T('cicas:serveurs') . '</h1>';

		echo '<div class="cadre">' . typo(_T('cicas:info_serveurs')) . '</div>';

		echo debut_cadre_relief('', true, '', _T('cicas:serveurs'));

		$ciedit = !cicas_parametrage_par_fichier();
		$serveurs = cicas_lire_serveurs_additionnels();

		$res = '';
		foreach ($serveurs as $id_serveur => $serveur) {
			$res .=	'<li><a href="' . generer_url_ecrire("cicas_serveur", "id_serveur=" . intval($id_serveur)) . '">' . interdire_scripts($serveur['cicasurldefaut']) . '</a></li>';
		}

		if ($res)
			echo '<ul>' . $res . '</ul>';
		elseif ($ciedit)
			echo _T('cicas:aucun_serveur');
		else
			echo _T('cicas:aucun_serveur_fichier_param');


		echo fin_cadre_relief(true);

		if ($ciedit) {
			echo "\n<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
			echo "<tr>";
			echo "<td width='10%'>";
			if (spip_version() >= 3) {
				echo icone_verticale(_T('cicas:titre_creer_serveur'), generer_url_ecrire("cicas_serveur", "new=oui"), "article-24.png", "creer.gif");
			} else {
				echo icone_inline(_T('cicas:titre_creer_serveur'), generer_url_ecrire("cicas_serveur", "new=oui"), "article-24.gif", "creer.gif");
			}
			echo "</td><td width='90%'>";
			echo "</td></tr></table>";
		}

		echo fin_gauche(), fin_page();
	}
}
