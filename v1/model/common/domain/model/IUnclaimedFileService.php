<?php

namespace model\common\domain\model;

interface IUnclaimedFileService {

	/**
	 * @return bool whether an unclaimed file with id $id exists
	 */
	public function exists(string $id) : bool;

	/**
	 * Claim unclaimed file with id $id and move this file to $path
	 * 
	 * @param string $id id of the file to be claimed
	 * @param string $path where the claimed file should be moved to
	 * @return IFileDescriptor
	 */
	public function claim(string $id, string $path) : IFileDescriptor;
}


?>