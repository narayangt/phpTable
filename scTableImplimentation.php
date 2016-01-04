include_once("scTable.php");
include_once("scTables.php");
include_once("scInit.php");

$tables	=	new tables();

class location extends table
{
	public function __construct()
	{
		parent::__construct("location");
		
		$this->addRecordWithOptions("location_id","",false,false);
		$this->addRecordWithOptions("longitude",requestDefault("longitude",0.0),true,true);
		$this->addRecordWithOptions("latitude",requestDefault("latitude",0.0),true,true);
		$this->addRecordWithOptions("altitude",requestDefault("altitude",0.0),true,true);
		
		$this->addPrimeryKey("location_id");
		$this->addNotNullKey("longitude",datasets::double);
		$this->addNotNullKey("latitude",datasets::double);
		$this->addNotNullKey("altitude",datasets::double);
		
		$this->insertValues();
	}
	
	public function insertValues()
	{
		$search['longitude']=requestDefault("longitude",0.0);
		$search['latitude']=requestDefault("latitude",0.0);
		$search['altitude']=requestDefault("altitude",0.0);
		$this->runQuery($this->createTable());
		if(!parent::search($search))
			return parent::insertValues();
		return true;
	}
}

class visitor_log extends table
{
	private $loc;
	public function __construct()
	{
		parent::__construct("visitor_log");
		
		$this->loc= new location();
		//$this->loc->insertValues();
		$ip=NULL;
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else 
			$ip = $_SERVER['REMOTE_ADDR'];
			
			
		$this->addRecordWithOptions("visitor_log_id",requestDefault("visitor_lig_id",""), true, false);
		$this->addRecordWithOptions("location_id",$this->loc->getFieldValue("location_id"),false,true);
		$this->addRecordWithOptions("timestamp",$_SERVER['REQUEST_TIME'],false,true);
		$this->addRecordWithOptions("ip",$ip,false,true);
		$this->addRecordWithOptions("user_detect",$_SERVER['HTTP_USER_AGENT'],false,true);
		
		$this->addPrimeryKey("visitor_log_id");
		$this->addForeighKey("location_id","location");
		$this->addNotNullKey("timestamp",datasets::int_12);
		$this->addNotNullKey("ip",datasets::varchar_50);
		$this->addNotNullKey("user_detect",datasets::varchar_250);
		
		$this->insertValues();	
	}
	
	public function insertValues()
	{
		$ip=NULL;
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else 
			$ip = $_SERVER['REMOTE_ADDR'];
			
		$search['ip']=$ip;
		$search['user_detect']=$_SERVER['HTTP_USER_AGENT'];
		$this->runQuery($this->createTable());
		if(!parent::search($search))
			return parent::insertValues();
		return true;
	}
}

class useridinf extends table
{
	public $loc;
	public $visitor_log;
	
	
	public function __construct()
	{
		parent::__construct("useridinf");
		$this->loc= new location();
		$this->visitor_log = new visitor_log();
		$this->addRecordWithOptions("userid","",true,false);
		$this->addRecordWithOptions("password",requestDefault("password",md5("P@55mord")),true,true);
		$this->addRecordWithOptions("gender","gender",false,true);
		$this->addRecordWithOptions("location_id",$this->loc->getFieldValue("location_id"),false,false);
		$this->addRecordWithOptions("visitor_log_id",$this->visitor_log->getFieldValue("visitor_log_id"),false,false);
		$this->addRecordWithOptions("state",1,false,false);
		$this->addRecordWithOptions("role","Guest",false,true);
		$this->addRecordWithOptions("date_created",getTimestamp(),false,true);
		$this->addRecordWithOptions("date_modified",getTimestamp(),false,true);
		
		$this->addPrimeryKey("userid");
		$this->addNotNullKey("password",datasets::varchar_250);
		$this->addNotNullKey("gender",datasets::varchar_20);
		$this->addForeighKey("location_id","location");
		$this->addForeighKey("visitor_log_id","visitor_log");
		$this->addNotNullKey("state",datasets::int_12);
		$this->addNotNullKey("role",datasets::varchar_20);
		$this->addNotNullKey("date_created",datasets::int_12);
		$this->addNotNullKey("date_modified",datasets::int_12);
	}
}


class usernameinf extends table
{
	private $loc;
	private $visitor_log;
	
	
	public function __construct()
	{
		parent::__construct("usernameinf");
		$this->loc= new location();
		$this->visitor_log = new visitor_log();
		
		$this->addRecordWithOptions("username_id","",false,false);
		$this->addRecordWithOptions("userid",requestDefault("userid",""),false,false);
		$this->addRecordWithOptions("username",requestDefault("username","username"),true,true);
		$this->addRecordWithOptions("location_id",$this->loc->getFieldValue("location_id"),false,false);
		$this->addRecordWithOptions("visitor_log_id",$this->visitor_log->getFieldValue("visitor_log_id"),false,false);
		$this->addRecordWithOptions("state",1,false,false);
		$this->addRecordWithOptions("date_created",getTimestamp(),false,true);
		$this->addRecordWithOptions("date_modified",getTimestamp(),false,false);
		
		$this->addPrimeryKey("username_id");
		$this->addForeighKey("userid","useridinf");
		$this->addUniqueKey("username",datasets::varchar_250);
		$this->addForeighKey("location_id","location");
		$this->addForeighKey("visitor_log_id","visitor_log");
		$this->addNotNullKey("state",datasets::int_12);
		$this->addNotNullKey("date_created",datasets::int_12);
		$this->addNotNullKey("date_modified",datasets::int_12);
	}
	
}

$tables	=	new tables();

$location		=	new location();
$visitor_log		=	new visitor_log();
$useridinf		=	new useridinf();

$tables->addTable($location);
$tables->addTable($visitor_log);
$tables->addTable($useridinf);


//echo $tables;  // to print all table details.
/*

// just un-comment one to perform either populate tables or to drop
//if($tables->populateTables()) // to populate tables into database
//if($tables->dropTables()) 	// to drop all tables into database
	echo'sucess';
else
	echo'failed';
*/

