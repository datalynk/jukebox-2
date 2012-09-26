<?php

class juke_pdo extends PDO
{
  private $show_errors = true;
  private $error_log_file = "pdo_log.txt";

  function __construct($host=MYSQL_HOST, $user=MYSQL_USER, $pass=MYSQL_PASS, $db=MYSQL_DB)
  {
	try
	  {
		parent::__construct("mysql::host={$host};dbname={$db}", $user, $pass);
	  }
	catch (PDOException $e)
	  {
		$this->_error(2, $e->getMessage());
	  }
  }

  public function add_album($album)
  {
	
  }

  private function _error($level, $error, $pub_error)
  {
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

?>