<?php

class jPDO extends PDO
{
  private $show_errors = true;
  public $error = false;
  private $error_log_file = "pdo_log.txt";

  function __construct($host=MYSQL_HOST, $user=MYSQL_USER, $pass=MYSQL_PASS, $db=MYSQL_DB)
  {
	$this->_init_pdo($host, $user, $pass, $db);
  }

  public function prep_assoc($table, $assoc)
  {
	try {
	  $prep = $this->prepare("INSERT INTO `{$table}` (`" . implode("`,`", $assoc) . "`) " . 
							 "VALUES(:" . implode(",:", $assoc) . ")");
	} catch (PDOException $e) {
	  $prep = false;
	  self::_error(1, $e->getMessage());
	}

	return $prep;
  }

  public function get_album_paths()
  {
	try
	  {
		$paths = $this->query("SELECT path FROM albums");
		$result = $paths->fetchAll(self::FETCH_COLUMN, "path");
	  }
	catch (PDOException $e)
	  {
		$result = array();
		self::_error(1, $e->getMessage());
	  }

	return $result;
  }

  public function get_alnum_artists()
  {
	try
	  {
		$artists = $this->query("SELECT id, artist FROM artists");

		while ($row = $artists->fetch(self::FETCH_ASSOC))
		  $result[$row["id"]] = alnum($row["artist"]);
	  }
	catch (PDOException $e)
	  {
		self::_error(1, $e->getMessage());
	  }

	return isset($result) ? $result : array();
  }

  private function _init_pdo($host, $user, $pass, $db)
  {
	try
	  {
		parent::__construct("mysql::host={$host};dbname={$db}", $user, $pass);
	  }
	catch (PDOException $e)
	  {
		self::_error(2, $e->getMessage());
	  }
  }

  public static function _error($level, $error, $pub_error=false)
  {
	echo $error;

	$this->error = true;

	if (self::inTransaction())
	  $this->rollBack();

	if (is_writable($this->error_log_file))
	  {
		$hdl = fopen($this->error_log_file, "a");
		
		if ($hdl)
		  {
			fwrite($hdl, $error);
			fclose($hdl);
		  }
	  }
  }
}

function alnum($str)
{
  return strtolower(preg_replace("/[^[:alnum:]]/", "", $str));
}

?>