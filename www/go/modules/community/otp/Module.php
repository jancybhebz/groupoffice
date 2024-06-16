<?php
namespace go\modules\community\otp;

use go\core;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\Settings;
use go\core\util\QRcode;
use go\core\validate\ErrorCode;
use go\modules\community\otp\model;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\User;

class Module extends core\Module {
	/**
	 * The development status of this module
	 * @return string
	 */
	public function getStatus() : string{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV";
	}

	public function autoInstall(): bool
	{
		return true;
	}
	
	public function defineListeners() {
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
		User::on(core\jmap\Entity::EVENT_VALIDATE, static::class, 'onUserValidate');
	}

	public static function onUserValidate(User $user)
	{
		if($user->isModified(['otp']) && !$user->otp) {
			// Prevent validation errors when admin tries to disable OTP for non-admin users
			if (go()->getAuthState()->isAdmin() && go()->getUserId() !== $user->id) {
				return;
			}

			$v = $user->isPasswordVerified();
			if($v) {
				return;
			} else if($v === null) {
				$user->setValidationError("currentPassword", ErrorCode::REQUIRED);
			} else {
				$user->setValidationError("currentPassword", ErrorCode::INVALID_INPUT);
			}
		}
	}

	public static function onMap(Mapping $mapping) {		
		$mapping->addHasOne("otp", model\OtpAuthenticator::class, ['id' => 'userId'], false);
		return true;
	}

	protected function afterInstall(ModuleModel $model): bool
	{
		if(!OtpAuthenticator::register()) {
			return false;
		}		

		return parent::afterInstall($model);
	}


	protected function beforeInstall(\go\core\model\Module $model): bool
	{
		// Share module with Internal group
		$model->permissions[Group::ID_INTERNAL] = (new \go\core\model\Permission($model))
			->setRights(['mayRead' => true]);

		return parent::beforeInstall($model); // TODO: Change the autogenerated stub
	}


	public function getSettings()
	{
		return model\Settings::get();
	}


	/**
	 * Get the blob id of the QR code image
	 *
	 * @param string $name
	 * @param string $secret
	 * @param string $title
	 * @param array $params
	 *
	 * @return boolean/string
	 */
	public function downloadQr() {

		$user = go()->getAuthState()->getUser();

		header("Content-Type: image/png");

		$user->otp->outputQr();


	}

}
