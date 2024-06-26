<?php
namespace go\core;

use go\core\model\Link;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\EmailAddress;

class TemplateParserTest extends \PHPUnit\Framework\TestCase
{
	private function getAddressBook() {
		$addressBook = AddressBook::find()->where(['name' => 'Test'])->single();
		if(!$addressBook) {
			$addressBook = new AddressBook();
			$addressBook->name = "Test";
			$success = $addressBook->save();

			$this->assertEquals(true, $success);
		}
		return $addressBook;
	}

	public function testLinks()
	{
		$addressBook = $this->getAddressBook();

		$contact1 = new Contact();
		$contact1->addressBookId = $addressBook->id;
		$contact1->firstName = "John";
		$contact1->lastName = "Doe";

		$contact1->addresses[0] = $a = new Address($contact1);

		$a->type = Address::TYPE_POSTAL;
		$a->address =	"Street 1";
		$a->city = "Den Bosch";
		$a->zipCode = "5222 AE";
		$a->countryCode = "NL";


		$contact1->emailAddresses[] = (new EmailAddress($contact1))
			->setValues(["type" => EmailAddress::TYPE_WORK, 'email' => 'work@intermesh.localhost']);

		$contact1->emailAddresses[] = (new EmailAddress($contact1))
			->setValues(["type" => EmailAddress::TYPE_HOME, 'email' => 'home@intermesh.localhost']);

		$contact1->emailAddresses[] = (new EmailAddress($contact1))
			->setValues(["type" => EmailAddress::TYPE_HOME, 'email' => 'aaa@intermesh.localhost']);

		$success = $contact1->save();
		$this->assertEquals(true, $success);


		$contact2 = new Contact();
		$contact2->addressBookId = $addressBook->id;
		$contact2->firstName = "Linda";
		$contact2->lastName = "Smith";
		$success = $contact2->save();
		$this->assertEquals(true, $success);

		Link::create($contact1, $contact2);

		$tplParser = new TemplateParser();
		$tplParser->addModel('contact', $contact1);

		$tpl = '[assign firstContactLink = contact | links:Contact | first]{{firstContactLink.name}}';

		$str = $tplParser->parse($tpl);

		$this->assertEquals($contact2->name, $str);

		$tpl = '[assign address = contact.addresses | filter:type:"postal" | first]{{address.zipCode}}';

		$str = $tplParser->parse($tpl);

		$this->assertEquals($a->zipCode, $str);

		$tpl = '[assign address1 = contact.addresses | filter:type:"postal" | first]{{address1.zipCode}}[assign address = contact]{{address.addresses[0].zipCode}}';

		$str = $tplParser->parse($tpl);

		$this->assertEquals($a->zipCode.$a->zipCode, $str);


		$tpl = '{{contact.addresses |  filter:type:"postal" | first | prop:"zipCode"}}';
		$zipCode = $tplParser->parse($tpl);
		$this->assertEquals($a->zipCode, $zipCode);

		$tpl = '{{contact.addresses |  filter:type:"notexisting" | first | prop:"zipCode"}}';
		$notexistingType = $tplParser->parse($tpl);
		$this->assertEquals(null, $notexistingType);

		$tpl = '{{contact.addresses |  filter:type:"postal" | first | prop:"notexisting"}}';
		$notexistingProp = $tplParser->parse($tpl);
		$this->assertEquals(null, $notexistingProp);


		$tpl =  '{{contact.id | Entity:Contact | prop:emailAddresses | first | prop:email}}';
		$firstEmail = $tplParser->parse($tpl);
		$this->assertEquals($contact1->emailAddresses[0]->email, $firstEmail);


		$tpl =  '{{contact.id | Entity:Contact | prop:emailAddresses | sort:email | first | prop:email}}';
		$firstSortedEmail = $tplParser->parse($tpl);
		$this->assertEquals($contact1->emailAddresses[2]->email, $firstSortedEmail);

		$tpl =  '{{contact.id | Entity:Contact | prop:emailAddresses | rsort:type:home | first | prop:type}}';
		$home = $tplParser->parse($tpl);
		$this->assertEquals(EmailAddress::TYPE_HOME, $home);
	}



}