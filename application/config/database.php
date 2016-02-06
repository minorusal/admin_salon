<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH.'libraries/config_vars.php');
$vars = new config_vars();
$vars->load_vars('assets/cfg/db.cfg');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'global_system';
$active_record = TRUE;

// $db['global_system']['hostname'] = '192.168.230.28';
$db['global_system']['hostname'] = $vars->db['db_mysql_host'];
$db['global_system']['username'] = $vars->db['db_mysql_user'];
$db['global_system']['password'] = $vars->db['db_mysql_pass'];
$db['global_system']['database'] = $vars->db['db_mysql_db1'];
$db['global_system']['dbdriver'] = $vars->db['db_engine'];
$db['global_system']['dbprefix'] = $vars->db['db_dbprefix'];
$db['global_system']['pconnect'] = $vars->db['db_pconnect'];
$db['global_system']['db_debug'] = $vars->db['db_debug'];
$db['global_system']['cache_on'] = $vars->db['db_cache_on'];
$db['global_system']['cachedir'] = $vars->db['db_cachedir'];
$db['global_system']['char_set'] = $vars->db['db_char_set'];
$db['global_system']['dbcollat'] = $vars->db['db_dbcollat'];
$db['global_system']['swap_pre'] = $vars->db['db_swap_pre'];
$db['global_system']['autoinit'] = $vars->db['db_autoinit'];
$db['global_system']['stricton'] = $vars->db['db_stricton'];

$db['mx']['hostname'] = $vars->db['db_mysql_host'];
$db['mx']['username'] = $vars->db['db_mysql_user'];
$db['mx']['password'] = $vars->db['db_mysql_pass'];
$db['mx']['database'] = $vars->db['db_mysql_db2'];
$db['mx']['dbdriver'] = $vars->db['db_engine'];
$db['mx']['dbprefix'] = $vars->db['db_dbprefix'];
$db['mx']['pconnect'] = $vars->db['db_pconnect'];
$db['mx']['db_debug'] = $vars->db['db_debug'];
$db['mx']['cache_on'] = $vars->db['db_cache_on'];
$db['mx']['cachedir'] = $vars->db['db_cachedir'];
$db['mx']['char_set'] = $vars->db['db_char_set'];
$db['mx']['dbcollat'] = $vars->db['db_dbcollat'];
$db['mx']['swap_pre'] = $vars->db['db_swap_pre'];
$db['mx']['autoinit'] = $vars->db['db_autoinit'];
$db['mx']['stricton'] = $vars->db['db_stricton'];


/* End of file database.php */
/* Location: ./application/config/database.php */