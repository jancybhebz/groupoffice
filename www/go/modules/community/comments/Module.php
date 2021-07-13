<?php
namespace go\modules\community\comments;

use go\core;
use go\core\cron\GarbageCollection;
use go\core\jmap\Entity;
use Faker;
use go\core\model\Group;
use go\core\model\Module as GoModule;
use go\modules\community\comments\model\Comment;

class Module extends core\Module {	

	public function getAuthor() {
		return "Intermesh BV";
	}

	public function autoInstall()
	{
		return true;
	}
	
	public function defineListeners() {
		GarbageCollection::on(GarbageCollection::EVENT_RUN, static::class, 'garbageCollection');
	}

	protected function afterInstall(GoModule $model) {
		
		if(!$model->findAcl()
						->addGroup(Group::ID_INTERNAL)
						->save()) {
			return false;
		}
		
		return parent::afterInstall($model);
	}
	
	public static function garbageCollection() {
//		$types = EntityType::findAll();
//
//		go()->debug("Cleaning up comments");
//		foreach($types as $type) {
//			if($type->getName() == "Link" || $type->getName() == "Search" ||  !is_a($type->getClassName(), Entity::class, true)) {
//				continue;
//			}
//
//			$cls = $type->getClassName();
//
//			if(is_a($cls,  ActiveRecord::class, true)) {
//				$tableName = $cls::model()->tableName();
//			} else{
//				$tableName = array_values($cls::getMapping()->getTables())[0]->getName();
//			}
//			$query = (new Query)->select('sub.id')->from($tableName);
//
//			$stmt = go()->getDbConnection()->delete('comments_comment', (new Query)
//				->where('entityTypeId', '=', $type->getId())
//				->andWhere('entityId', 'NOT IN', $query)
//			);
//			$stmt->execute();
//
//			go()->debug("Deleted ". $stmt->rowCount() . " comments for $cls");
//		}
	}

	public static function demoComments(Faker\Generator $faker, Entity $entity) {

		gc_collect_cycles();

		$faker = Faker\Factory::create();

		$users = core\model\User::find(['id'])->limit(10)->all();

		$userCount = count($users) - 1;

		$commentCount = $faker->numberBetween(1, 5);

		$date = $faker->dateTimeBetween("-2 years", "-" . $commentCount . "days");

		for($i = 0; $i < $commentCount; $i++) {
			$user = $users[$faker->numberBetween(0, $userCount)];

			$comment = new Comment();
			$comment->setEntity($entity);
			$comment->text = nl2br($faker->realtext);
			$comment->createdBy = $user->id;
			$comment->date = $date;

			$date = $date->add(new \DateInterval("P1D"));

			$comment->save();
		}
	}
}
