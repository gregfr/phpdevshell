<?php


/**
 * A black-box object to represent a query for data
 *
 * A general use is to have SQL queries to the main database: just provide the sql text in $sql
 *
 * You can inspect input parameters and result data with checkParameters() and checkResults()
 *
 * You can tune how data is returned with $keyField, $focus, $noEmptyRow, $singleRow...
 *
 * You can protect the query input parameters manually or automatically with $autoProtect and $autoQuote
 *
 * You can override the core behavior with run()
 *
 * @see http://www.phpdevshell.org/content/models
 *
 * As for version 3.5, some API changes have been made, to improve strength:
 * - run() have been added to better support complex and/or non-sql queries
 * - invoke doesn't return false anymore in case of error, but throws an exception
 *
 * These deprecated fields and methods have been dropped (because of bad casing):
 * - $single_value, $return_id, $auto_protect, $single_row, $no_empty_row
 * - rows (use arrayToListSQL() instead)
 * - as_array(), as_line(), as_one(), as_whole()
 * - extra_build(), protect_array(), get_key()
 * - get_results()
 *
 * This class is tested with PHPDS_queryTest.php and PHPDS_stubQueryTest.php
 *
 * TODO: add support for multiple queries based on the connector name
 *
 * @see /unittests/framework/includes/PHPDS_queryTest.php
 * @see /unittests/framework/includes/PHPDS_stubQueryTest.php
 *
 */
class PHPDS_query extends PHPDS_dependant
{
    /**
     * An implementation dependent link to a query result
     *
     * Don't touch it if you're using the default implementation.
     * You can use it as you wish if you're providing your own implementation
     *
     * @var mixed $resource
     */
    protected $resource;

    /**
     * The explicit SQL query
     *
     * This value, if present, is used when not overidden by an array in the field named "fields"
     * It can be accessed from the outside world thought the sql() method.
     * @see $fields
     * @see sql()
     * @var string,
     */
    protected $sql;

    /**
     * The name of the field to use as a key.
     * Use '__auto__' if you want the primary key to dictate the key of the array rows.
     * When this field is left empty the array will be build normally.
     *
     * @var string
     */
    protected $keyField = '';

    /**
     * Make a field the point of interest
     *
     * This field changes the way some arrays are returned:
     * - if $focus contains a field name, a row will be the value of this field (scalar) instead of an array of all values in the row
     * - if the row doesn't contain a field an empty value is used for the row
     * @var string
     */
    protected $focus = ''; // can be empty for 'no' or any other value for field name

    /**
     * strips any row with no content
     * @var boolean
     */
    protected $noEmptyRow = false;

    /**
     * Guidelines to typecast/forcecast the result data
     *
     * @var string | array of strings
     */
    protected $typecast;

    /**
     * The first line of the result is returned instead of a one-line array
     *
     * @var boolean
     */
    protected $singleRow = false;

    /**
     * Automatically escape bad chars for all in-parameters
     * @var boolean
     */
    protected $autoProtect = true;

    /**
     * If you want your non-numeric values to be quoted, set the quote character here
     * @var string
     */
    protected $autoQuote = null;

    /**
     * Instead of the query result, returns the last_insert_id()
     * @var boolean
     */
    protected $returnId = false;

    /**
     * Return one value from the asked field of the asked line
     * @var boolean
     */
    protected $singleValue = false;

    /**
     * A link between the query and the actual database server.
     *
     * Set this to the connector class name if you want something else than the default one
     *
     * @var string|iPHPDS_dbConnector, the connector used to carry the query (either name or instance)
     */
    protected $connector = null;

    /**
     * The list of fields to study
     *
     * If present, this associative array contains the fields which will be present in the SELECT ... clause.
     * This will override the $sql field; however if you use the sql('something') method after preBuild() the new query string will override the fields
     * @see $sql
     * @see preBuild()
     * @see sql()
     * @var array (optional)
     */
    protected $fields;

    /**
     * The WHERE clause
     *
     * A default WHERE clause; note you can use the addWhere() method to concatenate after this value
     * @var string (optional)
     */
    protected $where;
    protected $groupby = '';
    protected $orderby = '';
    protected $limit = '';

    /**
     * In some specific case (namely debugging) this will contain a cached version of the results
     * AVOID playing with that
     * @date 20110218 (greg) added
     * @var array
     */
    protected $cachedResult;

    /**
     * number of rows counted from fetching the result - only valid after the whole result has been fetched
     *
     * Note this can be different of what the database returns: for example, a SELECT query returning 4 lines
     * having the $singleRow flag will have rowCount = 1 and affectedRow = 4
     *
     * @var int
     */
    protected $rowCount = -1;

    /**
     * number of rows affected by the query - validity depends on the DB used
     *
     * For a SELECT query, it's the original rowCount AFTER result have been fetched
     *
     * @var int
     */
    protected $affectedRows = -1;

    /**
     * Constructor
     */
    public function construct()
    {
        if (empty($this->connector)) {
            $this->connector($this->db->connector()); // use default connector
        } else {
            $this->connector($this->connector);
        }
    }

    /**
     * Get and/or set the actual connector instance
     *
     * Note: can only be set if it was not set before
     *
     * @param iPHPDS_dbConnector|string $connector
     * @return iPHPDS_dbConnector
     */
    public function connector($connector = null)
    {
        if (!is_a($this->connector, 'iPHPDS_dbConnector')) {
            if (is_string($connector)) {
                $connector = $this->db->connector($connector);
            }
            if (is_a($connector, 'iPHPDS_dbConnector')) {
                $this->connector = $connector;
            }
        }
        return $this->connector;
    }

    /**
     * The usual process of a query: check the parameters, send the query to the server, check the results
     *
     * Return the results as an array (for SELECT queries), true for other successful queries, false on failure
     *
     * @version 2.0.1
     *
     * @date 20100709 (1.1.1) (greg) changed is_resource() to !empty() because it may return something else
     * @date 20130221 (2.0) (greg) added exceptions ; moved fix for non-select queries to getResults() ; changed query() to run()
     * @date 20130726 (2.0.1) (greg) be nicer to encapsulated exceptions
     *
     *
     * @param mixed $parameters
     *
     * @throws PHPDS_databaseException
     * @throws PHPDS_exception
     *
     * @return array or boolean
     */
    public function invoke($parameters = null)
    {
        try {
            if ($this->freeResource()) { // todo called twice! here and inside query() (called by run() )
                if ($this->checkParameters($parameters)) {
                    $this->resource = $this->run($parameters);
                    if (false !== $this->resource) {
                        $results = $this->getResults();
                        if ($this->checkResults($results)) {
                            return $results;
                        }
                        throw new PHPDS_exception('Bad results');
                    }
                    throw new PHPDS_exception('Unable to query');
                }
                throw new PHPDS_exception('Incorrect parameters');
            }
            throw new PHPDS_exception('Unable to free resource');
        } catch (Exception $e) {
            $msg = '<p>The faulty query source sql is:<br /><pre class="ui-state-highlight ui-corner-all">'
                .$this->sql().'</pre><br />';
            if (!empty($parameters)) {
                $msg .= '<tt>' . PU_dumpArray($parameters, _('The query parameters were:')) . '</tt>';
            }
            /* @var PHPDS_databaseException $ex */
            $ex = $this->factoryWith(
                'PHPDS_databaseException',
                array(
                    _('Error invoking a query of class "'. get_class($this).'"'),
                    0,
                    $e
                )
            );
            $ex->extendMessage($msg);
            throw $ex;
        }
    }

    /**
     * Build and send the query to the database
     *
     * @since 20100219
     * @version 1.1
     * @date 20110216 (greg) (v1.0.3) added a log of the sql + the class name
     * @date 20110731 (greg) altered to use $this->querySQL
     * @date 20130223 (1.1) (greg) now call freeResource() before executing a new request TODO called twice!!
     *
     * @author    greg
     * @param mixed|null $parameters array (optional) the parameters to inject into the query
     * @return resource some kind of link to the result, depending on the database connector
     */
    public function query($parameters = null)
    {
        if (false === $this->freeResource()) {
            /* @var PHPDS_databaseException $e */
            $e = $this->factory('PHPDS_databaseException', 'Unable to free connection', 0, null);
            throw $e;
        }
        $sql = $this->build($parameters);
        return $this->querySQL($sql);
    }

    /**
     * Directly send the query to the database
     *
     * @since 20110731
     * @version 1.0.1
     * @date 20110731 (greg) added based on old $this->query
     * @date 20120724 (v1.0.1) (greg) added PHPDS_queryException
     * @author greg
     * @param string $sql, the sql request
     * @return resource some kind of link to the result, depending on the database connector
     */
    public function querySQL($sql)
    {
        try {
            $this->rowCount = -1;
            $result = $this->connector->query($sql);
            $this->affectedRows = $this->connector->affectedRows();
            $this->rowCount = $this->connector->numrows();

            $this->queryDebug($sql);

            return $result;
        } catch (Exception $e) {
            throw new PHPDS_queryException($sql, 0, $e);
        }
    }

    /**
     * Firephp-specific debug display of the query
     *
     * @param string $sql
     */
    public function queryDebug($sql)
    {
        $debug = $this->debugInstance();
        $firephp = $this->errorHandler->getFirePHP();
        if ($debug->enable() && $firephp && !headers_sent()) {

            $flags =
                ($this->singleRow ? ' singleRow' : '') . ($this->singleValue ? ' singleValue' : '') . ($this->noEmptyRow ? ' noEmptyRow ' : '')
                    . (empty($this->focus) ? '' : ' focus=' . $this->focus) . (empty($this->keyField) ? '' : ' keyField=' . $this->keyField) . (empty($this->typeCast) ? '' : ' typeCast=' . $this->typeCast);

            $table = array();
            $table[] = array('', '');
            $table[] = array('SQL', $sql);
            $table[] = array('count', $this->affectedRows . ' rows');
            $table[] = array('flags', $flags);

            $firephp->table('Query: ' . get_class($this), $table);

            /*$firephp->group('Query: '.get_class($this),
                                array('Collapsed' => true,
                                            'Color' => '#64c40a'));

            $firephp->log($sql, '[ SQL ]');
            $firephp->log($this->count(). ' rows', '[ count ]');
            $firephp->log($flags,'[ flags ]');
            //$firephp->log($this->asWhole(), '[ '.$this->count(). ' rows ]');
            $firephp->groupEnd();*/
        }
    }

    /**
     * Build a query combination of columns and rows specifically designed to write rows of data to the database.
     *
     * This function expects either:
     *         an array of arrays, i.e. an array which pieces are arrays of scalars
     *         or an array of string-convertible entities (strings, numbers, even convertible objects)
     *
     * Note that if the structure is not correct an error maybe may raised
     *
     * Note also this method deals with NULL values
     *
     * @version 1.1
     * @date 20121120 (v1.1) (greg)
     * @author greg <greg@phpdevshell.org>
     * @author jason
     *
     * @param array $parameters an array of (arrays or strings)
     * @param boolean $protect is the array to be protected by the method?
     *
     * @return string
     */
    public function arrayToListSQL(array $parameters, $protect = false)
    {
        if ($protect) {
            $parameters = $this->db->protectArray($parameters, '\'');
        }
        $build = array();
        foreach ($parameters as $line) {
            $string = '';
            if (is_array($line)) {
                $string = '';
                foreach ($line as $element) {
                    $string .= (!empty($string) ? ', ' : '') . (is_null($element) ? 'NULL' : (string)$element);
                }
            } else {
                $string = is_null($line) ? 'NULL' : (string)$line;
            }
            $build[] = '(' . $string . ')';
        }
        return implode(', ', $build);
    }

    /**
     * Get/set actual sql string.
     *
     * You may want to override this to alter the sql string as whole, and/or build it from various sources.
     * Note this is only the first part of the query (SELECT ... FROM ...), NOT including WHERE, GROUP BY, ORDER BY, LIMIT
     *
     * @param string $sql (optional) if given, stored into the object's sql string
     * @return string the sql text
     * @version    1.0
     * @author greg
     */
    public function sql($sql = null)
    {
        if (!empty($sql)) $this->sql = $sql;
        return $this->sql;
    }

    /**
     * Build the query based on the private sql and the parameters
     *
     * TODO: allow a callable in $parameters
     *
     * @since 20100216
     * @since 20100428    (v1.0.1) (greg) use sql() instead of sql
     * @date 20100630 (v1.0.2) (greg) use array_compact to avoid null values
     * @date 20121014 (v1.0.3) (greg) removed used of array_compact
     * @version 1.0.3
     * @author greg <greg@phpdevshell.org>
     * @param $parameters (optional)array, the parameters to inject into the query
     * @return string, the sql query string
     */
    public function build($parameters = null)
    {
        $sql = '';

        try {
            $this->preBuild();
            $sql = $this->sql() . $this->extraBuild($parameters);

            if (!empty($parameters)) {
                if (is_scalar($parameters)) {
                    $parameters = array($parameters);
                }

                if (is_array($parameters)) {
                    if ($this->autoProtect) {
                        $parameters = $this->protectArray($parameters, $this->autoQuote);
                    }
                    $sql = PU_sprintfn($sql, $parameters);
                }
                //TODO is parameters is neither scalar nor array what should we do?
            }
        } catch (Exception $e) {
            throw new PHPDS_databaseException('Error building sql for <tt>' . get_class() . '</tt>', 0, $e);
        }
        return $sql;
    }

    /**
     * Construct the extra part of the query (WHERE ... GROUP BY ... ORDER BY...)
     * Doesn't change $this->sql
     *
     * @param array $parameters
     * @return string (sql)
     * @version    1.0
     * @author greg
     */
    public function extraBuild($parameters = null)
    {
        $extra_sql = '';

        if (!empty($this->where)) $extra_sql .= ' WHERE ' . $this->where . ' ';
        if (!empty($this->groupby)) $extra_sql .= ' GROUP BY ' . $this->groupby . ' ';
        if (!empty($this->orderby)) $extra_sql .= ' ORDER BY ' . $this->orderby . ' ';
        if (!empty($this->limit)) $extra_sql .= ' LIMIT ' . $this->limit . ' ';

        return $extra_sql;
    }

    /**
     * If the fields list has been set, construct the SELECT statement (or else do nothing)
     *
     * @version 1.0.1
     * @author greg
     * @date 20100628 (v1.0.1) (greg) the build sql string replaces the obejct's sql field, instead of being appended
     */
    public function preBuild()
    {
        $fields = $this->fields;
        if (!empty($fields)) {
            $sql = '';
            $key = $this->getKey();
            if ($key && !in_array($key, $fields)) $fields[$key] = true;
            foreach (array_keys($fields) as $key) if (!is_numeric($key)) $sql .= $key . ', ';
            $sql = 'SELECT ' . rtrim($sql, ', ');

            if (!empty($this->tables)) $sql .= ' FROM ' . $this->tables;
            $this->sql = $sql;
        }
    }

    /**
     * Add a subclause to the main WHERE clause of the query
     *
     * @param string $sql
     * @return self
     */
    public function addWhere($sql, $mode = 'AND')
    {
        if (empty($this->where)) $this->where = '1';
        $this->where .= " $mode $sql ";
        return $this;
    }

    /**
     * Protect a array of strings from possible hacker (i.e. escape possible harmfull chars)
     *
     * @since 20100216
     * @version 1.1
     * @date 20111010 (v1.1) (greg) added "quote" parameter
     * @author  greg
     * @param $a    array, the strings to protect
     * @param $quote string, the quotes to add to each non-numerical scalar value
     * @return array, the same string but safe
     */
    public function protectArray(array $a, $quote = '')
    {
        return $this->db->protectArray($a, $quote);
    }

    /**
     * Protect a strings from possible hacker (i.e. escape possible harmfull chars)
     *
     * @since 20101109
     * @version 1.0
     * @author  Jason
     * @param string $string the strings to protect
     * @return string the same string but safe
     */
    public function protectString($string)
    {
        $clean = $this->connector->protect($string);
        return $clean;
    }

    /**
     * Try to figure out which is the key field.
     *
     * TODO: we assume first column is a key field, this is wrong!!!
     *
     * @param array $row, a sample row to study
     * @return string (or null), the key field name
     * @version    1.0
     * @author greg
     */
    public function getKey($row = null)
    {
        $key = $this->keyField;
        if (is_array($row)) {
            if ('__auto__' == $key) {
                $keys = array_keys($row);
                $key = array_shift($keys);
            }
            return ($key && !empty($row[$key])) ? $row[$key] : null;
        } else {
            return '__auto__' != $key ? $key : null;
        }
    }

    /**
     * Returns all lines from the result as a big array of arrays
     *
     * @since 20100216    (v1.0) (greg)
     * @since 20100428    (v1.1) (greg) use the focus parameter; use the smart key
     * @since 20100607 (v1.2) (greg) renamed compact to focus, use the noEmptyRow/single_line parameters
     * @version 1.1
     * @author    greg
     * @return array, all the lines as arrays
     */
    public function asWhole()
    {
        $result = array();
        $count = 0;

        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $this->asLine()) {
            $count++;
            $key = $this->getKey($row);
            if (!empty($this->focus)) {
                $row = (isset($row[$this->focus])) ? $row[$this->focus] : null;
            }
            if ($row || !empty($this->noEmptyRow)) {
                if ($key) {
                    $result[$key] = $row;
                } else {
                    $result[] = $row;
                }
            }
        }
        $this->rowCount = $count;
        return $result;
    }

    /**
     *
     * @param <type> $values
     * @param <type> $key
     * @return <type>
     */
    public function typeCast($values, $key = null)
    {
        if (!empty($this->typecast)) {
            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    $values[$key] = $this->typeCast($value, $key);
                }
            } else {
                $type = is_array($this->typecast) ? (!empty($this->typecast[$key]) ? $this->typecast[$key] : null) : $this->typecast;
                switch ($type) {
                    case 'string':
                        $values = (string)$values;
                        break;
                    case 'int':
                    case 'integer':
                        $values = (int)$values;
                        break;
                    case 'bool':
                    case 'boolean':
                        $values = (bool)$values;
                        break;
                    case 'float':
                    case 'double':
                        $values = (float)$values;
                        break;
                    // default is to NOT change the $value
                }
            }
        }
        return $values;
    }

    /**
     * Deal with all special cases (i.e flags) regarding how results should be returned
     *
     * The special cases handled are these (in order of precedence):
     * - returnId (instead of the actual result, lastId is returned)
     * - singleValue (only the first value is returned as a scalar)
     * - singleRow (the first row is returned as a an one-dimension array)
     *
     * Cell-specific handling is done elsewhere
     *
     * In the absence of special case, the whole result is returned as an array of arrays (by calling as_whole() )
     *
     * @version 1.2
     *
     * @date 20100610 (greg) (v1.0) added, based on Jason's work
     * @date 20100617 (jason) (v1.0.1) added support for "string" setting
     * @date 20100620 (greg) (v1.0.2) cleaned up using class methods
     * @date 20100708 (greg) (v1.1) clean up with definitive API
     * @date 20110812 (greg) (v1.1.1) removed special "empty" case since only MySQL supports it
     * @date 20130221 (greg) (v1.2) moved in the "fix" for non-select statements
     *
     * @return mixed usually an array of data, although can be null if the resultset is empty, or int for an ID, or even the field content as string
     */
    public function getResults()
    {
        // Fix to prevent invoke from returning false if INSERT REPLACE DELETE etc... is executed on success.
        if ($this->resource === true && empty($this->returnId)) {
            $result = $this->connector->affectedRows();
        } elseif (!empty($this->returnId)) {
            $result = $this->connector->lastId();
        } elseif (!empty($this->singleValue)) {
            $result = $this->asOne();
            $this->affectedRows = $this->rowCount;
            $this->rowCount = !is_null($result) ? 1 : -1;
        } elseif (!empty($this->singleRow)) {
            $result = $this->asLine();
            $this->affectedRows = $this->rowCount;
            $this->rowCount = is_array($result) ? 1 : -1;
        } else {
            $result = $this->asWhole();
            $this->affectedRows = $this->rowCount;
            $this->rowCount = is_array($result) ? count($result) : -1;
        }

        return $result;
    }

    /**
     * Returns a single field from every line, resulting in an array of values (ie some kind of "vertical" fetching)
     *
     * Note: this is different from as_whole, since only ONE value is present in each line
     *
     * @since 20100216
     * @version 1.0.2
     * @date 20110816 (v1.0.1) (greg) added a count field
     * @date 20120202 (v1.0.2) (greg) using $this::getKey() to get the key column
     * @author greg
     * @param $field    string, the field to extract on each line
     * @return array, all the values
     */
    public function asArray($field)
    {
        $a = array();
        $count = 0;

        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $this->connector->fetchAssoc()) {
            $count++;
            if (!empty($row[$field])) {
                $value = $row[$field];
                $key = $this->getKey($row);

                if (!empty($key) && !empty($row[$key])) {
                    $a[$row[$key]] = $value;
                } else {
                    $a[] = $value;
                }
            }
        }

        $this->rowCount = $count;
        return $a;
    }

    /**
     * Returns the asked line as an array
     *
     * You can either ask for the next line (no parameter) or given a row number in the result.
     *
     * Note: the row number is based on the result, it may not be same as the row number in the complete table
     *
     * @since 3.0
     * @version 1.0.1
     * @author greg
     *
     * @date 20100810 (v1.0.1) (greg) return null if the resultset is empty
     * @param integer $row_number (optional) - NOT USED ANYMORE
     * @return array| null, the line or null if the resultset is empty
     */
    public function asLine($row_number = null)
    {
        if ($this->count() != 0) {
            $row = $this->connector->fetchAssoc();
            return $this->typeCast($row);
        }
        return null;
    }

    /**
     * Return one value from the asked field of the asked line
     *
     * @since 3.0
     * @version 1.0.5
     * @author greg
     *
     * @date 20100620 (v1.0.1) (greg) made parameters optional (no "field" means first field)
     * @date 20100630 (v1.0.2) (greg) object's focus is used if "$field" parameter is empty
     * @date 20100810 (v1.0.3) (greg) return null if the resultset is empty
     * @date 20110908 (v1.0.4) (greg) fixed a bug when dealing with an empty result line
     * @date 20130221 (v1.0.5) (greg) return null if no data is available; fixed a bug with focus
     *
     * @param integer $row_number (optional)
     * @param string $field field name (optional)
     * @return string | null
     */
    public function asOne($row_number = null, $field = null)
    {
        if ($this->count() != 0) {
            $row = $this->asLine($row_number);
            if (!is_array($row)) {
                return null;
            }
            if (empty($field)) {
                $field = $this->focus;
            }
            if (!empty($field)) {
                return (isset($row[$field]) ? $row[$field] : null);
            } else {
                return array_shift($row);
            }
        }
        return null;
    }

    /**
     * Return the number of lines in a result
     *
     * @since 20100216
     * @version 2.0
     * @date 20110816 (v2.0) (greg) changed not use a call to the connector since a PDO might give a wrong result
     * @author greg
     * @return integer, the number of rows, or -1 if it cannot be evaluated
     */
    public function count()
    {
        return $this->rowCount;
    }

    /**
     * Return the number of rows affected by the last query
     *
     * @return int
     */
    public function total()
    {
        return $this->affectedRows;
    }

    /**
     * Limits query.
     *
     * @param int $limit
     */
    public function limit($limit)
    {
        // TODO: check parameter
        $this->limit = $limit;
    }

    /**
     * Get an instance of debug object specific to this query
     *
     * @param null $domain
     * @return object
     */
    public function debugInstance($domain = null)
    {
        return parent::debugInstance(empty($domain) ? 'QUERY%' . get_class($this) : $domain);
    }

    /**
     * Returns the desired charset for the db link
     *
     * @version 1.1
     * @date 20130224 (1.1) (greg) check for property before accessing it
     *
     * @return string
     */
    public function charset()
    {
        $connector = $this->connector();
        return property_exists($connector, 'Charset') ? $connector->Charset : '';
    }


    /* THESE METHODS ARE MEANT TO BE OVERRIDDEN */

    /**
     * Allows daughter classes to check the parameters array before the query is sent
     *
     * Returning false will trigger an exception
     *
     * @param array $parameters the unprotected parameters
     * @return boolean true is it's ok to sent to the query, false otherwise
     */
    public function checkParameters(&$parameters = null)
    {
        return true;
    }

    /**
     * Allows daughter classes to check the results array before it's sent back
     *
     * Returning false will trigger an exception
     *
     * @param array $results the unprotected parameters
     * @return boolean true is it's ok to sent to the caller, false otherwise
     */
    public function checkResults(&$results = null)
    {
        return true;
    }

    /**
     * Allow daughter classes to completely override the main behavior
     *
     * Parameters have already been checked by checkParameters(), and
     * the result will be checked by checkResults() - see above
     *
     * Default behavior is to build and run the query, however you can change that
     *
     * @since 3.5
     *
     * @version 1.0
     *
     * @param array $parameters the unprotected parameters
     * @return resource|boolean whatever is meaningful to the query - usually a database-specific resource, or false if it failed
     */
    public function run(&$parameters = null)
    {
        return $this->query($parameters);
    }

    /**
     *
     * @return boolean, TRUE on success or FALSE on failure
     */
    public function freeResource()
    {
        return $this->connector->free();
    }

    /* End of user-overridable methods */

}




