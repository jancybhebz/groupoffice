<?php

namespace go\core\event;

use go\core\App;
use go\core\model\Module;
use go\core\orm\Property;
use go\core\Singleton;

/**
 * Contains and executes all static event listeners
 * 
 * Static event listeners can be set defined by any class that implements the
 * {@see EventListenerInterface}
 * 
 * This class is not used directly. Objects can use the {@see EventEmiterTrait} 
 * to emit events. Because we need all listeners together in one object this 
 * singleton class  holds them all.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Listeners extends Singleton {

	protected $listeners;


	/**
	 * Clears all listeners
	 *
	 * @return void
	 */
	public function clear() {
		$this->listeners = null;
	}

	/**
	 * Add an event listener
	 *
	 * @param string $firingClass
	 * @param string $event Defined in constants prefixed by EVENT_
	 * @param string $listenerClass
	 * @param string $method
	 */
	public function add(string $firingClass, string $event, string $listenerClass, string $method) {

		$this->checkInit();

		if (!isset($this->listeners[$firingClass][$event])) {
			$this->listeners[$firingClass][$event] = [];
		}
		$this->listeners[$firingClass][$event][] = [$listenerClass, $method];		
	}


	private function checkInit() {
		if(isset($this->listeners)) {
			return;
		}

		$this->listeners = App::get()->getCache()->get('listeners-2');

		if($this->listeners !== null) {
			return;
		}		

		$this->init();
	}

	/**
	 * Runs through all Module.php files and calls {@see \go\core\Base::defineListeners()}
	 * 
	 * Then stores all these listeners in the cache.
	 */
	public function init() {		

		$this->listeners = [];

		//disable events to prevent recursion
		go()->disableEvents();

		foreach (Module::find(['id', 'name', 'package', 'version', 'enabled'], true)->where(['enabled' => true]) as $module) { /* @var $module Module */

			if(!isset($module->package)) {//backwards compatibility hack. Remove once refactored.
				
				$cls = "GO\\" . ucfirst($module->name) . "\\" . ucfirst($module->name).'Module';
				if(!class_exists($cls)) {
					continue;
				}
				if(method_exists($cls, 'defineListeners')){
					$o = new $cls;
					$o->defineListeners();
				}
				continue;
			}

			$module->module()->defineListeners();
		}

		//disable events to prevent recursion
		go()->enableEvents();

		
		App::get()->getCache()->set('listeners-2', $this->listeners);		
	}

	/**
	 * Fire an event and execute all listeners
	 *
	 * @param string $calledClass
	 * @param string $traitUser
	 * @param string $event
	 * @param array $args
	 * @return mixed Returns the last listener return value or false if one of the listeners explicitly returns false
	 */
	public function fireEvent(string $calledClass, string $traitUser, string $event, array $args)
	{
		$this->checkInit();

		$returnVal = null;

		if (isset($this->listeners[$calledClass][$event])) {
			foreach ($this->listeners[$calledClass][$event] as $listener) {	
//				App::get()->log("Event '$calledClass::$event' calls listener $listener[0]::$listener[1]");
				$return = call_user_func_array($listener, $args);
				if ($return === false) {
					App::get()->warn("Listener returned false for event " . $event . " " . var_export($listener, true));
					return false;
				}

				if($return != null) {
					$returnVal = $return;
				}
			}
		}
		
		//recurse up to the parents until the class is found that uses the eventemitter trait.
		//This way you can use go\core\orm\Entity::on(EVENT_SAVE) for all entities.

		// An exception is made for Property::EVENT_MAPPING because sometimes you don't want to inherit all the
		// dynamic properties in an extended model.

		if($calledClass != $traitUser) { // && $event != Property::EVENT_MAPPING) {
			$parent = get_parent_class($calledClass);
			if($parent) {
				$parentReturn = $this->fireEvent($parent, $traitUser, $event, $args);
				if($parentReturn === false) {
					return false;
				}
				if($parentReturn) {
					$returnVal = $parentReturn;
				}
			}
		}
		return $returnVal;
	}
}
