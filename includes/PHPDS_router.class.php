<?php


/**
 * A class to deal with route (ie mapping URL to a node)
 *
 * The purpose of this class is to find what previously registered "catcher" should be
 * selected when a given URL is requested.
 *
 * Note it doesn't do anything with the catcher, just return it for the caller to do
 * whatever is necessary.
 *
 * Note: to comply with usual terminology, we use "module" here in the meaning of what we
 * call "plugin" in general in PHPDevShell
 *
 * The list is browsed backwards, so the last matching route is selected.
 *
 * By default, the two patterns are searched for variables:
 *     - (#numvar) : the piece is completely numerical (like /58/) and is present (not empty string)
 *     - (:var) : anything, including an empty search
 */


/** @noinspection PhpUndefinedClassInspection */
class PHPDS_router extends PHPDS_dependant
{

    /**
     * The list of route descriptors which are not connected to a module (i.e. prioritary)
     * This is a plain array so we can handle identical patterns - route are matched in reverse order
     * @var array
     */
    protected $orphans = array();

    /**
     * List of route descriptors, gathered by modules
     * This is an associative array, they is the module name
     * The routes with no modules are not here
     * @var array
     */

    protected $modules = array();

    /**
     * A simple associative array with the parameters (ie URL variables) found in the URL
     * @var array
     */
    protected $parameters = array();

    /**
     * Defaults values
     * @var array
     */
    protected $defaults = array(
      'module' => ''
    );

    protected $regs = array(
        '/\(#(?<varname>[[:alnum:]]+)\)/' => '/^[[:digit:]]+$/',
        '/\(:(?<varname>[[:alnum:]]+)\)/' => '/.*/'
    );

    /**
     * Build the router
     *
     * @return bool
     */
    public function construct() // variable argument list
    {
        $configuration = $this->configuration;
        if (!empty($configuration['default_routes']) && is_array($configuration['default_routes'])) {
            foreach($configuration['default_routes'] as $route) {
                $this->addRoute($route['catcher'], $route['pattern'], $route['module'], $route['defaults']);
            }
        }
        return parent::construct();
    }

    /**
     * Add a route to the list
     *
     * @param mixed $catcher whatever should be run when the route is matched (usually the ID of the node)
     * @param string $pattern the pattern which, if matched, will trigger the node
     * @param null|string $module an optional module (i.e. plugin)
     * @param null|array $defaults default parameters for this route (default values for the variables)
     *
     * @return bool, true if the route has been added
     */
    public function addRoute($catcher, $pattern, $module = null, $defaults = null)
    {
        if (empty($catcher) || empty($pattern)) {
            return false;
        }

        $route = array(
            'catcher' => $catcher,
            'pattern' => $pattern,
            'module' => $module,
            'defaults' => $defaults
        );

        if (empty($module)) {
            $this->orphans[] = $route;
        } else {
            if (empty($this->modules[$module])) {
                $this->modules[$module] = array();
            }
            $this->modules[$module][] = $route;
        }
        return true;
    }

    /**
     * Look for a route inside the given route list
     *
     * @version 1.0
     *
     * @author greg <greg@phpdevshell.org>
     *
     * @date 20161108 (1.0) (greg) added
     *
     * @param array $parts The parts of the URI
     * @param array $list The list of routes to look inside
     *
     * @return bool|array The route data or false if not found
     */
    public function matchRouteList($parts, $list)
    {
        $result = false;
        foreach (array_reverse($list) as $route) {
            if ($this->match1Route($route['pattern'], $parts)) {
                $result = $route;
                break;
            }
        }
        return $result;
    }

    /**
     * Test a url path to find a matching node
     *
     * @version 2.0
     *
     * @date 20161108 (2.0) (greg) rewritten to make orphans (routes with no module) prioritary if no module is given in the url
     * @date 20131215 (1.0.1) (greg) should return null instead of false (or 404 will not be caught)
     *
     * @param $path string the path part of the URL
     *
     * @return string|null, the ID of the matching node, or null if none have been found
     */
    public function matchRoute($path)
    {
        $parts = $this->splitURL($path);
        $module = !empty($this->modules[$parts[0]]) ? array_shift($parts) : $this->defaults['module'];

        $this->debugInstance()->debug("Matching route '$path' and module '$module' (parts are ".print_r($parts, true));

        $this->parameters = array();
        $result = false;
        if ($module) {
            $result = $this->matchRouteList($parts, $this->modules[$module]);
        }
        if (!$result) {
            $result = $this->matchRouteList($parts, $this->orphans);
            if (!$result) {
                foreach ($this->modules as $route_list) {
                    $result =$this->matchRouteList($parts, $route_list);
                    if ($result) {
                        break;
                    }
                }
            }
        }

        $this->log("Route '$path' ".($result ? 'gives '.print_r($result['catcher'], true) : 'failed'));
        return $result ?  $result['catcher'] : null;
    }

    /**
     * Try to match a given route pattern to the url path
     *
     * @version 1.0.1
     *
     * @date 20161107 (1.0.1) (greg) added debug code
     *
     * @param $route string, the route pattern
     * @param $parts array, the parts of the url path (split on slash)
     *
     * @return bool, true if they match
     */
    public function match1Route($route, array $parts)
    {
        if (($route == $parts[0]) || ($route == '/'.$parts[0])) {
            return true;
        }
        $mismatch = false;
        $pieces = $this->splitURL($route);
        foreach($pieces as $piece) {
            $matches = array();
            $found = false;
            $part = array_shift($parts);

            foreach($this->regs as $piece_reg => $part_reg) {
                if (preg_match($piece_reg, $piece, $matches)) {
                    if (!$part_reg || preg_match($part_reg, $part)) {
                        $found = true;
                        if ($part) {
                            $this->parameters[$matches['varname']] = $part;
                        }
                        break;
                    }
                }
            }

            if (!$found) {
                if ($part != $piece) {
                    $mismatch = true;
                    break;
                }
            }
        }
        $this->log("Matching 1 route '$route' ".($mismatch ? "doesn't" : 'does')." match");
        return !$mismatch;
    }

    /**
     * @param $url
     * @return array
     */
    public function splitURL($url)
    {
        $parts = explode('/', $url);
        if ((count($parts) > 1) && empty($this->modules[$parts[0]])) {
            array_shift($parts);
        }
        return $parts;
    }

    /**
     * Read only accessor to the parameters found in the URL
     *
     * @return array, associative array of (name=>value)
     */
    public function parameters()
    {
        return $this->parameters;
    }

    /**
     * Just return the recorded routes
     *
     * @return array
     */
    /*public function routes()
    {
        return $this->routes;
    }*/
}