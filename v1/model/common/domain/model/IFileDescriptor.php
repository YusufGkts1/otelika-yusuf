<?php

namespace model\common\domain\model;

interface IFileDescriptor {

	public function path() : string;

	public function name() : string;

	public function extension() : string;
}

?>