<?php

    /** @noinspection PhpMultipleClassesDeclarationsInOneFile */

    /**
     * Exception extention.
     *
     * @version 1.1
     * @date 20120807 (v1.1) (greg) added "more info" ; support for factory (hence dependency)
     */
    class PHPDS_exception extends Exception
    {
        protected $ignoreLines = -1;
        protected $extendedMessage = '';

        /* @var Exception the previous exception in the chain */
        protected $previous = null;

        /* @PHPDS_dependant use for the dependency tree */
        protected $seed;

        /**
         * @param string $message The localized description of the exception
         * @param int $code The error code of the exception
         * @param null $previous A link to the previous exception in the chain (optional)
         * @param null $dependancy A link to build the dependency tree (optional)
         */
        public function __construct($message = "", $code = 0, $previous = null, $dependancy = null)
        {
            $this->seed = $dependancy;

            $this->construct($message, $code, $previous);
        }

        /**
         * Actual constructor
         *
         * @version 1.0.1
         *
         * @date 20131102 (1.0.1) (greg) handle empty $message parameter
         *
         * @param string $message
         * @param int $code
         * @param null $previous
         */
        public function construct($message = "", $code = 0, $previous = null)
        {
            if (is_a($previous, 'Exception')) {
                $this->previous = $previous;
            }
            if (empty($message)) {
                $message = _('Unknown exception');
            }
            $this->message = $message;
            $this->code = $code;
        }

        /**
         * Build this exception with the parameters of the given exception
         *
         * @param Exception $e
         */
        public function merge(Exception $e)
        {
            $this->construct($e->getMessage(), $e->getCode());
        }

        /**
         * Returns the "real" exception, in case there's one hidden
         *
         * @return PHPDS_exception
         */
        public function getRealException()
        {
            /* @var PHPDS_exception $this->previous */
            /** @noinspection PhpUndefinedMethodInspection */
            return is_a($this->previous, 'PHPDS_exception') ? $this->previous->getRealException() : $this;
        }

        /**
         * Return the previous (i.e. one-level higher) exception in the chain, if any
         * @return Exception|null
         */
        public function getPreviousException()
        {
            return $this->previous;
        }

        /**
         * Returns the number of ignored lines
         *
         * @return int
         */
        public function getIgnoreLines()
        {
            return $this->ignoreLines;
        }

        /**
         * Returns an html-formated extended message
         *
         * @return string
         */
        public function getExtendedMessage()
        {
            $msg = '<p>'.$this->extendedMessage.'</p>';

            $p = $this->previous;

            while (is_a($p, 'Exception')) {
                $msg .= '<p>'.$p->getMessage().'</p>';
                $p = $p->getPrevious();
            }

            return $msg;
        }

        /**
         * Returns the stack trace
         *
         * @return array
         */
        public function getExtendedTrace()
        {
            return empty($this->trace) ? $this->getTrace() : $this->trace;
        }

        /**
         * @param string $str Set the extended message string
         */
        public function extendMessage($str)
        {
            $this->extendedMessage .= $str;
        }

        /** the following methods are meant to be overriden */

        /**
         * some Exception may choose to display some possible cause for the error, to help tracking down the error
         */
        public function hasCauses()
        {
            return false;
        }

        /**
         *  returns a special message and a list of possible causes
         */
        public function getCauses()
        {
            return null;
        }

        /**
         * some Exception may choose to display more info for the error, to help fixing the error
         */
        public function hasMoreInfo()
        {
            return false;
        }

        /**
         *  returns more information
         */
        public function getMoreInfo()
        {
            return null;
        }
    }

    /**
     * Exception to handle errors during ajax requests
     */
    class PHPDS_remoteCallException extends PHPDS_exception
    {
        public $HTTPcode = 501;
    }

    /**
     * Exception to handle generic errors
     */
    class PHPDS_fatalError extends PHPDS_exception
    {
        /**
         * PHPDS_fatalError constructor.
         *
         * @param string $message Unused
         * @param int $code Unused
         * @param null $previous Unused
         */
        public function __construct($message = "", $code = 0, $previous = null)
        {
            $error = error_get_last();

            if (isset($error['message'])) $this->message = $error['message'];
            if (isset($error['type'])) $this->code = $error['type'];
            if (isset($error['file'])) $this->file = $error['file'];
            if (isset($error['line'])) $this->line = $error['line'];

            //$this->ignoreLines = 2;
        }
    }

    /**
     * Class PHPDS_databaseException
     *
     * @date 20170122 (1.1) (greg) Moved causes from mysql class to get generic help
     */
    class PHPDS_databaseException extends PHPDS_exception
    {
        /**
         * Can we give some information?
         *
         * @return bool
         */
        public function hasMoreInfo()
        {
            return is_a($this->seed, 'PHPDS_dependant');
        }

        /**
         *  returns more information
         */
        public function getMoreInfo()
        {
            /* @var PHPDS_dependant $this ->seed */
            $config = $this->seed->configuration;

            $db_settings = PU_GetDBSettings($config);

            return "Database <i>{$db_settings['database']}</i> with prefix <i>{$db_settings['prefix']}</i> on host <i>{$db_settings['host']}</i> with user <i>{$db_settings['username']}</i>.";
        }

        /**
         * Can we give some help?
         *
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
         * Returns a list of possible causes based on the error code.
         *
         * Note: we recognize a few SQLSTATE codes
         *
         * @return array
         */
        public function getCauses()
        {
            $special = '';
            switch ($this->getCode()) {
                case 1044:
                case 1045:
                    $special = 'db_access_denied';
                    break;
                case 0:
                    $special = 'db_unknown';
                    break;
                case 1049:
                    $special = 'db_unknown';
                    break;
                case 2002:
                    $special = 'db_silent';
                    break;
                case 1146:
                    $special = 'db_noexist';
                    break;
            }

            $coding_error = array(
                _('PHP Coding error interrupted query model, see uncaught exception below.'),
                _('This is normally nothing too serious just check your code and find the mistake you made by following the exception below.')
            );
            /** @noinspection HtmlUnknownTarget */
            $phpds_not_installed = array(
                _('You did not run the install script'),
                _('If you haven\'t run the installation procedure yet, you should <a href="other/service/index.php">run it</a> now.')
            );
            $db_wrong_cred = array(
                _('It is possible that the wrong credentials have been given in the configuration file.'),
                _('Please check the content of your configuration file(s).')
            );
            $db_wrong_dbname = array(
                _('It is possible that the wrong database name has been given in the configuration file.'),
                _('Please check the content of your configuration file(s).')
            );
            $db_down = array(
                _('The server is not running or is firewalled.'),
                _('Please check if the database server is up and running and reachable from the webserver.')
            );
            $db_denies = array(
                _('The server won\'t accept the database connection.'),
                _('Please check if the database server is configured to accept connection from the webserver.')
            );

            switch ($special) {
                case 'db_access_denied':
                    $result = array(
                        _('Access to the database was not granted using the parameters set in the configuration file.'),
                        array($phpds_not_installed, $db_wrong_cred)
                    );
                    break;
                case 'db_silent':
                    $result = array(
                        _('Unable to connect to the database (the database server didn\'t answer our connection request)'),
                        array($db_down, $db_denies, $db_wrong_cred)
                    );
                    break;
                case 'db_unknown':
                    $result = array(
                        _('The connection to the server is ok but the database could not be found.'),
                        array($coding_error, $phpds_not_installed, $db_wrong_dbname)
                    );
                    break;
                case 'db_noexist':
                    $result = array(
                        _('The connection to the server is ok and the database is known but the table doesn\'t exists.'),
                        array($phpds_not_installed, $db_wrong_dbname)
                    );
                    break;
                default:
                    $result = array(
                        _('Unknown special case.'),
                        array($coding_error, $phpds_not_installed, $db_wrong_dbname, $db_wrong_cred, $db_down, $db_denies)
                    );
            }

            return $result;
        }

    }

    /**
     * Base class for all access-related exception
     *
     * Unlikely to be instanciated
     */
    class PHPDS_accessException extends PHPDS_exception
    {
        public $HTTPcode;
    }

    /**
     * Instanciated when the user has unsufficient authorization (http error 401)
     */
    class PHPDS_securityException extends PHPDS_accessException
    {
        public $HTTPcode = 401;
    }

    /**
     * Intanciated when the resource is not to be served (http error 403)
     */
    class PHPDS_securityException403 extends PHPDS_accessException
    {
        public $HTTPcode = 403;
    }

    /**
     * Instanciated when the resource is not found (http error 404)
     */
    class PHPDS_pageException404 extends PHPDS_accessException
    {
        public $HTTPcode = 404;
    }

    /**
     * Instanciated when a bot is detected
     */
    class PHPDS_pageException418 extends PHPDS_accessException
    {
        public $HTTPcode = 418;
    }


    /**
     * This exception is sent when PU_sprintf() cannot operate
     */
    class PHPDS_sprintfnException extends PHPDS_exception
    {
        protected $ignoreLines = 5;

        /** @noinspection PhpDocSignatureInspection */
        /**
         * PHPDS_sprintfnException constructor.
         *
         * Actual parameters are
         *
         * @param string|array $message The format required as string or array ($format, $missing_key)
         * @param array $code The array of arguments
         * @param Exception|null $previous Optional upper-level exception
         */
        public function __construct($message = "", $code = 0, $previous = null) // CAUTION this declaration is NOT correct but PHP insists on this declaration
        {
            if (is_array($message)) {
                list($format, $key) = $message;
                $msg = sprintf(_('Missing named argument: "%s"'), $key);
            } else {
                $format = $message;
                $msg = _('Unable to build the string using sprintf');
            }
            $this->extendedMessage = '<p>The faulty string source is:<br /><pre class="ui-state-highlight ui-corner-all">'.htmlentities($format).'</pre><br />';
            if (!empty($code) && is_array($code)) {
                /** @var array $code */
                $this->extendedMessage .= '<p class="tt">'
                    .PU_dumpArray($code, _('The sprintfn parameters were:'), true).'</p>';
            }

            parent::__construct($msg, 0, $previous);
        }

        /**
         * We have a possible explanation
         *
         * @return true
         */
        public function hasCauses()
        {
            return true;
        }

        /**
         * We have a possible explanation
         *
         * @return array
         */
        public function getCauses()
        {
            $result = array(
                'Unable to build a string with <i>sprintfn</i>',
                array(
                    array('Some template or theme file has altered a module which doesn\'t comply to the given parameters.', 'Try a different theme or check for possible typos in the theme module list'),
                    array('You are using named parameters but you did not provided all parameters with their names', 'Check that you are using <b>invokeQueryWith</b> (since <b>invokeQuery</b> does not support named parameters)')
                )
            );

            return $result;
        }

    }


    /**
     * Exception extention.
     */
    class PHPDS_extendMenuException extends PHPDS_exception
    {
        /*protected $ignoreLines = 0;
        protected $extendedMessage = '';*/
        protected $extend = 0;
        protected $menuid = 0;

        /** @noinspection PhpDocSignatureInspection */
        /**
         * PHPDS_extendMenuException constructor.
         *
         * Actual parameters are
         *
         * @param array $message The menu definitation ($menu_id, $menu_extend)
         * @param int $code Unused
         * @param Exception|null $previous Optional upper-level exception
         */
        public function __construct($message = "", $code = 0, $previous = null) // CAUTION this declaration is NOT correct but PHP insists on this declaration
        {
            list($this->menuid, $this->extend) = $message;
            $msg = sprintf(___('Problem occurred extending menu item %s, it does not seem to exist.'), $this->extend);

            parent::__construct($msg, $code, $previous);
        }

        /**
         * Returns the stack trace
         *
         * @return array
         */
        public function getExtendedTrace()
        {
            return empty($this->trace) ? $this->getTrace() : $this->trace;
        }

        /**
         * some Exception may choose to display some possible cause for the error, to help tracking down the error
         */
        public function hasCauses()
        {
            return true;
        }

        /**
         *  returns a special message and a list of possible causes
         */
        public function getCauses()
        {
            $result = array(
                'The current menu item is actually a link to a base menu item, which cannot be accessed',
                array(
                    array('The "extend" field of the menu item maybe incorrect (wrong value, base menu has been deleted...)',
                        '<a href="index.php?m=3440897808&em='.$this->menuid.'">Edit the menu item</a> and specify a base menu item to extend from.'
                    ),
                    array('The base menu may exists but not be accessible for the current user',
                        '<a href="index.php?m=3440897808&em='.$this->extend.'">Edit the base menu item</a> and check its authorizations.'
                    )
                )
            );

            return $result;
        }
    }


    /**
     *  Exception for PHPDS_query
     *
     * @date 20120724 (v1.0) (greg) added
     */
    class PHPDS_queryException extends PHPDS_exception
    {
        protected $ignoreLines = 5;

        /**
         * PHPDS_queryException constructor.
         *
         * Actual parameters are
         *
         * @param string $message SQL query
         * @param int $code Unused
         * @param Exception|null $previous Optional upper-level exception
         */
        public function __construct($message = "", $code = 0, $previous = null) // CAUTION this declaration is NOT correct but PHP insists on this declaration
        {
            $msg = _('Error executing a query');
            $this->extendedMessage = '<p>'._('The faulty query REAL SQL was:').'<br /><pre class="ui-state-highlight ui-corner-all">'.$message.'</pre><br />';
            if (is_a($previous, 'PHPDS_exception')) {
                /** @var PHPDS_exception $previous */
                $previous->extendMessage($this->extendedMessage);
            }

            parent::__construct($msg, 0, $previous);
        }

    }

    /**
     *  Exception for the session starter
     *
     * @date 20120724 (v1.0) (greg) added
     */
    class PHPDS_sessionException extends PHPDS_exception
    {
        protected $ignoreLines = 4;

        protected $path;

        /**
         * PHPDS_sessionException constructor.
         *
         * Actual parameters are
         *
         * @param string $message Path to the session data
         * @param int $code Unused
         * @param Exception|null $previous Optional upper-level exception
         */
        public function __construct($message = "", $code = 0, $previous = null) // CAUTION this declaration is NOT correct but PHP insists on this declaration
        {
            $this->path = $message;
            $msg = _('Unable to start the session.');

            parent::__construct($msg, $code, $previous);
        }

        /**
         * some Exception may choose to display some possible cause for the error, to help tracking down the error
         */
        public function hasCauses()
        {
            return true;
        }

        /**
         *  returns a special message and a list of possible causes
         */
        public function getCauses()
        {
            $path = realpath($this->path);
            $result = array(
                'The session manager of PHP could not be started',
                array(
                    array('The session folder is not writable or missing',
                        'check that the folder "<p class="tt">'
                            .$path.'</p>" is present and writable, then reload this page.'
                    ),
                    array('The session file exists and is protected',
                        'check that in the folder "<p class="tt">'
                            .$path.'</p>", there is no file named as given below..'
                    )
                )
            );

            return $result;
        }
    }


    /**
     *  Exception when a controller is not found
     *
     * @date 20140606 (v1.0) (greg) added
     */
    class PHPDS_controllerNotFoundException extends PHPDS_accessException
    {
        protected $ignoreLines = 4;

        protected $controller;

        /**
         * This is the actual parameters:
         *
         * @param PHPDS_controller $controller
         * @param int $code
         * @param null $previous
         */
        public function __construct($message = '', $code = 0, $previous = null) // CAUTION this declaration is NOT correct but PHP insists on this declaration
        {
            $msg = 'Controller node not found';
            $this->controller = $message; // in fact a PHPDS_controller object

            parent::__construct($msg, $code, $previous);
        }

        /**
         * some Exception may choose to display some possible cause for the error, to help tracking down the error
         */
        public function hasCauses()
        {
            return true;
        }

        /**
         *  returns a special message and a list of possible causes
         */
        public function getCauses()
        {
            /** @var PHPDS_controller $this->controller */
            $configuration = $this->controller->configuration;

            $redirect_login = $configuration['redirect_login'];
            $loginandout = $configuration['loginandout'];
            $url = PU_MakeString($_SERVER["REQUEST_URI"], true);

            $result = array(
                'Unable to find a controller to answer the request',
                array(
                    array('The URL requested "<p class="tt">'
                        .$url.'</p>" would lead to a 404 (file not found) but the 404 handler doesn\'t exist',
                        ''
                    ),
                    array('The URL requested "<p class="tt">'
                        .$url.'</p>" would lead to a 403 (authorization needed) but the 403 handler is not accessible by guest',
                        'login as Admin and check that in the controller which ID is <a href="index.php?m='.$loginandout
                        .'">"<p class="tt">'.$loginandout.'</p>"</a> is accessible'
                    ),
                    array('The controller to be used after login is not accessible by the user (currently "<p class="tt">'
                        .$configuration['user_name'].'</p>" with ID '.$configuration['user_id'].')',
                        'login as Admin and <a href="edit-menu.html?em='.$redirect_login
                        .'">check</a> that in the controller which ID is <a href="index.php?m='.$redirect_login
                        .'">"<p class="tt">'.$redirect_login.'</p>"</a> is accessible'
                    )
                )
            );

            return $result;
        }
    }