<?php

// Fichier local de surcharge du fichier de langue

$GLOBALS[$GLOBALS['idx_lang']] = array(
'eq_avertissement' => '<b>ATTENTION</b> : Il est imp&eacute;ratif s&eacute;lectionner en premier (ci-dessous) le mode d\'authentification intitul&eacute; <b>"CAS ou SPIP"</b> afin de v&eacute;rifier, sans risque, le bon fonctionnement de l\'authentification CAS. Une fois cette v&eacute;rification effectu&eacute;e on pourra alors s&eacute;lectionner (ci-dessous) le mode d\'authentification intitul&eacute; "CAS".',
'eq_titre' => 'Configuration du plugin cicas',
'eq_titre_mode_auth' => 'Mode d\'authentification',
'eq_lien_auth_hybride' => 'Utiliser l\'authentification centralis&eacute;e',
'eq_titre_serveur_cas' => 'Configuration du serveur CAS',
'eq_texte_auth_cas' => 'CAS',
'eq_texte_auth_hybride' => 'CAS ou SPIP',
'eq_texte_auth_spip' => 'SPIP',
'eq_texte_host' => 'Serveur CAS',
'eq_texte_url' => 'URL du serveur CAS',
'eq_texte_repertoire' => 'R&eacute;pertoire du serveur CAS',
'eq_texte_port' => 'Port du serveur CAS',
'eq_texte_uid' => 'Identifiant utilisateur fournit par le serveur CAS',
'eq_logout_cas' => 'Se d&eacute;connecter de CAS et retour &agrave; l\'accueil',
'eq_texte_erreur1' => 'Vous n\'avez pas &eacute;t&eacute; authentifi&eacute; par le serveur CAS. Veuillez contacter le webmestre du site.',
'eq_texte_erreur2' => 'Vous avez bien &eacute;t&eacute; authentifi&eacute; par le serveur CAS, mais votre adresse &eacute;lectronique (ou votre login) est introuvable dans SPIP. Veuillez contacter le webmestre du site.',
'titre' => 'Configuration CAS (SSO)',
'cicas_titre' => 'Configurer CAS (SSO)',
'serveur' => 'Serveur CAS additionnel',
'serveurs' => 'Serveurs CAS additionnels',
'auteurs' => 'Cr&eacute;ation automatique d\'auteur',
'email' => 'email',
'login' => 'login',
"eq_texte_creer_auteur" => 'Si l\'authentification sur ce serveur CAS a r&eacute;ussi mais que l\'auteur n\'existe pas dans SPIP, faut-il le cr&eacute;er automatiquement ?',
"pas_creer_auteur" => 'Ne pas cr&eacute;er l\'auteur',
"creer_redacteur" => 'Cr&eacute;er l\'auteur avec le statut de r&eacute;dacteur',
"creer_visiteur" => 'Cr&eacute;er l\'auteur avec le statut de visiteur',
'supprimer_serveur_cas' => 'Supprimer ce serveur additionnel',
'titre_creer_serveur' => 'Ajouter un serveur',
'info_serveurs' => 'Il est possible d\'ajouter des serveurs CAS additionnels. Si l\'authentification sur le serveur CAS &eacute;choue, le plugin tentera l\'authentification sur le premier serveur CAS additionnel (si elle &eacute;choue &eacute;galement, le plugin tentera l\'authentification sur le second serveur CAS additionnel, etc.).',
'aucun_serveur' => 'Aucun serveur additionnel n\'a &eacute;t&eacute; cr&eacute;&eacute;.',
'aucun_serveur_fichier_param' => 'Aucun serveur additionnel ne figure dans le fichier de param&eacute;trage.',
'erreur_obligatoire' => 'Information obligatoire',
'erreur_int' => 'La valeur saisie doit être un nombre.',
'erreur_incorrect' => 'Valeur incorrecte.',
'choix_serveur' => 'Vous pouvez vous authentifier avec l\'un des serveurs d\'authentification suivant (si vous ne savez pas lequel choisir, cliquer d\'abord sur le premier) :',
'memoriser_choix'=>'M&eacute;moriser ce choix',
'email_non_modifiable'=>'Vous n\'êtes pas autorisé à modifier votre adresse email',

);

?>