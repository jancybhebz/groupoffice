<?php
namespace go\core\mail;


use PHPMailer\PHPMailer\Exception;

/**
 * PHPMailer SMTP extension
 *
 * Uses less memory
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Intermesh BV
 */
class PHPMailerSMTP extends \PHPMailer\PHPMailer\SMTP {
	public function data($msg_data)
	{
		//This will use the standard timelimit
		if (!$this->sendCommand('DATA', 'DATA', 354)) {
			return false;
		}

		/* The server is ready to accept data!
		 * According to rfc821 we should not send more than 1000 characters on a single line (including the LE)
		 * so we will break the data up into lines by \r and/or \n then if needed we will break each of those into
		 * smaller lines to fit within the limit.
		 * We will also look for lines that start with a '.' and prepend an additional '.'.
		 * NOTE: this does not count towards line-length limit.
		 */

		// Normalize line breaks
		//$msg_data = str_replace(array("\r\n", "\r"), "\n", $msg_data);

		/* To distinguish between a complete RFC822 message and a plain message body, we check if the first field
		 * of the first line (':' separated) does not contain a space then it _should_ be a header and we will
		 * process all lines before a blank line as headers.
		 */

		$firstline = substr($msg_data, 0, strcspn($msg_data, "\n", 0));
		$field = substr($firstline, 0, strpos($firstline, ':'));
		$in_headers = false;
		if (!empty($field) && strpos($field, ' ') === false) {
			$in_headers = true;
		}

		$offset = 0;
		$len = strlen($msg_data);
		while ($offset < $len) {
			//Get position of next line break
			$linelen = strcspn($msg_data, "\n", $offset);
			//Get the next line
			$line = trim(substr($msg_data, $offset, $linelen), "\r");
			//Remember where we have got to
			$offset += ($linelen + 1);
			$lines_out = [];

			if ($in_headers && $line === '') {
				$in_headers = false;
			}
			//Break this line up into several smaller lines if it's too long
			//Micro-optimisation: isset($str[$len]) is faster than (strlen($str) > $len),
			while (isset($line[self::MAX_LINE_LENGTH])) {
				//Working backwards, try to find a space within the last MAX_LINE_LENGTH chars of the line to break on
				//so as to avoid breaking in the middle of a word
				$pos = strrpos(substr($line, 0, self::MAX_LINE_LENGTH), ' ');
				//Deliberately matches both false and 0
				if (!$pos) {
					//No nice break found, add a hard break
					$pos = self::MAX_LINE_LENGTH - 1;
					$lines_out[] = substr($line, 0, $pos);
					$line = substr($line, $pos);
				} else {
					//Break at the found point
					$lines_out[] = substr($line, 0, $pos);
					//Move along by the amount we dealt with
					$line = substr($line, $pos + 1);
				}
				//If processing headers add a LWSP-char to the front of new line RFC822 section 3.1.1
				if ($in_headers) {
					$line = "\t" . $line;
				}
			}
			$lines_out[] = $line;

			//Send the lines to the server
			foreach ($lines_out as $line_out) {
				//Dot-stuffing as per RFC5321 section 4.5.2
				//https://tools.ietf.org/html/rfc5321#section-4.5.2
				if (!empty($line_out) && $line_out[0] === '.') {
					$line_out = '.' . $line_out;
				}
				$this->client_send($line_out . static::LE, 'DATA');
			}
		}

		//Message data has been sent, complete the command
		//Increase timelimit for end of DATA command
		$savetimelimit = $this->Timelimit;
		$this->Timelimit *= 2;
		$result = $this->sendCommand('DATA END', '.', 250);
		$this->recordLastTransactionID();
		//Restore timelimit
		$this->Timelimit = $savetimelimit;

		return $result;
	}
}