<?php
class FormModel {
	private static $selectorData = [];
    private static $offset = false;

    public static function offsetIncrement () {
        if (self::$offset === false) {
            self::$offset = 0;
            return;
        }
        self::$offset++;
    }

    public static function offsetGet () {
        return self::$offset;
    }

	private static function override (&$admin) {
		$reRequest = false;
		foreach ($_REQUEST as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $key2 => $value2) {
					if ((string)$key2 === 'template') {
						if (substr($value2, 0, 2) != '__' && substr($value2, 0, 1) != '\\') {
							throw new \Exception('"template" is a reserved admin field name.');
						}
						$oldRequest = substr(str_replace('\\', '_', strtolower($admin)), 1);
						$admin = str_replace('__', '\\', $value2);
						$reRequest = str_replace('__', '_', substr(strtolower($value2), 2));
						break;
					}
				}
			}
		}
		if ($reRequest !== false && $reRequest != '') {
			foreach ($_REQUEST as $key => $value) {
				if ($key == $oldRequest) {
					unset($_REQUEST[$oldRequest]);
					$_REQUEST[$reRequest] = $value;
					continue;
				}
				if (substr_count($key, $oldRequest) > 0) {
					unset($_REQUEST[$key]);
					$_REQUEST[str_replace($oldRequest, $reRequest, $key)] = $value;
				}
			}
		}
	}

	private static function runtimeCallback ($admin, $runtimeCallbacks) {
		if (count($runtimeCallbacks) < 1) {
			return;	
		}
		foreach ($runtimeCallbacks as $callbackKey => $callback) {
			$mode = 'replace';
			if (substr_count($callbackKey, '-append') > 0) {
				$mode = 'append';
				$callbackKey = str_replace('-append', '', $callbackKey);
			}
			if ($mode == 'replace') {
				$admin->{$callbackKey} = $callback;
			} else {
				if (is_array($admin->{$callbackKey})) {
					$admin->{$callbackKey}[] = $callback;
				} else {
					$tmp = $admin->{$callbackKey}();
					$admin->{$callbackKey} = [];
					$admin->{$callbackKey}[] = $tmp;
					$admin->{$callbackKey}[] = $callback;
				}
			}
		}
	}

	public static function request ($admin, $mode='', $id=false, $field=false, $data=[], array $runtimeCallbacks=[]) {
		if (is_array($admin)) {
			$admin = $admin[0];
		}
		self::override($admin);
		$admin = new $admin();
		if (isset($admin->filterClass)) {
			$admin->filterObject = new $admin->filterClass();
			$admin->filterMarker = $admin->filterObject->marker();
		}
		self::runtimeCallback($admin, $runtimeCallbacks);
		
		self::modeCoerce($admin, $mode);
		if ($id !== false) {
			if ($mode == 'update') {
				$_REQUEST[$admin->marker() . '-update'] = (string)$id;
			} elseif ($mode == 'append') {
				$_REQUEST[$admin->marker() . '-append'] = (string)$id;
			}
		}
		
		$saved = false;
		$data['id'] = self::documentSave($admin, $_REQUEST, $data['mode'], $saved);
		if ($data['id'] === false) {
			$updated = false;
			$data['id'] = self::documentUpdate($admin, $_REQUEST, $data['mode'], $updated);
			if ($data['id'] === false) {
				$data['id'] = self::documentAppend($admin, $_REQUEST, $data['mode'], $updated);
			}
		}
		if ($data['mode'] == 'update') {
			$admin->setActiveRecord(self::documentFindOne($admin, $data['id'], $_REQUEST));
		} elseif ($data['mode'] == 'save' && isset($_REQUEST[$admin->marker()])) {
			$admin->activeRecord = $_REQUEST[$admin->marker()];
		}
		if (self::documentRemove($admin, $_REQUEST)) {
			$function = $admin->documentRemoved();
			$function($admin, $_REQUEST);
			echo json_encode(array('success' => 1));
			exit;
		}
		
		if (in_array($data['mode'], ['save', 'update', 'append'])) {
			if (count($_POST) > 0 && !isset($admin->filter)) {
				$response = null;
				if (method_exists($admin, 'saveResponse') && $saved) {
					$function = $admin->saveResponse();
					try {
						ob_start();
						$function($admin, $_REQUEST);
						$response = ob_get_clean();
					} catch (\Exception $e) {
						echo $e->getMessage();
						exit;
					}
				}
				if (method_exists($admin, 'updateResponse') && $updated) {
					$function = $admin->updateResponse();
					try {
						ob_start();
						$function($admin);
						$response = ob_get_clean();
					} catch (\Exception $e) {
						echo $e->getMessage();
						exit;
					}
				}
				echo json_encode([
					'success' => 1,
					'errors' => (array)$admin->errors,
					'notices' => (array)$admin->notices,
					'mode' => $data['mode'],
					'marker' => $admin->marker(),
					'id' => (string)$data['id'],
					'updateJSFunction' => (isset($admin->updateJSFunction) ? $admin->updateJSFunction : ''),
					'saveJSFunction' => (isset($admin->saveJSFunction) ? $admin->saveJSFunction : ''),
					'appendJSFunction' => (isset($admin->appendJSFunction) ? $admin->appendJSFunction : ''),
					'updateJSFunctionContinue' => (isset($admin->updateJSFunctionContinue) ? $admin->updateJSFunctionContinue : ''),
					'saveJSFunctionContinue' => (isset($admin->saveJSFunctionContinue) ? $admin->saveJSFunctionContinue : ''),
					'appendJSFunctionContinue' => (isset($admin->appendJSFunctionContinue) ? $admin->appendJSFunctionContinue : ''),
					'saveRedirect' => (isset($admin->saveRedirect) ? $admin->saveRedirect : ''),
					'updateRedirect' => (isset($admin->updateRedirect) ? $admin->updateRedirect : ''),
					'alerts' => (isset($admin->alerts) ? $admin->alerts : ''),
					'response' => $response,
					'document' => $admin->activeRecord,
					'vc__admin' => get_class($admin),
					'vc__append_to' => ((isset($_REQUEST['vc__append_to'])) ? $_REQUEST['vc__append_to'] : ''),
                    'vc__append_field' => ((isset($_REQUEST['vc__append_field'])) ? $_REQUEST['vc__append_field'] : ''),
                    'vc__append_id' => ((isset($_REQUEST['vc__append_id'])) ? $_REQUEST['vc__append_id'] : ''),
                    'appendedId' => $admin->appendedId
				], JSON_HEX_AMP);
				exit;
			}
		} else {
			if (isset($admin->noList) && $admin->noList == true) {
				header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?' . $admin->marker() . '-save');
				exit;
			}
			$data['excel'] = false;
			if (isset($_REQUEST['N__EXCEL'])) {
				$data['excel'] = true;
			}
			$data['maxrows'] = 0;
			$data['limit'] = 40;
			$data['offset'] = 0;
			$data['search'] = false;			
			if (isset($admin->table) && isset($admin->table['limit'])) {
				$data['limit'] = $admin->table['limit'];
			}
			$data['offset'] = self::tableOffset($admin, $data['limit'], $_REQUEST);
			$data['search'] = self::tableSearch($admin, $_REQUEST);
			$data['sort'] = self::tableSort($admin, $_REQUEST);
			if (!isset($data['documents']) && isset($admin->storage['collection'])) {
				$data['documents'] = self::documentFind($admin, $data['search'], $data['sort'], $data['limit'], $data['offset'], $data['maxrows'], $data['excel']);
			} else {
				if (isset($admin->table['arraySort']) && $admin->table['arraySort'] == -1) {
					$data['documents'] = array_reverse($data['documents']);
				}
				$data['maxrows'] = count($data['documents']);
			}
		}

		if ($data['mode'] == 'table') {
			$function = $admin->beforeTableList();
			$function($admin);
		}
		
		if ($data['mode'] == 'update') {
			if (isset($admin->activeRecord['template'])) {
				$newAdmin = str_replace('__', '\\', $admin->activeRecord['template']);
				$newAdmin = new $newAdmin();
				foreach ($newAdmin->fields as $field) {
					if (isset($admin->fieldsByKey[$field['name']])) {
						$admin->fieldsByKey[$field['name']] = array_merge(
							$admin->fieldsByKey[$field['name']],
							$field
						);
					} else {
						$admin->fieldsByKey[$field['name']] = $field;
						$admin->fields[] = &$admin->fieldsByKey[$field['name']];
					}
				}
				self::documentTransformOut($admin, $admin->activeRecord);
				$path = pathinfo(str_replace('__', '/', $admin->activeRecord['template']));
				$admin->fieldsetTemplate = $path['filename'];
				if (isset($newAdmin->resetFieldStyle)) {
					foreach ($admin->fields as &$field) {
						unset($field['labelClass']);
						unset($field['tagClass']);
						unset($field['tagContainerClass']);
					}
				}
			}
			$function = $admin->beforeFieldsetUpdate();
			$function($admin);
		}

		if ($data['mode'] == 'save') {
			$function = $admin->beforeFieldsetSave();
			$function($admin);
		}
		
		if (in_array($data['mode'], ['save', 'update', 'append'])) {
			$function = $admin->beforeFieldset();
			$function($admin);
		}
		
		return [
			'admin'	=> $admin, 
			'data'	=> $data
		];
	}

	private static function tableRender (&$admin, &$documents, $limit, $offset, $maxrows, $form=true) {
		self::includes();
		$formTag = 'form';
		if (!$form) {
			$formTag = 'div';
		}
		ob_start();
		$page = ($offset < 1) ? 1 : (ceil($offset / $limit) + 1);
		$maxpage = ceil($maxrows / $limit);
		$pagination = false;
		if ($limit >= $maxrows) {
			$totalPhrase = $maxrows . ' Total';
		} else {
			$pagination = true;
			$totalPhrase = ((($page - 1) * $limit) + 1) . ' - ' . ((($page * $limit) < $maxrows) ? ($page * $limit) : $maxrows) . ' of ' . $maxrows . ' Total';
		}
		if (!isset($admin->formClass)) {
			$admin->formClass = 'form';
		}
		if (isset($admin->tranformRecords)) {
			$function = $admin->tranformRecords('html', $admin, $documents);
		}

		$headerStyle = '';
		$headerClass = '';
		if ($formTag == 'form') {
			$headerStyle = 'background-color: #0188CC; margin-left: -22px; width: 998px';
			$headerClass = 'table-header';
		}

		echo '
			<', $formTag, ' method="post" action="" class="admin-form ' . $admin->formClass . '" data-marker="', $admin->marker(), '" data-collection="', $admin->storage['collection'], '" data-id="', (string)$admin->activeRecord['_id'], '">
				<input type="hidden" name="N__ajax" value="1" />
				<input type="hidden" name="N__table" value="1" />
				<input type="hidden" name="vc__admin[]" value="', get_class($admin), '" />
				<div class="row ', $headerClass, ' table-row" style="', $headerStyle, '">
					<span class="span', $admin->width, '">
						<header class="subhead">
							<h1 class="table-title">', $admin->table['title'] , '<span class="total-phrase">', $totalPhrase, '</span></h1>';
		
		echo '
							<div class="table-actions">';

		if (isset($admin->table['features']) && in_array('add', $admin->table['features'])) {
			echo '
								<a class="form-right btn btn-primary" href="', $_SERVER['SCRIPT_NAME'], '?', $admin->marker(), '-save"><span>Add ', $admin->thing, '</span></a>';
		}
		if (isset($admin->table['features']) && in_array('append', $admin->table['features'])) {
			echo '
			<a class="form-right btn btn-small append"><span>Add ', $admin->thing, '</span></a>';
		}
		if (isset($admin->table['features']) && in_array('sortable', $admin->table['features'])) {
			$classes = 'btn-primary';
			if (isset($admin->subDocument)) {
				$classes = 'btn-small';
			}
			echo '
								<a class="form-right btn ', $classes, ' sort"><span>Sort</span></a>';
		}
		if (isset($admin->table['features']) && in_array('excel', $admin->table['features'])) {
			echo '
								<a class="form-right btn btn-primary excel"><span>Export to Excel</span></a>';
		}
		if (isset($admin->table['features']) && in_array('search', $admin->table['features'])) {
			echo '
								<div style="position: relative; margin-right: 10px" class="form-right search-container">
									<i class="icon-search" style="opacity: .3; position: absolute; left: 10px; top: 7px;"></i>
									<input name="', $admin->marker(), '-search" type="text" class="span2" style="padding-left: 28px; line-height: 19px;" placeholder="Search" autocomplete="off" spellcheck="false" x-webkit-grammar="builtin:search" lang="en" />
								</div>';
		}
		
		echo '				
							</div>';
		
		if ($pagination) {
			echo '
			<div class="form-left pagination">', Bootstrap::pagination(['linkBefore' => '', 'page' => $page, 'maxpage' => $maxpage, 'sep' => '']), '</div>';
		}
		
		echo '
					</header>
					</span>
				</div>
				<div class="row error-container" style="display: none">
					<span class="span', $admin->width, ' alert alert-error errors"></span>
				</div>
				<div class="row notice-container" style="display: none">
					<span class="span', $admin->width, ' alert alert-success notices"></span>
				</div>';
		
		if (isset($admin->filterClass)) {
			$filterClass = ' filter-closed ';
			if ($admin->filtered || $admin->mustFilter) {
				$filterClass = ' filter-open ';	
			}
			echo '<div class="row filter-row"><div class="fullwidth ', $admin->marker(), '-filter-container filter-container ', $filterClass, ' span', $admin->width, '"></div></div>';
		}

		echo self::tableRowRender($admin, $documents),
			'</', $formTag, '>';
		
		return ob_get_clean();
	}

	private static function fieldRender (&$field, $admin) {
		ob_start();
		$field['__admin'] = $admin;
		$displayCB = $field['display'];
		unset($field['display']);
		$displayCB($field);
		return ob_get_clean();
	}

	private function templateLocationFromName ($template) {
		$tmp = explode('\\', trim(str_replace('__', '\\', $template), '\\'));
		$i = 0;
		$file = '/';
		foreach ($tmp as $path) {
			$file .= $path . '/';
			if ($i == 1) {
				$file .= 'html/';
			}
			$i++;
		}
		return file_get_contents(N__DIR . substr($file, 0, -1) . '.html');
	}

	public static function render (&$admin, &$data, $containerSelector='.admin-container', $showSelector=false) {
		$DOM = DOMView::getDOM();
		if ($data['mode'] == '') {
			if (!isset($admin->displayMode) || $admin->displayMode != 'echo') {
				$DOM[$containerSelector]->html(self::tableRender($admin, $data['documents'], $data['limit'], $data['offset'], $data['maxrows']));
			} else {				
				echo self::tableRender($admin, $data['documents'], $data['limit'], $data['offset'], $data['maxrows'], false);
				return;
			}
		} else {
			$DOM[$containerSelector]->html(self::fieldsetRender($admin, $data['mode'], $data['id']));
		}
		if ($containerSelector != '.admin-container') {
			$DOM[$containerSelector]->addClass('admin-container');
		}
		foreach (self::$selectorData as $selector => $sData) {
			$DOM[$selector]->html($sData);
		}
		
		if ($data['mode'] == 'table' || $data['mode'] == '') {			
			$function = $admin->afterTableList();
			$function($admin);
		}
		
		if ($data['mode'] == 'update') {
			$function = $admin->afterFieldsetUpdate();
			$function($admin);
		}

		if ($data['mode'] == 'append') {
			$function = $admin->afterFieldsetAppend();
			$function($admin);
		}
		
		if ($data['mode'] == 'save') {
			$function = $admin->afterFieldsetSave();
			$function($admin);
		}
		
		if ($data['mode'] == 'update' || $data['mode'] == 'save') {
			$function = $admin->afterFieldset();
			$function($admin);
		}

		$DOM->form(self::fieldsetPopulate($admin, $data['mode'], $_REQUEST));
		
		$modal = false;
		if (isset($admin->alerts) && $admin->alerts == 'modal') {
			$modal = true;
		}
		
		//table filter
		if (isset($admin->filterObject) && ($data['mode'] == 'table' || $data['mode'] == '')) {
			$filterData = AdminModel::request($admin->filterObject, 'save');
			AdminModel::render($filterData['admin'], $filterData['data'], '.' . $admin->marker() . '-filter-container');
			$filterClass = ' icon-chevron-up ';
			if ($admin->filtered) {
				$filterClass = ' icon-chevron-down ';
			}
			$DOM['.' . $admin->marker() . '-filter-container']->prepend('<i class="icon ' . $filterClass . ' filter-toggle icon-white"></i>');
			$DOM['.' . $admin->marker() . '-filter-container']->append('
				<div class="row" style="background-color: #323232; margin-top: 15px; padding: 10px">
					<div class="span12">
						<a class="btn btn-filter btn-success" style="margin-right: 10px">Filter Results</a>
						<a class="btn btn-filter-clear">Clear Filters</a>
					</div>
				</div>');
		}

		if (count($admin->errors) > 0 && !$modal) {
			$DOM['.error-container']->attr('style', 'display: block');
			$DOM['.errors']->html(self::errorsRender($admin->errors));
		}
		if (count($admin->notices) > 0 && !$modal) {
			$DOM['.notice-container']->attr('style', 'display: block');
			$DOM['.notices']->html(self::errorsRender($admin->notices));
		}
		if (isset($_REQUEST['N__ajax']) && !isset($admin->filter)) {
			if (isset($_REQUEST['N__table'])) {
				ob_start();
				echo $DOM['.admin-form']->html();
				$table = ob_get_clean();
				DOMView::replaceStrings($table);
				echo json_encode(array(
					'table' => $table
				));
			} else {
				if ($showSelector == false) {
					$showSelector = '.admin-form';
				}
				$html = $DOM[$showSelector]->html();
				DOMView::replaceStrings($html);
				echo $html;
			}
			exit;
		}
	}

    public static function fieldsetRenderMulti (&$admin, $request=[], $prefix='') {
        if (isset($admin->fieldsetTemplate) && $admin->fieldsetTemplate !== false) {
            if (substr_count($admin->fieldsetTemplate, '\\') > 0) {
                $fieldsets = self::templateLocationFromName($admin->fieldsetTemplate);
            } else {
                if (isset($admin->activeRecord['template'])) {
                    $fieldsets = self::templateLocationFromName($admin->activeRecord['template']);
                } else {
                    $fieldsets = file_get_contents(N__DIR . '/' . N__CLIENT . '/' . N__PROJECT . '/html/admin/' . $admin->fieldsetTemplate . '.html');
                }
            }
        } else {
            $path = pathinfo(str_replace('\\', '/', get_class($admin)));
            $fieldsets = file_get_contents(N__DIR . '/' . N__CLIENT . '/' . N__PROJECT . '/html/admin/' . $path['filename'] . '.html');
        }
        $dom = DOMCSS::fragmentString($fieldsets);
        $fields = $admin->fields;
        $marker = $admin->marker();
        foreach ($fields as $field) {
            if (isset($field['display'])) {
                if (isset($admin->activeRecord[$field['name']])) {
                    $field['data'] = $admin->activeRecord[$field['name']];
                }
                $field['marker'] = $marker;
                $field['__CLASS__'] = get_class($admin);
                $selector = '#' . $field['name'] . '-field';
                $dom[$selector]->html(self::fieldRender($field, $admin));
                $selectorNew = $field['name'] . '-field';
                $dom[$selector]->addClass($selectorNew)->attr('id', '');
            }
        }

        $newRequest = [];
        self::requestArrayFormat($prefix, $request, $newRequest);
        $dom->form($newRequest);
        return (string)$dom;
    }

	private static function fieldsetRender (&$admin, $mode) {
		self::includes();
		if (isset($admin->fieldsetTemplate) && $admin->fieldsetTemplate !== false) {
			if (substr_count($admin->fieldsetTemplate, '\\') > 0) {
				$fieldsets = self::templateLocationFromName($admin->fieldsetTemplate);
			} else {
				if (isset($admin->activeRecord['template'])) {
					$fieldsets = self::templateLocationFromName($admin->activeRecord['template']);
				} else {
					$fieldsets = file_get_contents(N__DIR . '/' . N__CLIENT . '/' . N__PROJECT . '/html/admin/' . $admin->fieldsetTemplate . '.html');
				}
			}
		} else {
			$path = pathinfo(str_replace('\\', '/', get_class($admin)));
			$fieldsets = file_get_contents(N__DIR . '/' . N__CLIENT . '/' . N__PROJECT . '/html/admin/' . $path['filename'] . '.html');
		}
		
		$function = $admin->beforeFieldsetTemplate();
		$function($admin, $fieldsets);

		ob_start();
		$fields = $admin->fields;
		$marker = $admin->marker();

		foreach ($fields as $field) {
			if (isset($field['display'])) {
				if (isset($admin->activeRecord[$field['name']])) {
					$field['data'] = $admin->activeRecord[$field['name']];
				}
                $markerOffset = '';
                if (self::$offset !== false) {
                    $markerOffset = self::$offset . ':';
                }
				$field['marker'] = $markerOffset . $marker;
				$field['__CLASS__'] = get_class($admin);
				if (!isset($field['display-DOM']) || $field['display-DOM'] != true) {
                    $selector = '#' . $field['name'] . '-field';
                    self::$selectorData[$selector] = self::fieldRender($field, $admin);
				} else {
					self::fieldRender($field, $admin);
				}
			}
		}

		if ($mode == 'save' || $mode == 'append') {
			$title = 'New ';
			$verb = 'Save ';
		} elseif ($mode == 'update') {
			//$title = 'Edit ';
			$title = '';
			$verb = 'Update ';
		}
		
		if (!isset($admin->formClass)) {
			$admin->formClass = 'form';
		}

		$action = $_SERVER['SCRIPT_NAME'];
		if (isset($admin->action)) {
			$action = $admin->action;
		}
		
		if (isset($admin->filter)) {
			return $fieldsets;
		}

		echo '
			<form style="margin-left: 0" method="post" action="', $action, '" class="admin-form ', $admin->formClass, '" data-marker="', $admin->marker(), '" data-collection="', $admin->storage['collection'], '" data-id="', (string)$admin->activeRecord['_id'], '">';
		
		if ($admin->showHeader !== false) {
			echo '	
				<div class="row form-header" style="background-color: #0188CC; margin-left: -22px; width: 998px">
					<span class="span', $admin->widthForm, '">
						<header class="subhead">
							<h1>', $title, $admin->thing, '</h1>
							<a class="form-submit form-right" style="float: right"><span class="btn btn-primary">' . $verb . $admin->thing . '</span></a>
						</header>
					</span>
				</div>';
		}
		
			echo '
				<div class="row error-container" style="display: none">
					<span class="span', $admin->widthForm, ' alert alert-error errors"></span>
				</div>
				<div class="row notice-container" style="display: none">
					<span class="span', $admin->widthForm, ' alert alert-success notices"></span>
				</div>',
				$fieldsets, '
				<input type="hidden" name="vc__admin[]" value="', get_class($admin), '" />
				<input type="hidden" name="', $admin->marker(), '-', $mode, '" value="" />
				<input type="hidden" name="', $admin->marker(), '-', $mode, '-step" value="2" />
				', ((isset($_REQUEST['vc__append_id'])) ? '<input type="hidden" name="vc__append_id" value="' . $_REQUEST['vc__append_id'] . '" />' : ''), '
				', ((isset($_REQUEST['vc__append_field'])) ? '<input type="hidden" name="vc__append_field" value="' . $_REQUEST['vc__append_field'] . '" />' : ''), '
				', ((isset($_REQUEST['vc__append_to'])) ? '<input type="hidden" name="vc__append_to" value="' . $_REQUEST['vc__append_to'] . '" />' : ''), '
				', ((isset($_REQUEST['vc__append_simulate'])) ? '<input type="hidden" name="vc__append_simulate" value="' . $_REQUEST['vc__append_simulate'] . '" />' : ''), '
			</form>';
		
		return ob_get_clean();
	}
	
	private static function fieldsetPopulate ($admin, $mode, $request) {
		$newRequest = [];
		$newNewRequest = [];
		
		if ($mode == 'save' || $mode == 'append') {
			foreach ($admin->fields as $field) {
				if (!isset($field['default'])) {
					continue;
				}
				if (!isset($request[$admin->marker()][$field['name']]) || $request[$admin->marker()][$field['name']] == '') {
					if (is_callable($field['default'])) {
						$function = $field['default'];
						$request[$admin->marker()][$field['name']] = $function();
					} else {
						$request[$admin->marker()][$field['name']] = $field['default'];
					}
				}
			}
		}
		if (isset($admin->activeRecord) && count($admin->activeRecord) > 0) {
			if (isset($request[$admin->marker()]) && is_array($request[$admin->marker()]) && count($request[$admin->marker()]) > 0) {
				$request[$admin->marker()] = self::arrayMergeRecursiveSimple(
					$request[$admin->marker()], $admin->activeRecord
				);
			} else {
				$request[$admin->marker()] = $admin->activeRecord;
			}
		}
		
		self::requestArrayFormat('', $request, $newRequest);
		foreach ($newRequest as $key => $value) {
			if (substr($key, 0, 1) == '[') {
				$tmp = explode(']', $key, 2);
				$key = implode('', $tmp);
				$newNewRequest[substr($key, 1, (strlen($key)))] = $value;
			} else {
				$newNewRequest[$key] = $value;
			}
		}
		
		return $newNewRequest;
	}
	
	public static function requestArrayFormat ($top='', $request, &$newRequest) {
		foreach ($request as $key => $value) {
			if (is_array($value)) {
				self::requestArrayFormat($top . '[' . $key . ']', $value, $newRequest);
			} else {
				if ($top == '') {
					$newRequest[$key] = (string)$value;
				} else {
					$newRequest[($top . '[' . $key . ']')] = (string)$value;
				}
			}
		}
	}

	private static function arrayMergeRecursiveSimple () {
		if (func_num_args() < 2) {
			return;
		}
		$arrays = func_get_args();
		$merged = [];
		while ($arrays) {
			$array = array_shift($arrays);
			if (!$array) {
				continue;
			}
			foreach ($array as $key => $value) {
				if (is_string($key)) {
					if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
						$merged[$key] = call_user_func('self::arrayMergeRecursiveSimple', $merged[$key], $value);
					} else {
						$merged[$key] = $value;
					}
				} else {
					$merged[] = $value;
				}
			}
		}
		return $merged;
	}
	
	private static function tableRowRender ($admin, $data) {
		if (!is_array($data)) {
			return;
		}
	
		ob_start();
		$delete = false;
		if (isset($admin->table['features']) && in_array('delete', $admin->table['features'])) {
			$delete = true;
		}
		$readonly = false;
		if (isset($admin->table['features']) && in_array('readonly', $admin->table['features'])) {
			$readonly = true;
		}
		
		echo '<div class="row"><div class="span', $admin->width, ' fullwidth"><table class="table" data-sort_key="', $admin->sortKey, '" data-collection="', $admin->storage['collection'], '" data-marker="', $admin->marker(), '" data-thing="', $admin->thing, '" data-things="', $admin->things, '"><!-- <colgroup>';
		$rowWidth = [];
		foreach ($admin->table['columns'] as $column) {
			if (isset($column[1]) && $column[1] != '') {
				$rowWidth[] = $column[1];
				echo '<col style="width: ', $column[1], '" />';
			} else {
				$rowWidth[] = floor(100 / count($admin->table['columns'])) . '%';
				echo '<col />';
			}
		}
		$rowWidth[] = '5%';
		echo '<col style="width: 5%" />';
		echo '</colgroup> -->';
		echo '<tbody>';
		echo '<tr>';
		foreach ($admin->table['columns'] as $column) {
			$sortClass = '';
			$caretClass = '';
			if (isset($admin->table['sort']) && isset($admin->table['sort'][$column[0]])) {
				if ($admin->table['sort'][$column[0]] == 1) {
					$sortClass = ' asc ';
					$caretClass = '';
				} else {
					$sortClass = ' desc ';
					$caretClass = ' caret-up';
				}
			}
			echo '
			<th style="width: ', array_shift($rowWidth), '" class="vcms-th">
			<a class="tablesort', $sortClass, '" data-field="', $column[0], '">', $column[2],
			(($sortClass != '') ? '<span class="caret' . $caretClass . '"></span>' : ''),
			'</a>
			</th>';
		}
		if ($delete == true) {
			echo '<th style="width: 65px" class="vcms-th"><a class="del">Delete</a></th>';
		}
		echo '</tr>';
		foreach ($data as $row => $record) {
			echo '<tr ';
			if (isset($admin->table['subdocument']) && isset($record['value']['_id'])) {
				echo ' data-id="', (string)$record['value']['parent_id'], '" data-sub-field="', $admin->table['subdocument'], '" data-sub-id="', (string)$record['value']['_id'], '" ';
			} else {
				echo ' data-id="', $record['_id'], '" ';
			}
			echo '>';
			foreach ($admin->table['columns'] as $column) {
				$out = $record[$column[0]];
				if (!$readonly) {
					$prefix = '<a class="link">';
					$suffix = '</a>';
				} else {
					$prefix = '';
					$suffix = '';
				}
				$force = false;
				if (isset($column[3]) && is_callable($column[3])) {
					$prefix = '';
					$suffix = '';
					$function = $column[3];
					if (isset($record[$column[0]])) {
						$out = $function($record[$column[0]], $record);
					} else {
						$force = true;
						$out = $function([], $record);
					}
				}
				echo '<td>', $prefix;
				if (isset($record[$column[0]]) || $force) {
					echo $out;
				}
				echo $suffix, '</td>';
			}
			if ($delete == true) {
				echo '<td class="delete"><a class="btn" title="Delete this ', $admin->thing, '"><i class="icon-remove"></i></a></td>';
			}
			echo '</tr>';
		}
		echo '</tbody></table></div></div>';
		return ob_get_clean();
	}
	
	private static function applyFieldTransformationIn ($admin, &$request, $mode, $fieldCheck=false, $parentId=false) {
		foreach ($admin->fields as $field) {
			if ($fieldCheck !== false && $field['name'] != $fieldCheck) {
				continue;
			}
			if (!isset($field['transformIn'])) {
				continue;
			}
			if (!isset($request[$admin->marker()][$field['name']])) {
				continue;
			}
			$function = $field['transformIn'];
			$request[$admin->marker()][$field['name']] = $function($request[$admin->marker()][$field['name']], $request[$admin->marker()], $mode, $field, $admin, $parentId);
		}
	}
	
	private static function modeCoerce (&$admin, $mode) {
		if ($mode == '') {
			return;
		}
		if (!in_array($mode, ['save', 'update'])) {
			throw new \Exception('Only valid modes are: save, update');
		}
		$key = $admin->marker() . '-' . $mode;
		$step = $admin->marker() . '-' . $mode . '-step';
		$_REQUEST[$key] = true;
	}

	private static function documentSave (&$admin, &$request, &$mode, &$saved) {
		$key = $admin->marker() . '-save';
		$step = $admin->marker() . '-save-step';
		if (!isset($request[$key])) {
			return false;
		}
		if ($request[$key] == '' || $request[$key] == 'true') {
			$admin->activeRecord['_id'] = new \MongoId(); 
			$request[$key] = (string)$admin->activeRecord['_id'];
		} else {
			$admin->activeRecord['_id'] = new \MongoId($request[$key]);
		}
		$mode = 'save';
		if (!isset($request[$step]) || $request[$step] != 2) {
			return false;
		}
		if (!isset($admin->storage['collection']) || empty($admin->storage['collection'])) {
			throw new \Exception('Can not save document: no collection specified in admin.');
		}
		if (self::documentValidate($admin, $request)) {
			$request[$admin->marker()]['_id'] = new \MongoId();
			self::applyFieldTransformationIn($admin, $request, 'save');
			$document = (array)$request[$admin->marker()];
			unset($document['_id']);
			try {
				self::callCallback($admin, 'documentSave', $document);
				self::db($admin->storage['collection'])->
					update(
						['_id' => new \MongoId((string)$request[$key])], 
						['$set' => $document], 
						['safe' => true, 'fsync' => true, 'upsert' => true]);
				$document['_id'] = new \MongoId((string)$request[$key]);
				self::callCallback($admin, 'documentSaved', $document);
				$admin->notices[] = 'Record has been saved.';
			} catch (\Exception $e) {
				$admin->errors[] = $e->getMessage();
				return;
			}
			$mode = 'update';
			$saved = true;
			return (string)$request[$key];
		}
	}

	private static function callCallback ($admin, $key, &$document) {
		if (is_array($admin->{$key})) {
			foreach ($admin->{$key} as $callback) {
				$callback($admin, $document);
			}
		} else {
			$function = $admin->{$key}();
			$function($admin, $document);
		}
	}

	public static function collectionSort ($collection, $sort, $sortKey='sort_key') {
		foreach ($sort as $key => $id) {
			self::db($collection)->update(['_id' => new \MongoId($id)], ['$set' => [$sortKey => $key]]);
		}
	}

	public static function subDocumentSort ($request) {
		$parent = new $request['vc__append_to']();

        $field = $request['vc__append_field'];
        $parentId = $request['vc__append_id'];
        $topField = $field;
        $sub = false;
        if (substr_count($field, '.') > 0) {
            $sub = true;
            $topField = explode('.', $field)[0];
            $topFieldId = explode('.', $field)[1];
            $subField = explode('.', $field)[2];
            $field = Model::mongoIdToOffset($parent->storage['collection'], $parentId, $field);
        }

		$document = self::db($parent->storage['collection'])->findOne(['_id' => new \MongoId($parentId)], [$topField]);
		if (!isset($document['_id'])) {
			return;
		}
		if (!isset($document[$topField]) || !is_array($document[$topField])) {
			return;
		}
		$newArray = [];
        if ($sub) {
            foreach ($document[$topField] as $subdocument) {
                if ((string)$subdocument['_id'] == $topFieldId) {
                    $documentSort = $subdocument[$subField];
                    break;
                }
            }
            foreach ($request['sort'] as $id) {
                foreach ($documentSort as $subkey => $subdocument) {
                    if ((string)$subdocument['_id'] == $id) {
                        $newArray[] = $subdocument;
                        unset($documentSort[$subkey]);
                    }
                }
            }
            self::db($parent->storage['collection'])->update(['_id' => new \MongoId($parentId)],
                ['$set' => [$field => $newArray]]);
        } else {
            foreach ($request['sort'] as $id) {
                foreach ($document[$topField] as $subkey => $subdocument) {
                    if ((string)$subdocument['_id'] == $id) {
                        $newArray[] = $subdocument;
                        unset($document[$topField][$subkey]);
                    }
                }
            }
            self::db($parent->storage['collection'])->update(['_id' => new \MongoId($parentId)],
                ['$set' => [$topField => $newArray]]);
        }
	}

	private static function tableFilterApply ($admin, &$criteria, $filterMapreduce=false) {
		$admin->filtered = false;
		$prefix = '';
		if ($filterMapreduce) {
			$prefix = 'value.';
		}
		$countIn = serialize($criteria);
		if (!isset($admin->filterMarker)) {
			return;
		}
		if (!isset($_SESSION[$admin->filterMarker])) {
			$_SESSION[$admin->filterMarker] = [];
		}
		if (isset($_REQUEST[$admin->filterMarker])) {
			foreach ($_REQUEST[$admin->filterMarker] as $key => $value) {
				if (empty($value) && $value !== false) {
					unset($_SESSION[$admin->filterMarker][$key]);
				} elseif (is_array($value) && count($value) == 1 && (empty($value[0]) && $value[0] !== false)) {
					unset($_SESSION[$admin->filterMarker][$key]);
				} elseif (is_array($value)) {
					$blank = true;
					foreach ($value as $tKey => $tVal) {
						if (!empty($tVal) && $tVal !== false) {
							$blank = false;
							break;
						}
					}
					if (!$blank) {
						$_SESSION[$admin->filterMarker][$key] = $value;
					} else {
                        unset($_SESSION[$admin->filterMarker][$key]);
					}
				} else {
					$_SESSION[$admin->filterMarker][$key] = $value;
				}
			}
		}

		$filterObject = new $admin->filterClass();
		foreach ($_SESSION[$admin->filterMarker] as $key => $value) {
			if (preg_match('/_temp$/', $key)) {
				continue;
			}
			$_REQUEST[$admin->filterMarker][$key] = $value;
			if (isset($filterObject->fieldsByKey[$key]['transformIn'])) {
				$function = $filterObject->fieldsByKey[$key]['transformIn'];
				$value = $function($value);
			}
			if (isset($filterObject->fieldsByKey[$key]['filterCriteria'])) {
				$function = $filterObject->fieldsByKey[$key]['filterCriteria'];
				$function($value, $criteria, $prefix, $_REQUEST[$admin->filterMarker]);
				continue;
			}
			if (is_array($value) && count($value) > 0) {
				$criteria[$prefix . $key] = ['$in' => $value];
			} elseif (!is_array($value) && (!empty($value) && $value !== false)) {
				$criteria[$prefix . $key] = $value;
			}
		}
		$countOut = serialize($criteria);
		if ($countOut != $countIn) {
			$admin->filtered = true;
		}
	}
	
	private static function applyTableCustomization (&$admin, &$config) {
		if ($config === null) {
			return;
		}
		if (isset($admin->table) && is_array($admin->table) && isset($config['table']) && is_array($config['table']) && count($config['table']) > 0) {
			foreach ($config['table'] as $column) {
				self::applyColumnCustomization($admin->table['columns'], $column);
			}
		}
		if (isset($config['excel']) && is_array($config['excel']) && count($config['excel']) > 0) {
			foreach ($config['excel'] as $column) {
				self::applyColumnCustomization($admin->excel['cols'], $column);
			}
		}
	}

	private static function applyColumnCustomization (&$table, &$column) {
		if (!is_array($column)) {
			throw new \Exception('Custom column must be an array');
		}
		if (!is_integer($column[0])) {
			throw new \Exception('Position of custom column not specified');
		}
		$position = array_shift($column);
		array_splice($table, $position, 0, [$column]);
	}

	private static function documentFind ($admin, $search, $sort, $limit=100, $offset=0, &$maxrows, $excel=false) {
		self::applyTableCustomization($admin, Config::admintable()[get_class($admin)]);
		$criteria = [];
		$searchCollection = $admin->storage['collection'];
		if ($admin->documents != false && is_array($admin->documents)) {
			return $admin->documents;
		}
		if (method_exists($admin, 'documentFind')) {
			$function = $admin->documentFind();
			return $function($admin, $search, $sort, $limit, $offset, $maxrows, $excel);
		}
		$filterMapreduce = false;
		if (isset($admin->table['mapreduce'])) {
			$filterMapreduce = true;
			$command = [
				'mapreduce' => $admin->storage['collection'],
				'map' => $admin->table['mapreduce']['map'],
				'reduce' => $admin->table['mapreduce']['reduce'],
				'out' => $admin->table['mapreduce']['collection']
			];
			if (isset($admin->table['mapreduce']['query'])) { 
				$command['query'] = $admin->table['mapreduce']['query'];
			}
			$reduceResult = self::db()->command($command);
			if ($reduceResult['ok'] != true) {
				$admin->errors[] = $reduceResult["errmsg"];
				$admin->errors[] = $reduceResult["assertion"];
			}
			$admin->storage['collection'] = $admin->table['mapreduce']['collection'];
		}

		if (isset($admin->table['criteria'])) {
			if (is_callable($admin->table['criteria'])) {
				$function = $admin->table['criteria'];
				$criteria = $function();
			} else {
				$criteria = $admin->table['criteria'];
			}
		}

		if ($search !== false) {
			$search .= ' AND db:' . Config::db()['name'] . ' AND collection:' . $searchCollection;
			$results = \vc\cl\Solr::search($admin->indexUrl, $search, []);
			$ids = [];
			foreach ($results as $result) {
				$ids[] = new \MongoId($result['id']);
			}
			$criteria[$admin->searchInclusionKey] = ['$in' => $ids];
		}
		try {
			self::tableFilterApply($admin, $criteria, $filterMapreduce);
		} catch (\Exception $e) {
			$admin->errors[] = $e->getMessage();
			return;
		}
		if (isset($admin->mustFilter) && !$admin->filtered) {
			return [];
		}
		$cursor = self::db($admin->storage['collection'])->find($criteria, []);
		if (isset($admin->logCriteria)) {
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/criteria.txt', print_r($criteria, true));
		}
		$maxrows = $cursor->count();
		if ($excel == true) {
			if (!isset($admin->excel['title']) || !isset($admin->excel['file']) || !isset($admin->excel['cols'])) {
				throw new \Exception('To generate an Excel file, the title, file and cols must be set in the Admin file.');
			}
			if (!isset($admin->excel['flatten'])) {
				$admin->excel['flatten'] = false;
			}
			$excelSort = [];
			if (isset($admin->excel['sort'])) {
				$excelSort = $admin->excel['sort'];
			} else {
				$excelSort = $sort;
			}
			if (!isset($admin->excel['finishCallback'])) {
				$admin->excel['finishCallback'] = false;
			}

            if (isset($admin->excel['variableFields'])) {
                $isMapreduce = false;
                if (isset($admin->table['mapreduce'])) {
                    $isMapreduce = true;
                }
                if (!isset($admin->excel['variableFieldsExcept'])) {
                    $admin->excel['variableFieldsExcept'] = [];
                }
                self::documentToExcelVariableFields($isMapreduce, $admin->excel['cols'], $admin->storage['collection'], $criteria, $admin->excel['variableFieldsExcept']);
            }

			$cursor->
				sort($excelSort)->
				limit(10000)->
				fetchToExcel($admin->excel['title'], $admin->excel['file'], $admin->excel['cols'], true, $admin->excel['finishCallback'], $admin->excel['flatten']);
			exit;
		}

		return $cursor->
			limit($limit)->
			skip($offset)->
			sort($sort)->
			fetchAll();
	}

    public static function documentToExcelVariableFields ($isMapreduce, &$columns, $collection, $criteria, $columnsExcept=[]) {
        $doc = self::db($collection)->findOne($criteria);
        if (!isset($doc['_id'])) {
            return;
        }
        if ($isMapreduce) {
            $doc = $doc['value'];
        }
        $tmpCols = [];
        foreach ($columns as $column) {
            $tmpCols[] = $column[0];
        }
        foreach ($doc as $fieldKey => $fieldName) {

            if (in_array($fieldKey, $tmpCols) || in_array($fieldKey, $columnsExcept)) {
                continue;
            }
            $columns[] = [$fieldKey, $fieldKey, function (&$value, &$record) use ($isMapreduce, $fieldKey) {
                if ($isMapreduce) {
                    $data = $record['value'][$fieldKey];
                } else {
                    $data = $record[$fieldKey];
                }
                if (is_a($data, 'MongoId')) {
                    $data = (string)$data;
                }
                if (is_a($data, 'MongoDate')) {
                    $data = date('Y-m-d', $data->sec);
                }
                if (is_array($data)) {
                    $data = var_export($data, true);
                }
                return $data;
            }];
        }
    }
	
	public static function tableOffset ($admin, $limit, &$request) {
		if (isset($request[$admin->marker() . '-page']) && is_numeric($request[$admin->marker() . '-page'])) {
			return (($request[$admin->marker() . '-page'] - 1) * $limit);
		}
		return 0;
	}
	
	private static function tableSearch ($admin, &$request) {
		if (isset($request[$admin->marker() . '-search']) && trim($request[$admin->marker() . '-search']) != '') {
			return $request[$admin->marker() . '-search'];
		}
		return false;
	}

	private static function tableSort ($admin, &$request) {
		if (isset($request[$admin->marker() . '-sort'])) {
			$sort = explode(':', $request[$admin->marker() . '-sort']);
			$sort = [
				$sort[0] => (($sort[1] == 'asc') ? 1 : -1)
			];
			$admin->table['sort'] = $sort;
			return $sort;
		}
		if (isset($admin->table['sort'])) {
			return $admin->table['sort'];
		}
		return [];
	}
	
	private static function documentFindOne ($admin, $id, &$request) {
		//coming here from a sub-document found in a map-reduce
		if (isset($request['vc__append_subid']) && isset($request['vc__append_field'])) {
			if (!empty($request['vc__append_subid']) && !empty($request['vc__append_field'])) {
				$request['vc__append_id'] = $id;
				$request['vc__append_to'] = get_class($admin);
				$id = $request['vc__append_subid'];
				$request[$admin->marker() . '-update'] = $id;
				$request['vc__append_simulate'] = 1;
			}
		}
		if (isset($request['vc__append_to']) && isset($request['vc__append_field']) && isset($request['vc__append_id'])) {
			$parent = new $request['vc__append_to']();
            $field = $request['vc__append_field'];
            $parentId = $request['vc__append_id'];
            $topField = $field;
            $sub = false;
            if (substr_count($field, '.') > 0) {
                $sub = true;
                $topField = explode('.', $field)[0];
                $subId = $id;
                $id = explode('.', $field)[1];
                $subField = explode('.', $field)[2];
            }
            $document = self::db($parent->storage['collection'])->findOne(['_id' => new \MongoId($parentId)], [$topField]);
			if (!isset($document['_id'])) {
				return;
			}
			if (!isset($document[$topField]) || !is_array($document[$topField])) {
				return;
			}
			foreach ($document[$topField] as $subdocument) {
				if ((string)$subdocument['_id'] == $id) {
					$document = $subdocument;
					break;
				}
			}
            if ($sub) {
                foreach ($document[$subField] as $subdocument) {
                    if ((string)$subdocument['_id'] == $subId) {
                        $document = $subdocument;
                        break;
                    }
                }
            }
		} else {
			$document = self::db($admin->storage['collection'])->
				findOne(['_id' => new \MongoId((string)$id)], []);
		}

		self::documentTransformOut($admin, $document);

		return $document;
	}
	
	private static function documentTransformOut ($admin, &$document) {
		foreach ($admin->fields as &$field) {
			if (!isset($field['transformOut'])) {
				continue;
			}
			if (isset($field['transformOutDone'])) {
				continue;
			}
			$field['transformOutDone'] = true;
			$function = $field['transformOut'];
			$document[$field['name']] = $function($document[$field['name']]);
		}
	}

	private static function documentRemove ($admin, &$request) {
		$key = $admin->marker() . '-remove';
		if (!isset($request[$key])) {
			return false;
		}
		if (isset($request['vc__append_to']) && isset($request['vc__append_field']) && isset($request['vc__append_id'])) {
			$parent = new $request['vc__append_to']();
            $field = $request['vc__append_field'];
            $parentId = $request['vc__append_id'];
            $topField = $field;
            $sub = false;
            if (substr_count($field, '.') > 0) {
                $sub = true;
                $topField = explode('.', $field)[0];
                $field = Model::mongoIdToOffset($parent->storage['collection'], $parentId, $field);
            }
            $pull = [
                '$pull' => [
                    $topField => [
                        '_id' => new \MongoId((string)$request[$key])
                    ]
                ]];
            if ($sub) {
                $pull = [
                    '$pull' => [
                        $field => [
                            '_id' => new \MongoId((string)$request[$key])
                        ]
                    ]
                ];
            }
            self::db($parent->storage['collection'])->
                update([
                    '_id' => new \MongoId($parentId)
                ], $pull);
            $document = [];
			$function = $admin->documentAppendRemoved();
			$function($admin, $document, $request);
			return true;
		}
		$function = $admin->documentRemove();
		$function($admin, $request);
		try {
			self::db($admin->storage['collection'])->remove(['_id' => new \MongoId($request[$key])]);
		} catch (\Exception $e) {
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/Exception.txt', $e->getTraceAsString());
			return;
		}
		$function = $admin->documentRemoved();
		$function($admin, $request);
	}

	public static function documentUpdate ($admin, &$request, &$mode, &$updated) {
		$key = $admin->marker() . '-update';
		$step = $admin->marker() . '-update-step';
		if (!isset($request[$key])) {
			return false;
		}
		$mode = 'update';
		if (!isset($request[$step]) || $request[$step] != 2) {
			return $request[$key];
		}
		if (isset($request['vc__append_to']) && isset($request['vc__append_field']) && isset($request['vc__append_id'])) {
			$parent = new $request['vc__append_to']();
            $field = $request['vc__append_field'];
            $parentId = $request['vc__append_id'];
            $topField = $field;
            $sub = false;
            if (substr_count($field, '.') > 0) {
                $sub = true;
                $topField = explode('.', $field)[0];
                $field .= '.' . (string)$request[$key];
                $subId = $request[$key];
                $id = explode('.', $field)[1];
                $subField = explode('.', $field)[2];
                $field = Model::mongoIdToOffset($parent->storage['collection'], $parentId, $field);
            }
			$document = self::db($parent->storage['collection'])->findOne(['_id' => new \MongoId($parentId)], [$topField]);
			if (!isset($document['_id'])) {
				return;
			}
			if (!isset($document[$topField]) || !is_array($document[$topField])) {
				return;
			}
			$request[$admin->marker()]['_id'] = new \MongoId($request[$key]);
			$subdocumentEditted = [];
			self::applyFieldTransformationIn($admin, $request, 'update', false, $parentId);
			$subdocument = (array)$request[$admin->marker()];
			$function = $admin->documentAppendUpdate();
			$function($admin, $document, $subdocument);
			if (!self::documentValidate($admin, $request)) {
				return false;
			}
			$subdocumentEditted = $subdocument;
			try {
                if ($sub) {
                    self::db($parent->storage['collection'])->update(
                        ['_id' => new \MongoId((string)$parentId)],
                        ['$set' => [$field => $subdocument]]
                    );
                    foreach ($document[$topField] as &$tempDoc) {
                        if ((string)$tempDoc['_id'] == (string)$id) {
                            foreach ($tempDoc[$subField] as &$tempDoc2) {
                                if ((string)$tempDoc2['_id'] == (string)$subId) {
                                    $tempDoc2 = $subdocument;
                                    break;
                                }
                            }
                            break;
                        }
                    }
                } else {
                    self::db($parent->storage['collection'])->update(
                        [
                            '_id' => new \MongoId((string)$parentId),
                            ($topField . '._id') => new \MongoId((string)$request[$admin->marker()]['_id'])
                        ],
                        ['$set' => [
                            ($topField . '.$') => $subdocument]
                        ]
                    );
                    foreach ($document[$topField] as $tempDoc) {
                        if ((string)$tempDoc['_id'] == (string)$request[$admin->marker()]['_id']) {
                            $document[$topField] = $subdocument;
                            break;
                        }
                    }
                }
				$function = $admin->documentAppendUpdated();
				$function($admin, $document, $subdocumentEditted);
				$updated = true;
				$admin->notices[] = 'Record has been updated.';
			} catch (\Exception $e) {
				$admin->errors[] = $e->getMessage();
				return;
			}
			if (isset($request['vc__append_simulate']) && $admin->appendJSFunction == 'subDocumentAppended') {
				$admin->appendJSFunction = null;
				$admin->updateJSFunction = null;
			}
			return $parentId;
		}

		if (!self::documentValidate($admin, $request)) {
			return $request[$key];
		}
		$request[$admin->marker()]['_id'] = new \MongoId($request[$key]);
		self::applyFieldTransformationIn($admin, $request, 'update');
		$query = [
			'_id' => $request[$admin->marker()]['_id']
		];
		$document = (array)$request[$admin->marker()];
		unset($document['_id']);
		try {
			self::callCallback($admin, 'documentUpdate', $document);
			self::db($admin->storage['collection'])->
				update ($query, ['$set' => $document], ['safe' => true]);
				$updated = true;
            $document['_id'] = (string)$request[$admin->marker()]['_id'];
            self::callCallback($admin, 'documentUpdated', $document);
            $admin->notices[] = 'Record has been updated.';
        } catch (\Exception $e) {
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/Exception.txt', print_r($e->getTrace(), true));
			$admin->errors[] = $e->getMessage();
			return;
		}
		return (string)$request[$admin->marker()]['_id'];
	}

	private static function documentAppend ($admin, &$request, &$mode, &$appended) {
		$key = $admin->marker() . '-append';
		$step = $admin->marker() . '-append-step';
		if (!isset($request[$key])) {
			return false;
		}
		$mode = 'append';
		$parentClass = $request['vc__append_to'];
		$parent = new $parentClass();
		if (!isset($request[$step]) || $request[$step] != 2) {
			return $request[$key];
		}
		if (!self::documentValidate($admin, $request)) {
			return $request[$key];
		}
        $id = $request['vc__append_id'];
        $field = $request['vc__append_field'];
        $topField = $field;
        if (substr_count($field, '.') > 0) {
            $topField = explode('.', $field)[0];
            $field = Model::mongoIdToOffset($parent->storage['collection'], $id, $field);
        }
		$request[$admin->marker()]['_id'] = new \MongoId();
        $admin->appendedId = (string)$request[$admin->marker()]['_id'];
		self::applyFieldTransformationIn($admin, $request, 'append');
		$query = ['_id' => new \MongoId((string)$id)];
		$document = (array)$request[$admin->marker()];
		$record = self::db($parent->storage['collection'])->findOne($query, [$field]);
		if (!isset($record[$topField]) || !is_array($record[$topField])) {
			self::db($parent->storage['collection'])->update($query, ['$set' => [$topField => []]], ['upsert' => true]);
		}
		try {
			$function = $admin->documentAppend();
			$function($admin, $record, $document);
			self::db($parent->storage['collection'])->
				update ($query, ['$push' => [$field => $document]], ['safe' => true]);
			$function = $admin->documentAppended();
			$function($admin, $record, $document);
			$appended = true;
			$admin->notices[] = 'Record has been updated.';
		} catch (\Exception $e) {
			$admin->errors[] = $e->getMessage();
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/Exception.txt', print_r($e->getTrace(), true));
			return;
		}
		return (string)$id;
	}

	public static function documentValidate ($admin, &$request, $fieldCheck=false) {
		$passed = true;
		foreach ($admin->fields as $field) {
            if ($field['label'] == '' && isset($field['errorLabel'])) {
                $field['label'] = $field['errorLabel'];
            }
			if ($fieldCheck !== false && $field['name'] != $fieldCheck) {
				continue;
			}
			if (isset($field['required']) && is_callable($field['required'])) {
				$required = $field['required'];
				$field['required'] = $required($request[$admin->marker()][$field['name']], $request[$admin->marker()]);
			}
			if (isset($field['required']) && $field['required'] == true) {
				if (!self::fieldValidateRequired ($field, $request, $admin->marker())) {
					$passed = false;
					if (isset($field['label']) && $field['label'] != '') {
						$admin->errors[] = $field['label'] . ' must have a value.';
					} else {
						$admin->errors[] = ucwords(str_replace('_', ' ', $field['name'])) . ' must have a value.';
					}
					continue;
				}
			}
			if (isset($field['validate'])) {
				$validate = $field['validate'];
				$error = $validate($request[$admin->marker()][$field['name']], $request[$admin->marker()]);
				if ($error !== true) {
					$passed = false;
					if (isset($field['label']) && $field['label'] != '') {
						$admin->errors[] = $field['label'] . ': ' . $error;
					} else {
						$admin->errors[] = ucwords(str_replace('_', ' ', $field['name'])) . ': ' . $error;
					}
				}
			}
		}
		return $passed;
	}

	private static function fieldValidateRequired ($field, &$request, $marker) {
		if (substr_count($field['name'], '-') == 0) {
			if (isset($request[$marker]) && isset($request[$marker][$field['name']])) {
				if (is_array($request[$marker][$field['name']])) {
					if (count($request[$marker][$field['name']]) == 0) {
						return false;
					}
				} elseif (trim($request[$marker][$field['name']]) == '') {
					return false;
				}
			} else {
				return false;
			}
		} else {
			$parts = explode('-', $field['name']);
			$size = count($parts);
			if ($size > 3) {
				throw new \Exception('too many parts in field name.');
			}
			if ($size == 2 && (isset($request[$marker]) && isset($request[$marker][$parts[0]][$parts[1]]))) {
				if (trim($request[$marker][$parts[0]][$parts[1]]) == '') {
					return false;
				}
			} elseif ($size == 3 && (isset($request[$marker]) && isset($request[$marker][$parts[0]][$parts[1]][$parts[2]]))) {
				if (trim($request[$marker][$parts[0]][$parts[1]][$parts[2]]) == '') {
					return false;
				}
			} else {
				return false;
			}
		}
		return true;
	}
	
	public static function errorsRender (&$errors) {
		ob_start();
		if (count($errors) > 1) {
			echo '<ul>';
			foreach ($errors as $error) {
				echo '<li>', $error, '</li>';
			}
			echo '</ul>';
		} else {
			echo $errors[0];
		}
		return ob_get_clean();
	}
	
	public static function upload ($options, $ckeditor=false) {
		$info = Upload::handle(array_merge([
			'upload_dir' => '/tmp/',
			'upload_url' => '/tmp/'
		], $options));
		$upload = false;
		if (is_array($info) && count($info) > 0) {
			foreach ($info as &$file) {
				if (!isset($file['size'])) {
					continue;
				}
				if ($file['size'] == 0 || $file['size'] == '') {
					continue;
				}
				$upload = true;
				$s3Path = 'files/' . date('Y-m-d-H') . '/' . $file['name'];
				Storage::s3()->putObjectFile($file['url'], S3_BUCKET, $s3Path, \vc\cl\S3::ACL_PUBLIC_READ);
				unlink($file['url']);
				$file['url'] = S3_URL . $s3Path;
				if (preg_match('/\.(jpg|gif|png)$/i', $file['url']) != 0) {
					$file['thumbnail_url'] = ImageResizer::getPath($file['url'], 50, 50, '1:1');
				}
			}
			if ($upload) {
				if ($ckeditor === false) {
					Upload::json($info);
					return;
				}
				$errorNumber = 0;
				$msg = "";
				switch ($errorNumber) {
					case 0:
						$msg = 'Upload successful';
						break;
	
					case 1:
						$msg = 'An error occurred';
						break ;
	
					case 201:
						$msg = 'A file with the same name is already available. The uploaded file has been renamed to "' . $file['name'] . '"' ;
						break;
	
					case 202:
						$msg = 'Invalid file' ;
						break ;
	
					default:
						$msg = 'Error on file upload. Error number: ' + $errorNumber ;
						break ;
				}
	
				$rpl = array('\\' => '\\\\', '"' => '\\"');
				echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(', $_REQUEST['CKEditorFuncNum'], ', "' . strtr($file['url'], $rpl) . '", \'\');</script>';
				return;
			}
		}
	}

	public static function classNameToMarker ($class) {
		return ltrim(strtolower(str_replace('\\', '_', $class)), '_');
	}
	
	public static function subDocumentToRequest($marker, $document) {
		if (empty($marker)) {
			return;
		}
		$_REQUEST[$marker] = $document;
		unset($_REQUEST[$marker]['_id']);
		unset($_REQUEST[$marker]['template']);
	}

    public static function subDocumentPostModifyRequest ($adminData) {
        if (substr_count($_REQUEST['vc__selector'], '.') == 2) {
            $subField = str_replace('#', '', explode('.', $_REQUEST['vc__selector'])[0]);
            $subId = explode('.', $_REQUEST['vc__selector'])[1];
            $selector = '#' . explode('.', $_REQUEST['vc__selector'])[2];
            $collection = $adminData['admin']->storage['collection'];
            $id = $adminData['data']['id'];
            $document = Model::db($collection)->findOne([
                '_id' => new \MongoId((string)$id)
            ], [
                $subField => [
                    '$elemMatch' => [
                        '_id' => new \MongoId((string)$subId)
                    ]
                ]
            ])[$subField][0];
            $subAdmin = str_replace('__', '\\', $document['template']);

            $newRequest = [
                'N__ajax' => 1,
                'vc__admin' => [$subAdmin],
                'vc__append_field' => $subField,
                'vc__append_id' => $adminData['data']['id'],
                'vc__append_to' => $_REQUEST['vc__admin'][0],
                'vc__display' => 'modal',
                (DOMFormTable::makeMarker($subAdmin, 'update')) => $subId,
                'vc__selector' => $selector
            ];
            $_REQUEST = $newRequest;
            $controller = new AdminController();
            $controller->action();
            exit;
        }
    }
}