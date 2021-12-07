<?php

use DB\QueryFilter as DBQueryFilter;
use DB\QueryObject as DBQueryObject;
use \model\auth\Session;

use \model\IdentityAndAccess\application\AuthorizationService;

use model\common\QueryObject;
use model\common\QueryFilter;
use model\common\ExceptionCollection;

abstract class RestEndpoint extends Controller
{

	private ?AuthorizationService $authorization_service = null;
	private ?Session $session_service = null;

	private $attributes = null;

	public function index()
	{
		$this->uri = func_get_args();

		// Authorization is not required if submoduleId is not greater than 0
		if ($this->requiresAuhorization()) {
			$authorization_service = $this->authorizationService();
			$session_service = $this->sessionService();

			$personnel_id = $session_service->getPersonnelId($this->getBearerToken());
		}

		try {
			$this->uow->begin();

			switch ($this->requestMethod()) {
				case 'GET':
					if ($this->requiresAuhorization() && false == $authorization_service->canView($personnel_id, $this->submoduleId())) {
						$this->forbidden();
						return;
					}

					$this->get();
					break;
				case 'POST':
					if ($this->requiresAuhorization() && false == $authorization_service->canCreate($personnel_id, $this->submoduleId())) {
						$this->forbidden();
						return;
					}

					$this->post();
					break;
				case 'PATCH':
					if ($this->requiresAuhorization() && false == $authorization_service->canUpdate($personnel_id, $this->submoduleId())) {
						$this->forbidden();
						return;
					}

					$this->patch();
					break;
				case 'DELETE':
					if ($this->requiresAuhorization() && false == $authorization_service->canDelete($personnel_id, $this->submoduleId())) {
						$this->forbidden();
						return;
					}

					$this->delete();
					break;
			}

			$this->uow->commit();
		} catch (ExceptionCollection $exception_collection) {
			$this->uow->rollback();

			$errors = array();

			$errors_without_duplicates = array();

			foreach ($exception_collection->getExceptions() as $exception) {
				$exists = false;

				foreach ($errors_without_duplicates as $err) {
					if ($err->getCode() == $exception->getCode()) {
						$exists = true;
						break;
					}
				}

				if (false == $exists)
					$errors_without_duplicates[] = $exception;
			}

			foreach ($errors_without_duplicates as $exception) {
				$errors[] = array(
					'code' => $exception->getCode(),
					'detail' => $exception->getMessage()
				);
			}

			$this->error($errors);
		} catch (NotFoundException $exception) {
			$this->uow->rollback();

			throw $exception;

			$this->notFound(array(
				'code' => $exception->getCode(),
				'detail' => $exception->getMessage()
			));
		} catch (AuthorizationException $exception) {
			$this->uow->rollback();

			$this->unauthorized(array(
				'code' => $exception->getCode(),
				'detail' => $exception->getMessage()
			));
		} catch (Exception $exception) {
			$this->uow->rollback();
			 throw $exception;
			$this->error(array([
				'code' => $exception->getCode(),
				'detail' => $exception->getmessage()
			]));
		}
	}

	protected abstract function get();

	protected abstract function post();

	protected abstract function patch();

	protected abstract function delete();

	protected abstract function submoduleId(): int;

	protected function requiresAuhorization()
	{
		return $this->submoduleId() > 0;
	}

	protected function uriAt(int $n)
	{
		if (false == isset($this->uri[$n]))
			return null;
		else
			return $this->uri[$n];
	}

	protected function getArg(string $name, bool $omittable = true)
	{
		$args = array();

		foreach ($this->request->get as $key => $arg)
			$args[strtolower($key)] = $arg;

		if (false == isset($args[$name])) {
			if ($omittable)
				return null;
			else
				throw new \Exception("Missing required query parameter '" . $name . "'");
		}

		return $args[$name];
	}

	protected function data()
	{
		$contents = json_decode(file_get_contents("php://input"));

		if (null == $contents)
			return null;

		if (false == isset($contents->data))
			return null;

		return $contents->data;
	}

	protected function getData(string $field, bool $omittable = false)
	{
		if (null == $this->data())
			throw new \Exception("Body is missing required field 'data'");

		if (property_exists($this->data(), $field))
			return $this->data()->{$field};
		else {
			if (false == $omittable)
				throw new \Exception("Missing required data field '" . $field . "'");
			else
				return null;
		}
	}

	protected function dataIsSet(string $field)
	{
		if (property_exists($this->data(), $field))
			return true;
		else
			return false;
	}

	protected function attributes()
	{
		if (null == $this->attributes) {
			if (null == $this->data())
				throw new \Exception("Required body parameter 'data' is missing");

			if (false == property_exists($this->data(), 'attributes'))
				throw new \Exception("Required body parameter 'data.attributes' is missing");

			$this->attributes = $this->data()?->attributes;
		}

		return $this->attributes;
	}

	protected function getAttr(string $attribute, bool $omittable = false)
	{
		if (property_exists($this->attributes(), $attribute))
			return $this->attributes()->{$attribute};
		else {
			if (false == $omittable)
				throw new \Exception("Missing required attribute '" . $attribute . "'");
			else
				return null;
		}
	}

	protected function attrIsSet(string $attribute): bool
	{
		if (property_exists($this->attributes(), $attribute))
			return true;
		else
			return false;
	}

	protected function requestMethod()
	{
		return $this->request->server['REQUEST_METHOD'];
	}

	protected function success($data, $code = 200)
	{
		$response = $data;

		$this->response->addHeader('HTTP/2 ' . $code);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response, JSON_UNESCAPED_UNICODE));
	}

	protected function raw($data, $code = 200, $content_type = null)
	{
		$response = $data;

		$this->response->addHeader('HTTP/2 ' . $code);

		if ($content_type)
			$this->response->addHeader('Content-Type: ' . $content_type);

		$this->response->setOutput($response);
	}

	protected function noContent()
	{
		$response = array();

		$this->response->addHeader('HTTP/2 204');
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	protected function error($errors, $code = 422)
	{
		$response = [
			"errors" => $errors
		];

		$this->response->addHeader('HTTP/2 ' . $code);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	protected function notFound($error = array())
	{
		if (null == $error) {
			$error = array(
				"detail" => "Requested resource was not found"
			);
		}

		$this->error(array($error), 404);
	}

	protected function forbidden()
	{
		$this->error(array([
			"detail" => "Forbidden"
		]), 403);
	}

	protected function unauthorized($error = array())
	{
		if (null == $error) {
			$error = array(
				"detail" => "You are not authorized"
			);
		}

		$this->error(array(
			$error
		), 401);
	}

	protected function badRequest(string $message = "Bad request")
	{
		$this->error(array([
			"detail" => $message
		]));
	}

	protected function notImplemented(string $message = "Not implemented")
	{
		$this->error(array([
			'detail' => $message
		]), 501);
	}

	protected function operator() : ?Operator {
		if($this->session->isset('operator'))
			return $this->session->get('operator');
		else
			return null;
	}

	protected function missingParameters(ReflectionMethod $method, object $source): bool
	{
		foreach ($method->getParameters() as $param) {
			$param_name = $param->getName();
			if (false == property_exists($source, $param_name))
				return true;
		}

		return false;
	}

	/* filters */
	protected abstract function orderBySupportingFields(): array;

	protected abstract function filterSupportingFields(): array;

	protected function pageSizeLimit(): ?int
	{
		return null;
	}

	protected function queryObject()
	{
		return new QueryObject(
			$this->orderBy(),
			$this->pageSize(),
			($this->pageNumber() - 1) * $this->pageSize(),
			$this->filters(),
			$this->andFilters()
		);
	}

	protected function queryServiceQueryObject(): ?DBQueryObject
	{
		return new DBQueryObject(
			$this->queryServiceSort(),
			$this->pageSize(),
			($this->pageNumber() - 1) * $this->pageSize(),
			$this->queryServiceFilters(),
			array(),
			$this->queryServiceIncludes(),
			$this->andFilters()
		);
	}

	protected function queryServiceSort()
	{
		$sort = array();

		if (null == $this->getArg('sort', true))
			return $sort;

		$split = explode(',', $this->getArg('sort', true));

		foreach ($split as $field) {
			if (str_starts_with($field, '-'))
				$sort[] = array(
					substr($field, 1) => 'DESC'
				);
			else
				$sort[] = array(
					$field => 'ASC'
				);
		}

		return $sort;
	}

	/**
	 * @return DBQueryFilter[]
	 */
	protected function queryServiceFilters(): array
	{
		$filters = array();

		if (null == $this->getArg('filter', true))
			return $filters;

		foreach ($this->getArg('filter', true) as $key => $filter) {
			if (is_array($filter[key($filter)]))
				foreach ($filter[key($filter)] as $f) {
					$filters[] = new DBQueryFilter(
						$key,
						key($filter),
						$f
					);
				}
			else {
				$filters[] = new DBQueryFilter(
					$key,
					key($filter),
					$filter[key($filter)]
				);
			}
		}

		return $filters;
	}

	protected function queryServiceIncludes()
	{
		if (null == $this->getArg('include', true))
			return array();

		$args = explode(',', $this->getArg('include', true));

		$include = array();

		foreach ($args as $arg) {

			$already_included = false;
			$is_inclusive = false;

			if (in_array($arg, $include) !== false) {
				$already_included = true;
			}

			foreach ($include as $in => $i) {
				if (strpos($arg, $i) === 0) {
					$include[$in] = $arg;
					$is_inclusive = true;
				}
			}

			if (false == $already_included && false == $is_inclusive)
				$include[] = $arg;
		}

		// remove duplicate values and reindex
		$formatted = array_values(array_unique($include));

		return $formatted;
	}

	protected function include(): ?array
	{
		if (null == $this->getArg('include'))
			return array();

		$args = explode(',', $this->getArg('include'));

		$include = array();

		foreach ($args as $arg) {
			if (false == in_array($arg, $include))
				$include[] = $arg;

			$inc = implode('.', explode('.', $arg, -1));

			while (strlen($inc) > 0) {
				if (false == in_array($inc, $include))
					$include[] = $inc;

				$inc = implode('.', explode('.', $inc, -1));
			}
		}

		return $include;
	}

	/**
	 * $mapping array must be of following format:
	 * array (
	 *      array[RESOURCE_NAME] => [
	 *          getter => [
	 *              array[GETTER_NAME] => function
	 *          ],
	 *          include => [
	 *              array[RESOURCE_NAME] => GETTER_NAME
	 *          ]
	 *      ]
	 * )
	 * 
	 * Example $mapping object:
	 * 
	 * array(
	 *      'feature' => [
	 *          'getters' => [
	 *              'single_by_feature_id' => function
	 *          ]
	 *          'structure',
	 *          'numbering'
	 *      ],
	 *      'structure' => [
	 *          'feature',
	 *          'numbering',
	 *          'csbm'
	 *      ],
	 *      'numbering' => [
	 *          'feature',
	 *          'structure',
	 *          'independent_section'
	 *      ]
	 *  );
	 */
	protected function populate(array $mapping)
	{
	}

	protected function isIncluded(string $inc): bool
	{
		return in_array($inc, $this->include());
	}

	/**
	 * a.b.c seklinde bir relation verildiginde
	 * bu relation'in baslangicindan `context`
	 * bolumunu cikarar.
	 * 
	 * ornek:
	 *      includeContext(['a.b.c', 'a.b.c.d'], 'a.b') => ['c', 'c.d']
	 */
	protected function includeContext(array $current, string $context): array
	{
		return array_map(function ($relation) use ($context) {
			return substr($relation, strlen($context) + 1);
		}, array_filter($current, function ($relation) use ($context) {
			return strpos($relation, $context) === 0;
		}));
	}

	/**
	 * Yazilacack testler:
	 *  buraya sql injection dene
	 */

	/**
	 * @return QueryFilter[]
	 */
	protected function filters(): array
	{
		$filters = array();

		if (null == $this->getArg('filter'))
			return $filters;

		foreach ($this->getArg('filter') as $key => $filter) {
			if (in_array($key, $this->filterSupportingFields())) {
				if (is_array($filter[key($filter)]))
					foreach ($filter[key($filter)] as $f) {
						$filters[] = new QueryFilter(
							$key,
							key($filter),
							$f
						);
					}
				else {
					$filters[] = new QueryFilter(
						$key,
						key($filter),
						$filter[key($filter)]
					);
				}
			}
		}

		return $filters;
	}

	protected function andFilters(): bool
	{
		$map = array(
			'1' => true,
			1 => true,
			'true' => true,
			'0' => false,
			0 => false,
			'false' => false
		);

		if (null == $this->getArg('filter'))
			return false;

		foreach ($this->getArg('filter') as $key => $filter) {
			if ('and' == $key) {
				if (null == $filter)
					return false;
				else if (in_array(strtolower($filter), array_keys($map)))
					return $map[strtolower($filter)];
				else
					return false;
			}
		}

		return false;
	}

	protected function orderBy()
	{
		$order_by = array();

		if (null == $this->getArg('orderby'))
			return $order_by;

		foreach ($this->getArg('orderby') as $key => $filter) {
			if (in_array($key, $this->orderBySupportingFields())) {
				$order_by[] = array(
					$key => $filter
				);
			}
		}

		return $order_by;
	}

	protected function sort()
	{
		$sort_values = array(
			'ASC', 'DESC'
		);

		if ($this->getArg('sort') && in_array(strtoupper($this->getArg('sort')), $sort_values))
			return strtoupper($this->getArg('sort'));
		else
			return 'ASC';
	}

	protected function pageNumber()
	{
		if (isset($this->request->get['page']['number']) && $this->request->get['page']['number'] > 0)
			return $this->request->get['page']['number'];
		else
			return 1;
	}

	protected function pageSize()
	{
		$page_size_limit = $this->pageSizeLimit();

		if (!$page_size_limit)
			$page_size_limit = 20000;

		if (isset($this->request->get['page']['size']) && $this->request->get['page']['size'] <= $page_size_limit)
			return $this->request->get['page']['size'];
		else if (isset($this->request->get['page']['size']) && $this->request->get['page']['size'] > $page_size_limit)
			return $page_size_limit;
		else
			return 25;
	}

	/* services */

	protected function sessionService(): Session
	{
		if (null == $this->session_service) {
			$this->load->module('auth');

			$this->session_service = $this->module_auth->service('Session');
		}

		return $this->session_service;
	}

	protected function authorizationService(): AuthorizationService
	{
		if (null == $this->authorization_service) {
			$this->load->module('IdentityAndAccess');

			$this->authorization_service = $this->module_identity_and_access->service('Authorization');
		}

		return $this->authorization_service;
	}

	/* authorization */

	protected function canViewSubmodule(): bool
	{
		return $this->authorizationService()->canView($this->personnelId(), $this->submoduleId());
	}

	protected function canCreateSubmodule(): bool
	{
		return $this->authorizationService()->canCreate($this->personnelId(), $this->submoduleId());
	}

	protected function canUpdateSubmodule(): bool
	{
		return $this->authorizationService()->canUpdate($this->personnelId(), $this->submoduleId());
	}

	protected function canDeleteSubmodule(): bool
	{
		return $this->authorizationService()->canDelete($this->personnelId(), $this->submoduleId());
	}

	protected function personnelId(): int
	{
		return $this->sessionService()->getPersonnelId($this->getBearerToken());
	}

	/* auth token */

	protected function getAuthorizationHeader()
	{
		$headers = null;
		if (isset($this->request->server['Authorization'])) {
			$headers = trim($this->request->server['Authorization']);
		} else if (isset($this->request->server['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
			$headers = trim($this->request->server['HTTP_AUTHORIZATION']);
		} elseif (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			//print_r($requestHeaders);
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}
		return $headers;
	}

	protected function getBearerToken()
	{
		$headers = $this->getAuthorizationHeader();
		// HEADER: Get the access token from the header
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}
}
