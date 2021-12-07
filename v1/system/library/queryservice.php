<?php

use DB\QueryInclude;
use DB\QueryObject;
use DB\QueryResult;

abstract class QueryService {
	/**
	 * uses method names to build and execute queries
	 * ex:
	 * 		getPersonnelByNameAndLastname -> builds and executes a query that will find a personnel in table personnel by name and lastname
	 * 		fetchPersonnelByDepartmentId -> builds and executes a query that will find personnels with department_id
	 * 
	 * supported operations MUST be specified in the config
	 * keywords are case sensitive
	 * keywords MUST NOT repeat
	 *
	 * A QueryObject can be provided as the last arguemnt of method call. Includes in this query object will be ignored and constructed with method name
	 * 
	 * Config example:
	 * 		array(
	 * 			'consumer' => [
	 * 				'table' => 'consumer',
	 * 				'single' => [
	 * 					'by' => [
	 * 						null => [
	 * 							'nullable' => false,
	 * 							'provider' => [
	 * 								'consumer_key' => 'consumerKey'
	 * 							]
	 * 						],
	 * 						'id' => [
	 * 							'nullable' => true,
	 * 							'provider' => [
	 * 								'consumer_key' => 'consumerKey'
	 * 							]
	 * 						]
	 * 					]
	 * 				],
	 * 				include' => [
	 * 					'subscription' => [
	 * 						'handler' => 'methodName'
	 * 					]
	 * 				]
	 * 			],
	 * 			'subscription' => [
	 * 				'multiple' => [
	 * 					'by' => [
	 * 						'consumer_key' => [
	 * 							'nullable' => true,
	 * 							'null_exception' => EventNotFoundException::class,
	 * 							'provider' => [
	 * 								'consumer_key' => 'consumerKey'
	 * 							]
	 * 						]
	 * 					],
	 * 					'filter' => [
	 * 						'context', 'event'
	 * 					],
	 * 					'sort' => [
	 * 						'context', 'event'
	 * 					],
	 * 					'pagination' => [
	 * 						'disabled' => true,
	 * 						'max_size' => 30
	 * 					]
	 * 				],
	 * 				'include' => [
	 * 					'consumer' => [
	 * 						'field_left' => 'consumer_key',
	 * 						'field_right' => 'key',
	 * 						'single' => true,
	 * 						'processor' => 'subscriptionConsumerProcessor'
	 * 					],
	 * 					'consumer.event' => [
	 * 						'field_left' => 'key',
	 * 						'field_right' => 'consumer_key'
	 * 					],
	 * 					'consumer.event.subscription' => [
	 * 						'field_left' => 'subscription_id',
	 * 						'field_right' => 'subscription'
	 * 					]
	 * 				]
	 * 			],
	 * 			'event' => [
	 * 				'single' => [
	 * 					'by' => [
	 * 						'context,type' => [
	 * 							'nullable' => true,
	 * 							'provider' => [
	 * 								'consumer_key' => 'consumerKey'
	 * 							]
	 * 						]
	 * 					]
	 * 				]
	 * 			],
     *          'chat' => [
     *              'multiple' => [
     *                  'by' => [
     *                      'relation' => [
     *                          'nullable' => true,
     *                          'over' => [
     *                              'table' => 'chatter',
     *                              'key' => 'id',
     *                              'foreign_key' => 'chat_id',
     *                              'provider' => [
     *                                  'relation_id' => 'userId'
     *                              ]
     *                          ],
     *                          'authorize' => 'userIsInChat',
     *                          'provider' => [
     *                              'name' => 'YOKA_CHAT'
     *                          ]
     *                      ]
     *                  ]
     *              ],
	 * 				'json_api' => [
	 * 					'id_field' => 'id',
	 * 					'type' => 'chat',
	 * 					'exclude' => [
	 * 						'id'
	 * 					],
	 * 					'translate' => [
	 * 						'date_added' => [
	 * 							'translator' => 'formatDate',
	 * 							'replace' => false,
	 * 							'name' => 'date_formatted'
	 * 						]
	 * 					]
	 * 				],
	 * 				'processor' => 'processChat'
     *          ]
	 * 		);
	 */

	/**
	 * ?*TEST:
	 * 		security
     *
     *      authorize fonksiyonu
     *
     *      provider fonksiyonu
	 *
	 * 		translate fonksiyonu
	 *
	 * 		exclude fonksiyonu
	 */

	private array $keywords = array(
		'get',
		'fetch',
		'By',
		'Including'
	);

	protected string $entity;
	protected bool $is_get;
	protected \DB $db;
	# TODO: GECICI KALDIR
	protected $options;

	/**
	 * Subclass MUST return a valid configuration 
	 * array by implementing this method
	 */
	protected abstract function config() : array;

	protected abstract function db() : \DB;

	/**
	 * @param string $entity entity name
	 * @param string $relation relation entity name
	 * 
	 * @return QueryInclude
	 * 
	 * @example
	 * 		inclusionData('personnel', 'department.tenant')
	 */
	private function inclusionData(string $entity, string $relation) : QueryInclude {
		$this->assertEntityIsSupported($entity);
		$this->assertEntitySupportsInclusions($entity);
		$this->assertEntitySupportsSpecifiedInclusion($entity, $relation);

		$fields = $this->config()[$entity]['include'][$relation];
		$over = null;

		if(array_key_exists('over', $fields))
			$over = $fields['over'];

		$data = [];

		$data['field_left'] = $fields['field_left'];
		$data['field_right'] = $fields['field_right'];

		if(false !== strpos($relation, '.')) {
			$last_two = array_slice(explode('.', $relation), -2);

			throw new \Exception('QueryService::inclusionData THIS PART IS NOT YET IMPLEMENTED');

			$data['table_left'] = $this->entityTable($last_two[0]);
			$data['table_right'] = $this->entityTable($last_two[1]);
		}
		else {
			if($over) {
				$data['table_left'] = $this->entityTable($entity);
				$data['table_right'] = $over['table'];
			}
			else {
				$data['table_left'] = $this->entityTable($entity);
				$data['table_right'] = $this->entityTable($relation);
			}
		}

		$inner_incl = null;

		if($over)
			$inner_incl = new QueryInclude(
				$over['table'],
				$this->entityTable($relation),
				$over['field_left'],
				$over['field_right']
			);

		return new QueryInclude(
			$data['table_left'],
			$data['table_right'],
			$data['field_left'],
			$data['field_right'],
			$inner_incl
		);
	}

	/**
	 * @return string Returns table name specified in config. Returns entity name if table name is omitted from the config.
	 */
	protected function entityTable(string $entity) : string {
		$this->assertEntityIsSupported($entity);

		if(array_key_exists('table', $this->config()[$entity]))
			return $this->config()[$entity]['table'];
		else
			return $entity;
	}

	private function lowerArrayKeysRecursive(&$arr) {
		$arr = array_change_key_case($arr);

		foreach($arr as $i => &$v) {
			if(is_array($v))
				$this->lowerArrayKeysRecursive($v);
		}
	}

	private function toSnakeCase(string $str) : string {
	    $lc_first = lcfirst($str);

        if (preg_match('/[A-Z]/', $lc_first)) {
            $pattern = '/([a-z])([A-Z])/';
            $replacement = preg_replace_callback($pattern, function ($ms) use ($lc_first) {
                return $ms[1] . '_' . strtolower($ms[2]);
            }, $lc_first);

            $s = $replacement;
        } else
            $s = $lc_first;

        return $s;
    }

	function __call($name, $arguments) {
		$is_get = str_starts_with($name, 'get');
		$is_fetch = str_starts_with($name, 'fetch');
		
		$this->is_get = $is_get;
		$this->db = $this->db();

		if(!$is_get && !$is_fetch)
			throw new BadMethodCallException("Method name MUST start with either 'get' or 'fetch' keywords");

		if($is_get)
			$conf_plurality_selector = 'single';
		else
			$conf_plurality_selector = 'multiple';

		# find keyword positions
		$keyword_pos = array();

		foreach($this->keywords as $kw) {
			$pos = strpos($name, $kw);

			$keyword_pos[$kw] = $pos;
		}

		# split method name into parts
		$parts = array();

		preg_match('/^(get|fetch)(?P<entity>\w+?)((By)(?P<by>\w+?))?((Including)(?P<include>\w+))?$/', $name, $parts);

		# entity cannot be omitted
		if(null == $parts['entity'])
			throw new BadMethodCallException("Method name MUST specify an entity following 'get' or 'fetch' keyword. get<entity>By...");

		# ENTITY
		$entity = $this->toSnakeCase($parts['entity']);
		$this->entity = $entity;

		if($is_get)
			$this->assertEntitySupportsGet($entity);
		else
			$this->assertEntitySupportsFetch($entity);

		# TABLE NAME
		# Use Entity name as table name if table name is omitted. Use explicitly specified table name otherwise.
		$table = $this->entityTable($entity);

		# CONDITIONS
		$conditions = array();

		$method_call_condition_count = 0;

		if(array_key_exists('by', $parts) || array_key_exists(null, $this->config()[$entity][$conf_plurality_selector]['by'])) {
		    if(array_key_exists('by', $parts)) {
                # split conditions by 'And' since multiple conditions can be specified by joining them with 'And' keyword
                $split = explode('And', $parts['by']);

                $conditions_provided_by_method_call = (count($split) > 0);

                $method_call_condition_count = count($split);

                foreach ($split as &$s)
                    $s = $this->toSnakeCase($s);

                $fields_joined = implode(',', $split);

                if($conditions_provided_by_method_call) {
                    if($is_get)
                        $this->assertEntitySupportsSpecifiedGet($entity, $fields_joined);
                    else
                        $this->assertEntitySupportsSpecifiedFetch($entity, $fields_joined);
                }

                if(count($split) > count($arguments))
                    throw new BadMethodCallException("Argument count MUST be equal or more than 'By' statement conditions");

                foreach($split as $i => $col) {
                    # first N arguments of the method will correspond to N conditions specified in 'By' statement joined with 'And' keywords
                    # ex:
                    #	getEntityByField1AndField2(field1, field2, {other_args})
                    $conditions[$col] = $arguments[$i];
                }
            }
		    else
		        $fields_joined = null;

			# authorize
			if(array_key_exists('authorize', $this->config()[$entity][$conf_plurality_selector]['by'][$fields_joined])) {
			    $auth_method = $this->config()[$entity][$conf_plurality_selector]['by'][$fields_joined]['authorize'];

			    $this->assertMethodExists($auth_method);

			    if(true !== call_user_func([$this, $auth_method], ...$arguments))
			        throw new \AuthorizationException();
            }

            if(array_key_exists('over', $this->config()[$entity][$conf_plurality_selector]['by'][$fields_joined])) {
                $over = $this->config()[$entity][$conf_plurality_selector]['by'][$fields_joined]['over'];

                if(array_key_exists('provider', $over)) {
                    foreach($over['provider'] as $k => $p) {
                        $this->assertMethodExists($p);

                        $conditions[$over['key']]['relation'][] = [
                            'table' => $over['table'],
                            'key' => $over['key'],
                            'foreign_key' => $over['foreign_key'],
                            'field' => $k,
                            'value' => call_user_func(array($this, $p))
                        ];
                    }
                }
            }
            else
                $over = null;

			if(array_key_exists('provider', $this->config()[$entity][$conf_plurality_selector]['by'][$fields_joined])) {
				$providers = $this->config()[$entity][$conf_plurality_selector]['by'][$fields_joined]['provider'];

				foreach($providers as $k => $p) {
				    $this->assertMethodExists($p);

				    $conditions[$k] = call_user_func(array($this, $p));
                }
			}

			if($is_get && count($conditions) < 1)
				throw new BadMethodCallException("Get operations must have at least one condition");
		}

		# INCLUDES
		$includes = array();
		$nodes = [];

		if(array_key_exists('include', $parts)) {
			$inclusions = array_map(function($item) {
				return strtolower($item);
			}, explode('And', $parts['include'])); # ex: PersonnelWithDepartmentWithTenantAndRoleWithPrivilege (personnel.department.tenant, role.privilege)

			foreach($inclusions as $incl)
				$nodes[] = str_replace('with', '.', $incl);
		}

		$query_object_in_args = count($arguments) > 0 && is_object(array_slice($arguments, -1)[0]) && get_class(array_slice($arguments, -1)[0]) == 'DB\QueryObject';

		$options = null;

		if($query_object_in_args) {
			$options = array_slice($arguments, -1)[0]; /** @var QueryObject $options */

			$nodes = array_merge($nodes, $options->entityIncludes());
		}

		foreach($nodes as $incl) {
			$split = explode('.', $incl);
			$root = $split[0]; # ex: Personnel

			if('location' == $root)
				continue;

			$root_incl = $this->inclusionData($entity, $root);

			$inner_incl = null;

			for($i = count($split) - 1 ; $i >= 0; $i--) {
				if($i == 0)
					continue;

				$left = array_slice($split, $i - 1, 1)[0];
				$right = array_slice($split, $i, 1)[0];

				if('location' == $right)
					continue;

				$incl = $this->inclusionData($left, $right);

				if(null == $inner_incl)
					$inner_incl = $incl;
				else {
					$temp = $inner_incl;
					$inner_incl = $incl;
					$inner_incl->setInclude($temp);
				}
			}

			if($inner_incl)
				$root_incl->setInclude($inner_incl);

			$includes[] = $root_incl;

			// e20('incl: ');
			// e12($root_incl->prettyPrint());
		}

		if($is_get)
			$result = $this->db->get($table, $conditions, $includes);
		else {
			# OPTIONS
			$query_object_in_args = (count($arguments) > $method_call_condition_count) &&
									get_class(array_slice($arguments, -1)[0]) == 'DB\QueryObject';

			if($query_object_in_args) {
				$options = array_slice($arguments, -1)[0]; /** @var QueryObject $options */

				$options->setIncludes($includes);
			}
			else {
				$options = new QueryObject(
					includes: $includes
				);
			}

			if($this->paginationIsDisabled($entity))
				$options->disablePagination();

			$this->assertQueryObjectIsValid($entity, $options);

			$result = $this->db->fetch($table, $conditions, $options);
		}

		// TODO: GECICI, KALDIR
		if($options)
			$this->options = $options;

		$config = $this->config();
		$nullable_option_specified = array_key_exists('nullable', $config[$entity][$conf_plurality_selector]['by'][$fields_joined]);
		if($nullable_option_specified) {
			if(false == $config[$entity][$conf_plurality_selector]['by'][$fields_joined]['nullable'] && $result->isEmpty()) {
				if(array_key_exists('null_exception', $config[$entity][$conf_plurality_selector]['by'][$fields_joined])) {
					$class_name = $config[$entity][$conf_plurality_selector]['by'][$fields_joined]['null_exception'];
					throw new $class_name();
				}
				//  else
				//  	 throw new NotFoundException();
			}
		}
		else {
			# result is nullable by default. absence 
			# of nullable config implies that the
			# result is nullable. so no other operation
			# is required
		}

		return $result;
	}

	private function assertEntityIsSupported(string $entity) {
		if(false == array_key_exists($entity, $this->config()))
			throw new BadMethodCallException("This query service does not recognize an entity of type '" . $entity . "'");
	}

	private function assertEntitySupportsGet(string $entity) {
		if(false == array_key_exists('single', $this->config()[$entity]))
			throw new BadMethodCallException("Entity '" . $entity . "' does not support 'get' operation");
	}
	
	private function assertEntitySupportsFetch(string $entity) {
		if(false == array_key_exists('multiple', $this->config()[$entity]))
			throw new BadMethodCallException("Entity '" . $entity . "' does not support 'fetch' operation");
	}

	/**
	 * @param string $get join multiple fields with ',' to see if this entity supports specified composite get operation
	 */
	private function assertEntitySupportsSpecifiedGet(string $entity, string $get) {
		if(false == array_key_exists($get, $this->config()[$entity]['single']['by']))
			throw new BadMethodCallException("Entity '" . $entity . "' does not support specified get operation '" . $get . "'");
	}

	/**
	 * @param string $fetch join multiple fields with ',' to see if this entity supports specified composite fetch operation
	 */
	private function assertEntitySupportsSpecifiedFetch(string $entity, string $fetch) {
		if(false == array_key_exists($fetch, $this->config()[$entity]['multiple']['by']))
			throw new BadMethodCallException("Entity '" . $entity . "' does not support specified fetch operation '" . $fetch . "'");
	}

	protected function assertEntitySupportsInclusions(string $entity) {
		if(false == array_key_exists('include', $this->config()[$entity]))
			throw new BadMethodCallException("Entity '" . $entity . "' does not support any inclusion operations");
	}

	protected function assertEntitySupportsSpecifiedInclusion(string $entity, string $relation) {
		if(false == array_key_exists($relation, $this->config()[$entity]['include']))
			throw new BadMethodCallException("Entity '" . $entity . "' does not support inclusion operation '" . $relation . "'");
	}

	protected function assertEntitySupportsFilters(string $entity) {
		if(false == array_key_exists('filter', $this->config()[$entity]['multiple']))
			throw new BadMethodCallException("Entity '" . $entity . "' does not support filtering operations");
	}

	protected function assertEntitySupportsSpecifiedFilter(string $entity, string $field) {
		if(false == in_array($field, $this->config()[$entity]['multiple']['filter']))
			throw new BadMethodCallException("Entity '" . $entity . "' does not support filter '" . $field . "'");
	}

	protected function assertEntitySupportsOrderBy(string $entity) {
		if(false == array_key_exists('sort', $this->config()[$entity]['multiple']))
			throw new BadMethodCallException("Entity '" . $entity . "' does not support sorting operations");
	}

	protected function assertEntitySupportsSpecifiedOrderBy(string $entity, string $field) {
		if(false == in_array($field, $this->config()[$entity]['multiple']['sort']))
			throw new BadMethodCallException("Entity '" . $entity . "' does not support sort option '" . $field . "'");
	}

    private function assertMethodExists(string $method_name) {
	    if(false == method_exists($this, $method_name))
	        throw new BadMethodCallException("Provider '" . $method_name . "' is not defined or not accessible");
    }

	protected function assertEntitySupportsPagination(string $entity) {
		$config = $this->config();

		$pagination_option_specified = array_key_exists('pagination', $config[$entity]['multiple']);

		# pagination is enabled by default. so pagination is 
		# supported if not specified otherwise in the config. 
		# absence of pagination config implies that the default
		# behaviour should applied
		if(false == $pagination_option_specified)
			return;

		if(array_key_exists('disabled', $config[$entity]['multiple']['pagination']) && $config[$entity]['multiple']['pagination']['disabled'])
			throw new BadMethodCallException("Entity '" . $entity . "' does not support pagination operations");
	}

	protected function assertEntitySupportsSpecifiedPageSize(string $entity, int $page_size) {
		if(array_key_exists('pagination', $this->config()[$entity]['multiple']) && array_key_exists('max_size', $this->config()[$entity]['multiple']['pagination']))
			$max_size = $this->config()[$entity]['multiple']['pagination']['max_size'];
		else
			$max_size = 100;

		if($page_size < 0 || $page_size > $max_size)
			throw new BadMethodCallException("Entity '" . $entity . "' does not support page sizes larger than " . $max_size);
	}

	protected function paginationIsDisabled(string $entity) {
		$multiple = $this->config()[$entity]['multiple'];

		if(array_key_exists('pagination', $multiple) && array_key_exists('disabled', $multiple['pagination']) && $multiple['pagination']['disabled'])
			return true;

		return false;
	}

	/**
	 * !!! IMPORTANT !!!
	 * 		DOES NOT validate includes
	 */
	protected function assertQueryObjectIsValid(string $entity, QueryObject $query_object) {
		$this->assertEntityIsSupported($entity);

		if($query_object->filters()) {
			$this->assertEntitySupportsFetch($entity);
			$this->assertEntitySupportsFilters($entity);

			foreach($query_object->filters() as $filter)
				$this->assertEntitySupportsSpecifiedFilter($entity, $filter->field());
		}

		if($query_object->orderBy()) {
			$this->assertEntitySupportsFetch($entity);
			$this->assertEntitySupportsOrderBy($entity);

			foreach($query_object->orderBy() as $ob)
				$this->assertEntitySupportsSpecifiedOrderBy($entity, array_keys($ob)[0]);
		}

		if($query_object->paginationIsEnabled()) {
			$this->assertEntitySupportsFetch($entity);
			$this->assertEntitySupportsPagination($entity);

			$this->assertEntitySupportsSpecifiedPageSize($entity, $query_object->limit());
		}
	}
}

?>