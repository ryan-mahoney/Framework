<?php
class Form {
	private $marker = false;
    private $markerOverride = false;
	public $fields = [];
	public $fieldsByKey = [];
	public $fieldsets = [];
	public $tables = [];
	public $activeRecord = [];
	public $fieldsetTemplate = false;
	public $errors = [];
	public $notices = [];
	public $showHeader = true;
	public $documents = false;
	public $width = 12;
	public $widthForm = 12;
	public $filtered = false;
	public $sortKey = 'sort_key';
	public $searchInclusionKey = '_id';
    public $multipleFlag = true;
    public $appendedId = false;

    public function markerOverride ($marker) {
        $this->markerOverride = $marker;
    }

	public function fieldLabelClass () {
		if (isset($this->labelClass)) {
			return $this->fieldLabelClass;
		}
		return '';
	}

	public function fieldTagClass () {
		if (isset($this->fieldTagClass)) {
			return $this->fieldTagClass;
		}
		return '';
	}

	public function __construct() {
		$parent = get_parent_class($this);
		if (!in_array(trim($parent, '\\'), ['vc\pf\DOMFormTable', 'vc\pf\DOMFormTableFilter', 'vc\pf\DOMFormTableSubDocument'])) {
			self::parseClassMethods($parent, 'Field');
		}
		self::parseClassMethods($this);
		$this->errors = new \ArrayObject();
		$this->notices = new \ArrayObject();
		if (method_exists($this, 'excel')) {
			$this->excel = $this->excel();
		}
	}

	public function killMethod ($name) {
		foreach ($this->fields as $key => $field) {
			if ($field['name'] == $name) {
				unset($this->fields[$key]);
			}
		}
	}

	private function parseClassMethods ($object, $filter=false) {
		$reflector = new \ReflectionClass($object);
		$methods = $reflector->getMethods();
		foreach ($methods as $method) {
			if (substr_count((string)$method->name, 'Fieldset') > 0) {
				if ($filter === false || $filter == 'Fieldset') {
					$this->fieldsets[] = $method->invoke($this);
				} else {
					continue;
				}
			} elseif (substr_count((string)$method->name, 'Field') > 0) {
				if ($filter === false || $filter == 'Field') {
					$data = $method->invoke($this);
					if (isset($this->fieldsByKey[$data['name']])) {
						if (isset($data['destroy']) && $data['destroy'] === true) {
							unset($this->fieldsByKey[$data['name']]);
							$this->killMethod($data['name']);
						} else {
							$this->fieldsByKey[$data['name']] = array_merge(
								$this->fieldsByKey[$data['name']],
								$method->invoke($this)
							);
						}
					} else {
						$this->fieldsByKey[$data['name']] = $data;
						$this->fields[] = &$this->fieldsByKey[$data['name']];
					}
				} else {
					continue;
				}
			} elseif (substr_count((string)$method->name, 'defaultTable') > 0) {
				if ($filter === false || $filter == 'Table') {
					$this->table = $method->invoke($this);
				}
			}
		}
	}
	
	public function setActiveRecord ($activeRecord) {
		$this->activeRecord = $activeRecord;
	}
	
	public static function adminAsSubDocumentTemplate () {
		return str_replace('____', '__', ('__' . str_replace(['\\', 'Admin'], ['__', 'SubAdmin'], get_called_class())));
	}

	public function marker () {
        if ($this->markerOverride !== false) {
            return $this->markerOverride;
        }
		if ($this->marker === false) {
			$this->marker = strtolower(str_replace('\\', '_', get_class($this)));
		}
		return $this->marker;
	}

    public static function makeMarker ($class, $mode='') {
        if ($mode != '') {
            $mode = '-' . $mode;
        }
        return strtolower(str_replace('\\', '_', trim($class, '\\'))) . $mode;
    }

	public function documentRemove () {
		return function ($admin, &$request) {};
	}
	
	public function documentRemoved () {
		return function ($admin, &$request) {};
	}

	public function documentSave () {
		return function ($admin, &$document) {};
	}
	
	public function documentSaved () {
		return function ($admin, &$document) {};
	}

	public function documentUpdate () {
		return function ($admin, &$document) {};
	}
	
	public function documentUpdated () {
		return function ($admin, &$document) {};
	}
	
	public function documentAppend () {
		return function ($admin, &$document, &$subdocument) {};
	}
	
	public function documentAppended () {
		return function ($admin, &$document, &$subdocument) {};
	}

	public function documentAppendUpdate () {
		return function ($admin, &$document, &$subdocument) {};
	}
	
	public function documentAppendUpdated () {
		return function ($admin, &$document, &$subdocument) {};
	}

	public function documentAppendRemove () {
		return function ($admin, &$document, &$request) {};
	}
	
	public function documentAppendRemoved () {
		return function ($admin, &$document, &$request) {};
	}
	
	public function beforeFieldsetUpdate () {
		return function ($admin) {};
	}
	
	public function beforeFieldset () {
		return function ($admin) {};
	}
	
	public function beforeFieldsetSave () {
		return function ($admin) {};
	}
	
	public function beforeTableList () {
		return function ($admin) {};
	}
	
	public function afterFieldsetUpdate () {
		return function ($admin) {};
	}
	
	public function afterFieldset () {
		return function ($admin) {};
	}

	public function afterFieldsetSave () {
		return function ($admin) {};
	}
	
	public function afterFieldsetAppend () {
		return function ($admin) {};
	}
	
	public function afterTableList () {
		return function ($admin) {};
	}
	
	public function beforeFieldsetTemplate () {
		return function ($admin) {};
	}
}