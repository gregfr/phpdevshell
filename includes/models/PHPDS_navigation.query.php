<?php

/**
 * Navigation - Check if we can find a menu id by using the alias.
 * @author Jason Schoeman, Contact: titan [at] phpdevshell [dot] org.
 *
 */
class NAVIGATION_findMenuQuery extends PHPDS_query
{
    protected $sql = "
        SELECT
            t1.menu_id
        FROM
            _db_core_menu_items t1
        WHERE
            t1.alias = '%s'
        OR
            t1.menu_id = '%s'
    ";

    protected $singleValue = true;
}

/**
 * Navigation - Check if we can find an alias by menu id.
 * @author Jason Schoeman, Contact: titan [at] phpdevshell [dot] org.
 *
 */
class NAVIGATION_findAliasQuery extends PHPDS_query
{
    protected $sql = "
        SELECT
            t1.alias
        FROM
            _db_core_menu_items t1
        WHERE
            t1.menu_id = '%s'
    ";

    protected $singleValue = true;
}

/**
 * Navigation - Extract all available menus user belongs too.
 *
 * @author Jason Schoeman, Contact: titan [at] phpdevshell [dot] org.
 * @author greg <greg@phpdevshell.org>
 *
 * @parameter string a comma-separated list of roles as text: the node list
 *      will be the nodes available to these users
 * @return void
 *
 * @version 2.0
 *
 * @date 20130701 (2.0) (greg) complete rewrite for right inheritance
 *
 */
class NAVIGATION_extractMenuQuery extends PHPDS_query
{
    protected $sql = "
		SELECT DISTINCT SQL_CACHE
			t1.menu_id, t1.parent_menu_id, t1.menu_name, t1.menu_link, t1.plugin, t1.menu_type, t1.extend, t1.new_window, t1.rank, t1.hide, t1.template_id, t1.alias, t1.layout, t1.params,
			t3.is_parent, t3.type,
			t6.template_folder
		FROM
			_db_core_menu_items t1
		LEFT JOIN
			_db_core_user_role_permissions t2
		USING
			(menu_id)
		LEFT JOIN
			_db_core_menu_structure t3
		USING
			(menu_id)
		LEFT JOIN
			_db_core_templates t6
		ON
			t1.template_id = t6.template_id";
            /*
            WHERE
                (t2.user_role_id IN (%s))
            ORDER BY
                t3.id
            ASC
            ";*/

    protected $keyField = 'menu_id';
    protected $autoProtect = true;

    /**
     * Entry point of the query: gather all the nodes available to the role list given as parameter
     *
     */
    public function invoke($parameters = null)
    {
        // first we determine the nodes which are explicitly available to the roles

        $this->where = '(t2.user_role_id IN (%s))';
        $this->order = 't3.id';

        //error_log('invoking!'.$parameters);

        $direct_hits = parent::invoke($parameters);

        //error_log('COUNT1:'.count($direct_hits));

        // then we determine the nodes available via inheritance

        $this->sql .= ' LEFT JOIN (SELECT *  FROM `pds_core_user_role_permissions`
            WHERE user_role_id = -1) as t4 USING (menu_id)';
        $this->where = 'NOT isnull(t4.user_role_id)';

        $herited_hits = parent::invoke($parameters);

        //error_log('COUNT2:'.count($herited_hits));

        // finally we merge both lists so a node doesn't appear twice

        $all_hits = $this->filterAndMerge($direct_hits, $herited_hits);

        //error_log('COUNT3:'.count($all_hits));

        $this->navigation->navigation = $all_hits;
    }

    /**
     * Check that the parameter is a comma-separated list given as a string
     *
     * @param mixed $parameters
     *
     * @return bool
     * @throws PHPDS_Exception
     */
    public function checkParameters(&$parameters = null)
    {
        $all_user_roles = $parameters[0];
        if (empty($all_user_roles) || !is_string($all_user_roles)) {
            throw new PHPDS_Exception('Cannot extract menus when no roles are given.');
        }
        $parameters = $all_user_roles;
        return true;
    }

    /**
     * Returns the list of nodes available
     *
     * @return array
     */
    public function getResults()
    {
        $select_menus = $this->asWhole();

        //error_log('RESULT: '.count($select_menus));

        $navigation = $this->navigation;
        $configuration = $this->configuration;

        $aburl = $configuration['absolute_url'];
        $sef = !empty($this->configuration['sef_url']);
        $append = $configuration['url_append'];
        $charset = $this->core->mangleCharset($this->charset());
        $father = $this->PHPDS_dependance();

        $hits = array();

        foreach ($select_menus as $mr) {
            $hits[$mr['menu_id']] = $this->makeNode($mr, $charset, $sef, $aburl, $append, $father, $navigation);
        }

        // error_log('HITS: '.count($hits));
        return $hits;
    }

    /**
     * Makes a usable node data record based on specific data from the DB and other generic data
     *
     * @version 1.0
     * @author  greg <greg@phpdevshell.org>
     * @date    20120920 (v1.0) (greg) added, based on the code previously found in invoke()
     *
     * @param  array   $mr      the DB record
     * @param  string  $charset the charset
     * @param  boolean $sef     are we using pretty urls?
     * @param  string  $aburl   absolute url
     * @param  string  $append  something to append to the url
     * @param  PHPDS   $father  root of dependance
     *
     * @return array    the usable array
     *
     */
    public function makeNode($mr, $charset, $sef, $aburl, $append, $father, $navigation)
    {
        $id = $mr['menu_id'];
        $new_menu = array();
        $father->copyArray($mr, $new_menu,
            array('menu_id', 'parent_menu_id', 'alias', 'menu_link', 'rank', 'hide', 'new_window',
                'is_parent', 'type', 'template_folder', 'layout', 'plugin', 'menu_type', 'extend'
            )
        );
        /* @var PHPDS_navigation $navigation */
        $new_menu['menu_name'] = $navigation->determineMenuName(
            $mr['menu_name'], $mr['menu_link'], $id, $mr['plugin']
        );

        // TODO: we have to use the silent operator @ to work around a php bug :(
        $new_menu['params'] = !empty($mr['params'])
            ? @html_entity_decode($mr['params'], ENT_COMPAT, $charset)
            : '';

        $new_menu['plugin_folder'] = 'plugins/' . $mr['plugin'] . '/';
        if ($sef && !empty($mr['alias'])) {
            $navigation->navAlias[$mr['alias']] =
                ($mr['menu_type'] != PHPDS_navigation::node_jumpto_link) ? $id : $mr['extend'];
            $new_menu['href'] = $aburl . '/' . $mr['alias'] . $append;
        } else {
            $new_menu['href'] = $aburl . '/index.php?m=' .
                ($mr['menu_type'] != PHPDS_navigation::node_jumpto_link ? $id : $mr['extend']);
        }

        // Writing children for single level dropdown.
        if (!empty($mr['parent_menu_id'])) {
            if (isset($navigation->child[$mr['parent_menu_id']])) {
                $navigation->child[$mr['parent_menu_id']][$id] = $id;
            } else {
                $navigation->child[$mr['parent_menu_id']] = array($id => $id);
            }
        }

        if (!empty($mr['alias'])) {
            $this->router->addRoute($id, $mr['alias'], $mr['plugin']);
        }

        return $new_menu;
    }

    /**
     * Merge two lists of nodes (explicitly available and implicitly available)
     *
     * @param $direct_hits array, a list of nodes
     * @param $herited_hits array, a list of nodes
     *
     * @return mixed
     */
    public function filterAndMerge($direct_hits, $herited_hits)
    {
        $watchdog = 100;

        while (!empty($herited_hits) && $watchdog--) {
            $keys = array_keys($herited_hits);
            $ID = array_shift($keys);

            $item = $herited_hits[$ID];
            unset($herited_hits[$ID]);

            // first a few sanity checks to avoid infinite loops
            if (!isset($item['menu_id']) || ($item['menu_id'] != $ID)) {
                continue;
            }
            if (!isset($item['parent_menu_id']) || ($item['parent_menu_id'] == $ID)) {
                continue;
            }

            $fatherID = $item['parent_menu_id'];

            if (!empty($direct_hits[$fatherID])) {
                // first case: direct inheritance of allowed item
                $direct_hits[$ID] = $item;
                continue;
            }

            if (!empty($herited_hits[$fatherID])) {
                // second case: inheritance of inheritant item, we must climb up
                $stack = array($item);
                $item = $herited_hits[$fatherID];
                unset($herited_hits[$fatherID]);
                do {
                    if (isset($direct_hits[$item['parent_menu_id']])) {
                        // first case, an allowed item is found up the chain: all the chain is allowed
                        $direct_hits[$item['menu_id']] = $item;
                        foreach ($stack as $item) {
                            $direct_hits[$item['menu_id']] = $item;
                        }
                        $item = null;
                    } elseif (isset($herited_hits[$item['parent_menu_id']])) {
                        // second case, another inheritance, we must continue climbing
                        array_push($stack, $item);
                        unset($herited_hits[$item['menu_id']]);
                        $item = $herited_hits[$item['parent_menu_id']];
                    } else {
                        // last case: dead end, give up on this one (the chain has already been removed)
                        $item = null;
                    }
                } while (!empty($item) && $watchdog--);
                continue;
            }
        };

        return $direct_hits;
    }


} // end of ExtNAV_extractMenuQuery

