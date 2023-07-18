<?php
/**
 * Plugin CICAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');
include_spip('inc/cicas_commun');


function action_supprimer_serveur_cas($id_serveur=null){

	if (is_null($id_serveur)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$id_serveur = $securiser_action();
	}

	if (autoriser('configurer', 'configuration')) {
		cicas_supprimer_serveur_additionnel(intval($id_serveur));
	}
}

?>