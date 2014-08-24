<?php

    include_spip('inc/config');
    include_spip('inc/cicas_commun');

    function formulaires_cicas_server_nb_charger() {
        $valeurs = array();

        $valeurs['server_nb'] = lire_config('cicas/server_nb',1);    

        return $valeurs;
    }

    function formulaires_cicas_server_nb_verifier() {
        $erreurs = array();

        $server_nb = _request('server_nb');
        
        if (!is_numeric($server_nb) || $server_nb < 1)
            $erreurs['server_nb'] = "Nombre positif obligatoire";

        return $erreurs;
    }

    function formulaires_cicas_server_nb_traiter() {
        include_spip('inc/meta');
        
        $res = array();

    	$cicas_config['server_nb'] = _request('server_nb');
    
        ecrire_config('cicas/server_nb',$cicas_config['server_nb']);
        lire_metas();
        $res['message_ok'] = "Enregistrement réussi !";

        return $res;
    }
?>
