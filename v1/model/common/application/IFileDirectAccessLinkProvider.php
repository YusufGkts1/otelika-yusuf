<?php 

namespace model\common\application;

interface IFileDirectAccessLinkProvider {
	/**
	 * @var string $path path of the file to be provided
	 * @var string $name name to be put in the response
	 * @var string $extension extension to be put in the response
	 */
	public function getLink(string $path, ?string $name, ?string $extension) : string;
}

?>