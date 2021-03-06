<?php
/**
 * Template - Get user roles.
 * @author Jason Schoeman, Contact: titan [at] phpdevshell [dot] org.
 *
 */
class TEMPLATE_cronExecutionLogQuery extends PHPDS_query
{
	protected $sql = "
		UPDATE
			_db_core_cron t1
		SET
			t1.last_execution = '%s'
		WHERE
			t1.menu_id = '%s'
	";
}
class TEMPLATE_rollbackQuery extends PHPDS_query
{
	protected $sql = "ROLLBACK";
}

