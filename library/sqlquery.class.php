<?php

class SQLQuery {
    protected $_dbHandle;
    protected $_result;
	protected $_query;
	protected $_table;

	protected $_describe = array();

	protected $_orderBy;
	protected $_order;
	protected $_extraConditions;
	protected $_hO;
	protected $_hM;
	protected $_hMABTM;
	protected $_page;
	protected $_limit;

    /** Connects to database **/
	
    function connect($address, $account, $pwd, $name) {
        $this->_dbHandle = @mysql_connect($address, $account, $pwd);
        if ($this->_dbHandle != 0) {
            if (mysql_select_db($name, $this->_dbHandle)) {
                return 1;
            }
            else {
                return 0;
            }
        }
        else {
            return 0;
        }
    }
 
    /** Disconnects from database **/

    function disconnect() {
        if (@mysql_close($this->_dbHandle) != 0) {
            return 1;
        }  else {
            return 0;
        }
    }

    /** Select Query **/

	function where($field, $value) {
		$this->_extraConditions .= '`'.$this->_model.'`.`'.$field.'` = \''.mysql_real_escape_string($value).'\' AND ';
	}

	function like($field, $value) {
		$this->_extraConditions .= '`'.$this->_model.'`.`'.$field.'` LIKE \'%'.mysql_real_escape_string($value).'%\' AND ';
	}

	function showHasOne() {
		$this->_hO = 1;
	}

	function showHasMany() {
		$this->_hM = 1;
	}
	
	function hideHasOne() {
		$this->_hO = 0;
	}

	function hideHasMany() {
		$this->_hM = 0;
	}

	function showHMABTM() {
		$this->_hMABTM = 1;
	}

	function setLimit($limit) {
		$this->_limit = $limit;
	}

	function setPage($page) {
		$this->_page = $page;
	}

	function orderBy($orderBy, $order = 'ASC') {
		$this->_orderBy = $orderBy;
		$this->_order = $order;
	}

	function select($id) {
    	$query = 'select * from `'.$this->_table.'` where `id` = \''.mysql_real_escape_string($id).'\'';
		$result = mysql_query($query, $this->_dbHandle);
		if(!$result || $result == null){
			return false;	
		}
    	return json_decode(json_encode($result),false);
    }
	
	function select_all($field,$case) {
    	$query = 'select * from `'.$this->_table.'` where `'.$field.'` = \''.mysql_real_escape_string($case).'\'';
		//print_r($query);
		$result = mysql_query($query, $this->_dbHandle);
		if(!$result || $result == null){
			return false;	
		}
    	return json_decode(json_encode($result),false);
    }

	function search() {
		
		$inflect = new Inflection();;
		/*
		Normalize multi-word model names like UserFiles
		to their databes table name user_files
		*/
	
		if($this->count_capitals($this->_model)>1){
			$this->_table = $inflect->from_camel_case($inflect->pluralize($this->_model));
		}
		$from = '`'.$this->_table.'` as `'.$this->_model.'` ';
		//print_r($from);
		$conditions = '\'1\'=\'1\' AND ';
		$conditionsChild = '';
		$fromChild = '';

		if ($this->_hO == 1 && isset($this->hasOne) && $this->hasOne != null) {
			
			foreach ($this->hasOne as $alias => $model) {
				#echo '<!--'.$alias." = ".$model.'-->';;
				$table = strtolower($inflect->pluralize($model));
				$singularAlias = strtolower($alias);
				$from .= 'LEFT JOIN `'.$table.'` as `'.$alias.'` ';
				$from .= 'ON `'.$this->_model.'`.`'.$singularAlias.'_id` = `'.$alias.'`.`id`  ';
			}
		}
	
		if (isset($this->id)) {
			$conditions .= '`'.$this->_model.'`.`id` = \''.mysql_real_escape_string($this->id).'\' AND ';
		}

		if ($this->_extraConditions) {
			$conditions .= $this->_extraConditions;
		}

		$conditions = substr($conditions,0,-4);
		
		if (isset($this->_orderBy)) {
			$conditions .= ' ORDER BY `'.$this->_model.'`.`'.$this->_orderBy.'` '.$this->_order;
		}

		if (isset($this->_page)) {
			$offset = ($this->_page-1)*$this->_limit;
			$conditions .= ' LIMIT '.$this->_limit.' OFFSET '.$offset;
		}
		
		$this->_query = 'SELECT * FROM '.$from.' WHERE '.$conditions;
				$this->_result = mysql_query($this->_query, $this->_dbHandle);
		//echo $this->_query . "<br>";
		//echo mysql_num_rows($this->_result) . "<br>";
		$result = array();
		$table = array();
		$field = array();
		$tempResults = array();
		$numOfFields = mysql_num_fields($this->_result);
		for ($i = 0; $i < $numOfFields; ++$i) {
		    array_push($table,mysql_field_table($this->_result, $i));
		    array_push($field,mysql_field_name($this->_result, $i));
		}
		if (mysql_num_rows($this->_result) > 0 ) {
			while ($row = mysql_fetch_row($this->_result)) {
				for ($i = 0;$i < $numOfFields; ++$i) {
					$tempResults[$table[$i]][$field[$i]] = $row[$i];
				}

				if ($this->_hM == 1 && isset($this->hasMany) && $this->hasMany != null) {
					foreach ($this->hasMany as $aliasChild => $modelChild) {
						$queryChild = '';
						$conditionsChild = '';
						$fromChild = '';
						$tableChild = strtolower($inflect->pluralize($modelChild));
						$pluralAliasChild = strtolower($inflect->pluralize($aliasChild));
						$singularAliasChild = strtolower($aliasChild);

						$fromChild .= '`'.$tableChild.'` as `'.$aliasChild.'`';
						
						$conditionsChild .= '`'.$aliasChild.'`.`'.strtolower($this->_model).'_id` = \''.$tempResults[$this->_model]['id'].'\'';
	
						$queryChild =  'SELECT * FROM '.$fromChild.' WHERE '.$conditionsChild;	
						//echo '<br>'.$queryChild.' has many <br>';
						$resultChild = mysql_query($queryChild, $this->_dbHandle);
					
						$tableChild = array();
						$fieldChild = array();
						$tempResultsChild = array();
						$resultsChild = array();
						
						if (mysql_num_rows($resultChild) > 0) {
							$numOfFieldsChild = mysql_num_fields($resultChild);
							for ($j = 0; $j < $numOfFieldsChild; ++$j) {
								array_push($tableChild,mysql_field_table($resultChild, $j));
								array_push($fieldChild,mysql_field_name($resultChild, $j));
							}

							while ($rowChild = mysql_fetch_row($resultChild)) {
								for ($j = 0;$j < $numOfFieldsChild; ++$j) {
									$tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
								}
								array_push($resultsChild,$tempResultsChild);
							}
						}
						
						$tempResults[$aliasChild] = $resultsChild;
						
						mysql_free_result($resultChild);
					}
				}


				if ($this->_hMABTM == 1 && isset($this->hasManyAndBelongsToMany) && $this->hasManyAndBelongsToMany != null) {
					foreach ($this->hasManyAndBelongsToMany as $aliasChild => $tableChild) {
						$queryChild = '';
						$conditionsChild = '';
						$fromChild = '';

						$tableChild = strtolower($inflect->pluralize($tableChild));
						$pluralAliasChild = strtolower($inflect->pluralize($aliasChild));
						$singularAliasChild = strtolower($aliasChild);

						$sortTables = array($this->_table,$pluralAliasChild);
						sort($sortTables);
						$joinTable = implode('_',$sortTables);

						$fromChild .= '`'.$tableChild.'` as `'.$aliasChild.'`,';
						$fromChild .= '`'.$joinTable.'`,';
						
						$conditionsChild .= '`'.$joinTable.'`.`'.$singularAliasChild.'_id` = `'.$aliasChild.'`.`id` AND ';
						$conditionsChild .= '`'.$joinTable.'`.`'.strtolower($this->_model).'_id` = \''.$tempResults[$this->_model]['id'].'\'';
						$fromChild = substr($fromChild,0,-1);

						$queryChild =  'SELECT * FROM '.$fromChild.' WHERE '.$conditionsChild;	
						//echo '<br>'.$queryChild.' hABTM<br>';
						$resultChild = mysql_query($queryChild, $this->_dbHandle);
						
						$tableChild = array();
						$fieldChild = array();
						$tempResultsChild = array();
						$resultsChild = array();
						
						if (mysql_num_rows($resultChild) > 0) {
							$numOfFieldsChild = mysql_num_fields($resultChild);
							for ($j = 0; $j < $numOfFieldsChild; ++$j) {
								array_push($tableChild,mysql_field_table($resultChild, $j));
								array_push($fieldChild,mysql_field_name($resultChild, $j));
							}

							while ($rowChild = mysql_fetch_row($resultChild)) {
								for ($j = 0;$j < $numOfFieldsChild; ++$j) {
									$tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
								}
								array_push($resultsChild,$tempResultsChild);
							}
						}
						
						$tempResults[$aliasChild] = $resultsChild;
						mysql_free_result($resultChild);
					}
				}

				array_push($result,$tempResults);
			}
			
			if (mysql_num_rows($this->_result) == 1 && isset($this->id)) {
				mysql_free_result($this->_result);
				$this->clear();
				return(json_decode(json_encode($result[0]),false));
			} else {
				mysql_free_result($this->_result);
				$this->clear();
				return(json_decode(json_encode($result),false));
			}
		} else {
			
			mysql_free_result($this->_result);
			$this->clear();
			return json_decode(json_encode($result),false);
		}

	}

    /** Custom SQL Query **/

	function custom($query) {
		//print_r($query) . "<br>";
		$inflect = new Inflection();

		$this->_result = mysql_query($query, $this->_dbHandle);
		if(!$this->_result){
			return false;	
		}
		$result = array();
		$table = array();
		$field = array();
		$tempResults = array();

		if(substr_count(strtoupper($query),"SELECT")>0) {
			if (mysql_num_rows($this->_result) > 0) {
				$numOfFields = mysql_num_fields($this->_result);
				for ($i = 0; $i < $numOfFields; ++$i) {
					array_push($table,mysql_field_table($this->_result, $i));
					array_push($field,mysql_field_name($this->_result, $i));
				}
					while ($row = mysql_fetch_row($this->_result)) {
						for ($i = 0;$i < $numOfFields; ++$i) {
							$table[$i] = ucfirst($inflect->singularize($table[$i]));
							$tempResults[$table[$i]][$field[$i]] = $row[$i];
						}
						array_push($result,$tempResults);
					}
			}
			mysql_free_result($this->_result);
		}	
		$this->clear();
		return($result);
	}

    /** Describes a Table **/

	protected function _describe() {
		$cache = new Cache();

		$this->_describe = $cache->get('describe'.$this->_table);

		if (!$this->_describe) {
			$this->_describe = array();
			$query = 'DESCRIBE '.$this->_table;
			$this->_result = mysql_query($query, $this->_dbHandle);
			while ($row = mysql_fetch_row($this->_result)) {
				 array_push($this->_describe,$row[0]);
			}

			mysql_free_result($this->_result);
			$cache->set('describe'.$this->_table,$this->_describe);
		}
		
		foreach ($this->_describe as $field) {
			$this->$field = null;
		}
		
	}

    /** Delete an Object **/

	function delete($id= NULL) {
		if ($this->id || $id >= 1) {
			if($id >= 1){
				$this->id = $id;	
			}
			$query = 'DELETE FROM '.$this->_table.' WHERE `id`=\''.mysql_real_escape_string($this->id).'\'';		
			$this->_result = mysql_query($query, $this->_dbHandle);
			$this->clear();
			if ($this->_result == 0) {
			    /** Error Generation **/
				return -1;
		   }
		} else {
			/** Error Generation **/
			return -1;
		}
	}

    /** Saves an Object i.e. Updates/Inserts Query **/

	function my_save($post){
		$inflect = new Inflection();
		
		if(!isset($this->_table)){
			if($this->count_capitals(get_class($this))>1){
				$this->_table = $inflect->from_camel_case($inflect->pluralize(get_class($this)));
			}else{
				$this->_table = strtolower($inflect->pluralize(get_class($this)));
			}
		}
		
		$fields = '';
		$values = '';
		foreach ($post as $field=>$value) {
			$fields .= '`'.$field.'`,';
			$values .= '\''.mysql_real_escape_string($value).'\',';
		}
		
		$values = substr($values,0,-1);
		$fields = substr($fields,0,-1);
$table_colums = $this->field_list();
		$query = 'INSERT INTO '.$this->_table.' ('.$fields.') VALUES ('.$values.')';
		$ret = $this->custom($query);
		return $ret;
	}
	
	function field_list(){
		$res = $this->custom("DESCRIBE ".$this->_table);
		//die(print_r($res));
	}
	
	function save() {
		$query = '';
		if (isset($this->id)) {
			$updates = '';
			foreach ($this->_describe as $field) {
				if ($this->$field) {
					$updates .= '`'.$field.'` = \''.mysql_real_escape_string($this->$field).'\',';
				}
			}

			$updates = substr($updates,0,-1);

			$query = 'UPDATE '.$this->_table.' SET '.$updates.' WHERE `id`=\''.mysql_real_escape_string($this->id).'\'';			
		} else {
			$fields = '';
			$values = '';
			foreach ($this->_describe as $field) {
				if ($this->$field) {
					$fields .= '`'.$field.'`,';
					$values .= '\''.mysql_real_escape_string($this->$field).'\',';
				}
			}
			$values = substr($values,0,-1);
			$fields = substr($fields,0,-1);

			$query = 'INSERT INTO '.$this->_table.' ('.$fields.') VALUES ('.$values.')';
		}
		$this->_result = mysql_query($query, $this->_dbHandle);
		$this->clear();
		if ($this->_result == 0) {
            /** Error Generation **/
			return -1;
        }
	}
 
	/** Clear All Variables **/

	function clear() {
		foreach($this->_describe as $field) {
			$this->$field = null;
		}

		$this->_orderby = null;
		$this->_extraConditions = null;
		$this->_hO = null;
		$this->_hM = null;
		$this->_hMABTM = null;
		$this->_page = null;
		$this->_order = null;
	}

	/** Pagination Count **/

	function totalPages() {
		if ($this->_query && $this->_limit) {
			$pattern = '/SELECT (.*?) FROM (.*)LIMIT(.*)/i';
			$replacement = 'SELECT COUNT(*) FROM $2';
			$countQuery = preg_replace($pattern, $replacement, $this->_query);
			$this->_result = mysql_query($countQuery, $this->_dbHandle);
			$count = mysql_fetch_row($this->_result);
			$totalPages = ceil($count[0]/$this->_limit);
			return $totalPages;
		} else {
			/* Error Generation Code Here */
			return -1;
		}
	}

	/** Cast an object to another object **/
	function cast($destination, $sourceObject)
	{
		if (is_string($destination)) {
			$destination = new $destination();
		}
		$sourceReflection = new ReflectionObject($sourceObject);
		$destinationReflection = new ReflectionObject($destination);
		$sourceProperties = $sourceReflection->getProperties();
		foreach ($sourceProperties as $sourceProperty) {
			$sourceProperty->setAccessible(true);
			$name = $sourceProperty->getName();
			$value = $sourceProperty->getValue($sourceObject);
			if ($destinationReflection->hasProperty($name)) {
				$propDest = $destinationReflection->getProperty($name);
				$propDest->setAccessible(true);
				$propDest->setValue($destination,$value);
			} else {
				$destination->$name = $value;
			}
		}
		#print_r($destination);
		return $destination;
	}

    /** Get error string **/

    function getError() {
        return mysql_error($this->_dbHandle);
    }
	
	/** Find out if model is multi-word**/
	function count_capitals($s) {
	  return strlen(preg_replace('![^A-Z]+!', '', $s));
	}
}