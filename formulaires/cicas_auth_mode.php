<?php

    function formulaires_cicas_auth_mode_charger() {
        include_spip('inc/cicas_commun');

        $valeurs = array();

        if (cicas_lire_meta()) {
            $valeurs['auth_mode'] = $GLOBALS['ciconfig']['cicas'];
        }        
        return $valeurs;
    }

    function formulaires_cicas_auth_mode_verifier() {
        $erreurs = array();

        $auth_mode = _request('cicas');
        if (!in_array($auth_mode,array('hybride','oui','non')))
            $erreurs['auth_mode'] = "Mode d'authentification invalide";

        return $erreurs;
    }

    function formulaires_cicas_auth_mode_traiter() {
        include_spip('inc/meta');
        include_spip('inc/config');
        
        $res = array();    
        $auth_mode = _request('cicas');
    
        ecrire_config('cicas/cicas',$auth_mode);
        lire_metas();
        $res['message_ok'] = "Enregistrement réussi !";

        return $res;
    }
?>
