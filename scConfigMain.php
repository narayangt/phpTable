<?php

if (!isset($_SESSION))
{
	 session_start();
}


//Object to connect database narayan_userdb
class maindb_con
{
    private $hostname;
    private $username;
    private $password;
    private $dbname;

    function __construct()
    {
        $this->hostname = "**********";     // hostname
        $this->username = "**********";     // username
        $this->password = "**********";     // password
        $this->dbname = "**********";       // database name that would be initially selected after sucessful connection
        if(!mysql_connect($this->hostname, $this->username, $this->password,$this->dbname))
        {
        	echo'Error:: 1001 Couldnot connect to database. Please update hostname, username and password';      
        			// special error code to represent connection error
        	exit();
        }
    }
    /*function maindb_con($hname, $uname, $pwd, $dbname)
    {
        $this->hostname = $hname;
        $this->username = $uname;
        $this->password = $pwd;
        $this->dbname = $dbname;
    }*/
    function get_con()
    {
        $con=mysql_connect($this->hostname, $this->username, $this->password);
        mysql_select_db($this->dbname,$con);
        return $con;
    }
    function get_dbname()
    {
    	return $this->dbname;
    }
    public function __destruct()
	{
		mysql_close($this->con);
	}
} 
?>
