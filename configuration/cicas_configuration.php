<?php
/**
 * Plugin Authentification CAS
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/config');
include_spip('inc/cicas_commun');

function configuration_cicas_configuration()
{
	// lire la configuration du plugin
	$ciedit = cicas_lire_meta();
	
	$cicas = $GLOBALS['ciconfig']['cicas'];
	$cicasurldefaut = $GLOBALS['ciconfig']['cicasurldefaut'];
	$cicasrepertoire = $GLOBALS['ciconfig']['cicasrepertoire'];
	$cicasuid = $GLOBALS['ciconfig']['cicasuid'];
	$cicasport = $GLOBALS['ciconfig']['cicasport'];
	
	$action = generer_url_ecrire('cicas_config');
	
	$res .= "<form action='$action' method='post'>".form_hidden($action)
	. "<input type='hidden' name='changer_config' value='oui' />";
	
	$res .= "<br />"._T('cicas:eq_avertissement')."<br />";
	
	$res .= "<br />\n"
	. debut_cadre_relief("", true, "", _T('cicas:eq_titre_mode_auth'))
	. "<table border='0' cellspacing='1' cellpadding='3' width=\"100%\">";

	
    // cicas
	$res .= "\n<tr><td class='verdana2'>"
	. cicas_afficher_choix('cicas', $cicas,
		array('oui' => _T('cicas:eq_texte_auth_cas'),
			'hybride' => _T('cicas:eq_texte_auth_hybride'),
			'non' => _T('cicas:eq_texte_auth_spip')), " &nbsp; ", !$ciedit)
	. "</td></tr>\n";
	
	$res .= "\n</table>"
	. fin_cadre_relief(true);

	$res .= "<br />\n"
	. debut_cadre_relief("", true, "", _T('cicas:eq_titre_serveur_cas'))
	. "<table border='0' cellspacing='1' cellpadding='3' width=\"100%\">";
	
    // cicasurldefaut
	$res .= "\n<tr><td class='verdana2'>"
	. "<label for='cicasurldefaut'>"._T('cicas:eq_texte_url')."</label><br />"
	. " <input type='text' name='cicasurldefaut' id='cicasurldefaut' value=\"$cicasurldefaut\" size='40' class='formo' ".($ciedit ? "" : "disabled='disabled'"). "/><br />"
	. "</td></tr>";

    // cicasrepertoire
	$res .= "\n<tr><td class='verdana2'>"
	. "<label for='cicasrepertoire'>"._T('cicas:eq_texte_repertoire')."</label><br />"
	. " <input type='text' name='cicasrepertoire' id='cicasrepertoire' value=\"$cicasrepertoire\" size='40' class='formo' ".($ciedit ? "" : "disabled='disabled'"). " /><br />"
	. "</td></tr>";
	
    // cicasport
	$res .= "\n<tr><td class='verdana2'>"
	. "<label for='cicasport'>"._T('cicas:eq_texte_port')."</label><br />"
	. " <input type='text' name='cicasport' id='cicasport' value=\"$cicasport\" size='40' class='formo' ".($ciedit ? "" : "disabled='disabled'"). "/><br />"
	. "</td></tr>";
	
    // cicasuid
    $ci_tableau_uid = array("email" => "email", "login" => "login");
    if (!$cicasuid)
    	$cicasuid = "email";
	
	$res .= "\n<tr><td class='verdana2'>"
	. "<label for='cicasuid'>"._T('cicas:eq_texte_uid')."</label><br />"
    . "\n<select name='cicasuid' class='formo' ".($ciedit ? "" : "disabled='disabled'"). ">\n"
    . "<option value='$cicasuid' selected='selected'>".$ci_tableau_uid[$cicasuid]."</option>\n";
    
	foreach ($ci_tableau_uid as $cle => $valeur) {
	if ($cle <> $cicasuid)
		$res .= "<option value='$cle'>".$valeur."</option>\n";
	}
	
    $res .= "</select><br />\n";
	$res .= "</td></tr>"
	. "\n</table>";
	
	$res .= fin_cadre_relief(true);
    
    	
	$res .= '<span><input type="submit" class="fondo" style="float: right;" value="Valider"/></span>'
	. "</form>";
	
	$res = debut_cadre_trait_couleur("", true, "", _T('cicas:eq_titre'))
	. $res
	. fin_cadre_trait_couleur(true);

	return $res;

}

function cicas_afficher_choix($nom, $valeur_actuelle, $valeurs, $sep = "<br />", $disabled = false) {
	$choix = array();
        foreach ($valeurs as $valeur => $titre) {
		$choix[] = cicas_bouton_radio($nom, $valeur, $titre, $valeur == $valeur_actuelle, $disabled);
	}
	return "\n".join($sep, $choix);
}

function cicas_bouton_radio($nom, $valeur, $titre, $actif = false, $disabled = false) {
	static $id_label = 0;
	
	if ($disabled) $option = " disabled='disabled'";
	else $option = "";
    
	$texte = "<input type='radio' name='$nom' value='$valeur' id='label_${nom}_${id_label}'$option";
	if ($actif) {
		$texte .= ' checked="checked"';
		$titre = '<b>'.$titre.'</b>';
	}
	$texte .= " /> <label for='label_${nom}_${id_label}'>$titre</label>\n";
	$id_label++;
	return $texte;
}


?>