<?php

/**
 * Readme - Read all skins.
 * @author Jason Schoeman, Contact: titan [at] phpdevshell [dot] org.
 *
 */
class PHPDS_readSkinOptions extends PHPDS_query
{

	/**
	 * Initiate query invoke command.
     *
     * @version 1.1
     *
     * @date 20173101 (1.1) (greg) Using $core->themePath()
     *
	 * @param int
	 * @return string
	 */
	public function invoke($parameters = null)
	{
		$skin_selected = $parameters[0];
		$file = $this->factory('fileManager');
        $path = $this->template->mods->jqueryUIpath();
        $dir = $file->getDirListing($path);
		$skin_ = '';
		if (empty($dir)) $dir = array();
		foreach ($dir as $skin) {
			($skin_selected == $skin['folder']) ? $selected = 'selected' : $selected = '';
			$skin_ .= '<option value="' . $skin['folder'] . "\" $selected>" . $skin['folder'] . '</option>';
		}
		return $skin_;
	}
}

/**
 * Readme - Set Skin.
 * @author Jason Schoeman, Contact: titan [at] phpdevshell [dot] org.
 *
 */
class PHPDS_setSkin extends PHPDS_query
{

	/**
	 * Initiate query invoke command.
	 * @param int
	 * @return string
	 */
	public function invoke($parameters = null)
	{
		$this->db->writeSettings(array('skin' => $parameters[0]), 'PHPDevShell');
		$this->db->cacheClear();
	}
}