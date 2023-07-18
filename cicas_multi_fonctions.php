<?php
/**
 * Plugin Authentification CAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

/*-----------------------------------------------------------------
// Filtre pour afficher les adresses des multiples serveurs CAS
------------------------------------------------------------------*/

function cicas_liens_serveurs($var='') {
	
	include_spip('inc/cicas_commun');

	$return	= '';
	$self = self();
	$self = str_replace('cicas_multi','login',$self);
        $cistyle = "vertical-align:top;";
        if (spip_version()>=4){
            $cistyle .= "color:#fff;";
        }

	// serveur de base
	$tableau_config = cicas_lire_meta();
	$lien = parametre_url($self, 'cicas', 'oui');	
	$return .= '<li><a href="'.$lien.'"><img alt="'._T('cicas:eq_lien_auth_hybride').'" src="'.find_in_path('cicas.gif').'" /></a>'
	.'&nbsp;<a href="'.$lien.'" style="'.$cistyle.'">&#91;'.$tableau_config['cicasurldefaut'].'&#93;</a>
	&nbsp;<a href="'.$lien.'&amp;memoriser=oui" style="'.$cistyle.'">'._T('cicas:memoriser_choix').'</a></li>';		

	// serveurs additionnels
	$serveurs = cicas_lire_serveurs_additionnels();	
	foreach ($serveurs as $id_serveur=>$serveur){
		$lien = parametre_url($self, 'cicas', intval($id_serveur));		
		$return .= '<li><a href="'.$lien.'"><img alt="'._T('cicas:eq_lien_auth_hybride').'" src="'.find_in_path('cicas.gif').'" /></a>'
		.'&nbsp;<a href="'.$lien.'" style="'.$cistyle.'">&#91;'.$serveur['cicasurldefaut'].'&#93;</a>
		&nbsp;<a href="'.$lien.'&amp;memoriser=oui" style="'.$cistyle.'">'._T('cicas:memoriser_choix').'</a></li>';		
	}	

	if ($return)
		$return = '<ul style="text-align:left;padding:0;">'.$return.'</ul>';	
	
	return $return;
      	
}	            	
?>