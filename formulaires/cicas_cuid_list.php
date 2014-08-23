<?php

    include_spip('inc/config');

    function formulaires_cicas_cuid_list_charger() {

        $valeurs = array();

        $cuid_list = lire_config('cicas/cuid_list',array('login','email'));
        
        $valeurs['cuid_list'] = implode("\n",$cuid_list);
        
        return $valeurs;
    }

    function formulaires_cicas_cuid_list_verifier() {
        $erreurs = array();
        $erreur_cuid = false;

        $cuid_list = trim(_request('cuid_list'));
        
        //Chaque cuid est alphanumérique
        foreach (split("\n",$cuid_list) as $cuid) {
            if (!preg_match('/^[a-zA-Z0-9]*$/',$cuid))
                $erreur_cuid = true;
        }
        
        //Ne pas avoir de liste vide
        if (empty($cuid_list))
            $erreur_cuid = true;
        
        if ($erreur_cuid)
            $erreurs['cuid_list'] = "Erreur dans la saisie des cuid";
        
        return $erreurs;
    }

    function formulaires_cicas_cuid_list_traiter() {
        include_spip('inc/meta');
        
        $res = array();    
        $cuid_list = trim(_request('cuid_list'));
        
        //On stocke un tableau néttoyé des doublons éventuels
        $cuid_list = array_unique(            
            explode("\n",$cuid_list)
        );
            
        ecrire_config('cicas/cuid_list',$cuid_list);
        lire_metas();
        $res['message_ok'] = "Enregistrement réussi !";

        return $res;
    }
?>
