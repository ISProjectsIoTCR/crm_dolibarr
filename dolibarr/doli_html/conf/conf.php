<?php
// Config file for Dolibarr 14.0.5 (Tue Jan 25 15:50:10 CST 2022)

// ###################
// # Main parameters #
// ###################
$dolibarr_main_url_root='http://localhost';
$dolibarr_main_document_root='/var/www/html';
$dolibarr_main_url_root_alt='/custom';
$dolibarr_main_document_root_alt='/var/www/html/custom';
$dolibarr_main_data_root='/var/www/documents';
$dolibarr_main_db_host='mariadb';
$dolibarr_main_db_port='3306';
$dolibarr_main_db_name='dolibarr';
$dolibarr_main_db_prefix='llx_';
$dolibarr_main_db_user='dolibarr';
$dolibarr_main_db_pass='dolibarr';
$dolibarr_main_db_type='mysqli';
$dolibarr_main_db_character_set='utf8';
$dolibarr_main_db_collation='utf8_unicode_ci';

// ##################
// # Login          #
// ##################
$dolibarr_main_authentication='dolibarr';
$dolibarr_main_auth_ldap_host='127.0.0.1';
$dolibarr_main_auth_ldap_port='389';
$dolibarr_main_auth_ldap_version='3';
$dolibarr_main_auth_ldap_servertype='openldap';
$dolibarr_main_auth_ldap_login_attribute='uid';
$dolibarr_main_auth_ldap_dn='';
$dolibarr_main_auth_ldap_filter ='';
$dolibarr_main_auth_ldap_admin_login='';
$dolibarr_main_auth_ldap_admin_pass='';
$dolibarr_main_auth_ldap_debug='false';

// ##################
// # Security       #
// ##################
$dolibarr_main_prod='0';
$dolibarr_main_force_https='0';
$dolibarr_main_restrict_os_commands='mysqldump, mysql, pg_dump, pgrestore';
$dolibarr_nocsrfcheck='0';
$dolibarr_main_cookie_cryptkey='4ff6afd1c64daa8e5657130fa2c03e4cc48c3d9dc8a565c7da94cbf7622cf881';
$dolibarr_mailing_limit_sendbyweb='0';
