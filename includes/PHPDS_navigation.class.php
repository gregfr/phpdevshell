<?php

class PHPDS_navigation extends PHPDS_dependant
{


    const node_standard = 1;
    const node_plain_link = 2;
    const node_jumpto_link = 3;
    const node_external_file = 4;
    const node_external_link = 5;
    const node_placeholder = 6;
    const node_iframe = 7;
    const node_cron = 8;
    const node_widget = 9;
    const node_styled_ajax = 10;
    const node_lightbox = 11;
    const node_ajax_raw = 12;


    /**
     * @var array
     */
    protected $breadcrumbArray = null;
    /**
     * @var array of arrays, for each menu which have children, an array of the children IDs
     */
    public $child = null;
    /**
     * Holds all menu item information.
     *
     * @var array
     */
    protected $navigation;
    /**
     * Holds all menu item information.
     *
     * @var array
     */
    public $navAlias;

    /**
     * @var string|iPHPDS_deferred the route found by the router (valid after the router has been called)
     * @since 3.5
     */
    public $currentRoute;

    /**
     * @var string the path used by the router (valid after the router has been called)
     * @since 3.5
     */
    public $currentPath;

    /**
     * This methods loads the menu structure, this according to permission and conditions.
     *
     * @return PHPDS_navigation itself, for fluent coding
     *
     * @author Jason Schoeman
     */
    public function extractMenu ()
    {
        $db = $this->db;
        $all_user_roles = $this->user->getRoles($this->configuration['user_id']);
        if (true || $db->cacheEmpty('navigation')) { // todo change THAT
            if (empty($this->navigation)) $this->navigation = array();
            if (empty($this->child)) $this->child = array();
            if (empty($this->navAlias)) $this->navAlias = array();
            $db->invokeQuery('NAVIGATION_extractMenuQuery', $all_user_roles);

            $db->cacheWrite('navigation', $this->navigation);
            $db->cacheWrite('child_navigation', $this->child);
            $db->cacheWrite('nav_alias', $this->navAlias);
        } else {
            $this->navigation = $db->cacheRead('navigation');
            $this->child = $db->cacheRead('child_navigation');
            $this->navAlias = $db->cacheRead('nav_alias');
        }

        if ($this->debugInstance()->isEnabled()) {
            $k = array_keys($this->navigation);
            sort($k);
            $this->log('Extracted navigation : '.implode(', ', $k));
            $k = array_keys($this->navAlias);
            sort($k);
            $this->log('Extracted navAlias : '.implode(', ', $k));
        }

        return $this;
    }

    /**
     * Determines what the menu item should be named.
     *
     * @param string $replacement_name
     * @param string $menu_link
     * @param int $menu_id
     * @return string
     */
    public function determineMenuName ($replacement_name = '', $menu_link = '', $menu_id = false, $plugin='')
    {
        if (! empty($replacement_name)) {
            return __("$replacement_name", "$plugin");
        } else {
            return $menu_link;
        }
    }

    /**
     * Returns true if menu should show.
     *
     * @param integer $hide_type
     * @param integer $menu_id
     * @param integer $active_id
     */
    public function showMenu ($hide_type, $menu_id = null, $active_id = null)
    {
        if (! empty($menu_id) && ($hide_type == 4) && $active_id == $menu_id) {
            return true;
        } else {
            if ($hide_type == 0 || $hide_type == 2) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Compiles menu items in order.
     *
     * @return string
     * @author Jason Schoeman
     */
    public function createMenuStructure ()
    {
        $menu = false;
        $configuration = $this->configuration;
        $nav = $this->navigation;
        $mod = $this->template->mod;

        $root_mode = ($configuration['menu_behaviour'] == 'static');
        $node = $configuration['m'];

        if (! empty($nav)) {
            if ($root_mode) {
                // Note it is written with quotes, it is like this for a reason to prevent an exising bug.
                $menu_group = '0';
            } else {
                if (! empty($nav[$node]['is_parent'])) {
                    $menu_group = $node;
                } else {
                    if (! empty($nav[$node]['parent_menu_id'])) {
                        $menu_group = $nav[$node]['parent_menu_id'];
                    } else {
                        // Note it is written with quotes, it is like this for a reason to prevent an exising bug.
                        $menu_group = '0';
                    }
                }
            }
            // Start the main loop, the main loop handles the top level menus.
            // When child menus are found the callFamily function is used to render those menus. The callFamily function may or may not go recursive at that point.
            foreach ($nav as $m) {
                if ($this->showMenu($m['hide'], $m['menu_id'], $node) && ((string) $nav[$m['menu_id']]['parent_menu_id'] == (string) $menu_group)) {
                    ($m['menu_id'] == $node) ? $url_active = 'current' : $url_active = 'inactive';
                    if ($m['is_parent'] == 1) {
                        $call_family = $this->callFamily($m['menu_id']);
                        if (! empty($call_family)) {
                            $call_family = $mod->menuUlParent($call_family, 'navparent');
                            $p_type = 'grandparent';
                        } else {
                            $p_type = $url_active;
                        }
                        $menu .= $mod->menuLiParent($call_family, $mod->menuA($m, 'nav-grand'), $p_type, $m);
                    } else {
                        $menu .= $mod->menuLiChild($mod->menuA($m, 'child'), $url_active, $m);
                    }
                }
            }
            if (empty($menu)) {
                if (!empty($nav[$node])) {
                    $menu = $mod->menuLiChild($mod->menuA($nav[$node]), 'current');
                }
            }
        }
        return $menu;

    }

    /**
     * Assists write_menu in calling menu children.
     *
     * @param int $menu_id
     */
    public function callFamily ($menu_id = false)
    {
        $menu = false;
        $configuration = $this->configuration;
        $nav = $this->navigation;
        $mod = $this->template->mod;
        if (! empty($this->child[$menu_id])) {
            $child = $this->child[$menu_id];
            foreach ($child as $m) {
                if ($this->showMenu($nav[$m]['hide'], $m, $configuration['m'])) {
                    ($m == $configuration['m']) ? $url_active = 'current' : $url_active = 'inactive';
                    if ($nav[$m]['is_parent'] == 1) {
                        $call_family = $this->callFamily($m);
                        if (! empty($call_family)) {
                            $call_family = $mod->menuUlChild($call_family, 'ulchild');
                            $p_type = 'parent';
                        } else {
                            $p_type = $url_active;
                        }
                        $menu .= $mod->menuLiParent($call_family, $mod->menuA($nav[$m], 'nav-parent'), $p_type, $nav[$m]);
                    } else {
                        $menu .= $mod->menuLiChild($mod->menuA($nav[$m], 'child'), $url_active, $nav[$m]);
                    }
                }
            }
        }
        return $menu;
    }

    /**
     * This method compiles the history tree seen, this is the tree that the user sees expand when going deeper into menu levels.
     * On the default template this is the navigation link string top left above the menus.
     *
     * @date 20150219 (1.0.1) (greg) fixed a typo in front_page_id
     *
     * @return string
     */
    public function createBreadcrumbs ()
    {
        $configuration = $this->configuration;
        $nav = $this->navigation;
        $mod = $this->template->mod;

        $root_mode = ($configuration['menu_behaviour'] == 'static');

        $dashboard = $configuration['dashboard'];
        $home = $configuration[$this->user->isLoggedIn() ? 'front_page_id_in' : 'front_page_id'];

        if (($dashboard != $home) && ($nav[$dashboard]['menu_id'] == $dashboard)) {
            $jump_menu = $mod->menuLiJump($this->buildURL($dashboard), __('Dashboard', 'PHPDevShell'));
        } else {
            $jump_menu = '';
        }
        $main_item = $mod->menuLiHome($configuration['absolute_url'] . '/', ___('Home'), $jump_menu);
        $this->callbackParentItem($configuration['m']);
        $history_url = '';

        if ($root_mode && ! empty($nav[$configuration['m']]['parent_menu_id'])) {
            $history_url .= $this->callFamily($nav[$configuration['m']]['parent_menu_id']);
        } else {
            foreach (array_reverse($this->breadcrumbArray, true) as $key => $breadcrumb_id) {
                if (! empty($nav[$breadcrumb_id]['menu_name']) && ($nav[$breadcrumb_id]['menu_id'] != $nav[$configuration['m']]['parent_menu_id']) && (($breadcrumb_id != $configuration['m']) || ($key != 0))) {
                    if (! empty($nav[$breadcrumb_id]['is_parent'])) {
                        $bread_parent = $this->callFamily($breadcrumb_id);
                        if (! empty($bread_parent))
                            $bread_parent = $mod->menuUlParent($bread_parent, 'breadparent');
                        $history_url .= $mod->menuLiParent($bread_parent, $mod->menuA($nav[$breadcrumb_id], 'nav-grand'), 'grandparent');
                    }
                }
            }
        }
        if (! empty($nav[$configuration['m']]['parent_menu_id'])) {
            $up_parent_id = $nav[$configuration['m']]['parent_menu_id'];
            if (! empty($nav[$up_parent_id]['type']) && $nav[$up_parent_id]['type'] > 3 ) $up_parent_id = '0';
        } else {
            $up_parent_id = '0';
        }
        if (! empty($up_parent_id)) {
            $up = $mod->menuLiUp($this->buildURL($up_parent_id), ___('Up'));
        } else {
            $up = '';
        }
        if (empty($history_url)) $history_url = false;
        return $main_item . $history_url . $up;
    }

    /**
     * Method assists method generate_history_tree in getting breadcrumb links.
     *
     * @param integer
     */
    private function callbackParentItem ($menu_id_)
    {
        $nav = $this->navigation;
        if (! empty($nav[$menu_id_]['parent_menu_id'])) {
            $recall_parent_menu_id = $nav[$menu_id_]['parent_menu_id'];
        } else {
            $recall_parent_menu_id = '0';
        }
        $this->breadcrumbArray[] = $menu_id_;
        if ($recall_parent_menu_id) {
            $this->callbackParentItem($recall_parent_menu_id);
        }
    }

    /**
     * Simply returns current menu id.
     *
     * @return int
     */
    public function currentMenuID()
    {
        return $this->configuration['m'];
    }

    /**
     * Returns the complete current menu structure
     *
     * @version 1.0
     * @author greg <greg@phpdevshell.org>
     * @date 20120608 (1.0) (greg) added
     *
     * @return array
     */
    public function currentMenu()
    {
        return $this->node($this->currentMenuID());
    }

    /**
     * Will try and locate the full path of a filename of a given menu id, if it is a link, the original filename will be returned.
     *
     * @param int $menu_id
     * @param string $plugin
     * @return string
     */
    public function menuFile ($menu_id=false, $plugin=false)
    {
        if (empty($menu_id)) $menu_id = $this->configuration['m'];
        $absolute_path = $this->configuration['absolute_path'];
        list($plugin, $menu_link) = $this->menuPath($menu_id, $plugin);
        if (file_exists($absolute_path . 'plugins/' . $plugin . '/controllers/' . $menu_link)) {
            return $absolute_path . 'plugins/' . $plugin . '/controllers/' . $menu_link;
        } else if (file_exists($absolute_path . 'plugins/' . $plugin . '/' . $menu_link)) {
            return $absolute_path . 'plugins/' . $plugin . '/' . $menu_link;
        } else {
            return false;
        }
    }

    /**
     * Will locate the menus item full path.
     *
     * @param int $menu_id
     * @param string $plugin
     * @return array
     */
    public function menuPath ($menu_id=false, $plugin=false)
    {
        $configuration = $this->configuration;
        $navigation = $this->navigation;
        if (empty($configuration['m']))
            $configuration['m'] = 0;
        if (empty($menu_id)) $menu_id =  $configuration['m'];
        if (empty($navigation[$menu_id]['extend'])) {
            if (!empty($navigation[$menu_id])) {
                $menu_link = $navigation[$menu_id]['menu_link'];
                if (empty($plugin))
                    $plugin = $navigation[$menu_id]['plugin'];
            }
        } else {
            $extend = $navigation[$menu_id]['extend'];
            $menu_link = $navigation[$extend]['menu_link'];
            if (empty($plugin))
                $plugin = $navigation[$extend]['plugin'];
        }
        if (empty($plugin))
            $plugin = 'PHPDevShell';
        if (empty($menu_link))
            $menu_link = '';
        return array($plugin, $menu_link);
    }

    /**
     * Will return the url for a certain menu item when path is provided.
     * @param string $item_path The string to the path of the menu item, 'user/control-panel.php'
     * @param string $plugin_name The plugin name to look for it under, if empty, active plugin will be used.
     * @param string $extend_url Will extend url with some get values.
     * @return string Will return complete and cleaned sef url if available else normal url will be returned.
     */
    public function buildURLFromPath ($item_path, $plugin_name = '', $extend_url = '')
    {
        if (empty($plugin_name))
            $plugin_name = $this->core->activePlugin();
        $lookup = array('plugin'=>$plugin_name, 'menu_link'=>$item_path);
        $menu_id = PU_ArraySearch($lookup, $this->navigation);
        if (! empty($menu_id)) {
            return $this->buildURL($menu_id, $extend_url);
        } else {
            return $this->pageNotFound();
        }
    }

    /**
     * Returns the correct string for use in href when creating a link for a menu id. Will return sef url if possible.
     * Will return self url when no menu id is given. No starting & or ? is needed, this gets auto determined!
     * If left empty it will return current active menu.
     *
     * @param mixed The menu id or menu file location to create a url from.
     * @param string extend_url
     * @param boolean strip_trail Will strip unwanted empty operators at the end.
     * @return string
     * @author Jason Schoeman
     */
    public function buildURL ($menu_id = null, $extend_url = '', $strip_trail = true)
    {
        if (empty($menu_id)) $menu_id = $this->configuration['m'];
        if (! empty($this->configuration['sef_url'])) {
            if (empty($this->navigation["$menu_id"]['alias'])) {
                $alias = $this->db->invokeQuery('NAVIGATION_findAliasQuery', $menu_id);
            } else {
                $alias = $this->navigation["$menu_id"]['alias'];
            }
            if (! empty($extend_url)) {
                $extend_url = "?$extend_url";
            } else if ($strip_trail) {
                $extend_url = '';
            } else {
                $extend_url = '?';
            }
            $url_append = empty($this->configuration['url_append']) ? '' : $this->configuration['url_append'];
            $url = $alias . $url_append . "$extend_url";
        } else {
            if (! empty($extend_url)) {
                $extend_url = "&$extend_url";
            } else {
                $extend_url = '';
            }
            $url = 'index.php?m=' . "$menu_id" . "$extend_url";
        }
        if (! empty($url)) {
            return $this->configuration['absolute_url'] . "/$url";
        } else {
            return false;
        }
    }

    /**
     * Get a node (all its fields or a given field) among the nodes available to the current user
     *
     * @version 1.0
     *
     * @date 20161107 (1.0) (greg) added
     *
     * @param string|int $noderef The reference of the node to look for
     * @param string $fieldref The field to return (optional)
     *
     * @return mixed
     */
    public function node($noderef, $fieldref = null)
    {
        $result = null;

        if (!empty($noderef)) {
            if (!empty($this->navigation[(string)$noderef])) {
                $result = $this->navigation[(string)$noderef];
                if (!empty($fieldref)) {
                    if (!empty($result[$fieldref])) {
                        $result = $result[$fieldref];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Check if the current user is allowed the given node
     *
     * If (s)he's not and $use_default is true, return the ID of the default node
     *
     * @version 1.1.1
     *
     * @since 3.5
     *
     * @date 20161107 (1.0.1) (greg) added debug log info, fix unreachable node on 32bits systems
     * @date 20130305 (1.0) (greg) added
     * @date 20140608 (1.1) (greg) checked the default is actually reachable
     *
     * @param string $nodeID
     * @param boolean $use_default
     * @return string|boolean
     */
    public function checkNode($nodeID = 0, $use_default = true)
    {
        $node = $this->node($nodeID);

        if ((!$node) && $use_default) {
            $nodeID = $this->nodeDefaultID();
            $this->log('checkNode defaults to '.$nodeID);
            $node = $this->node($nodeID);
        }

        $this->log("Node $nodeID ".($node ? "checks" : "doesn't check"));

        return $nodeID;
    }

    /**
     * Returns the given node ID or the defaults if it's 0 (doesn't check if it's accessible)
     *
     * note that for this purpose zero is different from null
     *
     * @date 20150208 (1.0.1) (greg) null values are now accepted for default (instread of strict zero)
     * @version 1.0.1
     *
     * @param int $nodeID
     * @return  integer $nodeID
     */
    public function nodeDefaultID($nodeID = null)
    {
        if (empty($nodeID)) {
            return $this->user->isLoggedIn() ? $this->configuration['front_page_id_in'] : $this->configuration['front_page_id'];
        } else {
            return $nodeID;
        }
    }

    /**
     * Parses the REQUEST_URI to get the node id
     *
     * // todo: restore url->get
     *
     * The result can either be an actual node ID (unchecked), the special value 0 (zero) meaning 'default page',
     * or the special value NULL meaning 'not found'
     *
     * @version 2.0.3
     *
     * @date 20161107 (2.0.3) (greg) added debug log message
     * @date 20140608 (2.0.2) (greg) the access error has been stripped for later checking
     * @date 20131028 (2.0.1) (greg) fix a type error for non-existing routes
     * @date 20130304 (2.0) (greg) complete rewrite using the router
     * @date 20120312 (v1.1) (greg) added support for given parameter
     * @date 20101007 (v1.0.2) (greg) moved from PHPDS to PHPDS_navigation ; little cleanup
     * @date 20100109
     *
     * @author Ross Kuyper
     * @author greg <greg@phpdevshell.org>
     */
    public function parseRequestString($uri = '')
    {
        if (empty($uri) && !empty($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $configuration = $this->configuration;
        $route = null;
        $m = 0;

        $basepath = parse_url($configuration['absolute_url'], PHP_URL_PATH);
        $absolute_path = parse_url($uri, PHP_URL_PATH);
        $path = trim(str_replace($basepath, '', $absolute_path), '/');

        if (empty($path)) {
          // no path given, fall back to the what default page has been configured
//          $route = 0; // default will be picked up later
            $route = $this->nodeDefaultID();
        } else {
            // first case, old-style "index.php?m=menuid"
            if ('index.php' == $path) {
                $m = $_GET['m'];
                if (!empty($this->navigation[$m])) {
                    $route = $m;
                }
            } else { // second case, use the router
                $route = $this->router->matchRoute($path);
                if (empty($route)) { // strip off the extension if necessary
                    $path = str_replace($configuration['url_append'], '', $path);
                    $route = $this->router->matchRoute($path);
                }
            }
        }

        $configuration['m'] = $route;
        $this->currentPath = $path;
        $this->currentRoute = $route;

        $this->log("Request '$uri' parsed : route is '".(is_object($route) ? get_class($route) : $route)."', path is '$path'");

        return $route;
    }

    /**
     * Checks url access error type and sets it.
     *
     * @param string
     * @param string
     * @author Jason Schoeman
     */
    public function urlAccessError ($alias = null, $get_menu_id = null)
    {
        $required_menu_id = $this->db->invokeQuery('NAVIGATION_findMenuQuery', $alias, $get_menu_id);

        if (empty($required_menu_id)) {
            $this->core->haltController = array('type'=>'404','message'=>___('Page not found'));
            return false;
        } else {
            if ($this->user->isLoggedIn()) {
                $this->core->haltController = array('type'=>'403','message'=>___('Page found, but you don\'t have the required permission to access this page.'));
                return false;
            } else {
                $this->core->haltController = array('type'=>'auth','message'=>___('Authentication Required'));
                $this->configuration['m'] = $required_menu_id;
                return false;
            }
        }
    }

    /**
     * This function support output_script by looking deeper into menu structure to find last linked menu item that is not linked to another.
     *
     * @param integer $extendedMenuId
     * @return integer
     */
    public function extendMenuLoop ($extended_menu_id)
    {
        $navigation = $this->navigation;

        // Assign extention value.
        $extend_more = $navigation[$extended_menu_id]['extend'];
        // Check if we should look higher up for a working menu id and prevent endless looping.
        if (! empty($extend_more) && ($extended_menu_id != $navigation[$extend_more]['extend'])) {
            // recursive call, it's an "extend"
            return $this->extendMenuLoop($extend_more);
        } else {
            // Final check, to see if we had an endless loop that still has an extention.
            if (! empty($navigation[$extended_menu_id]['extend'])) {
                if (! empty($navigation[$extended_menu_id]['parent_menu_id'])) {
                    // Lets look even higher up now that we jumped the endless loop.
                    $this->extendMenuLoop($navigation[$extended_menu_id]['parent_menu_id']);
                } else {
                    // We now have no other choice but to show default home page.
                    return '0';
                }
            } else {
                return $extended_menu_id;
            }
        }
    }

    /**
     * This method saves the current URL with the option to add more $this->security->get variables like ("&variable1=1&variable2=2")
     * This is mostly used for when additional $this->security->get variables are required! Usefull when using forms.
     *
     * @param string Add more $this->security->get variables like ("&variable1=1&variable2=2")
     * @return string
     * @author Jason Schoeman
     */
    public function selfUrl ($extra_get_variables = '')
    {
        return $this->buildURL(false, $extra_get_variables, true);
    }

    /**
     * Will convert any given plugin script location to its correct url.
     *
     * @param string $file_path The full file path, "DummyPlugin/sample/sample1.php"
     * @param string $extend_url Should the url be extended with $_GET vars, 'e=12'
     * @param boolean $strip_trail Will strip unwanted empty operators at the end.
     * @return string
     */
    public function purl ($file_path, $extend_url = '', $strip_trail = true)
    {
        $menu_id = $this->createMenuId($file_path);
        return $this->buildURL($menu_id, $extend_url, $strip_trail);
    }

    /**
     * Simply converts a url to a clean SEF url if SEF is enabled.
     *
     * @param int $menu_id
     * @param string $extend_url 'test1=foo1&test2=foo2&test3=foobar'
     * @param boolean $strip_trail should extending ? be removed.
     *
     * @return string
     */
    public function sefURL ($menu_id = null, $extend_url = '', $strip_trail = true)
    {
        $url = $this->buildURL($menu_id, $extend_url, $strip_trail);

        if (! empty($this->configuration['sef_url'])) {
            return preg_replace(array('/\?/', '/\&/', '/\=/'), '/', $url);
        } else {
            return $url;
        }
    }

    /**
     * Convert plugin file location to unsigned CRC32 value. This is unique and allows one to locate a menu item from location as well.
     *
     * @param string $path The plugin folder the file is in.
     * @return integer
     *
     * @author Jason Schoeman
     */
    public function createMenuId ($path)
    {
        return sprintf('%u', crc32(str_ireplace('/', '', $path)));
    }

    /**
     * Redirects to new url
     *
     * Uses htlm META refresh, so it can be used to redirect after a the page had been displayed for a few seconds.
     * It's different from PU_relocate($url) which uses http headers and therefore doesn't display the page.
     *
     * @date 20160205 (1.0.1) (greg) fixed httpmenuRedirect method name
     *
     * @param string|boolean $url URL to redirect to.
     * @param integer $time Time in seconds before redirecting.
     * @author Jason Schoeman
     */
    public function redirect ($url = false, $time = 0)
    {
        if ($url == false) {
            $redirect_url = $this->template->mod->httpmenuRedirect($this->buildURL($this->configuration['m']), $time);
        } else {
            $redirect_url = $this->template->mod->httpmenuRedirect($url, $time);
        }
        print $redirect_url;
    }

    /**
     * Returns the url of the 404 page selected by the admin.
     *
     * @return string
     */
    public function pageNotFound ()
    {
        $menu_id = $this->db->getSettings(array('404_error_page'), 'PHPDevShell');
        return $this->buildURL($menu_id['404_error_page']);
    }

}