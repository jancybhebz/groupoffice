<?php
namespace go\modules\community\dev;

use go\core\model;
use go\modules\community\dev\model\DummyAuthenticator;

class Module extends \go\core\Module {
	
	public function getAuthor() {
		return "Intermesh BV";
	}

	protected function afterInstall(model\Module $model)
	{
//		DummyAuthenticator::register();

		return parent::afterInstall($model); // TODO: Change the autogenerated stub
	}

}
