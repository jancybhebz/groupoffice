<?php
/*
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * This class contains functions for string operations
 *
 * @copyright Copyright Intermesh
 * @version $Id: StringHelper.php 22467 2018-03-07 08:42:50Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.util
 * @since Group-Office 3.0
 */


namespace GO\Base\Util;


use Exception;
use go\core\ErrorHandler;
use go\core\util\StringUtil;

class StringHelper {
	
	/**
	 * Check if the given string is a valid JSON string
	 * 
	 * @param string $string
	 * @return boolean
	 */
	public static function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
	
	
	/**
	 * Normalize Carites Return Line Feed
	 * 
	 * @param string $text
	 * @param string $crlf
	 * @return string
	 */
	public static function normalizeCrlf($text, $crlf="\r\n"){		
		return \go\core\util\StringUtil::normalizeCrlf($text, $crlf);
	}
	
	/**
	 * Convert non ascii characters to chars that come close to them.
	 * @param type $string
	 * @return type 
	 */
		public static function utf8ToASCII($string) {

		//cyrillic
//		$cyr = array(
//		"а", "б", "в", "г", "д", "ђ", "е", "ж", "з", "и", "й", "ј", "к", "л", "љ", "м", "н",
//    "њ", "о", "п", "р", "с", "т", "ћ", "у", "ф", "х", "ц", "ч", "џ", "ш","ъ","ы","ь","э","ю","я",
//				
//    "А", "Б", "В", "Г", "Д", "Ђ", "Е", "Ж", "З", "И", "Й", "Ј", "К", "Л", "Љ", "М", "Н",
//    "Њ", "О", "П", "Р", "С", "Т", "Ћ", "У", "Ф", "Х", "Ц", "Ч", "Џ", "Ш","Ъ","Ы","Ь","Э","Ю","Я");
//		
//
//
//    $lat = array ("a", "b", "v", "g", "d", "d", "e", "z", "z", "i", "j", "j", "k", "l", "lj", "m", "n", "nj", "o", "p",
//    "r", "s", "t", "c", "u", "f", "h", "c", "c", "dz", "s","'","Y","'","e","yu","ya",
//				
//    "A", "B", "B", "G", "D", "D", "E", "Z", "Z", "I", "J", "J", "K", "L", "LJ", "M", "N", "NJ", "O", "P",
//    "R", "S", "T", "C", "U", "F", "H", "C", "C", "DZ", "S","'","Y","'","E","Yu","Ya"
//    );
//		$string = str_replace($cyr, $lat, $string);
		
		$rus = array("/а/", "/б/", "/в/",
				"/г/", "/ґ/", "/д/", "/е/", "/ё/", "/ж/",
				"/з/", "/и/", "/й/", "/к/", "/л/", "/м/",
				"/н/", "/о/", "/п/", "/р/", "/с/", "/т/",
				"/у/", "/ф/", "/х/", "/ц/", "/ч/", "/ш/",
				"/щ/", "/ы/", "/э/", "/ю/", "/я/", "/ь/",
				"/ъ/", "/і/", "/ї/", "/є/", "/А/", "/Б/",
				"/В/", "/Г/", "/ґ/", "/Д/", "/Е/", "/Ё/",
				"/Ж/", "/З/", "/И/", "/Й/", "/К/", "/Л/",
				"/М/", "/Н/", "/О/", "/П/", "/Р/", "/С/",
				"/Т/", "/У/", "/Ф/", "/Х/", "/Ц/", "/Ч/",
				"/Ш/", "/Щ/", "/Ы/", "/Э/", "/Ю/", "/Я/",
				"/Ь/", "/Ъ/", "/І/", "/Ї/", "/Є/", "/Ü/", 
				"/ü/", "/Ö/", "/ö/", "/Ä/", "/ä/", "/ß/");
		
		$lat = array("a", "b", "v",
				"g", "g", "d", "e", "e", "zh", 
				"z", "i",	"j", "k", "l", "m", 
				"n", "o", "p", "r",	"s", "t", 
				"u", "f", "h", "c", "ch", "sh",
				"sh'", "y", "e", "yu", "ya", "'", 
				"'", "i",	"i", "e", "A", "B", 
				"V", "G", "G", "D",	"E", "E", 
				"ZH", "Z", "I", "J", "K", "L",
				"M", "N", "O", "P", "R", "S", 
				"T", "U",	"F", "H", "C", "CH",
				"SH", "SH'", "Y", "E","YU", "YA", 
				"'", "'", "I", "I", "E", "Ue",
				"ue", "Oe", "oe", "Ae", "ae", "ss");
		
		$string = preg_replace($rus, $lat, $string);

		$converted = iconv("UTF-8", "US-ASCII//TRANSLIT", $string);
		if(!empty($converted)){
			return $converted;
		}else
		{
			$converted = preg_replace('/[^a-zA-Z0-9 ,-:_]+/','',$string);
			if(!empty($converted)){
				return $converted;
			}else
			{
				throw new Exception("Could not convert string to ASCII");
			}							
		}
		//return preg_replace('/[^a-zA-Z0-9 ,-:_]+/','',$string);
	}


	public static function get_first_letters($phrase) {

		//remove all non word characters
		$phrase = preg_replace('/[()\[\]\.<>\{\}]+/u','', $phrase);
		$phrase = str_replace(',',' ', $phrase);

		//remove double spaces
		$phrase = preg_replace('/[\s]+/u',' ', $phrase);

		//echo $phrase;

		$words = explode(' ',$phrase);

		$func = function_exists('mb_substr') ? 'mb_substr' : 'substr';
		
		for ($i=0;$i<count($words);$i++) {
			$words[$i] = $func($words[$i],0,1);
		}
		
		return implode('',$words);
	}

	public static function array_to_string($arr){
		$s='';
		foreach($arr as $key=>$value){
			$s .= $key.': '.$value."\n";
		}
		return $s;
	}

	
	public static function escape_javascript($str){
		return strtr($str, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
	}
	
	/**
	 * Tests if string contains 8bit symbols.
	 *
	 * If charset is not set, function defaults to default_charset.
	 * $default_charset global must be set correctly if $charset is
	 * not used.
	 * @param string $string tested string
	 * @param string $charset charset used in a string
	 * @return bool true if 8bit symbols are detected
	 */
	public static function is8bit($string, $charset = 'UTF-8') {
		
		/**
		 * Don't use \240 in ranges. Sometimes RH 7.2 doesn't like it.
		 * Don't use \200-\237 for iso-8859-x charsets. This ranges
		 * stores control symbols in those charsets.
		 * Use preg_match instead of ereg in order to avoid problems
		 * with mbstring overloading
		 */
		if (preg_match("/^iso-8859/i", $charset)) {
			$needle = '/\240|[\241-\377]/';
		} else {
			$needle = '/[\200-\237]|\240|[\241-\377]/';
		}
		return preg_match("$needle", $string);
	}


	public static function to_utf8($str, $from_charset=''){

		if(empty($str))
			return $str;
				
		if(strtoupper($from_charset)=='UTF-8'){
			return $str;
		}else{
			
			//Some mail clients send a different charset while the string is already utf-8 :(
			//
			//This went wrong with UTF-7
			//
//			if(function_exists('mb_check_encoding') && mb_check_encoding($str,'UTF-8'))
//				return $str;

			if(empty($from_charset)){

				/*if(function_exists('mb_detect_encoding'))
				{
					$from_charset = mb_detect_encoding($str, "auto");
				}
				if(empty($from_charset))*/
				$from_charset='windows-1252';
			}
			
			if(substr($from_charset,0,5)=='x-mac')
				return Charset\Xmac::toUtf8($str, $from_charset);
			
			$from_charset = self::fixCharset($from_charset);

			
			return iconv($from_charset, 'UTF-8//IGNORE', $str);
		}
	}
	
/**
	 * Makes charset name suitable for decoding cycles
	 *
	 * ks_c_5601_1987, x-euc-* and x-windows-* charsets are supported
	 * since 1.4.6 and 1.5.1.
	 *
	 * @since 1.4.4 and 1.5.0
	 * @param string $charset Name of charset
	 * @return string $charset Adjusted name of charset
	 */
	public static function fixCharset($charset) {
	
		$charset = preg_replace('/win-([0-9]+)/i','windows-$1', $charset);
		
		$charset=strtolower($charset);


		// OE ks_c_5601_1987 > cp949
		$charset = str_replace('ks_c_5601-1987', 'cp949', $charset);
		// Moz x-euc-tw > euc-tw
		$charset = str_replace('x_euc', 'euc', $charset);
		// Moz x-windows-949 > cp949
		$charset = str_replace('x-windows-', 'cp', $charset);

		// windows-125x and cp125x charsets
		$charset = str_replace('windows-', 'cp', $charset);

		// ibm > cp
		$charset = str_replace('ibm', 'cp', $charset);

		// iso-8859-8-i -> iso-8859-8
		// use same cycle until I'll find differences
		$charset = str_replace('iso-8859-8-i', 'iso-8859-8', $charset);

		return $charset;
	}
	
//	public static function stripInvalidUtf8($utf8string){
//		$utf8string = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
//
//			'|(?<=^|[\x00-\x7F])[\x80-\xBF]+'.
//
//			'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
//
//			'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
//
//			'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/',
//
//			'�', $utf8string );
//
//
//		$utf8string = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
//						'|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $utf8string );
//		
//		return $utf8string;
//	}

	public static function clean_utf8($str, $source_charset=null) {
		
			return StringUtil::cleanUtf8($str, $source_charset);
	}
	/**
	 * Check if string has UTF8 characters
	 * 
	 * @param string $str
	 * @return boolean
	 */
	public static function isUtf8($str) : bool{
		return strlen($str) != mb_strlen($str);
	}

	/**
	 * Replace a string within a string once.
	 *
	 * @param string $search
	 * @param string $replace
	 * @param string $subject
	 * @param bool $found Pass this to check if an occurence was replaced or not
	 * @return string
	 */

	public static function replaceOnce($search, $replace, $subject, &$found=false) {
		return StringUtil::replaceOnce($search, $replace, $subject, $found);
	}

	/**
	 * Reverse strpos. couldn't get PHP strrpos to work with offset
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @param int $offset
	 * @return int
	 */
	public static function rstrpos ($haystack, $needle, $offset=0)
	{
		$size = strlen ($haystack);
		$pos = strpos (strrev($haystack), strrev($needle), $size - $offset);

		if ($pos === false)
		return false;

		return $size - $pos - strlen($needle);
	}

	public static function trim_lines($text)
	{
		str_replace("\r\n","\n", $text);

		$trimmed='';

		$lines = explode("\n", $text);
		foreach($lines as $line)
		{
			if($trimmed=='')
			{
				$trimmed .= $line."\n";
			}elseif(empty($line))
			{
				$trimmed .= "\n";
			}elseif($line[0]!=' ')
			{
				return $text;
			}else{
				$trimmed .= substr($line,1)."\n";
			}
		}

		return $trimmed;
	}



	/**
	 * Grab an e-mail address out of a string
	 *
	 * @param	int $level The log level. See sys_log() of the PHP docs
	 * @param	StringHelper $message The log message
	 * @access public
	 * @return void
	 */
	public static function get_email_from_string($email) {
		if (preg_match("/(\b)([\w\.\-]+)(@)([\w\.-]+)([A-Za-z]{2,4})\b/i", $email, $matches)) {
			return $matches[0];
		} else {
			return false;
		}
	}

	/**
	 * Grab all e-mail addresses out of a string
	 *
	 * @param	int $level The log level. See sys_log() of the PHP docs
	 * @param	StringHelper $message The log message
	 * @access public
	 * @return void
	 */
	public static function get_emails_from_string($emails) {
		if (preg_match_all("/(\b)([\w\.\-]+)(@)([\w\.-]+)([A-Za-z]{2,4})\b/i", $emails, $matches)) {
			return $matches[0];
		} else {
			return false;
		}
	}

	/**
	 * Return only the contents of the body tag from a HTML page
	 *
	 * @param	StringHelper $html A HTML formatted string
	 * @access public
	 * @return string HTML formated string
	 */

	public static function get_html_body($html) {
		$to_removed_array = array ("'<html[^>]*>'si", "'</html>'si", "'<body[^>]*>'si", "'</body>'si", "'<head[^>]*>.*?</head>'si", "'<style[^>]*>.*?</style>'si", "'<object[^>]*>.*?</object>'si",);
		$html = preg_replace($to_removed_array, '', $html);
		return $html;

	}


	/**
	 * Give it a full name and it tries to determine the First, Middle and Lastname
	 *
	 * @param	StringHelper $full_name A full name (default value empty string)
	 * @access public
	 * @return array array with keys first, middle and last
	 */

	public static function split_name($full_name) {
		$full_name = $full_name ?? "";
		if (strpos($full_name,',')) {
			
			$parts = explode(',',$full_name);
			$full_name = implode(' ',array_reverse($parts));			
		} 
		
		$full_name = trim(preg_replace("/[\s]+/", " ", $full_name));
		
		$name_arr = explode(' ', $full_name);

		$name['first_name'] = $full_name;
		$name['middle_name'] = '';
		$name['last_name'] = '';
		$count = count($name_arr);
		$last_index = $count -1;
		for ($i = 0; $i < $count; $i ++) {
			switch ($i) {
				case 0 :
					$name['first_name'] = $name_arr[$i];
					break;

				case $last_index :
					$name['last_name'] = $name_arr[$i];
					break;

				default :
					$name['middle_name'] .= $name_arr[$i].' ';
					break;
			}
		}
		$name['middle_name'] = trim($name['middle_name']);
		
		return $name;
	}

	/**
	 * Get the regex used for validating an email address
	 * Requires the Top Level Domain to be between 2 and 6 alphanumeric chars
	 *
	 * @param	none
	 * @access	public
	 * @return	StringHelper
	 */
	public static function get_email_validation_regex() {
		return \go\core\mail\Util::EMAIL_REGEX;
	}


	/**
	 * Check if an email adress is in a valid format
	 *
	 * @param	StringHelper $email E-mail address
	 * @deprecated since version 4.1
	 * @return bool
	 */
	public static function validate_email($email) {
		return \go\core\mail\Util::validateEmail($email);
	}

	/**
	 * Checks for empty string and returns stripe when empty
	 *
	 * @param	StringHelper $input Any string
	 * @access public
	 * @return string
	 */
	public static function empty_to_stripe($input) {
		if ($input == "") {
			return "-";
		} else {
			return $input;
		}
	}

	/**
	 * Formats a name in Group-Office
	 *
	 * @param string $sort_name string Vlaue can be last_name or first_name
	 * @return string base64 encoded string
	 */
	public static function format_name($last, $first = '', $middle = '', $sort_name='') {

		if(is_array($last))
		{
			$first = isset($last['first_name']) ? $last['first_name'] : '';
			$middle = isset($last['middle_name']) ? $last['middle_name'] : '';
			$last = isset($last['last_name']) ? $last['last_name'] : '';
		}
		if(\GO::user())
			$sort_name = $sort_name == '' ? \GO::user()->sort_name : $sort_name;
		else
			$sort_name ='first_name';

		if ($sort_name== 'last_name') {
			$name = 	!empty ($last) ? $last : '';
			if(!empty($last) && !empty($first))
			{
				$name .= ', ';
			}
			$name .= !empty ($first) ? $first : '';
			$name .= !empty ($middle) ? ' '.$middle : '';
		} else {
			$name = !empty ($first) ? $first : ' ';
			$name .= !empty ($middle) ? ' '.$middle.' ' : ' ';
			$name .= $last;
		}

		return trim($name);
	}


	/**
	 * Chop long strings with 3 dots
	 *
	 * Chops of the string after a given length and puts three dots behind it
	 * function editted by Tyler Gee to make it chop at whole words
	 *
	 * @param	StringHelper $string The string to chop
	 * @param	int $maxlength The maximum number of characters in the string
	 * @access public
	 * @return string
	 */

	public static function cut_string($string, $maxlength, $cut_whole_words = true, $append='...') {
		if(!isset($string)) {
			return "";
		}
		if (strlen($string) > $maxlength) {
			
			$substrFunc = function_exists('mb_substr') ? 'mb_substr' : 'substr';
			
			$maxlength -= strlen($append);
			
			$temp = $substrFunc($string, 0, $maxlength);
			if ($cut_whole_words) {
				if ($pos = strrpos($temp, ' ')) {
					return $substrFunc($temp, 0, $pos).$append;
				} else {
					return $temp = $substrFunc($string, 0, $maxlength).$append;
				}
			} else {
				return $temp.$append;
			}

		} else {
			return $string;
		}
	}

	/**
	 * Trim plain text to a maximum number of lines
	 *
	 * @param $string
	 * @param $maxlines
	 * @return string
	 */
	public static function limit_lines($string,$maxlines)
	{
		$string = str_replace("\r", '', $string);
		$lines = explode("\n", $string, $maxlines);
		$new_string =  implode("\n", $lines);

		if(strlen($new_string)<strlen($string))
		{
			$new_string .= "\n...";
		}
		return $new_string;
	}


	/**
	 * Convert plain text to HTML
	 *
	 * @param	?string $text Plain text string
	 * @access public
	 * @return string HTML formatted string
	 */
	public static function text_to_html(?string $text, $convert_links=true) {

		if(empty($text)) {
			return "";
		}
	
		if($convert_links)
		{
			$text = preg_replace("/\b(https?:\/\/[\pL0-9\.&\-\/@#;`~=%?:_\+,\)\(]+)\b/ui", '{lt}a href={quot}$1{quot} target={quot}_blank{quot} class={quot}normal-link{quot}{gt}$1{lt}/a{gt}', $text."\n");
			$text = preg_replace("/\b([\pL0-9\._\-]+@[\pL0-9\.\-_]+\.[a-z]{2,4})(\s)/ui", "{lt}a class={quot}normal-link{quot} href={quot}mailto:$1{quot}{gt}$1{lt}/a{gt}$2", $text);
		}

		//replace repeating spaces with &nbsp;		
		$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
		$text = str_replace('  ', '&nbsp;&nbsp;', $text);

		
		$text = nl2br(trim($text));
		//$text = str_replace("\r", "", $text);
		//$text = str_replace("\n", "", $text);

		//we dont use < and > directly with the preg functions because htmlspecialchars will screw it up. We don't want to use
		//htmlspecialchars before the pcre functions because email address like <mschering@intermesh.nl> will fail.

		$text = str_replace("{quot}", '"', $text);
		$text = str_replace("{lt}", "<", $text);
		$text = str_replace("{gt}", ">", $text);

		return $text;
	}

	public static function html_to_text($text, $link_list=true){

		$htmlToText = new Html2Text ($text);
		return $htmlToText->get_text($link_list);
	}



	/**
	 * Convert Dangerous HTML to safe HTML for display inside of Group-Office
	 *
	 * This also removes everything outside the body and replaces mailto links
	 *
	 * @todo do this all client side in the next email module. Using DomParser api?
	 * @param	string $html Plain text string
	 * @access public
	 * @return string HTML formatted string
	 */
	public static function sanitizeHtml($html, $preserveHtmlStyle = true) {
		$html = StringUtil::sanitizeHtml($html, $preserveHtmlStyle);
		// Check for smilies to be enabled by the user (settings->Look & feel-> Show Smilies)
		if(go()->getUserId() && go()->getAuthState()->getUser(['show_smilies'])->show_smilies)
			$html = StringHelper::replaceEmoticons($html,true);

		return $html;
	}
	
	
	public static function encodeHtml($str) {
		
		if(is_array($str)){
			return array_map(array("\GO\Base\Util\StringHelper", "encodeHtml"),$str);
		}
		
		if(!is_string($str)){
			return $str;
		}
		return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');		
	}

	/**
	 * Convert text to emoticons
	 *
	 * @param string $string String without emoticons
	 * @return string String with emoticons
	 */
	public static function replaceEmoticons($string, $html = false) {

		$len = strlen($string);

		if($len > 1000000) {
			//avoid problems on very large strings
			return $string;
		}
		$emoticons = array(
//				":@" => "angry.gif",
//				":d" => "bigsmile.gif",
//				"(brb)" => "brb.gif",
				//"(o)"=>"clock.gif",
				//"(c)"=>"coffee.gif", //conflicts with copyright
//				"(co)" => "computer.gif",
//				":s" => "confused.gif",
//				":'(" => "cry.gif",
//				":'|" => "dissapointed.gif",
//				":^)" => "dontknow.gif",
				//"(e)"=>"email.gif",
//				"+o(" => "ill.gif",
//				"(k)" => "kiss.gif",
//				"(l)" => "love.gif",
//				"(mp)" => "mobile.gif",
//				"(mo)" => "money.gif",
//				"(n)" => "notok.gif",
//				"(y)" => "ok.gif",
//				"<o)" => "party.gif",
//				"(g)" => "present.gif",
				":(" => "sad.gif",
				":-(" => "sad.gif",
//				"^o)" => "sarcasm.gif",
//				"^-o)" => "sarcasm.gif",
//				":$" => "shy.gif",
//				"|-)" => "sleepy.gif",
				":)" => "smile.gif",
				":-)" => "smile.gif",
//				"(*)" => "star.gif",
//				"(h)" => "sunglasses.gif",
//				":o" => "surprised.gif",
//				":-o" => "surprised.gif",
//				"(ph)" => "telephone.gif",
//				"*-)" => "thinking.gif",
				":p" => "tongue.gif",
				":-p" => "tongue.gif",
				";)" => "wink.gif",
				";-)" => "wink.gif",
		);

		foreach ($emoticons as $emoticon => $img) {
			$rel = 'views/Extjs3/themes/' . \GO::user()->theme . '/img/emoticons/normal/' . $img;
			if(!file_exists(\GO::config()->root_path.$rel)) {
				$rel = 'views/Extjs3/themes/Paper/img/emoticons/normal/' . $img;
			}
			
			$imgpath = \GO::config()->host . $rel;
			$imgstring = '<img src="' . $imgpath . '" alt="' . $emoticon . '" />';
			if ($html)
				$string = StringHelper::htmlReplace($emoticon, $imgstring, $string);
			else
				$string = preg_replace('/([^a-z0-9])' . preg_quote($emoticon) . '([^a-z0-9])/i', "\\1" . $imgstring . "\\2", $string);
		}
		return $string;
	}

	/**
	 * Replace string in html. It will leave strings inside html tags alone.
	 *
	 * @param string $search
	 * @param string $replacement
	 * @param string $html
	 * @return string
	 * @throws Exception
	 */
	public static function htmlReplace($search, $replacement, $html){
    $html = preg_replace_callback('/<[^>]*('.preg_quote($search).')[^>]*>/uis',array('GO\Base\Util\StringHelper', '_replaceInTags'), $html);
		if($html === null) {
			throw new \Exception(preg_last_error_msg());
		}
    $html = preg_replace('/([^a-z0-9])'.preg_quote($search).'([^a-z0-9])/i',"\\1".$replacement."\\2", $html);
    
    //$html = str_ireplace($search, $replacement, $html);
    return str_replace('{TEMP}', $search, $html);
  }

	/**
	 * Private callback function for htmlReplace.
	 * 
	 * @param array $matches
	 * @return string
	 */
  public static function _replaceInTags($matches)
  {
    return stripslashes(str_replace($matches[1], '{TEMP}', $matches[0]));
  }
	
	/**
	 * Detect known XSS attacks.
	 * 
	 * @param boolean $string
	 * @return boolean
	 * @throws Exception 
	 */
	public static function detectXSS($string) {
		return StringUtil::detectXSS($string);
	}

	/**
	 * Filter possible XSS attacks
	 * 
	 * @param string $data;
	 * @return string
	 */
	public static function filterXSS($data)
	{
		//echo $data; exit();
		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
		
		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[a-z]+[^>]*+>#iu', '$1>', $data);
		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
				
//
//		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
//	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '', $data);
//	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '', $data);

		//the next line removed valid stuff from the body
		//$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

		return $data;
	}
	
	/**
	 * Change HTML links to Group-Office links. For example mailto: links will call
	 * the Group-Office e-mail module if installed.
	 *
	 *
	 * @param	StringHelper $text Plain text string
	 * @access public
	 * @return string HTML formatted string
	 */

	public static function convertLinks($html)
	{
		$baseUrl = '';
		if(preg_match('/base href="([^"]+)"/', $html, $matches)){
			if(isset($matches[1]))
			{
				$baseUrl = $matches[1];
			}
		}

		
//		Don't strip new lines or it will mess up <pre> tags
//		$html = str_replace("\r", '', $html);
//		$html = str_replace("\n",' ', $html);
//		
		//strip line breaks inside html tags
		$html = preg_replace_callback('/<[^>]+>/sm',function($matches){
			$replacement = str_replace("\r", '', $matches[0]);
			return str_replace("\n",'  ', $replacement);
		}, $html);

//		$regexp="/<a[^>]*href=\s*([\"']?)(http|https|ftp|bf2)(:\/\/)(.+?)>/i";
		//$html = preg_replace($regexp, "<a target=$1_blank$1 class=$1blue$1 href=$1$2$3$4>", $html);

		$html = str_replace('<a ', '<a target="_blank" ', $html);

		if(!empty($baseUrl)){
			$regexp="/href=\s*('|\")(?![a-z]+:)/i";
			$html = preg_replace($regexp, "href=$1".$baseUrl, $html);
		}

		return $html;
	}

	/**
	 * This function generates a randomized password.
	 *
	 * @access static
	 *
	 * @param int|null $password_length
	 *
	 * @param string|null $characters_allow
	 * @param string|null  $characters_disallow
	 *
	 * @return string
	 * @throws Exception
	 */
	static function randomPassword(?int $password_length = 0, ?string $characters_allow = 'a-z,1-9', ?string $characters_disallow = 'i,o' ): string
	{

		if($password_length==0) {
			$password_length = \GO::config()->default_password_length;
		}

		// Generate array of allowable characters.
		$characters_allow = explode(',', $characters_allow);

		$array_allow = [];
		for ($i = 0; $i < count($characters_allow); $i ++) {
			if (substr_count($characters_allow[$i], '-') > 0) {
				$character_range = explode('-', $characters_allow[$i]);

				for ($j = ord($character_range[0]); $j <= ord($character_range[1]); $j ++) {
					$array_allow[] = chr($j);
				}
			} else {
				$array_allow[] = $characters_allow[$i];
			}
		}

		// Generate array of disallowed characters.
		$characters_disallow = explode(',', $characters_disallow);
		$array_disallow = [];
		for ($i = 0; $i < count($characters_disallow); $i ++) {
			if (substr_count($characters_disallow[$i], '-') > 0) {
				$character_range = explode('-', $characters_disallow[$i]);

				for ($j = ord($character_range[0]); $j <= ord($character_range[1]); $j ++) {
					$array_disallow[] = chr($j);
				}
			} else {
				$array_disallow[] = $characters_disallow[$i];
			}
		}

		// Generate array of allowed characters by removing disallowed
		// characters from array.
		$array_allow = array_diff($array_allow, $array_disallow);
		// Resets the keys since they won't be consecutive after
		// removing the disallowed characters.
		$array_allow = array_values($array_allow);

		$password = '';
		while (strlen($password) < $password_length) {
			$character = random_int(0, count($array_allow) - 1);
			// Characters are not allowed to repeat
			if (substr_count($password, $array_allow[$character]) == 0) {
				$password .= $array_allow[$character];
			}
		}
		return $password;
	}

	/**
	 * This function generates the view with a template
	 *
	 * @access static
	 *
	 * @param string $template
	 * @param string $objectarray
	 *
	 * @return $objectarray
	 */
	static function reformat_name_template($template, $name)
	{
		$keys = array_keys($name);

		$editedKeys = array_map(array("GO\Base\Util\String", "_addAccolades"), $keys);

		$res = trim(preg_replace('/\s+/', ' ',str_replace($editedKeys, array_values($name),$template)));

		$res = str_replace(array('()','[]'),'', $res);

		return $res;
	}

	static protected function _addAccolades($string)
	{
		return '{'.$string.'}';
	}
	
	/**
	 * Check the length of a string. Works with UTF8 too.
	 * 
	 * @param string $str
	 * @return int 
	 */
	public static function length($str){
		return function_exists("mb_strlen") ? mb_strlen($str, 'UTF-8') : strlen($str);
	}
	
	public static function substr($string, $start, $length=null){
		return function_exists("mb_substr") ? mb_substr($string, $start, $length) : substr($string, $start, $length);
	}
	
	
	/**
	 * Encode an url but leave the forward slashes alone
	 * 
	 * @param string $str
	 * @return string
	 */
	public static function rawurlencodeWithourSlash($str){
		$parts = explode('/', $str);
		
		$parts = array_map('rawurlencode', $parts);
		
		return implode('/', $parts);
	}
	
		/**
	 * Replace linebreaks with the given char
	 * 
	 * @param string $text
	 * @param string $replacement
	 * @return string
	 */
	public static function convertLineBreaks($text,$replacement=";"){

		// replace the linebreak (\r\n OR \n) to the replacement char
		$text = str_replace(array("\r\n","\n"),$replacement, $text);
		
		// Check if the replace action did not place the replacement twice after each other.
		// If so, then replace it with only a single replacement char.
		$doubleReplacement = $replacement.$replacement;
		$text = str_replace($doubleReplacement,$replacement, $text);

		return $text;
	}
	

}
