<?php
/**
 * With version 4.0 we did an API cleanup.
 *
 * Direct access to database connection data is deprecated:
 * - $server, $dbUserName, $dbPassword, $dbName
 *
 * A note on semantics:
 * - a connector is the part which deals with the communication protocol (could be an abstraction layer)
 * - a dbSpecfics object deals with the server software specifics (like Oracle, MySQL...)
 */


/**
 * Database specifics features
 *
 * Note that this deals with software specific features (such as language, error codes...)
 * The connector deals with specific calls (i.e. communication protocol)
 */
interface iPHPDS_dbSpecifics
{

    /**
     * Build and throw a database specific exception
     *
     * The first parameter can be a message (as a string) or a previous exception (can also be omitted)
     * If a message is provided, an error code can also be provided
     *
     * @param PHPDS_Exception|string|null $e
     * @param integer|null error code
     */
    public function throwException($e = null, $code = 0);


    /**
     * Sets the configuration settings for this connector as per the configuration file.
     *
     * The first parameter is a pointer to the connector's internal data array
     *
     * The parameter allows flexible configuration:
     * - if it's empty, the configuration in $this->dbConfig is used; if the later is empty too,
     *     the default system config is used
     * - if it's a string, a configuration by that name is looked up into the global configuration
     * - if it's an array, it's used a direct connection info
     *
     * Note that if a connection is already up, it's disconnected
     *
     * @param array $current_config the field which actually holds the connector data
     * @param string|array|null $db_config new data to specify how to connect to the database
     * @return void
     *
     */
    public function applyConfig(&$current_config, $db_config = null);
}


/**
 * A generic implementation of the iPHPDS_dbSpecifics interface
 *
 * Used when there is not specific support for a given database server software
 */
class PHPDS_genericDB extends PHPDS_dependant implements iPHPDS_dbSpecifics
{
    /**
     * Secondary constructor
     *
     * You can pass a config ref (array or string, @see iPHPDS_dbSpecifics) to have to applied directly
     *
     * @param null $db_config
     * @return bool|void
     */
    public function  construct($db_config = null)
    {
        if (!empty($db_config)) {
            $this->applyConfig($db_config);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @date 20161001 (1.0.1) (greg) some rewrite to handle previous exception
     *
     * @version 1.0.1
     */
    public function throwException($e = null, $code = 0)
    {
        if (is_a($e, 'Exception')) {
            /* @var Exception $e */
            $message = $e->getMessage();
            $previous = $e;
        } else {
            $message = is_string($e) ? $e : 'Database error';
            $previous = null;
        }

        if (!is_a($e, 'PHPDS_DatabaseException')) {
            /* @var PHPDS_DatabaseException $e */
            $e = $this->factory('PHPDS_DatabaseException', $message, $code, $previous);
        }
        throw $e;
    }

    /**
     * {@inheritDoc}
     */
    public function applyConfig(&$current_config, $db_config = null)
    {
        $dbSettings = array();

        // try to find a source for the database config
        if (empty($db_config)) {
            $dbSettings = empty($current_config) ? $this->db->getDBSettings() : $current_config;
        } else {
            if (is_string($db_config)) {
                $dbSettings = $this->db->getDBSettings($db_config);
            } elseif (is_array($db_config)) {
                $dbSettings = $db_config;
            } else {
                $this->throwException('Wrong database setting specification');
            }
        }

        // build DSN if needed
        if (empty($dbSettings['dsn'])) {
            $dsn = empty($dbSettings['driver']) ? 'mysql' : $dbSettings['driver'];
            $dsn .= ':host=' . (empty($dbSettings['host']) ? 'localhost' : $dbSettings['host']);
            $dsn .= ';dbname=' . (empty($dbSettings['database']) ? 'PHPDS' : $dbSettings['database']);

            if (!empty($dbSettings['charset'])) {
                $dsn .= ';charset=' . $dbSettings['charset'];
            }

            $dbSettings['dsn'] = $dsn;
        }

        $current_config = $dbSettings;
    }
}

/**
 * This is a blueprint for a connector, ie an object which handles the basic I/O to a database.
 * Its main use it to add a layer of exception throwing to mysql functions
 *
 * All of these methods are based on php-mysql interface function of the same name
 *
 * @author Greg
 *
 * @see iPHPDS_dbSpecifics
 */
interface iPHPDS_dbConnector
{
    /**
     * Clears the current data result (useful for example if we're fetching one row
     * at a time and we give up before the end)
     *
     * @return boolean, TRUE on success or FALSE on failure
     * @see includes/PHPDS_db_connector#free()
     */
    public function free();

    /**
     * Connect to the database server
     *
     * If $db_config is provided and the connection is already up, disconnect it and reconnect with the new settings
     *
     * $db_config has been added in 4.0
     *
     * @see stable/phpdevshell/includes/PHPDS_db_connector#connect()
     */
    public function connect($db_config = null);

    /**
     * Shutdown the connection to the database
     *
     * Added in 4.0
     *
     * @return null
     */
    public function disconnect();

    /**
     * Actually send the query to MySQL (through $db)
     *
     * If $parameters is provided, the query maybe be prepared (or not, depending on the connector)
     *
     * $parameters has been added in 4.0
     *
     * May throw a PHPDS_databaseException
     *
     * @param $sql string, the actual sql query
     * @return resource the resulting resource (or false is something bad happened)
     * @see includes/PHPDS_db_connector#query()
     */
    public function query($sql, $parameters = null);

    /**
     * Protect a single string from possible hacker (i.e. escape possible harmful chars)
     *
     * @param    $param        string, the parameter to protect
     * @return string, the escaped string
     * @see includes/PHPDS_db_connector#protect()
     */
    public function protect($param);

    /**
     * Return the next line as an associative array
     *
     * @return array, the resulting line (or false is nothing is found)
     * @see includes/PHPDS_db_connector#fetch_assoc()
     */
    public function fetchAssoc();

    /**
     * Move the internal pointer to the asked line
     *
     * @param    $row_number        integer, the line number
     * @return boolean, TRUE on success or FALSE on failure
     * @see includes/PHPDS_db_connector#seek()
     */
    public function seek($row_number);

    /**
     * Return the number of rows in the result of a SELECT query
     *
     * @return integer, the number of rows
     * @see includes/PHPDS_db_connector#numrows()
     */
    public function numrows();

    /**
     * Return the number of affected rows by a non-SELECT query
     *
     * @return integer, the number of affected rows
     * @see includes/PHPDS_db_connector#affectedRows()
     */
    public function affectedRows();

    /**
     * Simply returns last inserted id from database.
     *
     * @return int
     */
    public function lastId();

    /**
     * Will return a single row as a string depending on what column was selected.
     *
     * @return string
     */
    public function rowResults();

    /**
     * Start SQL transaction.
     */
    public function startTransaction();

    /**
     * Ends SQL transaction.
     *
     * @param boolean $commit
     */
    public function endTransaction($commit = true);

    /**
     * Returns as much information as possible on the server
     *
     * @since 3.5
     * @date 20130609 (1.0) (greg) added
     *
     * @return array
     *
     **/
    public function serverInfo();
}

/*require_once 'PHPDS_legacyConnector.class.php';
require_once 'PHPDS_query.class.php';*/

/**
 * This is a new version of one the Big5: the db class
 *
 * This new version supports connectors and queries class and should be compatible with the old one
 *
 * @version        1.0
 * @date                20100219
 * @author         greg
 *
 */
class PHPDS_db extends PHPDS_dependant
{
    /**
     * Contains database server name where PHPDevShell runs on.
     *
     * @deprecated
     *
     * @var string
     */
    public $server;

    /**
     * Contains database user name where PHPDevShell runs on.
     *
     * @deprecated
     *
     * @var string
     */
    public $dbUsername;

    /**
     * Contains database user password where PHPDevShell runs on.
     *
     * @deprecated
     *
     * @var string
     */
    public $dbPassword;

    /**
     * Contains database name where PHPDevShell runs on.
     *
     * @deprecated
     *
     * @var string
     */
    public $dbName;

    /**
     * Contains connection data.
     *
     * @var object
     */
    public $connection;

    /**
     * Memcache object.
     *
     * @var object
     */
    public $memcache;

    /**
     * Array for log data to be written.
     *
     * @var string
     */
    public $logArray;

    /**
     * Count amount of queries used by the system.
     * Currently it is on -2, we are not counting Start and End transaction.
     *
     * @var integer
     */
    public $countQueries = -2;

    /**
     * Contains array of all the plugins installed.
     *
     * @var array
     */
    public $pluginsInstalled;

    /**
     * Contains variable of logo.
     *
     * @var array
     */
    public $pluginLogo;

    /**
     * Essential settings array.
     *
     * @var array
     */
    public $essentialSettings;

    /**
     * Display erroneous sql statements
     *
     * @var boolean
     */
    public $displaySqlInError = false;

    /**
     * Stores results
     *
     * @var string
     */
    public $result;

    /**
     * Database connector.
     * @var iPHPDS_dbConnector
     */
    protected $connector;

    /**
     * List of alternates connectors (i.e., not the default, primary connector)
     * @var array of iPHPDS_dbConnector
     */
    protected $connectors;

    /**
     * For backward compatibility: a default query instance used for sending sql queries directly
     * @var PHPDS_query
     */
    protected $defaultQuery;

    /**
     * Constructor.
     *
     * At construct time, a connector using the default ('master_database') settings is created
     *
     * @version 1.0
     *
     * @date 20130223 (1.0) (greg) added
     *
     * @author greg <greg@phpdevshell.org>
     *
     *
     */
    public function construct()
    {
        $dbSettings = $this->getDBSettings();

        // For backwards compatibility, set the database class's parameters here as we don't know if anyone references
        // db's properties somewhere else
        $this->server = $dbSettings['host'];
        $this->dbName = $dbSettings['database'];
        $this->dbUsername = $dbSettings['username'];
        $this->dbPassword = $dbSettings['password'];

        $connectorClass = empty($dbSettings['connector']) ? 'PHPDS_legacyConnector' : $dbSettings['connector'];
        $this->connector = $this->factory($connectorClass, $dbSettings);
    }

    /**
     * @return array
     */
    public function getDBSettings($dbConfigName = '')
    {
        return PU_GetDBSettings($this->configuration, $dbConfigName);
    }

    /**
     * Force database connection.
     *
     * @param string|array|null $db_config specification for the connection
     *
     * @date 20120308
     * @version 1.0.2
     * @date 20130222 (1.0.1) (greg) minor rewrite
     * @date 20131102 (1.0.2) (greg) add message to exception
     */
    public function connect($db_config = '')
    {
        try {
            $this->connector->connect($db_config);
        } catch (Exception $e) {
            /* @var PHPDS_databaseException $e */
            $e = $this->factory('PHPDS_databaseException', 'Unable to connect to the master database', 0, $e);
            throw $e;
        }
    }

    /**
     * Handle access to the alternate connector list
     *
     * Give a class name, the connector will be instantiated if needed
     *
     * @param string $connector, class name of the connector
     * @return iPHPDS_dbConnector
     */
    public function connector($connector = null)
    {
        if (is_null($connector)) {
            return $this->connector;
        }
        if (is_string($connector) && class_exists($connector)) {
            if (isset($this->connectors[$connector])) {
                return $this->connectors[$connector];
            } else {
                $new = $this->factory($connector);
                if (is_a($new, 'iPHPDS_dbConnector')) {
                    $this->connectors[$connector] = $new;
                    return $new;
                }
            }
        }
        throw new PHPDS_exception('Unable to factor such a connector.');
    }

    /**
     * Compatibility
     * Do direct sql query without models.
     *
     * @date 20110512
     * @param string
     * @version    1.0
     * @author jason
     * @return mixed
     */
    public function newQuery($query)
    {
        try {
            if (empty($this->defaultQuery))
                $this->defaultQuery = $this->makeQuery('PHPDS_query');
            $this->defaultQuery->sql($query);
            return $this->defaultQuery->query();
        } catch (Exception $e) {
            if (empty($this->defaultQuery))
                $msg = 'Unable to create default query: ' . $e->getMessage();
            else
                $msg = 'While running default query:<br /><pre>' . $this->defaultQuery->sql() . '</pre>' . $e->getMessage();
            throw new PHPDS_databaseException($msg, 0, $e);
        }
    }

    /**
     * Alias to newQuery
     *
     * @param string $query
     * @return mixed
     */
    public function sqlQuery($query)
    {
        return $this->newQuery($query);
    }

    /**
     * Locates the query class of the given name, loads it, intantiate it, send the query to the DB, and return the result
     *
     * @date 20100219
     * @version 1.2
     * @date 20100922 (1.2) (greg) now use invokeQueryWithArgs
     * @author greg
     * @param $query_name string, the name of the query class (descendant of PHPDS_query)
     * @return array (usually), the result data of the query
     */
    public function invokeQuery($query_name) // actually more parameters can be given
    {
        $params = func_get_args();
        array_shift($params); // first parameter of this function is $query_name
        return $this->invokeQueryWith($query_name, $params);
    }

    /**
     * Locates the query class of the given name, loads it, intantiate it, send the query to the DB, and return the result
     *
     * @date 20100922 (1.0) (greg) added
     * @version 1.0
     * @author greg
     * @param $query_name string, the name of the query class (descendant of PHPDS_query)
     * @param $args array of parameters
     * @return array (usually), the result data of the query
     */
    public function invokeQueryWith($query_name, $params)
    {
        $query = $this->makeQuery($query_name);
        if (!is_a($query, 'PHPDS_query'))
            throw new PHPDS_databaseException('Error invoking query');
        return $query->invoke($params);
    }

    /**
     * Locates the query class of the given name, loads it, instantiate it, and returns the query object
     *
     * @date 20100219 (greg) created
     * @date 20140611 (1.3) (greg) added support for class name aliasing
     * @date 20110812 (v1.2) (greg) doesn't provide the query with the default connector anymore (let the query requests it at construct time)
     * @version 1.3
     * @author greg
     * @param $query_name string, the name of the query class (descendant of PHPDS_query)
     * @return PHPDS_query descendant, the query object
     */
    public function makeQuery($query_name)
    {
        $configuration = $this->configuration;
        $navigation = $this->navigation;
        /** @var PHPDS $phpds */
        $phpds = $this->PHPDS_dependance();

        $o = null;
        $good = false;

        // support for plugin aliasing - we should unit this with the Factory
        $alias = $phpds->PHPDS_classFactory()->classParams($query_name);
        if ($alias) {
            $query_name = $alias['class_name'];
            $path = $configuration['absolute_path'] . 'plugins/' . $alias['plugin_folder'] . '/models/' . $query_name . '.query.php';
            $good = $phpds->sneakClass($query_name, $path);
        }

        if (!$good) {
            $good = (class_exists($query_name, false));
        }

        if (!$good) {
            list($plugin, $menu_link) = $navigation->menuPath();
            $query_file = 'models/' . $menu_link;
            $query_file = preg_replace('/\.php$/', '.query.php', $query_file);
            $query_file = $configuration['absolute_path'] . 'plugins/' . $plugin . '/' . $query_file;
            $good = $phpds->sneakClass($query_name, $query_file);
            // Execute class file.
            if (!$good) {
                $menu = $configuration['m'];
                if (!empty($navigation->navigation[$menu])) {
                    $plugin = $navigation->navigation[$menu]['plugin'];
                    $query_file = $configuration['absolute_path'] . 'plugins/' . $plugin . '/models/plugin.query.php';
                    $good = $phpds->sneakClass($query_name, $query_file);
                }
            }
        }
        // All is good create class.
        if ($good) {
            $o = $this->factory($query_name);
            if (is_a($o, 'PHPDS_query')) {
                return $o;
            }
            throw new PHPDS_Exception('Error factoring query: object is not a PHPDS_query, maybe you mistyped the class superclass.');
        }
        throw new PHPDS_Exception('Error making query: unable to find class "' . $query_name . '".');
    }

    /**
     * Set the starting point for a SQL transaction
     *
     * You should call end_transaction(true) for the queries to actually occur
     */
    public function startTransaction()
    {
        return $this->connector->startTransaction();
    }

    /**
     * Commits database transactions.
     *
     * @version 1.0.1
     *
     * @date 20130415 (1.0.1) (greg) removed returns to comply to declaration
     *
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function endTransaction()
    {
        $configuration = $this->configuration;
        // Should we commit or rollback?
        if (($configuration['demo_mode'] == true)) {
            if ($configuration['user_role'] != $configuration['root_role']) {
                // Roll back all database changes.
                $this->connector->endTransaction(false);
            } else {
                // Commit all database changes.
                $this->connector->endTransaction(true);
            }
        } else if ($configuration['demo_mode'] == false) {
            // Commit all database changes.
            $this->connector->endTransaction(true);
        }
    }

    /**
     * Protect a single string from possible hacker (i.e. escape possible harmfull chars)
     *
     * Actually deleguate the action to the connector
     *
     * @date 20100329
     * @version 1.1
     * @author greg
     * @date 20111018 (v1.1) (greg) $param can now be an array
     * @param $param mixed, the parameter to espace
     * @return string, the escaped string/array
     * @see includes/PHPDS_db_connector#protect()
     */
    public function protect($param)
    {
        if (is_array($param)) {
            return $this->protectArray($param);
        } else {
            return $this->connector->protect($param);
        }
    }

    /**
     * Protect a array of strings from possible hacker (i.e. escape possible harmfull chars)
     * (this has been moved from PHPDS_query)
     * @version 1.1.2
     * @date 20150708 (1.1.2) (greg) correctly handles empty values (lp:147277)
     * @date 20111010 (v1.1) (greg) added "quote" parameter
     * @date 20121120 (v1.1.1) (greg) recursively use the quote parameter
     * @author  greg
     * @param $a    array, the strings to protect
     * @param $quote string, the quotes to add to each non-numerical scalar value
     * @return array, the same string but safe
     */
    public function protectArray(array $a, $quote = '')
    {
        foreach ($a as $index => $value) {
            $v = null;
            if (is_array($value)) {
                $v = $this->protectArray($value, $quote);
            } elseif (is_scalar($value)) {
                $v = $this->connector->protect($value);
                if (!is_numeric($v) && $quote) {
                    $v = $quote . $v . $quote;
                }
            } elseif (empty($value) && $quote) {
                $v = "$quote$quote";
            }

            if (!empty($v)) {
                $a[$index] = $v;
            }
        }

        return $a;
    }

    /**
     * Will convert object configuration into array for parsing.
     *
     */
    private function debugConfig()
    {
        $converted_config = array();
        foreach ($this->configuration as $key => $extended_config) {
            $converted_config[$key] = $extended_config;
        }
        $this->log($converted_config);
    }

    /**
     * Checks if a database table exists.
     *
     * @param string $table
     * @return boolean
     */
    public function tableExist($table)
    {
        return $this->invokeQuery('DB_tableExistQuery', $table);
    }

    /**
     * Simple method to count number of rows in a table.
     *
     * @param string $table_name
     * @param string $column
     * @return integer
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function countRows($table_name, $column = false)
    {
        // Check what to count.
        if (empty($column)) $column = '*';
        return $this->invokeQuery('DB_countRowsQuery', $column, $table_name);
    }

    /**
     * Method logs menu access per user.
     *
     */
    public function logMenuAccess()
    {
        $configuration = $this->configuration;
        // Check if we need to log.
        // Log menu access...
        if ($configuration['access_logging'] == true && !empty($configuration['m']))
            $this->invokeQuery('DB_logMenuAccessQuery', $configuration['m'], $configuration['user_id'], $configuration['time']);
    }

    /**
     * Add an entry to the array which will be eventually written to the framework "system log" table
     *
     * // Log types are :
     * // 1 = OK
     * // 2 = Warning
     * // 3 = Critical
     * // 4 = Log-in
     * // 5 = Log-out
     *
     * @version 1.0
     * @date 20151130 (1.0) (greg) added
     * @since 3.5
     * @param array $logEntry
     */
    public function pushLog(array $logEntry)
    {
        if (!empty($logEntry['log_type'])) {
            $this->logArray[] = $logEntry;
        }
    }

    /**
     * Add an exception as an entry to the framework "system log" table
     *
     * @version 1.0
     * @date 20151130 (1.0) (greg) added
     * @since 3.5
     * @param Exception $e
     */
    public function pushException(Exception $e, $log_type = 2, $fileref = '')
    {
        $msg = get_class($e).': '.$e->getMessage();

        /*if (is_a($e, 'PHPDS_Exception')) {
            /* @var PHPDS_Exception $e *
            $msg .= "\n".$e->getExtendedMessage();
        }*/
        $this->db->pushLog(array(
            'log_type' => $log_type,
            'log_description' => $msg,
            'file_name' => (empty($fileref) ? $_SERVER['REQUEST_URI'] : '@'.$fileref)
        ));
    }

    /**
     * This method logs error and success entries to the database.
     *
     * @param integer $log_type
     * @param string $log_description
     * @author Jason Schoeman <titan@phpdevshell.org>
     *
     * @version 1.0.2
     *
     * @date ? (1.0.1) (?) Changed mysql_escape_string() to mysql_real_escape_string() [see http://www.php.net/manual/en/function.mysql-escape-string.php ]
     * @date 20131003 (1.0.2) (greg) added a test to protect against an empty log array
     */
    public function logThis()
    {
        if (!empty($this->logArray)) {
            $this->invokeQuery('DB_logThisQuery', $this->logArray);
        }
    }

    /**
     * This function gets all role id's for a given user id, while returning a string divided by ',' character or an array with ids.
     * To pull multiple user roles, provide a string for $user_ids like so: '2,5,10,19'.
     *
     * @deprecated
     * @param string $user_id
     * @param boolean $return_array
     * @return mixed If $return_array = false a comma delimited string will be returned, else an array.
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function getRoles($user_id = false, $return_array = false)
    {
        return $this->user->getRoles($user_id, $return_array);
    }

    /**
     * This function gets all group id's for given user ids, while returning a string divided by ',' character or an array with ids.
     * To pull multiple user groups, provide a string for $user_ids like so : '2,5,10,19'.
     *
     * @deprecated
     * @param string $user_id Leave this field empty if you want skip if user is root.
     * @param boolean $return_array
     * @param string $alias_only If you would like only items of a certain alias to be called.
     * @return mixed If $return_array = false a comma delimited string will be returned, else an array.
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function getGroups($user_id = false, $return_array = false, $alias_only = false)
    {
        return $this->user->getGroups($user_id, $return_array, $alias_only);
    }

    /**
     * Simple check to see if a certain role exists.
     *
     * @deprecated
     * @param integer $role_id
     * @return boolean
     */
    public function roleExist($role_id)
    {
        return $this->user->roleExist($role_id);
    }

    /**
     * Simple check to see if a certain group exists.
     *
     * @deprecated
     * @param integer $group_id
     * @return boolean
     */
    public function groupExist($group_id)
    {
        return $this->user->groupExist($group_id);
    }

    /**
     * Check if user belongs to given role. Returns true if user belongs to user role.
     *
     * @deprecated
     * @param integer $user_id
     * @param integer $user_role
     * @return boolean Returns true if user belongs to user role.
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function belongsToRole($user_id = false, $user_role)
    {
        return $this->user->belongsToRole($user_id, $user_role);
    }

    /**
     * Check if user belongs to given group. Returns true if user belongs to user group.
     *
     * @deprecated
     * @param integer $user_id
     * @param integer $user_group
     * @return boolean Returns true if user belongs to user group.
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function belongsToGroup($user_id = false, $user_group)
    {
        return $this->user->belongsToGroup($user_id, $user_group);
    }

    /**
     * Creates a query to extend a role query, it will return false if user is root so everything can get listed.
     * This is meant to be used inside an existing role query.
     *
     * @deprecated
     * @param string $query_request Normal query to be returned if user is not a root user.
     * @param string $query_root_request If you want a query to be processed for a root user seperately.
     * @return mixed
     */
    public function setRoleQuery($query_request, $query_root_request = false)
    {
        return $this->user->setRoleQuery($query_request, $query_root_request);
    }

    /**
     * Creates a query to extend a group query, it will return false if user is root so everything can get listed.
     * This is meant to be used inside an existing group query.
     *
     * @deprecated
     * @param string $query_request Normal query to be returned if user is not a root user.
     * @param string $query_root_request If you want a query to be processed for a root user seperately.
     * @return mixed
     */
    public function setGroupQuery($query_request, $query_root_request = false)
    {
        return $this->user->setGroupQuery($query_request, $query_root_request);
    }

    /**
     * Generates a prefix for plugin general settings.
     *
     * @param string $custom_prefix
     * @return string Complete string with prefix.
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function settingsPrefix($custom_prefix = false)
    {
        // Create prefix.
        if ($custom_prefix == false) {
            // Get active plugin.
            $active_plugin = $this->core->activePlugin();
            if (!empty($active_plugin)) {
                $prefix = $active_plugin . '_';
            } else {
                $prefix = 'PHPDevShell_';
            }
        } else {
            $prefix = $custom_prefix . '_';
        }
        return $prefix;
    }

    /**
     * Used to write general plugin settings to the database.
     * Class will always use plugin name as prefix for settings if no custom prefix is provided.
     * <code>
     * // Example:
     * $db->writeSettings(array('setting_name'=>'value')[,'Example'][,array('setting_name'=>'note')]);
     * </code>
     * @param array $write_settings This array should contain settings to write.
     * @param string $custom_prefix If you would like to have a custom prefix added to your settings.
     * @param array $notes For adding notes about setting.
     * @return boolean On success true will be returned.
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function writeSettings($write_settings, $custom_prefix = '', $notes = array())
    {
        return $this->invokeQuery('DB_writeSettingsQuery', $write_settings, $custom_prefix, $notes);
    }

    /**
     * Delete all settings stored by a given plugins name, is used when uninstalling a plugin.
     *
     * Example:
     * <code>
     * deleteSettings(false, 'SimplePhonebook')
     * </code>
     *
     * @param array $settings_to_delete Use '*' to delete all settings for certain plugin.
     * @param string $custom_prefix
     * @return boolean
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function deleteSettings($settings_to_delete = false, $custom_prefix = false)
    {
        return $this->invokeQuery('DB_deleteSettingsQuery', $settings_to_delete, $custom_prefix);
    }

    /**
     * Loads and returns required settings from database.
     * Class will always use plugin name as prefix for settings if no custom prefix is provided.
     *
     * @param array $settings_required
     * @param string $prefix This allows you to use a prefix value of your choice to select a setting from another plugin, otherwise PHPDevShell will be used.
     * @return array An array will be returned containing all the values requested.
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function getSettings($settings_required = false, $custom_prefix = false)
    {
        return $this->invokeQuery('DB_getSettingsQuery', $settings_required, $custom_prefix);
    }

    /**
     * Used to get all essential system settings from the database, preventing multiple queries.
     *
     * @return array Contains array with essential settings.
     */
    public function getEssentialSettings()
    {
        // Pull essential settings and assign it to essential_settings.
        if ($this->cacheEmpty('essential_settings')) {
            $this->essentialSettings = $this->getSettings($this->configuration['preloaded_settings'], 'PHPDevShell');
            // Write essential settings data to cache.
            $this->cacheWrite('essential_settings', $this->essentialSettings);
        } else {
            $this->essentialSettings = $this->cacheRead('essential_settings');
        }
    }

    /**
     * Determines whether the specified search string already exists in the specified field within the supplied table.
     * Optional: Also looks at an id field (typically the primary key of a table) to make sure that the record you are working with
     * is NOT included in the search.
     * Usefull when modifying an existing record and you need first to check if another record with the same value doesn't already exist.
     *
     * @param string $table_name The name of the database table.
     * @param mixed $search_column_names The array names of the columns in which to look for the search strings, a single value can also be given.
     * @param mixed $search_field_valuesIn the same order as $search_column_name array, the search strings in array that should not be duplicated, a single value can also be given.
     * @param string $column_name_for_exclusionThe name of the primary key column name of the record you will be updating.
     * @param string $exclude_field_value The value of the primary key of the record you will be updating that should not be included in the search.
     * @return boolean If TRUE is returned it means the record already exists, FALSE means the record doesn't exist.
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function doesRecordExist($table_name, $search_column_names, $search_field_values, $column_name_for_exclusion = false, $exclude_field_value = false)
    {
        return $this->invokeQuery('DB_doesRecordExistQuery', $table_name, $search_column_names, $search_field_values, $column_name_for_exclusion, $exclude_field_value);
    }

    /**
     * Get a single result from database with minimal effort.
     *
     * @param string $from_table_name
     * @param string $select_column_name
     * @param string $where_column_name
     * @param string $is_equal_to_column_value
     * @return string
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function selectQuick($from_table_name, $select_column_name, $where_column_name, $is_equal_to_column_value)
    {
        return $this->invokeQuery('DB_selectQuickQuery', $select_column_name, $from_table_name, $where_column_name, $is_equal_to_column_value);
    }

    /**
     * Delete data from the database with minimal effort.
     *
     * @param string $from_table_name
     * @param string $where_column_name
     * @param string $is_equal_to_column_value
     * @param string $return_column_value
     * @return string
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function deleteQuick($from_table_name, $where_column_name, $is_equal_to_column_value, $return_column_value = false)
    {
        return $this->invokeQuery('DB_deleteQuickQuery', $from_table_name, $where_column_name, $is_equal_to_column_value, $return_column_value);
    }

    /**
     * This method is used to generate a new name value for a particular string in the database.
     *
     * Usage Example :
     * <code>
     *  // This code generates a copy of the text "Some Value". The name_of_new_copy function
     *  // checks that it doesn't duplicate the name.
     *  $result = $db->nameOfNewCopy('PHPDS_table', 'some_field', 'Some Value');
     *
     *  // name_of_new_copy() returns 'Copy of Some Value', unless 'Copy of Some Value' already exists. If
     *  // it does, the function will return with 'Copy (x) of Some Value' where x is the next available
     *  // number that is not in use.
     * </code>
     *
     * @param string $table_name The name of the table to search within.
     * @param string $name_field The fieldname to search withing.
     * @param string $orig_name The original text to search for.
     * @author Don Schoeman <don@delphexonline.com>
     */
    function nameOfNewCopy($table_name, $name_field, $orig_name)
    {
        return $this->invokeQuery('DB_nameOfNewCopyQuery', $table_name, $name_field, $orig_name);
    }

    /**
     * Writes array of all the installed plugins on the system.
     * @author Jason Schoeman <titan@phpdevshell.org>
     */
    public function installedPlugins()
    {
        $this->invokeQuery('DB_installedPluginsQuery');
    }

    /**
     * Does the connection to the memcache server.
     * Currently memcache is the primary supported engine.
     */
    public function connectCacheServer()
    {
        $configuration = $this->configuration;

        // Get cache configuration.
        $conf['cache_refresh_intervals'] = $configuration['cache_refresh_intervals'];

        // Assign configuration arrays.
        if ($configuration['cache_type'] != 'PHPDS_sessionCache') {
            $conf['cache_host'] = $configuration['cache_host'];
            $conf['cache_port'] = $configuration['cache_port'];
            $conf['cache_persistent'] = $configuration['cache_persistent'];
            $conf['cache_weight'] = $configuration['cache_weight'];
            $conf['cache_timeout'] = $configuration['cache_timeout'];
            $conf['cache_retry_interval'] = $configuration['cache_retry_interval'];
            $conf['cache_status'] = $configuration['cache_status'];
        }

        // Load Cache Class.
        require_once 'cache/' . $configuration['cache_type'] . '.inc.php';
        $this->memcache = new $configuration['cache_type'];

        // Check connection type.
        $this->memcache->connectCacheServer($conf);
    }

    /**
     * Writes new data to cache.
     * @param string $unique_key
     * @param mixed $cache_data
     * @param boolean $compress
     * @param int $timeout
     */
    public function cacheWrite($unique_key, $cache_data, $compress = false, $timeout = false)
    {
        // Check caching type.
        $this->memcache->cacheWrite($unique_key, $cache_data, $compress, $timeout);
    }

    /**
     * Return exising cache result to required item.
     * @param mixed $unique_key
     * @return mixed
     */
    public function cacheRead($unique_key)
    {
        return $this->memcache->cacheRead($unique_key);
    }

    /**
     * Clear specific or all cache memory.
     * @param mixed $unique_key
     */
    public function cacheClear($unique_key = false)
    {
        $this->memcache->cacheClear($unique_key);
    }

    /**
     * Checks if we have an empty cache container.
     * @param mixed $unique_key
     * @return boolean
     */
    public function cacheEmpty($unique_key)
    {
        return $this->memcache->cacheEmpty($unique_key);
    }

}
