<?php

use \model\common\infrastructure\SubmoduleRepository;
use \model\common\domain\model\ModuleId;
use \model\common\domain\model\SubmoduleId;
use \model\common\domain\model\Submodule;
use \model\common\domain\model\ISubmoduleRepository;

use PHPUnit\Framework\TestCase;


class SubmoduleRepositoryTest extends TestCase {

	private static \DB $db;

	public static function setUpBeforeClass() : void {
        global $framework;
        $config = $framework->get('config');

        self::$db = new \DB(
            $config->get('db_common_type'),
            $config->get('db_common_hostname'),
            $config->get('db_common_username'),
            $config->get('db_common_password'),
            $config->get('db_common_database'),
            $config->get('db_common_port')
        );

   		self::$db->command("DELETE FROM submodule");
	}

	public function testIfSubmoduleOfDbMatchenWithFindById() {

		$submodule_repository = new SubmoduleRepository();

		self::$db->command("INSERT INTO submodule (id, module_id, name) VALUES ('4', '1', 'submodule_name')");

		$submodule = $submodule_repository->findById(new SubmoduleId(4));

		$this->assertTrue($submodule->equals(new Submodule(new SubmoduleId(4), new ModuleId(1), 'new_submodule')));
	
	}

	public function testIfSubmoduleReturnsNullIfIdIsNull() {

		$submodule_repository = new SubmoduleRepository();

		$submodule = $submodule_repository->findById(new SubmoduleId(2));

		$this->assertEmpty($submodule);

	}

}

?>