<?php
/**
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\tasks;
							
use Exception;
use Faker\Generator;
use go\core;
use go\core\model;
use go\core\model\Group;
use go\core\model\Link;
use go\core\model\Permission;
use go\core\model\User;
use go\core\orm\exception\SaveException;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\comments\Module as CommentsModule;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\Tasklist;
use go\modules\community\tasks\model\UserSettings;
use GO\Projects2\Model\Project;

class Module extends core\Module {
							
	public function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function autoInstall(): bool
	{
		return true;
	}

	public function defineListeners()
	{
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
		User::on(User::EVENT_BEFORE_DELETE, static::class, 'onUserDelete');
		User::on(User::EVENT_BEFORE_SAVE, static::class, 'onUserBeforeSave');
	}


	public function initListeners()
	{
		if(model\Module::isInstalled('legacy', 'projects2')) {
			$prj = new Project();
			$prj->addListener('beforedelete', self::class, 'onBeforeProjectDelete');
		}
	}



	public static function onMap(Mapping $mapping) {
		$mapping->addHasOne('tasksSettings', UserSettings::class, ['id' => 'userId'], true);
		$mapping->addScalar('taskPortletTaskLists', "tasks_portlet_tasklist", ['id' => 'userId']);
	}

	protected function rights(): array
	{
		return [
			'mayChangeTasklists', // allows Tasklist/set (hide ui elements that use this)
			'mayChangeCategories', // allows creating global  categories for everyone. Personal cats can always be created.
		];
	}

	protected function beforeInstall(model\Module $model): bool
	{
		// Share module with Internal group
		$model->permissions[Group::ID_INTERNAL] = (new Permission($model))
			->setRights(['mayRead' => true, 'mayChangeTasklists' => true, 'mayChangeCategories' => false]);

		return parent::beforeInstall($model);
	}


	/**
	 * @throws Exception
	 */
	public static function onUserDelete(core\db\Query $query) {
		Tasklist::delete(['createdBy' => $query]);
	}


	/**
	 * @throws Exception
	 */
	public static function onBeforeProjectDelete(Project $project): bool
	{
		$query = (new core\orm\Query())->where(['role' => Tasklist::Project, 'projectId' => $project->id]);
		$success = TaskList::delete($query);
		return $success;
	}


	public static function onUserBeforeSave(User $user)
	{
		if (!$user->isNew() && $user->isModified('displayName')) {
			$oldName = $user->getOldValue('displayName');
			$tasklist = Tasklist::find()->where(['createdBy' => $user->id, 'name' => $oldName])->single();
			if ($tasklist) {
				$tasklist->name = $user->displayName;
				$tasklist->save();
			}
		}
	}

	public function demo(Generator $faker)
	{
		$tasklists = Tasklist::find()->where(['role' => Tasklist::List]);

		foreach($tasklists as $tasklist) {
			$this->demoTasks($faker, $tasklist);
		}
	}

	/**
	 * @throws SaveException
	 * @throws Exception
	 */
	public function demoTasks(Generator $faker, Tasklist $tasklist, bool $withLinks = true) {

		$titles = [
			"Finish tasks module",
			"Call Michael about Energy project",
			"Order printer paper",
			"Create functional design",
			"Create technical design",
			"Create database design",
			"Order machine parts",
			"Order lunch",
			"Schedule meeting with client",
			"Discuss design with John",
			"Fix issue with automatic problem solver",
			"Prepare Weekly board meeting",
			"Test two factor authentication",
			"Perform weekly penetration tests on Group-Office",
			"Implement Oauth 2.0",
			"Implement Open ID",
			"Feature request on autofill email addresses",
			"Feature request SMIME encryption",
			"Discuss roadmap for next release",
			"Buy bigger screens",
			"Verify backups",
			"Perform weekly penetration tests on servers",
			"Prepare quote for solar panels module",
			"Prepare quote for Wind mill project",
			"Review graphical designs for Group-Office website",
			"Design checkout process",
			"Take out the trash",
			"Order more coffee",
		];

		$titleCount = count($titles);

		for($i = 0; $i < 5; $i ++ ) {
			echo ".";
			$task = new Task();
			$task->title = $titles[$faker->numberBetween(0, $titleCount - 1)];
			$task->createdBy = $tasklist->createdBy;
			$task->responsibleUserId = $task->createdBy;
			$task->start = $faker->dateTimeBetween("-1 years", "now");
			$task->due =  $faker->dateTimeBetween($task->start, "now");
			$task->tasklistId = $tasklist->id;
			$task->percentComplete = $faker->randomElement([0, 20, 50, 80, 100]);

			$task->createdAt = $faker->dateTimeBetween("-1 years", "now");
			$task->modifiedAt = $faker->dateTimeBetween($task->createdAt, "now");

			if(!$task->save()) {
				throw new SaveException($task);
			}

			if($withLinks && core\model\Module::isInstalled("community", "comments")) {
				CommentsModule::demoComments($faker, $task);
			}

			if($withLinks) {
				Link::demo($faker, $task);
			}
		}
	}


}