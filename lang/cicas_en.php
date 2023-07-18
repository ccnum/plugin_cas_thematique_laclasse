<?php

// Fichier local de surcharge du fichier de langue

$GLOBALS[$GLOBALS['idx_lang']] = array(
'eq_avertissement' => '<b>CAUTION</b> : it is imperative to select (below) the authentification mode call "CAS or SPIP" to verify, without risk, the smooth running of the CAS authentification. Once this check made, we can then select (below) the authentification mode call "CAS".', 
'eq_titre' => 'CICAS plugin management',
'eq_titre_mode_auth' => 'Authentification mode',
'eq_lien_auth_hybride' => 'Use CAS authentification',
'eq_titre_serveur_cas' => 'CAS server management',
'eq_texte_auth_cas' => 'CAS',
'eq_texte_auth_hybride' => 'CAS or SPIP',
'eq_texte_auth_spip' => 'SPIP',
'eq_texte_host' => 'CAS host',
'eq_texte_url' => 'CAS url',
'eq_texte_repertoire' => 'CAS directory',
'eq_texte_port' => 'CAS port',
'eq_texte_uid' => 'Userid provide by the CAS server',
'eq_logout_cas' => 'CAS logout and back to home page',
'eq_texte_erreur1' => 'You have not been authenticated by the CAS server. Please contact the webmaster of the site.',
'eq_texte_erreur2' => 'You have been authenticated by the CAS server, but your e-mail address (or your login) has not been found in SPIP. Please contact the webmaster of the site.',
'titre' => "CAS management (SSO)",
'cicas_titre' => "CAS management (SSO)",
'serveur' => 'Additional CAS server',
'serveurs' => 'Additional CAS servers',
'auteurs' => 'Automatic creation of author',
'email' => 'email',
'login' => 'login',
"eq_texte_creer_auteur" => 'If authentication on the CAS server succeeded and the author does not exist in SPIP , should create the author automatically ?',
"pas_creer_auteur" => 'Do not create the author',
"creer_redacteur" => 'Create the author with writer status',
"creer_visiteur" => 'Create the author with visitor status',
'supprimer_serveur_cas' => 'Delete this additional server',
'titre_creer_serveur' => 'Add Server',
'info_serveurs' => 'It is possible to add additional CAS servers. If authentication on the CAS server fails , the plugin will try authentication on the first additional CAS server ( if it also fails, the plugin will try authentication on the second additional CAS server , etc.).',
'aucun_serveur' => 'No additional server has been created.',
'aucun_serveur_fichier_param' => 'No additional server listed in the configuration file.',
'erreur_obligatoire' => 'Required information',
'erreur_int' => 'The value must be a number.',
'erreur_incorrect' => 'Incorrect value.',
'choix_serveur' => 'You can authenticate with one of the following authentication servers (if you do not know which one to choose , click first on the first) :',
'memoriser_choix'=>'Remember this choice',
'email_non_modifiable'=>'You are not authorized to modify your email',

);

?>