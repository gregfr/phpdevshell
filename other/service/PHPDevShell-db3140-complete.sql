# Creating table for cron management and automated jobs.;
CREATE TABLE `pds_core_cron` (
	`menu_id` varchar(64) NOT NULL,
	`cron_desc` varchar(255) DEFAULT NULL,
	`cron_type` int(1) DEFAULT NULL,
	`log_cron` int(1) DEFAULT NULL,
	`last_execution` int(50) DEFAULT NULL,
	`year` int(4) DEFAULT NULL,
	`month` int(2) DEFAULT NULL,
	`day` int(2) DEFAULT NULL,
	`hour` int(2) DEFAULT NULL,
	`minute` int(2) DEFAULT NULL,
	PRIMARY KEY (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Insert default data in cron management.;
INSERT INTO `pds_core_cron` VALUES ('0', '', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `pds_core_cron` VALUES ('971937357', '', '2', '1', '1284101654', '1', '0', '0', '0', '0');
INSERT INTO `pds_core_cron` VALUES ('2749758364', '', '0', '1', '1284101669', '0', '0', '0', '0', '0');
INSERT INTO `pds_core_cron` VALUES ('2953441878', '', '2', '1', '1284101680', '0', '0', '0', '1', '0');

# Create filters for search.;
CREATE TABLE `pds_core_filter` (
	`search_id` int(255) unsigned NOT NULL AUTO_INCREMENT,
	`user_id` int(20) DEFAULT NULL,
	`menu_id` varchar(64) NOT NULL,
	`filter_search` varchar(255) DEFAULT NULL,
	`filter_order` varchar(5) DEFAULT NULL,
	`filter_by` varchar(255) DEFAULT NULL,
	`exact_match` varchar(2) DEFAULT NULL,
	PRIMARY KEY (`search_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Create logs table for watchdog.;
CREATE TABLE `pds_core_logs` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`log_type` int(2) DEFAULT NULL,
	`log_description` text,
	`log_time` int(10) DEFAULT NULL,
	`user_id` int(30) DEFAULT NULL,
	`user_display_name` varchar(255) DEFAULT NULL,
	`menu_id` varchar(64) NOT NULL,
	`file_name` varchar(255) DEFAULT NULL,
	`menu_name` varchar(255) DEFAULT NULL,
	`user_ip` varchar(30) DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

# Create menu access logs table.;
CREATE TABLE `pds_core_menu_access_logs` (
	`log_id` int(20) NOT NULL AUTO_INCREMENT,
	`menu_id` varchar(64) NOT NULL,
	`user_id` int(10) DEFAULT NULL,
	`timestamp` int(10) DEFAULT NULL,
	PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Create table for default menu items.;
CREATE TABLE `pds_core_menu_items` (
	`menu_id` varchar(64) NOT NULL,
	`parent_menu_id` varchar(64) DEFAULT NULL,
	`menu_name` varchar(255) DEFAULT NULL,
	`menu_link` varchar(255) DEFAULT NULL,
	`plugin` varchar(255) DEFAULT NULL,
	`menu_type` int(1) DEFAULT NULL,
	`extend` varchar(255) DEFAULT NULL,
	`new_window` int(1) DEFAULT NULL,
	`rank` int(100) DEFAULT NULL,
	`hide` int(1) DEFAULT NULL,
	`template_id` int(32) unsigned DEFAULT NULL,
	`alias` varchar(255) DEFAULT NULL,
	`layout` varchar(255) DEFAULT NULL,
	`params` varchar(1024) DEFAULT NULL,
	PRIMARY KEY (`menu_id`),
	KEY `index` (`parent_menu_id`,`menu_link`,`plugin`,`alias`),
	KEY `params` (`params`(255)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Insert default menu items.;
INSERT INTO `pds_core_menu_items` VALUES ('1016054546', '930839394', 'Edit Cronjob', 'cron-admin/cronjob-admin.php', 'PHPDevShell', '1', '', '0', '2', '4', '844895956', 'edit-cronjob', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1210756465', '294626826', 'Pending Users', 'user-admin/user-admin-pending.php', 'PHPDevShell', '1', '', '0', '4', '0', '844895956', 'pending-users', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('131201277', '294626826', 'Manage Users', 'user-admin/user-admin-list.php', 'PHPDevShell', '1', '', '0', '1', '0', '844895956', 'manage-users', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1363712008', '982913650', 'System Settings', 'system-admin/general-settings.php', 'PHPDevShell', '1', '', '0', '1', '0', '844895956', 'system-settings', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1405303115', '2751748213', 'Edit Role', 'user-admin/user-role-admin.link', 'PHPDevShell', '2', '2313706889', '0', '3', '4', '844895956', 'edit-role', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1411278578', '2509699192', 'Policy Admin', 'user/control-panel.user-control', 'PHPDevShell', '2', '940041356', '0', '6', '0', '844895956', 'policy-admin', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1440418834', '294626826', 'New User', 'user-admin/user-admin.php', 'PHPDevShell', '1', '', '0', '2', '0', '844895956', 'new-user', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1648130103', '3669783681', 'System Logs', 'logs-admin/system-logs.php', 'PHPDevShell', '1', '', '0', '2', '0', '844895956', 'system-logs', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1669337107', '982913650', 'Theme Admin', 'template-admin/template-admin-list.php', 'PHPDevShell', '1', '', '0', '4', '0', '844895956', 'theme-admin', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1772410402', '1814972020', 'New Group', 'user-admin/user-group-admin.php', 'PHPDevShell', '1', '', '0', '2', '0', '844895956', 'new-group', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1814972020', '1411278578', 'Group Admin', 'user-admin/user-group-admin-list.php.link', 'PHPDevShell', '2', '3276230420', '0', '4', '2', '844895956', 'group-admin', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1886139891', '2190226087', 'Email Token', 'registration-token-admin/email-token.php', 'PHPDevShell', '1', '', '0', '4', '4', '844895956', 'email-token', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1901799184', '0', 'Lost Password', 'user/lost-password.php', 'PHPDevShell', '1', '', '0', '3', '0', '844895956', 'lost-password', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2021208659', '3669783681', 'Upload Logs', 'logs-admin/fileupload-logs.php', 'PHPDevShell', '1', '', '0', '4', '0', '844895956', 'upload-logs', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('1784737923', '3669783681', 'File Log Viewer', 'logs-admin/file-log-viewer.php', 'PHPDevShell', '1', '', '0', '5', '0', '844895956', 'file-log-viewer', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2074704070', '982913650', 'Config Manager', 'system-admin/config-manager.php', 'PHPDevShell', '1', '', '0', '2', '0', '844895956', 'config-manager', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2143500606', '0', 'Finish Registration', 'user/register-finalize.php', 'PHPDevShell', '1', '', '0', '8', '1', '844895956', 'finish-registration', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2190226087', '1411278578', 'Token Admin', 'registration-token-admin/registration-token-admin-list.php.link', 'PHPDevShell', '2', '2387241520', '0', '5', '2', '844895956', 'token-admin', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2200445609', '2190226087', 'Edit Token', 'registration-token-admin/registration-token-admin.link', 'PHPDevShell', '2', '48580716', '0', '3', '4', '844895956', 'edit-token', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2266433229', '0', 'Readme', 'user/readme.php', 'PHPDevShell', '1', '', '0', '1', '0', '844895956', 'readme', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2273945344', '1814972020', 'Edit Group', 'user-admin/user-group-admin.link', 'PHPDevShell', '2', '1772410402', '0', '3', '4', '844895956', 'edit-group', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2313706889', '2751748213', 'New Role', 'user-admin/user-role-admin.php', 'PHPDevShell', '1', '', '0', '2', '0', '844895956', 'new-role', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2387241520', '2190226087', 'Manage Tokens', 'registration-token-admin/registration-token-admin-list.php', 'PHPDevShell', '1', '', '0', '1', '0', '844895956', 'manage-tokens', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2390350678', '3669783681', 'Access Logs', 'logs-admin/menu-access-logs.php', 'PHPDevShell', '1', '', '0', '3', '0', '844895956', 'access-logs', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2509699192', '0', 'System Management', 'user/control-panel.system-admin', 'PHPDevShell', '2', '940041356', '0', '10', '0', '844895956', 'system-management', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2749758364', '930839394', 'Repair Database', 'cron/repair-database.php', 'PHPDevShell', '8', '', '0', '5', '1', '844895956', 'repair-database', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2751748213', '1411278578', 'Role Admin', 'user-admin/user-role-admin-list.php.link', 'PHPDevShell', '2', '3642120161', '0', '3', '2', '844895956', 'role-admin', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('294626826', '1411278578', 'User Admin', 'user-admin/user-admin-list.php.link', 'PHPDevShell', '2', '131201277', '0', '2', '2', '844895956', 'user-admin', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2946674795', '0', 'User Preferences', 'user/edit-preferences.php', 'PHPDevShell', '1', '', '0', '6', '0', '844895956', 'user-preferences', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('2953441878', '930839394', 'Trim Logs', 'cron/trim-logs.php', 'PHPDevShell', '8', '', '0', '4', '1', '844895956', 'trim-logs', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('3204262040', '3968968736', 'Manage Menus', 'menu-admin/menu-item-admin-list.php', 'PHPDevShell', '1', '', '0', '1', '0', '844895956', 'manage-menus', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('32100600', '1411278578', 'Manage Tags', 'tagger-admin/tagger-admin.php', 'PHPDevShell', '1', '', '0', '6', '0', '844895956', 'manage-tags', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('3247623521', '1411278578', 'Access Control', 'menu-admin/menu-item-admin-permissions.php', 'PHPDevShell', '1', '', '0', '1', '0', '844895956', 'access-control', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('3276230420', '1814972020', 'Manage Groups', 'user-admin/user-group-admin-list.php', 'PHPDevShell', '1', '', '0', '1', '0', '844895956', 'manage-groups', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('3440897808', '3968968736', 'Edit Menu', 'menu-admin/menu-item-admin.link', 'PHPDevShell', '2', '967550350', '0', '3', '4', '844895956', 'edit-menu', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('3467402321', '294626826', 'Import Users', 'user-admin/user-admin-import.php', 'PHPDevShell', '1', '', '0', '5', '0', '844895956', 'import-users', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('3642120161', '2751748213', 'Manage Roles', 'user-admin/user-role-admin-list.php', 'PHPDevShell', '1', '', '0', '1', '0', '844895956', 'manage-roles', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('3669783681', '2509699192', 'System Status', 'system-admin/admin.php.link', 'PHPDevShell', '2', '863779375', '0', '1', '2', '844895956', 'system-status', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('3682403894', '0', 'Log In|Out', 'user/login-page.php', 'PHPDevShell', '1', '', '0', '4', '0', '844895956', 'login', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('3727066128', '0', 'Register Account', 'user/register.php', 'PHPDevShell', '1', '', '0', '2', '0', '844895956', 'register-account', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('3776270042', '0', 'Contact Admin', 'user/email-admin.php', 'PHPDevShell', '1', '', '0', '5', '0', '844895956', 'contact-admin', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('3968968736', '2509699192', 'Menu Admin', 'menu-admin/menu-item-admin-list.php.link', 'PHPDevShell', '2', '3204262040', '0', '5', '2', '844895956', 'menu-admin', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('4134883375', '930839394', 'Manage Cronjobs', 'cron-admin/cronjob-admin-list.php', 'PHPDevShell', '1', '', '0', '1', '0', '844895956', 'manage-cronjobs', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('4250544529', '3968968736', 'Access Control', 'menu-admin/menu-item-admin-permissions.link', 'PHPDevShell', '2', '3247623521', '0', '4', '0', '844895956', 'access-control', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('4283172353', '2946674795', 'New Password', 'user/new-password.php', 'PHPDevShell', '1', '', '0', '1', '0', '844895956', 'new-password', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('48580716', '2190226087', 'New Token', 'registration-token-admin/registration-token-admin.php', 'PHPDevShell', '1', '', '0', '2', '0', '844895956', 'new-token', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('585886089', '982913650', 'Plugins Admin', 'plugin-admin/plugin-activation.php', 'PHPDevShell', '1', '', '0', '3', '0', '844895956', 'plugins-admin', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('742061208', '930839394', 'System Cronjob', 'cron-admin/run-cron.php', 'PHPDevShell', '1', '', '0', '3', '1', '844895956', 'system-cronjob', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('863779375', '3669783681', 'System Info', 'system-admin/admin.php', 'PHPDevShell', '1', '', '0', '1', '0', '844895956', 'system-info', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('885145814', '294626826', 'Edit User', 'user-admin/user-admin.link', 'PHPDevShell', '2', '1440418834', '0', '3', '4', '844895956', 'edit-user', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('930839394', '2509699192', 'Cronjob Admin', 'cron-admin/cronjob-admin-list.php.link', 'PHPDevShell', '2', '4134883375', '0', '3', '2', '844895956', 'cronjob-admin', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('940041356', '0', 'Dashboard', 'user/control-panel.php', 'PHPDevShell', '1', '', '0', '7', '1', '844895956', 'cp', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('967550350', '3968968736', 'New Menu', 'menu-admin/menu-item-admin.php', 'PHPDevShell', '1', '', '0', '2', '0', '844895956', 'new-menu', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('971937357', '930839394', 'Optimize Database', 'cron/optimize-database.php', 'PHPDevShell', '8', '', '0', '6', '1', '844895956', 'optimize-database', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('982913650', '2509699192', 'System Admin', 'system-admin/general-settings.php.link', 'PHPDevShell', '2', '1363712008', '0', '2', '2', '844895956', 'system-admin', '', null);
INSERT INTO `pds_core_menu_items` VALUES ('998066830', '982913650', 'Class Registry', 'plugin-admin/class-registry.php', 'PHPDevShell', '1', '', '0', '4', '0', '844895956', 'class-registry', '', '');

# Create menu tree structure.;
CREATE TABLE `pds_core_menu_structure` (
	`id` int(50) unsigned NOT NULL AUTO_INCREMENT,
	`menu_id` varchar(64) NOT NULL,
	`is_parent` int(1) DEFAULT NULL,
	`type` int(1) DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `index` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Insert menu tree structure.;
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2266433229', '0', '2');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '3727066128', '0', '2');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1901799184', '0', '2');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '3682403894', '0', '2');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '3776270042', '0', '2');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2946674795', '1', '1');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '4283172353', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '940041356', '0', '2');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2143500606', '0', '2');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2509699192', '1', '1');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '3669783681', '1', '3');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '863779375', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1648130103', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2390350678', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2021208659', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1784737923', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '982913650', '1', '3');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1363712008', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2074704070', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '585886089', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1669337107', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '998066830', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '930839394', '1', '3');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '4134883375', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1016054546', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '742061208', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2953441878', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2749758364', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '971937357', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '3968968736', '1', '3');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '3204262040', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '967550350', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '3440897808', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '4250544529', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1411278578', '1', '3');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '3247623521', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '294626826', '1', '3');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '131201277', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1440418834', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '885145814', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1210756465', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '3467402321', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2751748213', '1', '3');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '3642120161', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2313706889', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1405303115', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1814972020', '1', '3');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '3276230420', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1772410402', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2273945344', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2190226087', '1', '3');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2387241520', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '48580716', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '2200445609', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '1886139891', '0', '4');
INSERT INTO `pds_core_menu_structure` VALUES (NULL, '32100600', '0', '4');

# Create plugins table.;
CREATE TABLE `pds_core_plugin_activation` (
	`plugin_folder` varchar(255) NOT NULL DEFAULT '0',
	`status` varchar(255) DEFAULT NULL,
	`version` int(16) NOT NULL,
	`use_logo` int(2) DEFAULT NULL,
	PRIMARY KEY (`plugin_folder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Insert available default plugins.;
INSERT INTO `pds_core_plugin_activation` VALUES ('ControlPanel', 'install', '1000', '0');
INSERT INTO `pds_core_plugin_activation` VALUES ('FileMan', 'install', '1000', '0');
INSERT INTO `pds_core_plugin_activation` VALUES ('Pagination', 'install', '1000', '0');
INSERT INTO `pds_core_plugin_activation` VALUES ('PHPMailer', 'install', '1000', '0');
INSERT INTO `pds_core_plugin_activation` VALUES ('PHPThumbs', 'install', '1000', '0');
INSERT INTO `pds_core_plugin_activation` VALUES ('Smarty', 'install', '1000', '0');
INSERT INTO `pds_core_plugin_activation` VALUES ('TinyMCE', 'install', '1000', '0');
INSERT INTO `pds_core_plugin_activation` VALUES ('userActions', 'install', '1000', '0');
INSERT INTO `pds_core_plugin_activation` VALUES ('StandardLogin', 'install', '1000', '0');
INSERT INTO `pds_core_plugin_activation` VALUES ('RedBeanORM', 'install', '1000', '0');
INSERT INTO `pds_core_plugin_activation` VALUES ('CRUD', 'install', '1000', '0');
INSERT INTO `pds_core_plugin_activation` VALUES ('BotBlock', 'install', '1000', '0');

# Create classes available from default plugins.;
CREATE TABLE `pds_core_plugin_classes` (
	`class_id` int(10) NOT NULL AUTO_INCREMENT,
	`class_name` varchar(155) DEFAULT NULL,
	`alias` varchar(155) DEFAULT NULL,
	`plugin_folder` varchar(255) DEFAULT NULL,
	`enable` int(1) DEFAULT NULL,
	`rank` int(4) DEFAULT NULL,
	PRIMARY KEY (`class_id`),
	KEY `index` (`class_name`,`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Insert classes available from default plugins.;
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'mailer', 'PHPDS_mailer', 'PHPMailer', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'wysiwygEditor', 'PHPDS_wysiwyg', 'TinyMCE', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'imaging', 'PHPDS_imaging', 'PHPThumbs', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'pagination', 'PHPDS_pagination', 'Pagination', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'views', 'PHPDS_views', 'Smarty', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'fileManager', 'PHPDS_fileManager', 'FileMan', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'groupTree', 'PHPDS_groups_tree', 'PHPDevShell', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'iana', 'PHPDS_iana', 'PHPDevShell', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'menuArray', 'PHPDS_menu_array', 'PHPDevShell', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'menuStructure', 'PHPDS_menu_structure', 'PHPDevShell', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'pluginManager', 'PHPDS_pluginmanager', 'PHPDevShell', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'timeZone', 'PHPDS_timezone', 'PHPDevShell', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'userPending', 'PHPDS_user_pending', 'PHPDevShell', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'controlPanel', 'PHPDS_controlPanel', 'ControlPanel', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'fileManager', 'PHPDS_fileManager', 'FileMan', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'imaging', 'PHPDS_imaging', 'PHPThumbs', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'userActions', 'PHPDS_userAction', 'userActions', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'StandardLogin', 'PHPDS_login', 'StandardLogin', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'orm', 'PHPDS_orm', 'RedBeanORM', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'crud', 'PHPDS_crud', 'CRUD', '1', '1');
INSERT INTO `pds_core_plugin_classes` VALUES (NULL, 'botBlock', 'PHPDS_botBlock', 'BotBlock', '1', '1');

# Create table for registrations that is in queue.;
CREATE TABLE `pds_core_registration_queue` (
	`user_id` int(20) unsigned NOT NULL DEFAULT '0',
	`registration_type` int(1) DEFAULT NULL,
	`token_id` int(20) DEFAULT NULL,
	PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Create table for registration tokens.;
CREATE TABLE `pds_core_registration_tokens` (
	`token_id` int(10) NOT NULL AUTO_INCREMENT,
	`token_name` varchar(255) DEFAULT NULL,
	`user_role_id` int(10) DEFAULT NULL,
	`user_group_id` int(10) DEFAULT NULL,
	`token_key` varchar(42) DEFAULT NULL,
	`registration_option` int(1) DEFAULT NULL,
	`available_tokens` int(25) DEFAULT NULL,
	PRIMARY KEY (`token_id`),
	KEY `index` (`token_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Create session table.;
CREATE TABLE `pds_core_session` (
	`cookie_id` int(20) unsigned NOT NULL AUTO_INCREMENT,
	`user_id` int(20) unsigned NOT NULL,
	`id_crypt` char(6) NOT NULL,
	`pass_crypt` char(32) NOT NULL,
	`timestamp` int(10) NOT NULL,
	PRIMARY KEY (`cookie_id`),
	KEY `index` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Create settings table.;
CREATE TABLE `pds_core_settings` (
	`setting_description` varchar(100) NOT NULL DEFAULT '',
	`setting_value` text,
	`note` text,
	PRIMARY KEY (`setting_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Insert default settings to make system work.;
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_access_logging', '1', 'Should access be logged?');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_allowed_ext', 'jpg,jpeg,png,gif,zip,tar,doc,xls,pdf', 'Extensions allowed when uploading.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_allow_registration', '2', 'Should new registrations be allowed.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_allow_remember', '1', 'Should users be allowed to login with remember.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_banned_role', '6', 'The banned role. No access allowed.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_charset', 'UTF-8', 'Site wide charset.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_charset_format', '.{charset}', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_cmod', '0777', 'How does a writable folder ');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_crop_thumb_dimension', '0,0,100,50', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_crop_thumb_fromcenter', '150', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_crypt_key', 'eDucDjodz8ZiMqFe8zeJ', 'General crypt key to protect system.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_custom_logo', '', 'Default system logo.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_date_format', 'F j, Y, g:i a O', 'Date format according to DateTime function of PHP.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_date_format_short', 'Y-m-d', 'Shorter date format.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_date_format_show', 'September 17, 2010, 12:59 pm +0000', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_date_format_show_short', '2010-09-17', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_debug_language', '', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_default_template', 'cloud', 'Default theme for all nodes.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_default_template_id', '844895956', 'Default template id.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_default_upload_directory', 'write/upload/', 'Writable upload directory.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_demo_mode', '0', 'Should system be set into demo mode, no transactions will occur.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_spam_assassin', '1', 'Should system attempt to protect public forms from spam bots?');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_do_create_resize_image', '1', 'Should image resize versions be created.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_do_create_thumb', '1', 'Should thumbnails be created.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_do_thumb_reflect', '1', 'Should image reflections be created.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_email_charset', 'UTF-8', 'Default email charset.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_email_critical', '1', 'Should critical errors be emailed to admin.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_email_encoding', '8bit', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_email_fromname', 'PHPDevShell', 'From which name should emails come.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_email_hostname', '', 'Email host name.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_email_new_registrations', '1', 'Should new registrations be mailed.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_email_option', 'smtp', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_empty_template_id', '1757887940', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_footer_notes', 'PHPDevShell.org (c) 2011 GNU/GPL License.', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_force_core_changes', '0', 'Should core changes be forced, like deleting root user.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_from_email', 'no-reply@phphdevshell.org', 'From Email address.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_front_page_id', '2266433229', 'The page to show when site is access.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_front_page_id_in', '940041356', 'The page to show when logged in and home or page is accessed.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_ftp_enable', '1', 'Should ftp be enabled.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_ftp_host', 'localhost', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_ftp_password', '', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_ftp_port', '21', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_ftp_root', '', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_ftp_ssl', '0', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_ftp_timeout', '90', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_ftp_username', 'usernameFTP', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_graphics_engine', 'gd', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_guest_group', '3', 'The systems guest group.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_guest_role', '5', 'The systems guest role.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_image_quality', '80', 'What is the compressions ratio for resized images.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_language', 'en', 'Default language.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_languages_available', 'en', 'List of language codes available');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_limit_favorite', '5', 'Control panel favorite menus limit.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_limit_messages', '5', 'Control panel log limit.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_locale_format', '{lang}_{region}{charset}', 'Complete locale format.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_loginandout', '3682403894', 'The page for log in and log out.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_login_message', '', 'a Default message to welcome users loging in.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_log_uploads', '1', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_massmail_limit', '100', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_max_filesize', '200000', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_max_filesize_show', '200 Kb', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_max_imagesize', '200000', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_max_imagesize_show', '200 Kb', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_meta_description', 'Administrative user interface based on PHPDevShell and other modern technologies.', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_meta_keywords', 'administrative, administrator, phpdevshell, interface, ui, user', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_move_verified_group', '2', 'When user is approved, he will be moved to this group by default.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_move_verified_role', '2', 'When user is approved, he will be moved to this role by default.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_printable_template', 'cloud-printable', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_queries_count', '1', 'Should queries be counted and info show.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_redirect_login', '3682403894', 'When a user logs in, where should he be redirected to?');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_region', 'US', 'Region settings.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_regions_available', 'US', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_registration_group', '3', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_registration_message', '', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_registration_page', '3727066128', 'Default registration page.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_registration_role', '4', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_resize_adaptive_dimension', '250,150', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_resize_image_dimension', '500,500', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_resize_thumb_dimension', '250,150', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_resize_thumb_percent', '50', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_root_group', '1', 'Root Group.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_root_id', '1', 'Root User.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_root_role', '1', 'Root Role.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_save', 'save', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_scripts_name_version', 'Powered by PHPDevShell', 'Footer message.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_sef_url', '0', 'Should SEF urls be enabled, not rename to .htaccess in root.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_sendmail_path', '/usr/sbin/sendmail', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_setting_admin_email', 'admin@phpdevshell.org', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_setting_support_email', 'default:System Support Query,default:General Query', 'Allows you to have multiple option for a email query.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_skin', 'flick', 'Default skin to use for styling.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_smtp_helo', '', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_smtp_host', 'smtp.gmail.com', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_smtp_password', '', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_smtp_port', '465', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_smtp_secure', 'ssl', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_smtp_timeout', '10', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_smtp_username', 'admin@phpdevshell.org', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_split_results', '30', 'When viewing paged results, how many results should be shown.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_system_down', '0', 'Is system currently down for development.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_system_down_message', '%s is currently down for maintenance. Some important features are being updated. Please return soon.', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_system_logging', '1', 'Should logs be written to database.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_system_timezone', 'UTC', 'Timezone.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_test_email', 'test_email', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_test_ftp', 'on', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_thumbnail_type', 'adaptive', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_thumb_reflect_settings', '40,40,80,true,#a4a4a4', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_trim_logs', '1000000', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_url_append', '.html', 'The url extension in the end.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_verify_registration', '1', 'Does users need to be verified after registration.');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_menu_behaviour', 'dynamic', 'How the menu system should behave when navigating');

INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_reg_email_admin', 'Dear Admin,\r\n\r\nYou have received a new registration at %1$s.\r\nThe user registered with the name %2$s, on this date %3$s, with the username %4$s.\r\n\r\nThank You,\r\n%5$s.%6$s %7$s %8$s\r\n\r\nYou must be logged-in to ban or approve users.', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_reg_email_approve', 'Dear %1$s,\r\n\r\nYou completed the registration at %2$s.\r\nYour registration was successful but is still pending for approval from admin staff.\r\n\r\nThank you for registering at %3$s, an Admin will attend to your request soon.', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_reg_email_direct', 'Dear %1$s,\r\n\r\nYou completed the registration at %2$s.\r\nYour registration was successful.\r\n\r\nThank you for registering at %3$s.', '');
INSERT INTO `pds_core_settings` VALUES ('PHPDevShell_reg_email_verify', 'Dear %1$s,\r\n\r\nYou requested registration at %2$s.\r\nYour registration was successful.\r\n\r\nPlease click on the *link\r\n%3$s\r\nto complete the registration process.\r\n\r\nThank you for registering at %4$s.\r\n\r\n*If you cannot click on the link, copy and paste the url in your browsers address bar.', '');

# Create tags table for tagging data.;
CREATE TABLE `pds_core_tags` (
	`tagID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`tagObject` varchar(45) DEFAULT NULL,
	`tagName` varchar(45) DEFAULT NULL,
	`tagTarget` varchar(45) DEFAULT NULL,
	`tagValue` text,
	PRIMARY KEY (`tagID`),
	UNIQUE KEY `UNIQUE` (`tagObject`,`tagName`,`tagTarget`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Create themes table to store installed themes.;
CREATE TABLE `pds_core_templates` (
	`template_id` int(32) unsigned NOT NULL,
	`template_folder` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Insert default themes.;
INSERT INTO `pds_core_templates` VALUES ('844895956', 'cloud');
INSERT INTO `pds_core_templates` VALUES ('1757887940', 'empty');
INSERT INTO `pds_core_templates` VALUES ('3566024413', 'emptyCloud');

# Create file upload storing registry and logs.;
CREATE TABLE `pds_core_upload_logs` (
  `file_id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `sub_id` int(20) DEFAULT NULL,
  `menu_id` varchar(64) NOT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `new_filename` varchar(255) DEFAULT NULL,
  `relative_path` text,
  `thumbnail` text,
  `resized` text,
  `extention` varchar(5) DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `file_desc` varchar(255) DEFAULT NULL,
  `group_id` int(20) DEFAULT NULL,
  `user_id` int(20) DEFAULT NULL,
  `date_stored` int(10) unsigned DEFAULT NULL,
  `file_size` int(14) DEFAULT NULL,
  `file_explained` text,
  PRIMARY KEY (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Create important user table to store all users.;
CREATE TABLE `pds_core_users` (
	`user_id` int(20) unsigned NOT NULL AUTO_INCREMENT,
	`user_display_name` varchar(255) DEFAULT NULL,
	`user_name` varchar(255) DEFAULT NULL,
	`user_password` varchar(100) DEFAULT NULL,
	`user_email` varchar(100) DEFAULT NULL,
	`user_group` int(10) DEFAULT NULL,
	`user_role` int(10) DEFAULT NULL,
	`date_registered` int(10) DEFAULT NULL,
	`language` varchar(10) DEFAULT NULL,
	`timezone` varchar(255) DEFAULT NULL,
	`region` varchar(10) DEFAULT NULL,
	PRIMARY KEY (`user_id`),
	UNIQUE KEY `index_user` (`user_name`,`user_email`),
	KEY `index_general` (`user_display_name`,`user_group`,`user_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Create extra groups table a user can belong to.;
CREATE TABLE `pds_core_user_extra_groups` (
	`user_id` int(20) NOT NULL DEFAULT '0',
	`user_group_id` int(20) NOT NULL DEFAULT '0',
	PRIMARY KEY (`user_id`,`user_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Create extra roles table a user can belong to.;
CREATE TABLE `pds_core_user_extra_roles` (
  `user_id` int(20) NOT NULL DEFAULT '0',
  `user_role_id` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`user_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Create primary groups table a user can belong to.;
CREATE TABLE `pds_core_user_groups` (
	`user_group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`user_group_name` varchar(255) DEFAULT NULL,
	`user_group_note` tinytext,
	`parent_group_id` int(10) DEFAULT NULL,
	`alias` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`user_group_id`),
	KEY `index` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Insert primary groups table a user can belong to.;
INSERT INTO `pds_core_user_groups` VALUES ('1', 'Super', null, '0', '');
INSERT INTO `pds_core_user_groups` VALUES ('2', 'Registered', null, '0', '');
INSERT INTO `pds_core_user_groups` VALUES ('3', 'Guest', null, '0', '');
INSERT INTO `pds_core_user_groups` VALUES ('4', 'Limited Admin', null, '0', '');
INSERT INTO `pds_core_user_groups` VALUES ('5', 'Demo', '', '0', '');

# Create primary roles table a user can belong to.;
CREATE TABLE `pds_core_user_roles` (
	`user_role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`user_role_name` varchar(255) DEFAULT NULL,
	`user_role_note` tinytext,
	PRIMARY KEY (`user_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Insert primary roles table a user can belong to.;
INSERT INTO `pds_core_user_roles` VALUES ('1', 'Super', '');
INSERT INTO `pds_core_user_roles` VALUES ('2', 'Registered', null);
INSERT INTO `pds_core_user_roles` VALUES ('4', 'Awaiting Confirmation', '');
INSERT INTO `pds_core_user_roles` VALUES ('5', 'Guest', '');
INSERT INTO `pds_core_user_roles` VALUES ('6', 'Disabled', '');
INSERT INTO `pds_core_user_roles` VALUES ('7', 'Limited Admin', '');
INSERT INTO `pds_core_user_roles` VALUES ('8', 'Branch Admin', '');
INSERT INTO `pds_core_user_roles` VALUES ('9', 'Demo', '');

# Create security role permissions table.;
CREATE TABLE `pds_core_user_role_permissions` (
  `user_role_id` int(10) NOT NULL DEFAULT '0',
  `menu_id` varchar(64) NOT NULL,
  PRIMARY KEY (`user_role_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Insert default user permissions.;
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1016054546');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1210756465');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '131201277');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1363712008');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1405303115');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1411278578');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1440418834');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1648130103');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1669337107');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1772410402');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1814972020');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1886139891');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2021208659');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '1784737923');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2074704070');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2190226087');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2200445609');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2266433229');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2273945344');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2313706889');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2387241520');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2390350678');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2509699192');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2749758364');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2751748213');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '294626826');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2946674795');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '2953441878');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '3204262040');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '32100600');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '3247623521');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '3276230420');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '3440897808');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '3467402321');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '3642120161');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '3669783681');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '3682403894');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '3776270042');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '3968968736');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '4134883375');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '4250544529');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '48580716');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '585886089');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '742061208');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '863779375');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '885145814');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '930839394');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '940041356');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '967550350');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '971937357');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '982913650');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '4283172353');
INSERT INTO pds_core_user_role_permissions VALUES ('1', '998066830');
INSERT INTO pds_core_user_role_permissions VALUES ('2', '2266433229');
INSERT INTO pds_core_user_role_permissions VALUES ('2', '2946674795');
INSERT INTO pds_core_user_role_permissions VALUES ('2', '3682403894');
INSERT INTO pds_core_user_role_permissions VALUES ('2', '3776270042');
INSERT INTO pds_core_user_role_permissions VALUES ('2', '4283172353');
INSERT INTO pds_core_user_role_permissions VALUES ('2', '940041356');
INSERT INTO pds_core_user_role_permissions VALUES ('4', '2143500606');
INSERT INTO pds_core_user_role_permissions VALUES ('4', '2266433229');
INSERT INTO pds_core_user_role_permissions VALUES ('4', '2946674795');
INSERT INTO pds_core_user_role_permissions VALUES ('4', '3682403894');
INSERT INTO pds_core_user_role_permissions VALUES ('4', '3776270042');
INSERT INTO pds_core_user_role_permissions VALUES ('4', '4283172353');
INSERT INTO pds_core_user_role_permissions VALUES ('4', '940041356');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '1901799184');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '2143500606');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '2266433229');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '2749758364');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '2953441878');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '3682403894');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '3727066128');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '3776270042');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '4283172353');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '742061208');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '940041356');
INSERT INTO pds_core_user_role_permissions VALUES ('5', '971937357');
INSERT INTO pds_core_user_role_permissions VALUES ('6', '3682403894');
INSERT INTO pds_core_user_role_permissions VALUES ('6', '940041356');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '1210756465');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '131201277');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '1363712008');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '1411278578');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '1440418834');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '1648130103');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '1772410402');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '1814972020');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '1886139891');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '2021208659');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '1784737923');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '2074704070');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '2190226087');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '2200445609');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '2266433229');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '2273945344');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '2387241520');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '2390350678');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '2509699192');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '294626826');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '2946674795');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '3276230420');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '3467402321');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '3669783681');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '3682403894');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '3776270042');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '4283172353');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '48580716');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '863779375');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '885145814');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '940041356');
INSERT INTO pds_core_user_role_permissions VALUES ('7', '982913650');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '1210756465');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '131201277');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '1411278578');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '1440418834');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '1772410402');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '1814972020');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '1886139891');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '2190226087');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '2200445609');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '2266433229');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '2273945344');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '2387241520');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '2509699192');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '294626826');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '2946674795');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '3276230420');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '3467402321');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '3682403894');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '3776270042');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '4283172353');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '48580716');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '885145814');
INSERT INTO pds_core_user_role_permissions VALUES ('8', '940041356');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1016054546');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1210756465');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '131201277');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1363712008');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1405303115');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1411278578');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1440418834');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1648130103');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1669337107');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1772410402');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1814972020');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1886139891');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1901799184');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2021208659');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '1784737923');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2074704070');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2190226087');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2200445609');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2266433229');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2273945344');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2313706889');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2387241520');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2390350678');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2509699192');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2749758364');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2751748213');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '294626826');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2946674795');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '2953441878');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '3204262040');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '32100600');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '3247623521');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '3276230420');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '3440897808');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '3467402321');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '3642120161');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '3669783681');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '3682403894');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '3727066128');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '3776270042');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '3968968736');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '4134883375');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '4250544529');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '4283172353');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '48580716');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '585886089');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '742061208');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '863779375');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '885145814');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '930839394');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '940041356');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '967550350');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '971937357');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '982913650');
INSERT INTO pds_core_user_role_permissions VALUES ('9', '998066830');