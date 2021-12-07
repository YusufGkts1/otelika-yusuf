<?php 


use model\IdentityAndAccess\domain\model\exception\PersonnelTcnoIsNotUniqueException;
use model\IdentityAndAccess\domain\model\exception\PersonnelEmailIsNotUniqueException;
use model\IdentityAndAccess\domain\model\exception\PersonnelPhoneIsNotUniqueException;
use model\IdentityAndAccess\domain\model\PersonnelDomainService;
use model\IdentityAndAccess\domain\model\PersonnelId;
use model\IdentityAndAccess\domain\model\Department;
use model\IdentityAndAccess\domain\model\DepartmentId;
use model\IdentityAndAccess\domain\model\Personnel;
use model\IdentityAndAccess\domain\model\IPersonnelRepository;

use PHPUnit\Framework\TestCase;
use \model\common\ExceptionCollection;



class PersonnelDomainServiceTest extends TestCase {

	
	private PersonnelDomainService $personnel_domain_service;
	
	protected function setUp() : void {

		$personnel_repository = $this->createMock(IPersonnelRepository::class);
		$personnel_repository->method('existsWithTcno')->willReturn(true);
		$personnel_repository->method('existsWithEmail')->willReturn(true);
		$personnel_repository->method('existsWithPhone')->willReturn(true);

	
		$this->personnel_domain_service = new PersonnelDomainService($personnel_repository);
	}


	public function testTcnoCannotBeDuplicateOnRegister() {

		$this->expectException(PersonnelTcnoIsNotUniqueException::class);

		try {
			$this->personnel_domain_service->registerPersonnel(null, null, new DepartmentId(1), 'jon', 'doe', '11223344556', 'male', '004939159915', 'jon_doe@mail.com', true);
		}
	  	catch (ExceptionCollection $e) {
	  		$this->throwFromExceptionCollection($e, PersonnelTcnoIsNotUniqueException::class);
		}
	}


	public function testEmailCannotBeDuplicateOnRegister() {

		$this->expectException(PersonnelEmailIsNotUniqueException::class);

		try{
			$this->personnel_domain_service->registerPersonnel(null, null,  new DepartmentId(1),'jon', 'doe', '11223344556', 'male', '004939159915', 'jon_doe@mail.com', true);
		}

		catch(ExceptionCollection $e) {
			$this->throwFromExceptionCollection($e, PersonnelEmailIsNotUniqueException::class);
		}
		
	}


	public function testPhoneCannotBeDuplicateOnRegister() {

		$this->expectException(PersonnelPhoneIsNotUniqueException::class);

		try{
			$this->personnel_domain_service->registerPersonnel(null, null, new DepartmentId(1), 'jon', 'doe', '11223344556', 'male', '004939159915', 'jon_doe@mail.com', true);
		}

		catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, PersonnelPhoneIsNotUniqueException::class);
		}
	
	}	


	public function testTcnoCannotBeDuplicateOnUpdate() {

		$this->expectException(PersonnelTcnoIsNotUniqueException::class);

		try{

			$department = new Department(new DepartmentId(1), 'first_dep', null, null, 1, 1,1,1);

			$personnel = new Personnel(new PersonnelId(1), null,new DepartmentId(1),  true,null, 'firstname', 'lastname', '00000000000', 'female', '123187289313', 'adsiosadjad@ijasdsad.com', null, null);
			$this->personnel_domain_service->updatePersonnel($personnel, null ,$department, null, 'jon', 'doe', '00000000000', 'male', '004939159915', 'jon_doe@mail.com', true);
		}

		catch(ExceptionCollection $e) {
			$this->throwFromExceptionCollection($e, PersonnelTcnoIsNotUniqueException::class);
		}
	}


	public function testPhoneCannotBeDuplicateOnUpdate() {

		$this->expectException(PersonnelPhoneIsNotUniqueException::class);

		try{

			$department = new Department(new DepartmentId(1), 'first_dep', null, null, 1, 1,1,1);

			$personnel = new Personnel(new PersonnelId(1), null, new DepartmentId(1), true, null, 'firstname', 'lastname', '00000000000', 'female', '1231873289313', 'adsiosadjad@ijasdsad.com', null, null);
			$this->personnel_domain_service->updatePersonnel($personnel, null, $department, null, 'jon', 'doe', '00000000000', 'male', '004939159915', 'jon_doe@mail.com', true);
		}

		catch(ExceptionCollection $e) {
			$this->throwFromExceptionCollection($e, PersonnelPhoneIsNotUniqueException::class);
		}
	}


	public function testEmailCannotBeDuplicateOnUpdate() {

		$this->expectException(PersonnelEmailIsNotUniqueException::class);

		try{

			$department = new Department(new DepartmentId(1), 'first_dep', null, null, 1, 1,1,1);

			$personnel = new Personnel(new PersonnelId(1), null, new DepartmentId(1), true,  null, 'firstname', 'lastname', '00000000000', 'female', '123187289313', 'adsiosadjad@ijasdsad.com', null, null);
			$this->personnel_domain_service->updatePersonnel($personnel, null, $department, null, 'jon', 'doe', '00000000000', 'male', '004939159915', 'jon_doe@mail.com', true);
		}

		catch(ExceptionCollection $e) {
			$this->throwFromExceptionCollection($e, PersonnelEmailIsNotUniqueException::class);
		}
	}


	public function testPhoneCannotBeDuplicateOnUpdateSelf() {

		$this->expectException(PersonnelPhoneIsNotUniqueException::class);

		try{
			$personnel = new Personnel(new PersonnelId(1), null, new DepartmentId(1), true, null, 'firstname', 'lastname', '00000000000', 'female', '094888039159915', 'adsiosadjad@ijasdsad.com', null, null);
			$this->personnel_domain_service->updateSelf($personnel, "asdada@asdad.com", "12313212313132");

		}

		catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, PersonnelPhoneIsNotUniqueException::class);
		}
	}


	public function testEmailCannotBeDuplicateOnUpdateSelf() {

		$this->expectException(PersonnelEmailIsNotUniqueException::class);

		try{
			$personnel = new Personnel(new PersonnelId(1), null,new DepartmentId(1),  true, null, 'firstname', 'lastname', '00000000000', 'female', '094888039159915', 'adsiosadjad@ijasdsad.com', null, null);
			$this->personnel_domain_service->updateSelf($personnel, "asdada@asdad.com", "12313212313132");

		}

		catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, PersonnelEmailIsNotUniqueException::class);
		}
	}



	private function throwFromExceptionCollection($exception_collection, $exception) {
			foreach($exception_collection->getExceptions() as $e) {
				if(get_class($e) == $exception) {
				   throw new $exception;
			}
		}
	}


}


?>