Ce dépôt contient un module spip servant à connecter les utilisateurs aux classes culturelles numériques.
Basé sur la version du 19 septembre 2022 du module cicas : plugin d’authentification avec CAS pour SPIP 
https://contrib.spip.net/cicas-plugin-d-authentification-avec-CAS-pour-SPIP

#  before spip

## dependancies
apt update
install php-xml php-zip git wget unzip libzip-dev
docker-php-ext-install mysqli zip pdo_mysql
a2enmod rewrite && /etc/init.d/apache2 reload


## database
apt install mariadb-server
sudo mariadb
CREATE USER 'spip'@'localhost' IDENTIFIED BY 'spip';
CREATE DATABASE ccn_spip4;
GRANT ALL PRIVILEGES ON *.* TO 'spip'@'localhost' WITH GRANT OPTION;
\q

# SPIP
cd /var/www/html/nouvelle_ccn
wget https://files.spip.net/spip/archives/spip-v4.2.4.zip
unzip spip-v4.2.4.zip
rm spip-v4.2.4.zip
chmod -R 777 IMG tmp local config
mv htaccess.txt .htaccess
http://localhost/nouvelle_ccn/ecrire/

# Modules

## thematiques

## CAS

git clone --branch cicas-spip4 https://github.com/ccnum/plugin_cas_thematique_laclasse.git /var/www/html/nouvelle_ccn/plugins/CAS/

## Modules compémentaires