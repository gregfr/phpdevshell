<?php

$HTMl = '';
$type = 'service';

require ('service.inc.php');
$TITLE = "$package Installer/Upgrader";

// Store everything in cache...
preparations();

/// main switch
headHTML();

headingPrint($package . " Installer/Upgrader Service");
displayIntro();

// end html
footHTML();

function displayIntro()
{
	global $package, $version;

	?>
		<h1>Welcome to the <?php echo $package ?> Installer/Upgrader!</h1>
		<div class="row">
			<div class="column grid_6">
				<h2>Fresh PHPDevShell install.</h2>
				<p><strong>If you are new to PHPDevShell</strong> and want a clean installation, click "Install New Copy".
				The installer will check your system for requirements and configure the database.
				To ensure only the owner of the site can actually do the installation, you will be asked several
                    parameters found in the configuration file.
				</p>
				<p>
                    <button onClick="parent.location='install.php'" value="install">
                        <span class="ui-icon ui-icon-check left"></span>Install New Copy</button>
                </p>
			</div>
			<div class="column grid_6 last">
				<h2>Upgrade existing PHPDevShell install.</h2>
				<p><strong>If you already have a previous version of PHPDevShell</strong> and want to move to the
                    new version <?php echo $version ?>, click "Upgrade Existing Installation".
				The upgrade script will run a series of upgrade SQL and do changes to multiple tables
                    to make it compatible with latest version of PHPDevShell.
				</p>
				<p>
                    <button onClick="parent.location='upgrade.php'" value="upgrade">
                        <span class="ui-icon ui-icon-transferthick-e-w left"></span>
                        Upgrade Existing Installation</button
                    ></p>
			</div>
		</div>
	<?php
}