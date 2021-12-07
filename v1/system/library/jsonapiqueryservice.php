<?php 

use queryservice\InvalidConfigException;

abstract class JsonApiQueryService extends QueryService {

	private $debug_enabled = false;

	/**
	 * Subclass MUST return a valid configuration 
	 * array by implementing this method
	 */
	protected abstract function config() : array;

	function __call($name, $arguments) {
		$result = parent::__call($name, $arguments);

		$formatted = [];

		if($this->is_get) {
			$data = $result->data();

			$formatted = $this->buildResource($data, $this->entity);
		}	
		else {
			$data = $result->data();

			foreach($data as &$d)
				$formatted[] = $this->buildResource($d, $this->entity);
		}

		$ret = [
			'data' => $formatted,
		];

		if($result->meta())
			$ret['meta'] = $result->meta();

		return $ret;
	}

	protected function buildResource(&$dbo, $entity) {
		$this->debug('--- DBO ---');
		$this->debug($dbo);
		$this->debug('--- ENTITY ---');
		$this->debug($entity);

		$this->assertEntityIsNotMissingJsonApiOptions($entity);
		$this->assertEntityIsNotMissingJsonApiOptionIdField($entity);
		$this->assertEntityIsNotMissingJsonApiOptionType($entity);

		$config = $this->config();

		# find resource id_field and type
		$id_field = $config[$entity]['json_api']['id_field'];
		$type = $config[$entity]['json_api']['type'];

		# determine which fields must be excluded
		if(array_key_exists('exclude', $config[$entity]['json_api']))
			$conf_excl = $config[$entity]['json_api']['exclude'];
		else
			$conf_excl = array();

		$excluded_fields = array_merge($conf_excl, array(
			$id_field,
			'_include'
		));

		# object in json api format
        $formatted = [];

        if(null !== $id_field)
            $formatted['id'] = $dbo[$id_field];

		$formatted['type'] = $type;

		foreach($dbo as $key => $value)
			$formatted['attributes'][$key] = $value;

		if(array_key_exists('translate', $config[$entity]['json_api'])) {
			$translations = $config[$entity]['json_api']['translate'];

			foreach($translations as $field => $translation) {
				if(false == array_key_exists('translator', $translation))
					throw new InvalidConfigException("Entity '" . $entity . "' is missing required config option 'json_api.translate.{field}.translator' for field '" . $field . "'");

				$translator = $translation['translator'];

				$this->assertMethodExists($translator);

				if(array_key_exists('replace', $translation))
					$replace = $translation['replace'];
				else # replace by default
					$replace = true;

				$translated = call_user_func([$this, $translator], $formatted['attributes'][$field]);

				if($replace)
					$formatted['attributes'][$field] = $translated;
				else {
					if(false == array_key_exists('name', $translation))
						throw new InvalidConfigException("Entity '" . $entity . "' is missing required config option 'json_api.translate.{field}.name' for field '" . $field . "'");

					$formatted['attributes'][$translation['name']] = $translated;
				}
			}
		}

		foreach($dbo as $key => $value) {
			if (in_array($key, $excluded_fields))
				unset($formatted['attributes'][$key]);
		}

		if(array_key_exists('_include', $dbo)) {
			foreach($dbo['_include'] as $rel => $d) {
				$rel_ent = null;

				foreach($config as $ent => $data) {
					$ent_table = $this->entityTable($ent);
					
					if($ent_table == $rel) {
						$rel_ent = $ent;
						break;
					}
				}

				if(null == $rel_ent)
					throw new \BadmethodCallException("Table '" . $rel . "' could not be associated with an entity");

				# handle over cases
				$over = false;

				foreach($config[$entity]['include'] as $k => $v) {
					if(array_key_exists('over', $v) && $v['over']['table'] == $rel_ent) {
						$rel_ent = $k;
						$over = $k;
					}
				}
				# ---

				# a relationship is considered multiple unless otherwise specified
				$is_single_relationship = array_key_exists('single', $config[$entity]['include'][$rel_ent]) && $config[$entity]['include'][$rel_ent]['single'];

				if(null == $d) {
					if($is_single_relationship)
						$formatted['relationships'][$rel_ent] = null;
					else
						$formatted['relationships'][$rel_ent] = array();
				}
				else {
					if($is_single_relationship) {
						if(is_numeric(array_keys($d)[0]))
							$formatted['relationships'][$rel_ent]['data'] = $this->process($formatted, $this->buildResource($d[0], $rel_ent), $entity, $rel_ent);
						else
							$formatted['relationships'][$rel_ent]['data'] = $this->process($formatted, $this->buildResource($d, $rel_ent), $entity, $rel_ent);
					}
					else {
						if(is_numeric(array_keys($d)[0])) {
							$this->debug('--- d ---');
							$this->debug($d);

							foreach($d as $i) {
								$this->debug('--- i ---');
								$this->debug($i);								

								if($over)
									$i = $i['_include'][array_keys($i['_include'])[0]][0];


								$formatted['relationships'][$rel_ent]['data'][] = $this->process($formatted, $this->buildResource($i, $rel_ent), $entity, $rel_ent);
							}
						}
						else {
							if($over)
								$d = $d['_include'][array_keys($d['_include'])[0]][0];

							$formatted['relationships'][$rel_ent]['data'][] = $this->process($formatted, $this->buildResource($d, $rel_ent), $entity, $rel_ent);
							
						}
					}
				}
			}
		}

		// TODO: Bu islemin queryservice icerisine tasinmasi lazim
		if(array_key_exists('processor', $config[$entity])) {
			$processor = $config[$entity]['processor'];

			$this->assertMethodExists($processor);

			$formatted = call_user_func([$this, $processor], $formatted);
		}

		return $formatted;
	}

	private function debug($message) {
		if(!$this->debug_enabled)
			return;

		if(is_array($message)) {
			echo PHP_EOL;
			print_r($message);

		}
		else
			e00($message);
	}

	private function process($parent, $res, $entity, $rel_ent) {
		$config = $this->config();

		if(!array_key_exists('processor', $config[$entity]['include'][$rel_ent]))
			return $res;

		$rel_processor = $config[$entity]['include'][$rel_ent]['processor'];

		$this->assertMethodExists($rel_processor);

		return call_user_func([$this, $rel_processor], $parent, $res);
	}

	private function assertEntityIsNotMissingJsonApiOptions($entity) {
		if(false == array_key_exists('json_api', $this->config()[$entity]))
			throw new InvalidConfigException("Entity '" . $entity . "' is missing required config option 'json_api'");
	}

	private function assertEntityIsNotMissingJsonApiOptionIdField($entity) {
		if(false == array_key_exists('id_field', $this->config()[$entity]['json_api']))
			throw new InvalidConfigException("Entity '" . $entity . "' is missing required config option 'json_api.id_field'");
	}

	private function assertEntityIsNotMissingJsonApiOptionType($entity) {
		if(false == array_key_exists('type', $this->config()[$entity]['json_api']))
			throw new InvalidConfigException("Entity '" . $entity . "' is missing required config option 'json_api.type'");
	}

	private function assertMethodExists(string $method_name) {
	    if(false == method_exists($this, $method_name))
	        throw new BadMethodCallException("Processor '" . $method_name . "' is not defined or not accessible");
    }
}

?>