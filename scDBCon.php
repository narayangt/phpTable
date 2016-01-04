<?php
final class scDBCon 
{
	private static $instance;
	private $hostname;
	private $username;
	private $password;
	private $dbname;
	private $connection;
	
  private function __construct() 
	{
		$this->hostname = "localhost";
		$this->username = "*********";  //  Default Username
		$this->password = "*********";  //  Default Pass
		$this->dbname 	= "*********";  //  Default DB name to connect
		
		$dns='mysql:host='.$this->hostname.';dbname='.$this->dbname.';charset=utf8';
		$database=NULL;
		
    try 
		{
			$this->connection =new PDO($dns, $this->username, $this->password , array(PDO::ATTR_PERSISTENT => true));
	
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} 
		catch(PDOException $e) 
		{
				echo $e->getmessage();
				die();
		}
  }
	
  public static function getInstance() 
	{
		if (self::$instance ===NULL)
				self::$instance = new scDBCon();
		return self::$instance;
	}
	
	public function __clone(){}
  public function __wakeup(){}
	
	public function getConnection()
	{
		return $this->connection;
	}
}
?>
