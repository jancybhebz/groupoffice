<?php

namespace go\core\customfield;

class Data extends Base {
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL()
	{
		return "MEDIUMTEXT NULL";
	}
	
}
