<?php

    include_spip('inc/config');
    include_spip('inc/cicas_commun');

    function formulaires_cicas_server_charger() {
        $valeurs = array();

        if ($ciedit = cicas_lire_meta()) {
            $valeurs['cicasurldefaut'] = $GLOBALS['ciconfig']['cicasurldefaut'];
            $valeurs['cicasrepertoire'] = $GLOBALS['ciconfig']['cicasrepertoire'];
            $valeurs['cicasport'] = $GLOBALS['ciconfig']['cicasport'];
            $valeurs['cicasuid'] = $GLOBALS['ciconfig']['cicasuid'];
            $valeurs['ci_tableau_uid'] = lire_config('cicas/cuid_list',array('login','email'));
            $valeurs['cicasstatutcrea'] = lire_config('cicas/cicasstatutcrea');
            $valeurs['tableau_statut'] = array('0minirezo','1comite','6forum');
            $valeurs['ciedit'] = $ciedit;
        }        
        return $valeurs;
    }

    function formulaires_cicas_server_verifier() {
        $erreurs = array();

        $cicasuid = _request('cicasuid');
        if (!in_array($cicasuid,lire_config('cicas/cuid_list',array('login','email'))))
            $erreurs['cicasuid'] = "uid CAS invalide";

        $cicasstatutcrea = _request('cicasstatutcrea');
        if (!empty($cicasstatutcrea) && !in_array($cicasstatutcrea,array('0minirezo','1comite','6forum')))
            $erreurs['cicasstatutcrea'] = "Choix de statut invalide";

        return $erreurs;
    }

    function formulaires_cicas_server_traiter() {
        include_spip('inc/meta');
        
        $res = array();
        $cicas_config = lire_config("cicas");

    	$cicas_config['cicasuid'] = _request('cicasuid');
    	$cicas_config['cicasurldefaut'] = _request('cicasurldefaut');
    	if (!_request('cicasrepertoire') OR _request('cicasrepertoire')=="/")
	    	$cicas_config['cicasrepertoire'] = "";
    	elseif (substr(_request('cicasrepertoire'),0,1)=="/")
	    	$cicas_config['cicasrepertoire'] = _request('cicasrepertoire');
    	else
	    	$cicas_config['cicasrepertoire'] = "/"._request('cicasrepertoire');

    	$cicas_config['cicasport'] = _request('cicasport');
        $cicas_config['cicasstatutcrea'] = _request('cicasstatutcrea');
    
        ecrire_config('cicas',$cicas_config);
        lire_metas();
        $res['message_ok'] = "Enregistrement réussi !";

        return $res;
    }
?>
