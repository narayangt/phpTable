<?php
include_once("scTable.php");

function ifRequestExist($key,$default) // function to check if $_REQUEST['$key'] and return value or default if key not available
{
	if(isset($_REQUEST[$key]))
		return $_REQUEST[$key];
	return $default;
}

// here we implement table class to structure a new table 'location'.
class location extends table
{ 
	public function __construct()
	{
		// call parent constructor to init table name
		parent::__construct("location");
		// add fields and default values
		$this->addReord("location_id","",false);
		$this->addReord("longitude",ifRequestExist("longitude",0.0),true);
		$this->addReord("latitude",ifRequestExist("latitude",0.0),true);
		$this->addReord("altitude",ifRequestExist("altitude",0.0),true);	
		// add field properties
		$this->addAlterIndex("location_id","bigint(20)");
		$this->addAlterIndex("location_id","NOT NULL");
		$this->addAlterIndex("location_id","AUTO_INCREMENT");
		
		$this->addAlterIndex("longitude","double");
		$this->addAlterIndex("longitude","NOT NULL");
		
		$this->addAlterIndex("latitude","double");
		$this->addAlterIndex("latitude","NOT NULL");
		
		$this->addAlterIndex("altitude","double");
		$this->addAlterIndex("altitude","NOT NULL");
		
		$this->addAlterIndex("location_id","PRIMARY KEY");
		$this->addAlterIndex("TABLE_PROPERTIES","ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1000000000001");
	}
	public function insertAndUpdateValues() // this method overrides parent method 
	{
		$search['longitude']=ifRequestExist("longitude",0.0);
		$search['latitude']=ifRequestExist("latitude",0.0);
		//$search['altitude']=ifRequestExist("altitude",0.0);
		if(!parent::searchAndUpdateValues($search)) // check if same record exists in database then retrive values 
			return parent::insertAndUpdateValues();   // if same record not found in database then only add new record
		return true;
	}
}
//echo new location();




// here we implement table class to structure a new table 'name'
class name extends table
{ 
	var $loc;         // another table location which determine location of user from location table and location_id to
	                  // be associate with name table as foreign key
	public function __construct()
	{
		// call parent constructor to init table name
		parent::__construct("name");
		// add fields and default values
		$this->loc= new location();
		
		$this->addReord("name_id","",false);
		$this->addReord("userid",ifRequestExist("userid",""),true);
		$this->addReord("title",ifRequestExist("title",""),true);
		$this->addReord("fname",ifRequestExist("fname",""),true);
		$this->addReord("mname",ifRequestExist("mname",""),true);
		$this->addReord("lname",ifRequestExist("lname",""),true);
		$this->addReord("state",1,true);	                  // state of record whether its is private, public, current or deleted
		$this->addReord("datecreated",$_SERVER['REQUEST_TIME'],false);
		$this->addReord("datemodified",$_SERVER['REQUEST_TIME'],false);
	
	
		// add field properties
		$this->addAlterIndex("name_id","bigint(20)");
		$this->addAlterIndex("name_id","NOT NULL");
		$this->addAlterIndex("name_id","AUTO_INCREMENT");
		
		$this->addAlterIndex("userid","bigint(20)");
		$this->addAlterIndex("userid","NOT NULL");
		
		$this->addAlterIndex("title","varchar(20)");
		$this->addAlterIndex("title","NOT NULL");
		
		$this->addAlterIndex("fname","varchar(50)");
		$this->addAlterIndex("fname","NOT NULL");
		
		$this->addAlterIndex("mname","varchar(50)");
		$this->addAlterIndex("mname","NOT NULL");
		
		$this->addAlterIndex("lname","varchar(50)");
		$this->addAlterIndex("lname","NOT NULL");
		
		
		$this->addAlterIndex("state","int(12)");
		$this->addAlterIndex("state","NOT NULL");
		
		$this->addAlterIndex("datecreated","int(12)");
		$this->addAlterIndex("datecreated","NOT NULL");
		
		$this->addAlterIndex("datemodified","int(12)");
		$this->addAlterIndex("datemodified","NOT NULL");
		
		$this->addAlterIndex("name_id","PRIMARY KEY");
		$this->addAlterIndex("TABLE_PROPERTIES","ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1000000000001");
	}
}
//echo new name();  // test to see how the structure of table name would look like



// lets populate our design into database

function populateTablesIntoDatabase()
{
	$location	=	new 	location();
	$name		= 	new		name();
	
	$location->runQuery($location->createTableIfNotExists());
	$name->runQuery($name->createTableIfNotExists());
	
	// lets see the result 
	// comment following 2 line if you dont want to see lengthy reports of tables
	
	echo $location;
	echo $name;
	
	
}

// lets run the code 

populateTablesIntoDatabase();


?>

