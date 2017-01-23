<?php
/**
 * These are the classes specific to MySQL
 */


class PHPDS_mysql extends PHPDS_genericDB
{
    /**
     * {@inheritDoc}
     */
    public function throwException($e = null, $code = 0)
    {
        $e = $this->factory('PHPDS_MySQLException', $e, $code);
        parent::throwException($e);
    }
}

/**
 * An exception handling MySQL error codes
 */
class PHPDS_MySQLException extends PHPDS_databaseException
{
    protected $ignoreLines = 4;

    /**
     * {@inheritDoc}
     */
    // CAUTION this declaration is NOT correct but PHP insists on this declaration,
    // last param $dependancy is missing
    public function construct($message = "", $code = 0, $previous = null)
    {
        if ($code == 1045) {
            $this->ignoreLines = 5;
        }
        parent::construct(_('Error from MySQL engine'), $code, $previous);
        $this->extendMessage(_('The MySQL database engine returned with an error') . ': "' . $message . '"');
    }

    /**
     * Can we give more help?
     * @return bool
     */
    public function hasCauses()
    {
        return in_array($this->getCode(), array(
            1044, 1045, // access denied
            0, // unknown error
            1049, // unknown database
            2002, // cannot connect
            1146 // table doesn't exist
        ));
    }

    /**
     * @date 20170122 (1.1) (greg) Deprecated and stripped down in favour of PHPDS_databaseException
     *
     * @deprecated
     *
     * @return array|null
     */
    public function getCauses()
    {
        return parent::getCauses();
    }


}
