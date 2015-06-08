<?php namespace Muvuca\Model;

abstract class AbstractModel {

	protected $id;
	protected $active;


	public function __construct($nameValue = []) {
		foreach ($nameValue as $name => $value) {
			$this->__set($name, $value);
		}
	}

	/**
	 * Return the attribute $name or null if it doesn't exist
	 */
	public function __get($name) {
		if (property_exists($this, $name)) {
			if (property_exists($this, $name . 'Id') && !$this->$name) {
				$this->lazyLoad($name);
			}
			if (substr($name, -1) == 's') {
				$columns = $this->getTableColumns();
				if (!in_array($name, $columns)) {
					$class = $this->getModelClass(rtrim($name, 's'));
					if (class_exists($class)) {
						$this->$name = $class::getAllByForeignId(
							$this->getTableName(get_class($this)),
							$this->id
						);
					}
				}
			}
			return $this->$name;
		} else {
			$methodName = 'get' . ucfirst($name);
			if (method_exists($this, $methodName)) {
				return $this->$methodName();
			}
		}

		return null;
	}

	protected function getModelClass($name) {
		return (new \ReflectionClass($this))->getNamespaceName() . '\\' . ucfirst($name) . 'Model';
	}

	protected function lazyLoad($name) {
		$class = $this->getModelClass($name);
		$this->$name = $class::getById($this->{$name . 'Id'});
	}

	/**
	 * Verify if the attribute $name exists. If so, then verifies for a proper validator
	 * to run against the supplied $value. If no validator is found then it's assumed that
	 * there's no need to validate the supplied $value and therefore it is assigned to the
	 * attribute $name
	 */
	public function __set($name, $value) {
		// Don't allow to set IDs because they are generated automatically by the database.
		if (property_exists($this, $name)) {
			$validateMethod = 'validate' . ucfirst($name);
			if (!method_exists($this, $validateMethod) ||
				 method_exists($this, $validateMethod) && $this->$validateMethod($value)) {
				if (property_exists($this, $name . 'Id') && property_exists($value, 'id')) {
					$this->{$name . 'Id'} = $value->id;
				}
				$this->$name = $value;
			}
		}
	}

	/**
	 * Singleton: http://www.phptherightway.com/pages/Design-Patterns.html#singleton
	 */
	protected static function getDb() {
        static $db = null;

		if ($db === null) {
			try {
				$db = new \PDO('sqlite:db/cinema.sqlite3');
			} catch(\PDOException $e) {
				die('An error occured while trying to connect to the database.');
			}
		}
		return $db;
	}

	protected static function getTableName($class) {
		return strtolower(preg_replace("/^.*\\\\(.*)Model$/", '$1', $class));
	}

	protected function getTableColumns() {
		$db = self::getDb();

		$class = get_called_class();
		$table = $this->getTableName($class);

		$result = $db->query("PRAGMA table_info(" . $table . ")");
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		$columns = [];
		foreach ($result as $row) {
			$columns[] = $row;
		}
		return $columns;
	}

	/**
	 * Get all instances of the calling Class
	 */
	public static function getAll() {
		$db = self::getDb();

		$class = get_called_class();
		$table = self::getTableName($class);

		$objects = [];
		foreach ($db->query('SELECT * FROM ' . $table) as $row) {
			$objects[] = new $class($row);
		}
		return $objects;
	}

	/**
	 * Get all instances of the calling Class
	 */
	public static function getAllByForeignId($foreignTable, $foreignId) {
		$db = self::getDb();

		$class = get_called_class();
		$table = self::getTableName($class);

		$objects = [];
		$query = 'SELECT * FROM ' . $table . ' WHERE ' . $foreignTable . 'Id=' . $foreignId;
		foreach ($db->query($query) as $row) {
			$objects[] = new $class($row);
		}
		return $objects;
	}

	/**
	 * Get a instance of the calling Class with the given $id
	 */
	public static function getById($id) {
		$db = self::getDb();

		$class = get_called_class();
		$table = self::getTableName($class);

		$stmt = $db->prepare('SELECT * FROM ' . $table . ' WHERE id=?');
		if ($stmt->execute(array($id))) {
			return new $class($stmt->fetch());
		}
		return null;
	}

	public function save() {
		$db = self::getDb();

		$class = get_called_class();
		$table = $this->getTableName($class);
		$columns = $this->getTableColumns();

		$fields = [];
		$placeHolders = [];
		$values = [];
		foreach ($columns as $column) {
			$fields[$column['name']] = $column['name'];
			$placeHolders[$column['name']] = ':' . $column['name'];
			$values[$column['name']] = $this->$column['name'];
		}
		$query = 'INSERT OR REPLACE INTO ' . $table . '(' . implode(', ', $fields) . ') VALUES ('. implode(', ', $placeHolders) . ')';
		$stmt = $db->prepare($query);
		foreach ($fields as $field) {
			$stmt->bindParam($placeHolders[$field], $values[$field]);
		}
		$res = $stmt->execute();
		$this->id = $db->lastInsertId();
		return $this->id;
	}

	public function delete() {
		$db = self::getDb();
		$stmt = $db->prepare('UPDATE ' . $this->getTableName($class) . ' SET active=0 WHERE id=?');
		$stmt->execute(array($this->id));
	}

}
