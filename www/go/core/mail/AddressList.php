<?php

namespace go\core\mail;

use ArrayAccess;
use Exception;
use go\core\imap\Utils;
use go\core\util\StringUtil;
use go\core\validate\ValidateEmail;
use Countable;

/**
 * A list of e-mail recipients
 * 
 * example:
 * 
 * "Merijn Schering" <mschering@intermesh.nl>,someone@somedomain.com,Pete <pete@pete.com>
 * 
 */
class AddressList implements ArrayAccess, Countable {

	/**
	 * Pass a e-mail string like:
	 *
	 * "Merijn Schering" <mschering@intermesh.nl>, someone@somedomain.com, Pete <pete@pete.com>
	 *
	 * @param string $emailRecipientList
	 * @param bool $strict
	 * @throws Exception
	 */
	public function __construct(string $emailRecipientList = '', bool $strict = false) {
		$this->strict = $strict;
		$this->addString($emailRecipientList);
	}

	/**
	 * Set to true if you want to throw an exception when an e-mail is invalid.
	 * @var boolean 
	 */
	public $strict = false;

	/**
	 * Check if an e-mail address is in this list
	 * 
	 * @param string $email
	 * @return int|false index of the array
	 */
	public function hasAddress(string $email) {
		for ($i = 0, $c = count($this->addresses); $i < $c; $i++) {
			if ($this->addresses[$i]->getEmail() == $email) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Remove a recipient from the list.
	 * 
	 * @param string $email 
	 */
	public function remove(string $email) {

		$index = $this->hasAddress($email);

		if ($index !== false) {
			unset($this->addresses[$index]);
		}
	}

	public function __toString() {
		$str = '';
		foreach ($this->addresses as $recipient) {
			$str .= $recipient . ', ';
		}
		return rtrim($str, ', ');
	}

	/**
	 * Get an array of formatted address:
	 * 
	 * $a[] = '"John Doe" <john@domain.com>';
	 * 
	 * @return Address[]
	 */
	public function toArray(): array
	{
		return $this->addresses;
	}

	/**
	 * The array of parsed addresses
	 *
	 * @var     array
	 * @access  private
	 */
	private $addresses = array();

	/**
	 * Temporary storage of personal info of an e-mail address
	 *
	 * @var     StringUtil
	 * @access  private
	 */
	private $name = null;

	/**
	 * Temporary storage
	 *
	 * @var     StringUtil
	 * @access  private
	 */
	private $buffer = '';

	/**
	 * Bool to check if a string is quoted or not
	 *
	 * @var     bool
	 * @access  private
	 */
	private $inQuotedString = false;

	/**
	 * Bool to check if we found an e-mail address
	 *
	 * @var     bool
	 * @access  private
	 */
	private $emailFound = false;


	public function add(Address $address) {
		$this->addresses[] = $address;
	}

	/**
	 * Pass a e-mail string like:
	 *
	 * "Merijn Schering" <mschering@intermesh.nl>,someone@somedomain.com,Pete <pete@pete.com
	 *
	 * @param string $addressStr
	 * @return array
	 * @throws Exception
	 */
	public function addString(string $addressStr): AddressList
	{

		$addressStr = trim($addressStr, ',; ');

		for ($i = 0; $i < strlen($addressStr); $i++) {
			$char = $addressStr[$i];

			switch ($char) {
				case "'":
				case '"':
					$this->handleQuote($char);
					break;

				case '<':
					$this->name = trim($this->buffer);
					$this->buffer = '';
					$this->emailFound = true;
					break;

				case '>':
					//do nothing
					if ($this->inQuotedString) {
						$this->buffer .= $char;
					}
					break;

				case ',':
				case ';':
					if ($this->inQuotedString) {// || (!$this->strict && !$this->_emailFound && !ValidateEmail::check(trim($this->_buffer)))) {
						$this->buffer .= $char;
					} else {
						$this->addBuffer();
					}
					break;


				default:
					$this->buffer .= $char;
					break;
			}
		}
		$this->addBuffer();

		return $this;
	}

	/**
	 * Adds the current buffers to the addresses array
	 *
	 * @access private
	 * @return void
	 * @throws Exception
	 */
	private function addBuffer() {
		$this->buffer = trim($this->buffer);
		if (!empty($this->name) && empty($this->buffer)) {
			$this->buffer = 'noaddress';
		}

		if (!empty($this->buffer)) {
			if ($this->strict && !Util::validateEmail($this->buffer)) {
				throw new Exception("Address " . $this->buffer . " is not valid");
			} else {
				$this->addresses[] = new Address($this->buffer, isset($this->name) ? Utils::mimeHeaderDecode($this->name) : null);
			}
		}
		$this->buffer = '';
		$this->name = null;
		$this->emailFound = false;
		$this->inQuotedString = false;
	}

	/**
	 * Hanldes a quote character (' or ")
	 *
	 * @access private
	 * @return void
	 */
	private function handleQuote($char) {
		if (!$this->inQuotedString && trim($this->buffer) == "") {
			$this->inQuotedString = $char;
		} elseif ($char == $this->inQuotedString) {
			$this->inQuotedString = false;
		} else {
			$this->buffer .= $char;
		}
	}
	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->addresses);
	}

	#[\ReturnTypeWillChange]
	public function offsetGet($offset) {
		return $this->addresses[$offset];
	}

	public function offsetSet($offset, $value) : void{

		if (!is_string($value)) {
			return;
		}

		$recipients = new AddressList($value);

		$this->addresses[$offset] = $recipients[0];
	}

	public function offsetUnset($offset) : void {
		unset($this->addresses[$offset]);
	}

	public function count() : int
	{
		return count($this->addresses);
	}
}
