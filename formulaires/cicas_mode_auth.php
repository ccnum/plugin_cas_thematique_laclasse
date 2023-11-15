<?php

/**
 * Plugin CICAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/cicas_commun');


function formulaires_cicas_mode_auth_charger_dist() {

	if (!autoriser('configurer', 'configuration'))
		return false;

	$valeurs = array();

	$tableau_config = cicas_lire_meta();
	$ciedit = !cicas_parametrage_par_fichier();

	$valeurs['mode_auth'] = $tableau_config['cicas'];
	$valeurs['ciedit'] = $ciedit;

	return $valeurs;
}

function formulaires_cicas_mode_auth_verifier_dist() {
	$erreurs = array();

	$valeur_saisie = _request('mode_auth');
	if (!$valeur_saisie)
		$erreurs['mode_auth'] = _T('info_obligatoire');
	if ($valeur_saisie and !in_array($valeur_saisie, array('oui', 'hybride', 'non')))
		$erreurs['mode_auth'] = _T('cicas:valeur_incorrecte');

	return $erreurs;
}

function formulaires_cicas_mode_auth_traiter_dist() {
	$res = array();

	// ne pas enregistrer si configuration par fichier
	$tableau_config = cicas_lire_meta();
	if (!cicas_parametrage_par_fichier()) {
		$valeur_saisie = _request('mode_auth');
		if ($valeur_saisie and in_array($valeur_saisie, array('oui', 'hybride', 'non'))) {
			$tableau_config['cicas'] = $valeur_saisie;
			include_spip('inc/meta');
			ecrire_meta('cicas', @serialize($tableau_config));
		}
	}

	$res['message_ok'] = "";
	$res['redirect'] = "";

	return $res;
}
