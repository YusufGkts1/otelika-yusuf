<?php 

namespace model\common\infrastructure;

use model\common\application\IFileDirectAccessLinkProvider;

class FileDirectAccessLinkProvider implements IFileDirectAccessLinkProvider {

	private \JWToken $jwt;
	private string $url;
	private string $ip;

	function __construct(\JWToken $jwt, string $url, string $ip) {
		$this->jwt = $jwt;
		$this->url = $url;
		$this->ip = $ip;
	}

	public function getLink(string $path, ?string $name, ?string $extension): string {
		$name_ = $name;

		if(null == $name_)
			$name_ = 'adsız';

		if($extension)
			$name_ .= '.' . $extension;

		$jwt = $this->jwt->encode(array(
			'ip' => $this->ip,
			'given_at' => (new \DateTime())->format(DATE_ISO8601),
			'path' => $path,
			'filename' => $name_
		));

		return $this->url . '?token=' . $jwt;
	}
}

?>