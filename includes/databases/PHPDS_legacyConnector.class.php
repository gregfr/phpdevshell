<?php

/** @noinspection PhpDeprecationInspection */

/**
 * CAUTION: legacy connector will stop working with certain version of PHP, since
 * PHP is dropping direct support of MySQL.
 * Use the PDO connector instead
 *
 * NOTE: you're not supposed to deal with connectors any way
 *
 * @deprecated PHP7 removed direct support for mysql_*() functions
 *
 * @author Greg
 *
 * @property string $dsn
 * @property string Charset
 * @property string host
 * @property string database
 * @property string username
 * @property string password
 * @property string prefix
 * @property string persistent
 *
 */
class PHPDS_legacyConnector extends PHPDS_dependant implements iPHPDS_dbConnector
{
    /**
     * Allow the connector class to provide itself with connection data.
     * Useful when you provided a database connection directly with a daughter class
     *
     * If it's not an array, it will be filled with the correct data at construction time.
     *
     * The content is an associative array as such:
     *
     * $this->dbSettings['dsn'] // a complete PDO DSN, built if not provided
     * $this->dbSettings['host']
     * $this->dbSettings['database']
     * $this->dbSettings['username']
     * $this->dbSettings['password']
     * $this->dbSettings['prefix']
     * $this->dbSettings['persistent']
     * $this->dbSettings['charset']
     *
     * @var string|array either a DSN or an array of configuration data
     */
    public $dbSettings;

    /**
     * @var resource    the link for the mysql connection (as returned by mysql_connect)
     */
    private $link;

    /**
     * This class helps dealing with database specific features
     *
     * @var iPHPDS_dbSpecifics $database
     */
    protected $dbSpecifics;

    /**
     * @var resource    the result resource of a query (as returned by mysql_query)
     */
    private $result;

    /* @var integer $selectedRows the number of rows selected by the last SELECT query */
    public $selectedRows = -1;

    /* @var integer $affectedRows the number of rows affected by the last INSERT/DELETE/etc query */
    public $affectedRows = -1;


    /**
     * Clears the current connection (useful for example if we're fetching one row at a time and we give up before the end)
     *
     * @date 20130222 (v1.0.1) (greg) fixed a bad default return value and bad check of $this->result
     *
     * @version 1.0.1
     *
     * @return boolean, TRUE on success or FALSE on failure
     * @see includes/PHPDS_db_connector#free()
     */
    public function free()
    {
        $result = true;
        if (is_resource($this->result)) {
            $result = mysql_free_result($this->result);
            $this->result = null;
        }
        return $result;
    }

    /**
     * @param string $db_config name of the db configuration to use (empty for default)
     */
    public function construct($db_config = '') // variable argument list
    {
        $this->dbSpecifics = $this->factory('PHPDS_mysql');
        $this->applyConfig($db_config);
    }

    /**
     * Sets the configuration settings for this connector as per the configuration file.
     *
     * @date        20120308
     * @date 20130226 (2.0) (greg) moved to PHPDS_mysql class
     * @version        2.0
     * @author        Don Schoeman
     * @see PHPDS_mysql
     */
    protected function applyConfig($db_config = '')
    {
        $this->dbSpecifics->applyConfig($this->dbSettings, $db_config);
    }

    /**
     * Connect to the database server (compatibility method)
     *
     * @date                20100219
     * @date 20130222 (1.0.1) (greg) minor rewrite of exception throwing
     * @date 20130609 (1.0.2) (greg) fixed a bug where the charset was not taken into account (#1189064)
     * @version        1.0.2
     *
     * @author        greg
     * @see stable/phpdevshell/includes/PHPDS_db_connector#connect()
     * @see https://bugs.launchpad.net/phpdevshell/+bug/1189064
     */
    public function connect($db_config = '')
    {
        // Apply database config settings to this instance of the connector
        $this->applyConfig($db_config);

        try {
            if (!empty($this->persistent)) {
                $this->link = mysql_pconnect($this->host, $this->username, $this->password);
            } else {
                $this->link = mysql_connect($this->host, $this->username, $this->password);
            }
            // Create database link.
            $ok = mysql_select_db($this->database, $this->link);
            // Display error on link.
            if (empty($ok)) {
                $this->dbSpecifics->throwException(mysql_error($this->link), mysql_errno($this->link));
            }
            if (!empty($this->dbSettings['charset'])) {
                mysql_set_charset($this->dbSettings['charset']);
            }
        } catch (PHPDS_databaseException $e) {
            $this->dbSpecifics->throwException($e);
        } catch (Exception $e) {
            if (!empty($this->link)) {
                $e = $this->factory('PHPDS_MySQLException', mysql_error($this->link), mysql_errno($this->link), $e);
            }
            $this->dbSpecifics->throwException($e);
        }
    }

    public function disconnect()
    {
        if (!empty($this->link)) {
            mysql_close($this->link);
        }
    }

    /**
     * Actually send the query to MySQL (through $db)
     *
     * May throw a PHPDS_databaseException
     *
     * @date        20100219
     * @version 2.0.4
     *
     * @author greg <greg@phpdevshell.org>
     *
     * @date 20100305 (2.0.1) (greg) fixed a bug with the _db_ prefix substitution
     * @date 20100729 (2.0.2) (greg) throw error
     * @date 20100729 (2.0.3) (greg) removed the outer exception throw
     * @date 20130222 (2.0.4) (greg) minor rewrite of exception throwing
     *
     * @throw PHPDS_MySQLException
     *
     * @param string $sql, the actual sql query
     * @param array|null $parameters unused
     * @return resource the resulting resource (or false is something bad happened)
     *
     * @see includes/PHPDS_db_connector#query()
     */
    public function query($sql, $parameters = null)
    {
        if (empty($this->link)) $this->connect();
        // Replace the DB prefix.
        $real_sql = preg_replace('/_db_/', $this->dbPrefix, $sql);
        // Run query.
        if (!empty($real_sql)) {
            // Count Queries Used...
            $this->db->countQueries++;
            $this->log($real_sql); // todo: really?
            $this->result = mysql_query($real_sql, $this->link);
            if (!$this->result) {
                $this->dbSpecifics->throwException(mysql_error($this->link), mysql_errno($this->link));
            }
            $this->selectedRows = is_resource($this->result) ? mysql_num_rows($this->result) : -1;
            $this->affectedRows = mysql_affected_rows($this->link);
            return $this->result;
        } else {
            return false;
        }
        // TODO: check result validity for non-select requests
    }

    /**
     * Protect a single string from possible hacker (i.e. escape possible harmful chars)
     *
     * @date            20100216
     * @version 1.0
     * @author    greg
     * @param    $param        string, the parameter to protect
     * @return string, the escaped string
     * @see includes/PHPDS_db_connector#protect()
     */
    public function protect($param)
    {
        return mysql_real_escape_string($param);
    }

    /**
     * Return the next line as an associative array
     *
     * @date            20100216
     * @version 1.0.1
     * @date 20130222 (1.0.1) (greg) minor rewrite
     * @author    greg
     * @return array, the resulting line (or false is nothing is found)
     * @see includes/PHPDS_db_connector#fetch_assoc()
     */
    public function fetchAssoc()
    {
        return is_resource($this->result) ? mysql_fetch_assoc($this->result) : false;
    }

    /**
     * Move the internal pointer to the asked line
     *
     * @date            20100216
     * @version 1.0.1
     * @date 20130222 (1.0.1) (greg) minor rewrite
     * @author    greg
     * @param    $row_number        integer, the line number
     * @return boolean, TRUE on success or FALSE on failure
     * @see includes/PHPDS_db_connector#seek()
     */
    public function seek($row_number)
    {
        return is_resource($this->result) ? mysql_data_seek($this->result, $row_number) : false;
    }

    /**
     * Return the number of rows in the result of the query
     *
     * @date            20100216
     * @version 2.0
     * @date 20130222 (1.0.1) (greg) minor rewrite
     * @date 20130325 (2.0) (greg) changed to use cached value from query()
     * @author    greg <greg@phpdevshell.org>
     * @return integer, the number of rows
     * @see includes/PHPDS_db_connector#numrows()
     */
    public function numrows()
    {
        return $this->selectedRows;
    }

    /**
     * Return the number of affected rows in the result of the query
     *
     * @date 20101103
     * @date 20130325 (2.0) (greg) changed to use cached value from query()
     * @version 2.0
     * @author    greg <greg@phpdevshell.org>
     * @return integer, the number of affected rows
     * @see includes/PHPDS_db_connector#affectedRows()
     */
    public function affectedRows()
    {
        return $this->affectedRows;
    }

    /**
     * This method returns the last MySQL error as a string if there is any. It will also
     * return the actual erroneous SQL statement if the display_sql_on_error property is
     * set to true. This is very helpfull when debugging an SQL related problem.
     *
     * @param string $query The actual query string.
     * @return string
     * @version 1.0.1
     * @date 20100329 prevent an exception if display_sql_on_error is not set
     * @author Don Schoeman <titan@phpdevshell.org>
     */
    public function returnSqlError($query)
    {
        $result = mysql_error($this->link);
        if (empty($this->displaySqlOnError) && !empty($result)) {
            $result = mysql_errno($this->link) . ": " . $result . '<br />' . $query;
        }
        return $result;
    }

    /**
     * Debugging Instance.
     *
     * @return debug object
     */
    public function debugInstance($ignored = null)
    {
        return parent::debugInstance('db');
    }

    /**
     * Simply returns last inserted id from database.
     *
     * @date 20100610 (greg) (v1.0.1) added $this->link
     * @version 1.0.1
     * @author jason
     * @return int
     */
    public function lastId()
    {
        return mysql_insert_id($this->link);
    }

    /**
     * Will return a single row as a string depending on what column was selected.
     *
     * @date 17062010 (jason)
     * @version 1.0.1
     * @date 20130222 (1.0.1) (greg) minor rewrite
     * @author jason
     * @return string
     */
    public function rowResults($row = 0)
    {
        return is_resource($this->result) ? mysql_result($this->result, $row) : false;
    }

    /**
     * Start SQL transaction.
     */
    public function startTransaction()
    {
        return $this->query("START TRANSACTION");
    }

    /**
     * Ends SQL transaction.
     *
     * @param boolean $commit
     */
    public function endTransaction($commit = true)
    {
        if ($commit) {
            $this->query("COMMIT");
        } else {
            $this->query("ROLLBACK");
        }
    }


    /**
     * magic method to get read-only access to various data
     *
     * @since 3.2.1
     * @version 1.0
     * @author greg <greg@phpdevshell.org>
     *
     * @date 20120611 (v1.0) (greg) added
     *
     * @param string $name name for the parameter to get (ie. "DSN", "Charset", "Host", ...)
     */
    public function __get($name)
    {
        if (isset($this->dbSettings[$name])) {
            return $this->dbSettings[$name];
        }
        // old style field name, deprecated
        if ((substr($name, 0, 2) == 'db') & isset($this->dbSettings[strtolower(substr($name, 2))])) {
            return $this->dbSettings[strtolower(substr($name, 2))];
        }
        return parent::__get($name);
    }

    /**
     * Returns as much information as possible on the server
     *
     * @since 3.5
     * @date 20130609 (1.0) (greg) added
     *
     * @return array
     *
     **/
    public function serverInfo()
    {
        $result = array();
        $result['server_info'] = mysql_get_server_info();
        $result['host_info'] = mysql_get_host_info();
        $result['client_info'] = mysql_get_client_info();
        $result['client_encoding'] = mysql_client_encoding();
        $result['stats'] = array();
        $stats = explode('  ', mysql_stat());
        foreach($stats as $stat) {
            list($key, $value) = explode(': ', $stat, 2);
            $result['stat'][$key] = $value;
        }

        return $result;
    }
}
