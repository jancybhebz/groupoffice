<?php
namespace go\core\customfield;

use go\core\util\Crypt;
use go\core\customfield\Base;

class EncryptedText extends Base {
	/**
	 * @inheritdoc
	 */
	protected function getFieldSQL(): string
	{
		return "TEXT NULL";
	}
	
	public function dbToApi($value, \go\core\orm\CustomFieldsModel $values, $entity) {
		return isset($value) ? Crypt::decrypt($value) : null;
	}
	
	public function apiToDb($value, \go\core\orm\CustomFieldsModel $values, $entity) {
		return isset($value) ? Crypt::encrypt($value) : null;
	}
	
}

