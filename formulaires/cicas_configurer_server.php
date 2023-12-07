<?php

include_spip('inc/config');
include_spip('inc/cicas_commun');

function formulaires_cicas_configurer_server_charger() {
    $valeurs = array();

    $valeurs['server_nb'] = lire_config('cicas/server_nb', 1);
    $valeurs['update_auteur_vide'] = lire_config('cicas/update_auteur_vide');
    $valeurs['update_auteur_all'] = lire_config('cicas/update_auteur_all');
    return $valeurs;
}

function formulaires_cicas_configurer_server_verifier() {
    $erreurs = array();

    $server_nb = _request('server_nb');

    if (!is_numeric($server_nb) || $server_nb < 1)
        $erreurs['server_nb'] = "Nombre positif obligatoire";

    return $erreurs;
}

function formulaires_cicas_configurer_server_traiter() {
    include_spip('inc/meta');

    $res = array();

    $cicas_config['server_nb'] = _request('server_nb', 1);
    ecrire_config('cicas/server_nb', $cicas_config['server_nb']);

    if ($cicas_config['update_auteur_vide'] = _request('update_auteur_vide'))
        ecrire_config('cicas/update_auteur_vide', $cicas_config['update_auteur_vide']);
    else
        effacer_config('cicas/update_auteur_vide');

    if ($cicas_config['update_auteur_all'] = _request('update_auteur_all'))
        ecrire_config('cicas/update_auteur_all', $cicas_config['update_auteur_all']);
    else
        effacer_config('cicas/update_auteur_all');

    lire_metas();
    $res['message_ok'] = "Enregistrement réussi !";

    return $res;
}
