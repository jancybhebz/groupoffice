<?php
namespace go\core\util;

use DateTime as PHPDateTime;
use DateTimeZone;
use go\core\data\ArrayableInterface;
use go\core\model\User;

class DateTime extends PHPDateTime implements ArrayableInterface, \JsonSerializable {

	public $hasTime = true;
	
	/**
	 * The date outputted to the clients. It's according to ISO 8601;	 
	 */
	const FORMAT_API = "c";

	const FORMAT_API_DATE_ONLY = "Y-m-d";

	public function toArray($properties = null) {
		return $this->format($this->hasTime ? self::FORMAT_API : self::FORMAT_API_DATE_ONLY);
	}

	public function jsonSerialize() {
		return $this->format($this->hasTime ? self::FORMAT_API : self::FORMAT_API_DATE_ONLY);
	}
	
	public function __toString() {
		return $this->format($this->hasTime ? self::FORMAT_API : self::FORMAT_API_DATE_ONLY);
	}

	private static $currentUser;

	private static function currentUser() {
		if(!isset(self::$currentUser)) {
			self::$currentUser = go()->getAuthState()->getUser(['dateFormat', 'timezone', 'timeFormat' ]);
			if(!self::$currentUser) {
				self::$currentUser = User::findById(1, ['dateFormat', 'timezone', 'timeFormat' ], true);
			}
		}
		return self::$currentUser;
	}

	public function toUserFormat($withTime = false)
	{
		// In case a user is not logged in
		if( empty(self::currentUser()) || empty(self::currentUser()->dateFormat)) {
			return $withTime ? $this->format(self::FORMAT_API) : $this->format(self::FORMAT_API_DATE_ONLY);
		}
		$f = self::currentUser()->dateFormat;
		if($withTime) {
			$date = clone $this;
			$date->setTimezone(new \DateTimeZone(self::currentUser()->timezone));
			$f .= ' ' . self::currentUser()->timeFormat;
			return $date->format($f);
		}
		return $this->format($f);
	}

	/**
	 * Overridden becausde it should return static. Apperently this has been fixed in PHP 8
	 * https://bugs.php.net/bug.php?id=79975
	 *
	 * @param string $format
	 * @param string $datetime
	 * @param DateTimeZone|null $timezone
	 * @return PHPDateTime|false|static
	 * @throws \Exception
	 */
	public static function createFromFormat($format, $datetime, DateTimeZone $timezone = null)
	{
		return new static("@" . parent::createFromFormat($format, $datetime, $timezone)->format("U"));
	}

}
