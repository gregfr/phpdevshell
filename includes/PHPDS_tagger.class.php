<?php

class PHPDS_tagger extends PHPDS_dependant
{
	const tag_user = 'user';
	const tag_menu = 'menu';
	const tag_role = 'role';
	const tag_group = 'group';

	/**
	 * Generic setter/getter for the tags.
	 * All parameters must be given explicitly
	 *
	 * As a setter, all 4 params must be given (for the [$object;$target] set the tag name $name to value $value)
	 * As a getter, don't provide the value ; a single value will be returned (or nothing if no such tag is found)
     *
     * @author greg <greg@phpdevshell.org>
	 *
	 * @param string $object
	 * @param string $name
	 * @param string $target
	 * @param string $value (optional)
	 * @return string|array|null
	 */
	public function tag($object, $name, $target, $value = null)
	{
		$parameters = array('object' => $object, 'name' => $name, 'target' => $target);
		if (!is_null($value)) {
			$parameters['value'] = $value;
			return $this->db->invokeQueryWith('PHPDS_taggerMarkQuery', $parameters);
		} else {
			return $this->db->invokeQueryWith('PHPDS_taggerLookupQuery', $parameters);
		}
	}
	
	/**
	 * Lookup a tag based on criteria
     *  - if several tags match the criteria, only the first one is returned
     *  - if no tag match the criteria, nothing is returned
	 *
     * @date 20120105 (1.0) (greg) added
     * @version 1.0
     * @author greg <greg@phpdevshell.org>
     *
	 * @param string $object (optional)
	 * @param string $name (optional)
	 * @param string $target (optional)
	 * @param string $value (optional)
	 * @return string|null
	 *
	 */
	public function tagLookup($object = null, $name = null, $target = null, $value = null)
	{
		$parameters = array('object' => $object, 'name' => $name, 'target' => $target, 'value' => $value);
		
		return $this->db->invokeQueryWith('PHPDS_taggerLookupQuery', $parameters);
	}

	/**
	 * List of [object;target] for the given tag (optionaly restricted to the given $object/$target)
	 *
	 * @param $object
	 * @param $target
	 */
	public function tagList($name, $object, $target = null)
	{
		$parameters = array('object' => $object, 'name' => $name, 'target' => $target);
		$result = $this->db->invokeQueryWith('PHPDS_taggerListQuery', $parameters);
		if (!is_array($result)) $result = array($result);
		return $result;
	}

	/*
	* List of [name;value] for the given [object;target]
	* 
	* @version 1.0
	* @since 3.2.1
	* @date 20121120 (v1.0) added
	* @author greg <greg@phpdevshell.org>
	* 
	* @param string  the object (i.e. the category, such as PHPDS_tagger::user)
	* @param string  the target (i.e. the ID of tags' owner, for example 1234)
	* 
	* @return array flat array (i.e. indexes are numeric) of two-values arrays [name,value]
	*/
	public function tagsOf($object, $target)
	{
		return $this->db->invokeQueryWith('PHPDS_taggerListTargetQuery', array($target, $object));
	}

	/**
	 * Tag (set/get) the user specified in $target
	 *
	 * @param $name
	 * @param $target
	 * @param $value
	 */
	public function tagUser($name, $target, $value = null)
	{
		return $this->tag(PHPDS_tagger::tag_user, $name, $target, $value);
	}

	/**
	 * Tag (set/get) the current user
	 *
	 * @param $name
	 * @param $value
	 */
	public function tagMe($name, $value = null)
	{
		$me = $this->user->currentUserID();
		if (empty($me)) return false;
		return $this->tag(PHPDS_tagger::tag_user, $name, $me, $value);
	}

	/**
	 * Tag (set/get) the menu specified in $target
	 *
	 * @param $name
	 * @param $target
	 * @param $value
	 */
	public function tagMenu($name, $target, $value = null)
	{
		return $this->tag(PHPDS_tagger::tag_menu, $name, $target, $value);
	}

	/**
	 * Tag (set/get) the current menu
	 *
	 * @param $name
	 * @param $value
	 */
	public function tagHere($name, $value = null)
	{
		$here = $this->navigation->currentMenuID();
		if (empty($here)) return false;
		return $this->tag(PHPDS_tagger::tag_menu, $name, $here, $value);
	}

	/**
	 * Tag (set/get) the role specified in $target
	 *
	 * @param $name
	 * @param $target
	 * @param $value
	 */
	public function tagRole($name, $target, $value = null)
	{
		return $this->tag(PHPDS_tagger::tag_role, $name, $target, $value);
	}

	/**
	 * Tag (set/get) the group specified in $target
	 *
	 * @param $name
	 * @param $target
	 * @param $value
	 */
	public function tagGroup($name, $target, $value = null)
	{
		return $this->tag(PHPDS_tagger::tag_group, $name, $target, $value);
	}

	/**
	 * This function creates tag list which allows a comma separated list of tags.
     *
     * If you provide a similar list in $tagArea, it will be pushed to the database
	 *
	 * @param string $object
	 * @param string $target
	 * @param string $tagArea (optional)
     * @param string $defaultValue (optional)
	 * @return string (maybe an empty string)
	 */
	public function tagArea($object, $target, $tagArea = null, $defaultValue = null)
	{
		if (!empty($tagArea)) {
			$this->db->invokeQuery('PHPDS_updateTagsQuery', $object, $target, $defaultValue, $tagArea);
		}

		$taglist = $this->db->invokeQuery('PHPDS_taggerListTargetQuery', $target, $object);

		$tagnames = '';
		if (! empty($taglist)) {
			asort($taglist);
			foreach ($taglist as $tag) {
				$tagname = trim($tag['tagName']);
				$tagvalue = trim($tag['tagValue']);
					if (! empty($tagvalue)) $tagvalue = ':' . $tagvalue; else $tagvalue = '';
				$tagnames .= "$tagname" . $tagvalue . "\r\n";
			}
			$tagnames = rtrim($tagnames, ",");
		}
		return $tagnames;
	}

    /**
     * Untag, ie remove a tag corresponding to the given parameters
     *
     * @version 1.0
     * @since 3.5
     * @date 20131007 (1.0) (greg) added
     *
     * @param string $object the object (i.e. the category, such as PHPDS_tagger::user)
     * @param string $name the name of the tag (i.e. the label, such as "subscribed_to")
     * @param string|null $target  the target (i.e. the ID of tags' owner, for example 1234)
     * @param string|null $value the tag will be removed only if it matches the value
     */
    public function untag($object, $name, $target = null, $value = null)
    {
        $this->db->invokeQueryWith('PHPDS_taggerUntagQuery', array('object' => $object, 'name' => $name, 'target' => $target, 'value' => $value));
    }

    public function remove($id, $object = null, $name = null, $target = null, $value = null)
    {
        $this->db->invokeQueryWith('PHPDS_taggerUntagQuery', array('id' => $id, 'object' => $object, 'name' => $name, 'target' => $target, 'value' => $value));
    }
}