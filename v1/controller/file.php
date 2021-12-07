<?php 

class ControllerFile extends RestEndpoint {

	/**
	 * 	@OA\Get(
	 * 		path="/file",
	 * 		summary="Get file",
	 * 		tags={"File"},
	 * 
	 * 		@OA\Parameter(
	 * 			parameter="token",
	 * 			in="path",
	 * 			required=true,
	 *			name="token",
	 * 			@OA\Schema(
	 * 				type="string",
	 * 				format="uri"
	 * 			)
	 * 		),
	 * 
	 *     	@OA\Response( 
	 * 			response="200",
	 * 			description="Success",
	 * 			@OA\MediaType(
	 * 				mediaType="application/octet-stream"
	 * 			)
	 * 	   	),
	 * 
	 * 		@OA\Response(
	 * 			response="401",
	 * 			ref="#/components/responses/401"
	 * 		),
	 * 
	 * 		@OA\Response(
	 * 			response="404",
	 * 			ref="#/components/responses/404"
	 * 		)
	 * )
	 */
	protected function get() {
		$token = $this->getArg('token');

		if(null == $token) {
			$this->badRequest("URI is missing required parameter 'token'");
			return;
		}

		$payload = $this->jwt->decode($token);

		if(false == $this->authorizePayload($payload)) {
            $this->forbidden();
            return;
		}

		if(false == file_exists($payload->path)) {
			$this->notFound();
			return;
		}

		header("Content-Type: application/octet-stream");

		if($payload->filename)
			header('Content-disposition: attachment; filename="' . $payload->filename . '"');
		else
			header('Content-disposition: attachment; ');

        readfile($payload->path);
	}

	protected function post() {
		if('upload' == $this->uriAt(0))
			$this->uploadFile();
		else if('zip' == $this->uriAt(0))
			$this->zip();
	}

	/**
	 * @OA\Post(
	 * 		path="/file/upload",
	 * 		summary="Upload File",
	 * 		description="Uploads file to the server and returns a unique ID that represents this file. Attach this unique ID to requests to claim this file. Unclaimed files will be removed after 1 hour",
	 * 		tags={"File"},
	 * 
	 * 		@OA\Parameter(
	 * 			ref="#/components/parameters/authorization"
	 * 		),
	 * 
	 * 		@OA\RequestBody(
	 * 			@OA\MediaType(
	 * 				mediaType="multipart/form-data"
	 * 			)
	 * 		),
	 * 
	 * 		@OA\Response(
	 * 			response="200",
	 * 			description="Success",
	 * 			@OA\JsonContent(
	 * 				type="object",
	 * 				@OA\Property(
	 * 					property="data",
	 * 					type="array",
	 * 					description="Key - ID pairs",
	 * 					@OA\Items(
	 * 						@OA\Property(
	 * 							property="key",
	 * 							type="string",
	 * 							description="key sent with the request"
	 * 						),
	 * 						@OA\Property(
	 * 							property="id",
	 * 							type="string",
	 * 							description="unique id created by the server"
	 * 						)
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 * 
	 * 		@OA\Response(
	 * 			response="401",
	 * 			ref="#/components/responses/401"
	 * 		)
	 * )
	 */
	private function uploadFile() {
		$response = [];

		if(!file_exists(DIR_TEMP))
			mkdir(DIR_TEMP, 0755, true);

		# upload files
		foreach($this->request->files as $k => $f) {
			$file_id = uniqid();

			move_uploaded_file($f['tmp_name'], DIR_TEMP . time() . '__' . $f['name'] . '__' . $file_id);

			$response[] = [
				'key' => $k,
				'id' => $file_id
			];
		}

		# remove unclaimed files
		$files = glob(DIR_TEMP . '*');

		foreach($files as $f) {
			$split = explode('__', $f);
			$split[0] = substr($split[0], strrpos($split[0], '/') + 1); # convert absolute path to relative path

			if(time() - (int)$split[0] > 3600) # one hours
				unlink($f);
		}

		# return response
		$this->success([
			'data' => $response
		]);
	}

	/**
	 * @OA\Post(
	 * 		path="/file/zip",
	 * 		summary="Zip Files",
	 * 		description="Create a zip file from given file URIs and returns the URI for that zip file. Unauthorized or non-existing files will be skipped.",
	 * 		tags={"File"},
	 * 
	 * 		@OA\Parameter(
	 * 			ref="#/components/parameters/authorization"
	 * 		),
	 * 
	 * 		@OA\RequestBody(
	 * 			@OA\JsonContent(
	 * 				@OA\Property(
	 * 					property="data",
	 * 					type="object",
	 * 					@OA\Property(
	 * 						property="attributes",
	 * 						type="object",
	 * 						@OA\Property(
	 * 							property="files",
	 * 							type="array",
	 *							@OA\Items(
	 *								type="string",
	 *								format="uri"
	 *							)
	 *						)
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 * 
	 * 		@OA\Response(
	 * 			response="200",
	 * 			description="Success",
	 * 			@OA\JsonContent(
	 * 				type="object",
	 * 				@OA\Property(
	 * 					property="data",
	 * 					type="object",
	 * 					@OA\Property(
	 * 						property="uri",
	 * 						type="string",
	 * 						format="uri"
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 * 
	 * 		@OA\Response(
	 * 			response="401",
	 * 			ref="#/components/responses/401"
	 * 		)
	 * )
	 */
	private function zip() {
		if(null == $this->data()) {
			$this->badRequest("Post body parameter 'data' is missing");
			return;
		}

		if(null == $this->data()->attributes) {
			$this->badRequest("'attributes' is missing");
			return;
		}

		$attr = $this->data()->attributes;

		if(false == property_exists($attr, 'files')) {
			$this->badRequest("Required attribute 'files' is missing");
			return;
		}

		$file_list = $attr->files;

		$seperator = 'token=';

		$files_to_zip = array();

		foreach($file_list as $file) {
			if(false === strpos($file, $seperator)) {
				$this->badRequest('Bad file link');
				return;
			}

			$token = substr(
				$file, 
				strpos($file, $seperator) + strlen($seperator)
			);

			$payload = $this->jwt->decode($token);

			if($this->authorizePayload($payload)) {
				$files_to_zip[] = array(
					'path' => $payload->path,
					'filename' => $payload->filename
				);
			}
		}

		$archive_path = DIR_CACHE . uniqid() . '.zip';

		touch($archive_path);

		$archive = new ZipArchive();
		$archive->open($archive_path, ZipArchive::CREATE);
		foreach($files_to_zip as $file) {
			if(file_exists($file['path']))
				$archive->addFile($file['path'], $file['filename']);
		}
		$archive->close();

		$jwt = $this->jwt->encode(array(
			'ip' => $this->request->server['REMOTE_ADDR'],
			'given_at' => (new \DateTime())->format(DATE_ISO8601),
			'path' => $archive_path,
			'filename' => 'archive.zip'
		));

		$this->success(array(
			'data' => array(
				'uri' => HTTP_SERVER . 'file' . '?token=' . $jwt
			)
		));
	}

	protected function patch() {
		$this->notImplemented();
	}

	protected function delete() {
		$this->notImplemented();
	}

	protected function submoduleId() : int {
		return -1;
	}

	protected function filterSupportingFields() : array {
		return array();
	}

	protected function orderBySupportingFields() : array {
		return $this->filterSupportingFields();
	}

	/* helper */

	private function authorizePayload($payload) : bool {
		if($payload->ip != $this->request->server['REMOTE_ADDR'])
			return false;

		if((strtotime($payload->given_at) + $this->config->get('jwt_duration')) < time())
			return false;

		return true;
	}
}

?>