<?php

namespace go\core\orm;

/**
 * Relation class
 * 
 * Defines a relation from one model to another. * 
 */
class Relation {

	const TYPE_HAS_ONE = 0;
	const TYPE_ARRAY = 1;
	const TYPE_MAP = 2;
	const TYPE_SCALAR = 3;


	/**
	 * The name of the relation
	 * 
	 * @var string 
	 */
	public $name;

	/**
	 * The class name of the {@see Property} or {@see Entity} this relation points to.
	 * 
	 * @var string 
	 */
	public $entityName;

	/**
	 * Associative array with key map
	 * 
	 * ```
	 * ['fromColumn' => 'toColumn']
	 * ```
	 * 
	 * @var array 
	 */
	public $keys;

	/**
	 * Constructor
	 * 
	 * @param string $name The name of the relation
	 * @param string $entityName The class name of the {@see Property} this relation points to.
	 * @param array $keys Associative array with key map
	 * ```
	 * ['fromColumn' => 'toColumn']
	 * ```
	 * 
	 * @param boolean $many Indicates if this relation is one to many
	 */
	public function __construct($name, $entityName, array $keys, $type = self::TYPE_HAS_ONE) {
		$this->name = $name;
		
		if(!is_subclass_of($entityName, Property::class, true)) {
			throw new \Exception($entityName . ' must extend '. Property::class);
		}
		
		if(is_subclass_of($entityName, Entity::class, true)) {
			throw new \Exception($entityName . ' may not be an '. Entity::class .'. Only '. Property::class .' objects can be mapped.');
		}
		
		$this->entityName = $entityName;
		$this->keys = $keys;
		$this->type = $type;
	}
}
