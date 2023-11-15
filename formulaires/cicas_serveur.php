<?php

/**
 * Plugin CICAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/cicas_commun');


function formulaires_cicas_serveur_charger_dist($id_serveur) {

	$valeurs = array();

	if (!autoriser('configurer', 'configuration'))
		return;

	$ciedit = !cicas_parametrage_par_fichier();

	if (intval($id_serveur) >= 1) {
		// serveur CAS additionnel
		$valeurs = cicas_lire_serveur_additionnel(intval($id_serveur));
	} elseif ($id_serveur == 'initial') {
		// serveur CAS
		$tableau_config = cicas_lire_meta(0, true);
		foreach (array('cicasurldefaut', 'cicasrepertoire', 'cicasport', 'cicasuid', 'cicas_creer_auteur') as $champ) {
			if (isset($tableau_config[$champ])) {
				$valeurs[$champ] = $tableau_config[$champ];
			}
		}
	}

	$valeurs['ciedit'] = $ciedit;
	$valeurs['id_serveur']	= $id_serveur;
	$valeurs['_hidden'] = "<input type='hidden' name='id_serveur' value='" . $id_serveur . "' />";

	return $valeurs;
}


function formulaires_cicas_serveur_verifier_dist($id_serveur) {
	$erreurs = array();

	$valeur_saisie = _request('cicasurldefaut');
	if (!$valeur_saisie)
		$erreurs['cicasurldefaut'] = _T('info_obligatoire');
	if ($valeur_saisie and !cicas_filtrer_url($valeur_saisie, true))
		$erreurs['cicasurldefaut'] =  _T('cibc:erreur_incorrect');

	$valeur_saisie = _request('cicasrepertoire');
	if ($valeur_saisie and !cicas_filtrer_url($valeur_saisie, true))
		$erreurs['cicasrepertoire'] =  _T('cibc:erreur_incorrect');

	$valeur_saisie = _request('cicasport');
	if (!$valeur_saisie)
		$erreurs['cicasport'] = _T('info_obligatoire');
	if ($valeur_saisie and intval($valeur_saisie) < 1)
		$erreurs['cicasport'] = _T('cinotif:erreur_int');

	$valeur_saisie = _request('cicasuid');
	if (!$valeur_saisie)
		$erreurs['cicasuid'] = _T('info_obligatoire');
	if ($valeur_saisie and !in_array($valeur_saisie, array('email', 'login')))
		$erreurs['cicasuid'] = _T('cinotif:erreur_incorrect');

	$valeur_saisie = _request('cicas_creer_auteur');
	if ($valeur_saisie and !in_array($valeur_saisie, array('1comite', '6forum')))
		$erreurs['cicas_creer_auteur'] = _T('cinotif:erreur_incorrect');


	return $erreurs;
}

function formulaires_cicas_serveur_traiter_dist($id_serveur) {
	$res = array();

	// cas d'un nouveau serveur additionnel
	if ($id_serveur == 'nouveau')
		$id_serveur = cicas_nombre_serveurs_additionnels() + 1;

	$c = array();
	foreach (array('cicasurldefaut', 'cicasrepertoire', 'cicasport', 'cicasuid', 'cicas_creer_auteur') as $champ)
		$c[$champ] = _request($champ);

	// casparticulier
	$champ = 'cicasrepertoire';
	if (!_request($champ) or _request($champ) == "/")
		$c[$champ] = "";
	elseif (substr(_request($champ), 0, 1) == "/")
		$c[$champ] = _request($champ);
	else
		$c[$champ] = "/" . _request($champ);


	// uniquement via parametrage par fichier
	foreach (array('cicasurls', 'cicasmailcompatible') as $champ)
		$c[$champ] = array();

	// uniquement via parametrage par fichier
	foreach (array('cicas_svu_url', 'cicas_svu_repertoire', 'cicas_svu_port') as $champ)
		$c[$champ] = '';

	// ne pas enregistrer si configuration par fichier
	if ($id_serveur == 'initial')
		$tableau_config = cicas_lire_meta();
	else
		$tableau_config = cicas_lire_meta($id_serveur);

	if (!cicas_parametrage_par_fichier()) {
		if (intval($id_serveur) >= 1) {
			// serveur CAS additionnel
			$redirect = generer_url_ecrire("cicas_serveurs");
			cicas_ecrire_serveur_additionnel($id_serveur, $c);
		} elseif ($id_serveur == 'initial') {
			// serveur CAS
			$redirect = generer_url_ecrire("cicas_config");
			foreach (array('cicasurldefaut', 'cicasrepertoire', 'cicasport', 'cicasuid', 'cicas_creer_auteur') as $champ) {
				$tableau_config[$champ] = $c[$champ];
			}
			include_spip('inc/meta');
			ecrire_meta('cicas', @serialize($tableau_config));
		}
	}

	$res['message_ok'] = "";
	$res['redirect'] = $redirect;

	return $res;
}
