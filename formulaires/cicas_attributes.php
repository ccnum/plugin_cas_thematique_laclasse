<?php

    include_spip('inc/config');
    include_spip('inc/meta');
    include_spip('inc/cicas_commun');

    function formulaires_cicas_attributes_charger($index = 1) {

        $valeurs = array();
        $valeurs['cicas_attributes'] = "";

        $ciedit = cicas_lire_meta();

        if ($index == 1 && $ciedit) {
            $cicas_attributes = lire_config('cicas/attributes',array());
        }

        if ($index > 1 && $ciedit) {
            $cicas_attributes = lire_config('cicas/config'.$index.'/attributes',array());
        }

        foreach($cicas_attributes as $key => $value) {
            $valeurs['cicas_attributes'] .= $key. " = ".$value."\n";
        }

        $valeurs['ciedit'] = true;

        return $valeurs;
    }

    function formulaires_cicas_attributes_verifier($index = 1) {
        $erreurs = array();

        $cicas_attributes = _request('cicas_attributes');

        return $erreurs;
    }

    function formulaires_cicas_attributes_traiter($index = 1) {

        $res = array();
        $attributes = array();

        $cicas_attributes = explode("\n",_request('cicas_attributes'));

        foreach($cicas_attributes as $value) {
            $matches = array();
            if (preg_match ("/(.*)=(.*)/", $value , $matches)) {
                $attributes[trim($matches[1])] = trim($matches[2]);
            }
        }

        if ($index == 1)
            ecrire_config('cicas/attributes',$attributes);
        else
            ecrire_config('cicas/config'.$index."/attributes",$attributes);
        lire_metas();
        $res['message_ok'] = "Enregistrement réussi !";

        return $res;
    }
?>
