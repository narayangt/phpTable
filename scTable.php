<?php
include_once("scConfigMain.php");
define("NEW_LINE", "</BR>");

class table
{
	protected $name;			// name of the table
	protected $records;			// fields in table
	protected $indexes;			// fielst in the table with references
	protected $required;		// fields that the value are scanned in the form, required by 
								// default to insert records in the table.
	protected $postIntoForm;		// Field that the values are display to fill by user
	protected $alterIndex;		// Special fields defination. eg. PRIMARY, UNIQUE, etc
	protected $con;				// connection class to database
	protected $garbage;			// error during the class emplimentation. 
	
	// constructor to define table bame and register destructor when life of abject is expire
	public function __construct($tableName)
	{
		register_shutdown_function(array($this,'__destruct'));
		$this->setName($tableName);
	}
	public function __destruct()
	{
		unset($this->name);
		unset($this->records);
		unset($this->indexes);
		unset($this->required);
		unset($this->postIntoForm);
		unset($this->alterIndex);
		unset($this->con);
		unset($this->garbage);
	}
	
	public function getFieldValueByName($field)
	{
		if($this->isFieldExist($field))
			return $this->records[$field];
		$this->addGarbage("Error: Record not found in function getFieldValueByName() for:".$field);
		return false;
	}
	// check if field in record is already exist
	private function isFieldExist($field)
	{
		return isset($this->records[$field]);
	}
	
	
	/*/ add new field in record list
		where, field is name of field
		$value is default value,
	
	/
	public function addReord($field,$value,$isRequired,$isPostIntoForm)
	{
		if(!$this->isFieldExist($field))
		{
			$this->records[$field]=$value;
			if($isRequired)
				$this->required[]=$field;
			if($isPostIntoForm)
				$this->postIntoForm[]=$field;
			return true;
		}
		$this->addGarbage("Error: Duplicate record found in function addRecord()");
		return false;
	}
	*/
	public function addReord($field,$value,$isRequired)
	{
		//return $this->addReord($field,$value,$isRequired,$isRequired);
		if(!$this->isFieldExist($field))
		{
			$this->records[$field]=$value;
			if($isRequired)
				$this->required[]=$field;
			if($isPostIntoForm)
				$this->postIntoForm[]=$field;
			return true;
		}
		$this->addGarbage("Error: Duplicate record found in function addRecord()");
		return false;
	}
	
	// remove existing field from record list
	public function removeRecordByField($fild)
	{
		if($this->isFieldExist($fild))
		{
			unset($this->records[$fild]);
			return true;
		}
		$this->addGarbage("Error: Unidentified field tried to remove in function removeRecordByField()");
		return false;
	}
	
	// remove existing field from record list by value
	protected function removeRecordByValue($vlaue)
	{
		$this->records=array_diff($this->records, array($vlaue));
	}
	
	// Update filed in record list
	public function updateRecordByField($field,$newValue)
	{
		if($this->isFieldExist($field))
		{
			$this->records[$field]=$newValue;
			return true;
		}
		$this->addGarbage("Error: Unidentified field:$field tried to update to:$newValue in function updateRecordByField()");
		return false;
	}
	protected function updateRecordByVlaue($oldValue,$newValue)
	{
		$this->records[array_search($oldValue,$this->records)]=$newValue;
	}
	protected function displayRecords()
	{
		print_r($this->records);
	}
	
	//for alter index
	public function addAlterIndex($field,$value)
	{
		if($this->isFieldExist($field)||$field=="TABLE_PROPERTIES")
		{
			$this->alterIndex[$field][]=$value;
			return true;
		}
		$this->addGarbage("Error: Properties added to Unidentified field: $field with properties: $value  function addAlterIndex()");
		return false;
	}
	public function removeAlterIndexByField($fild)
	{
		unset($this->alterIndex[$fild]);
	}
	public function removeAlterIndexByValue($vlaue)
	{
		$this->alterIndex=array_diff($this->alterIndex, array($vlaue));
	}
	public function updateAlterIndexByField($field,$newValue)
	{
		$this->alterIndex[$field]=$newValue;
	}
	public function updateAlterIndexByVlaue($oldValue,$newValue)
	{
		$this->alterIndex[array_search($oldValue,$this->alterIndex)]=$newValue;
	}
	
	// add foreign constraint 
	public function addForeighKey($key,$foreignTable)
	{
		if($this->isFieldExist($key))
		{
			$this->indexes[$key]=$foreignTable;
			return true;
		}
		else
			$this->addGarbage("Foreign key constraint on undefined field $key on addForeignKey()");
		return false;
	}
	
	public function runQuery($query)
	{
		$dbcon=new maindb_con();
   		$con=$dbcon->get_con();
		//echo "query on runQuery(): ".$query;
		if(mysql_query($query,$con)) //or die("couldnt run mysql_query on runQuery for ".$query)
			return true;
		return false;
	}
	public function getPrimeryKey()
	{
		$primeryKey=NULL;
		foreach($this->records as $aKey => $aValue)
			foreach($this->alterIndex as $bKey => $bValue)
				if($aKey==$bKey)
					foreach($bValue as $fieldProperties)
						if($fieldProperties=="PRIMARY KEY")
							$primeryKey=$aKey;
						//else if($fieldProperties=="UNIQUE KEY")
							//$uniqueKey=$aKey;
		if(!$primeryKey)
			return false;
		else
			return $primeryKey;
	}
	public function createTableIfNotExists()
	{
		$query="CREATE TABLE IF NOT EXISTS ".$this->getName()." ( ";
		$primeryKey=NULL;
		$uniqueKey=NULL;
		$tableProperties=NULL;
		$count=0;
		if(count($this->records)>0)
		{
			foreach($this->records as $aKey => $aValue)
			{
				if($count>0)
					$query.= " , ";
				$query.= $aKey." ";
				foreach($this->alterIndex as $bKey => $bValue)
				{
					if($aKey==$bKey)
					{
						foreach($bValue as $fieldProperties)
						{
							if($fieldProperties=="PRIMARY KEY")
								$primeryKey=$aKey;
							else if($fieldProperties=="UNIQUE KEY")
								$uniqueKey=$aKey;
							else
								$query.= $fieldProperties." ";
						}	
					}
					else if($bKey=="TABLE_PROPERTIES")
					{
						$tableProperties="";
						foreach($bValue as $fieldProperties)
							$tableProperties.= $fieldProperties." ";
					}
					$count++;
				}
			}
			if($primeryKey)
				$query.= " , PRIMARY KEY (".$primeryKey.") ";
			if($uniqueKey)
				$query.= " , UNIQUE KEY (".$uniqueKey.") ";
			if(count($this->indexes>0))
			{
				foreach($this->indexes as $fKey => $fTable)
				{
					
					$query.= " , ";
					//$query.= "CONSTRAINT fk_".$this->getName()."_".$fTable."_".$fKey. " FOREIGN KEY(".$fKey.") REFERENCES ".$fTable."(".$fKey.") ON DELETE RESTRICT ON UPDATE RESTRICT";
					
					$query.= " FOREIGN KEY (".$fKey.") REFERENCES ".$fTable."(".$fKey.") ON DELETE RESTRICT ON UPDATE RESTRICT";
				}
			}
			$query.=" ) ";
			if($tableProperties)
				$query.= " ".$tableProperties;
			$query.=" ; ";
			
			return $query;
		}
		else
		{
			$this->addGarbage("no records found to create query in createTableIfNotExists()");
			return false;
		}
		
	}
	
	public function turncateTable()
	{
		//SET FOREIGN_KEY_CHECKS = 0;
		$return="TRUNCATE TABLE ".$this->getName();
		$return.=";";
		
		//SET FOREIGN_KEY_CHECKS = 1
		return $return;
	}
	
	public function dropTable()
	{
		$return="DROP TABLE ".$this->getName();
		$return.=";";
		return $return;
	}
	
	public function valueAsJsonString()
	{
		$json='{"table":"'.$this->getName().'"';
		foreach($this->records as $key => $value)
			$json.=',"'.$key.'":"'.$value.'"';
		$json.='}';
		return $json;
		
	}
	
	public function searchAndReturnRecordsAsArray( $table,$search)
	{
		$count=0;
	 	$return= array();
		$query="SELECT * FROM ".$table->getName()." WHERE ";
		foreach($search as $aKey => $aValue)
		{
			if($count>0)
				$query.= " AND ";
			$query.= $aKey." = '".$aValue."'";
			$count++;
		}
		//$query.=" ORDER BY '".$table->getPrimeryKey()."' DESC";
		$query.=";";
		$dbcon=new maindb_con();
		$con=$dbcon->get_con();
		if($result=mysql_query($query,$con))
		{
			$num=mysql_num_rows($result);
			if($num>=1)
			{
				while($array=mysql_fetch_array($result))
				{
					$return[]=$array;
				}
			}	
		}
		//echo'<br /><br />Total records: '.count($return);
		//foreach($return as $test)
			//echo $test->getFieldValueByName('connectionid');

		return $return;
	}
	
	public function searchAndUpdateValues($search)
	{
		$count=0;
		$query="SELECT * FROM ".$this->getName()." WHERE ";
		foreach($search as $aKey => $aValue)
		{
			if($count>0)
				$query.= " AND ";
			$query.= $aKey." = '".$aValue."'";
			$count++;
		}
		$query.=" ORDER BY '".$this->getPrimeryKey()."' DESC";
		$query.=";";
		//echo $query;
		$dbcon=new maindb_con();
		$con=$dbcon->get_con();
		if($result=mysql_query($query,$con))
		{
			$num=mysql_num_rows($result);
			if($num>=1)
			{
				$array=mysql_fetch_array($result);
				foreach($this->records as $aKey => $aValue)
				{
					$this->updateRecordByField($aKey,$array[$aKey]);
				}
				return true;
			}	
			else
				$this->addGarbage("$num record found in searchAndUpdateValues()");
		}
		else
			$this->addGarbage("Couldnt run select query on searchAndUpdateValues()");
		return false;
	}
	public function insertAndUpdateValues()
	{
		$return=false;
		$query="";
		if(($numOfRecords=count($this->records))>0)
		{
			$primeryKey=$this->getPrimeryKey();
			//echo'Primery Key:'.$primeryKey.'. numOfRecords:'.$numOfRecords;
			if($this->runQuery($this->insert()))
			{
				$count=0;
				$query="SELECT * FROM ".$this->getName()." WHERE ";
				foreach($this->records as $aKey => $aValue)
				{
					if($aKey!=$primeryKey)
					{
						if($count>0)
							$query.= " AND ";
						$query.= $aKey." = '".$aValue."'";
						$count++;
					}
				}
				$query.=" ORDER BY '".$this->getPrimeryKey()."' DESC";
				$query.=";";
				
				//echo $query;
				
				$dbcon=new maindb_con();
				$con=$dbcon->get_con();
				//echo "query on runQuery(): ".$query;
				if($result=mysql_query($query,$con))
				{
					$num=mysql_num_rows($result);
					if($num>=1)
					{
						$array=mysql_fetch_array($result);
						foreach($this->records as $aKey => $aValue)
						{
							//echo NEW_LINE.$aKey.' = '.$array[$aKey];
							$this->updateRecordByField($aKey,$array[$aKey]);
						}
						$return=true;
					}	
					else
						$this->addGarbage("$num record found in insertAndUpdateValues()");
				}
				else
					$this->addGarbage("Couldnt run select query on insertAndUpdateValues()");
			}
			else
				$this->addGarbage("couldn't insert record ion insertAndUpdateValues()");
		}
		else
			$this->addGarbage("record number is less then 1 in insertAndUpdateValues()");
		return $return;;
	}
	public function insert()
	{
		$query="INSERT INTO ".$this->getName()." ( ";
		$primeryKey=NULL;
		$uniqueKey=NULL;
		$tableProperties=NULL;
		$count=0;
		if(count($this->records)>0)
		{
			foreach($this->records as $aKey => $aValue)
			{
				if($count>0)
					$query.= " , ";
				$query.= $aKey;
				/*foreach($this->alterIndex as $bKey => $bValue)
				{
					if($aKey==$bKey)
					{
						foreach($bValue as $fieldProperties)
						{
							if($fieldProperties=="PRIMARY KEY")
								$primeryKey=$aKey;
							else if($fieldProperties=="UNIQUE KEY")
								$uniqueKey=$aKey;
						}	
					}
				}*/
				$count++;
			}
			$query.=" ) VALUES (";
			$count=0;
			foreach($this->records as $aKey => $aValue)
			{
				if($count>0)
					$query.= " , ";
				if($aKey==$primeryKey)
					$query.= "''";
				else
					$query.= "'".$aValue."' ";
				$count++;
			}
			$query.=" ); ";
			
			return $query;
		}
		else
		{
			$this->addGarbage("no records found to create query in insert()");
			return false;
		}
		
	}

	public function updateTableWithPrimeryKey($update)
	{
		$primeryKey=$this->getPrimeryKey();
		$count=0;
		$query="UPDATE ".$this->getName()." SET ";
		foreach($update as $aKey => $aValue)
		{
			if($aKey!=$primeryKey)
			{
				if($count>0)
					$query.= " , ";
				$query.= $aKey." = '".$aValue."'";
				$count++;
			}
		}
		$query.=" WHERE ".$primeryKey." = ".$this->getFieldValueByName($primeryKey);
		
		$query.=" ; ";
		echo $query;
		return $this->runQuery($query);
			
	}
	public function getName()
	{
		return $this->name;
	}
	private function setName($newName)
	{
		$this->name=$newName;
	}
	private function addGarbage($error)
	{
		$this->garbage[]=$error;
	}
	public function displayGarbage()
	{
		print_r($this->garbage);
	}
	public function populateForm($submitScript)
	{
		/*
		$return='<form action="'.$submitScript.'" method="post" name="userlogin" id="userlogin">';
		$return.='<table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0">';
		if(count($this->records)>0)
		{
			foreach($this->records as $aKey => $aValue)
			{
				$return.='<tr>';
				$query.= $aKey." ";
				foreach($this->alterIndex as $bKey => $bValue)
				{
					if($aKey==$bKey)
					{
						foreach($bValue as $fieldProperties)
						{
							if($fieldProperties=="PRIMARY KEY")
								$primeryKey=$aKey;
							else if($fieldProperties=="UNIQUE KEY")
								$uniqueKey=$aKey;
							else
								$query.= $fieldProperties." ";
						}	
					}
					else if($bKey=="TABLE_PROPERTIES")
					{
						$tableProperties="";
						foreach($bValue as $fieldProperties)
							$tableProperties.= $fieldProperties." ";
					}
					$count++;
				}
				$return.='</tr>';
			}
			if($primeryKey)
				$query.= " , PRIMARY KEY (".$primeryKey.") ";
			if($uniqueKey)
				$query.= " , UNIQUE KEY (".$uniqueKey.") ";
			$query.=" ) ";
			if($tableProperties)
				$query.= " ".$tableProperties;
			$query.=" ; ";
			
			return $query;
		}
		else
		{
			$this->addGarbage("no records found to create query in createTableIfNotExists()");
			return false;
		}
		$return.='';
		$return.='';
		$return.='';
		$return.='';
		$return.='';
		$return.='';
		$return.='';
		$return.='';
		$return.='';
		$return.='</table>';
		$return.='</form>';
		*/
		return true;
		
		
	}
	public function scanSubmittedForm($searchOrNot)
	{
		$return=true;
		$search=NULL;
		foreach($this->records as $key => $value)
		{
			if(isset($_REQUEST[$key]))
			
			{
				$newValue=$_REQUEST[$key];
				$this->updateRecordByField($key,$newValue);
				$search[$key]=$newValue;
			}
			else
			{
				foreach($this->required as $req)
				{
					if($key==$req)
					{
						$this->addGarbage("Error: Requested value for key $key not found in scanSubmittedForm()");
						$return=false;
					}			
				}
			}
		}
		
		if(count($search)>0)
		{
			if($searchOrNot)
				return $this->searchAndUpdateValues($search);
		}
		return $return;
	}
	public function __toString()
	{
		$return = NEW_LINE."^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^";
		//$return.= NEW_LINE."===========================================================";
		$return.= NEW_LINE." ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^".NEW_LINE;
		
		$return.= NEW_LINE."@@ tableName: ".$this->getName()." @@".NEW_LINE;
		$return.= NEW_LINE."@@ Primery Key: ".$this->getPrimeryKey()." @@".NEW_LINE;
		$return.= NEW_LINE."Query to create table: ".$this->createTableIfNotExists().NEW_LINE;
		$return.= NEW_LINE."Query to insert table: ".$this->insert().NEW_LINE;
		$return.= NEW_LINE."Query to turncate table: ".$this->turncateTable().NEW_LINE;
		$return.= NEW_LINE."Query to drop table: ".$this->dropTable().NEW_LINE;
		$return.= NEW_LINE."json value: ".$this->valueAsJsonString().NEW_LINE;
		$count=1;
		if(count($this->required)>0)
		{
			$return.= NEW_LINE."@@ Requested field on form scan: ";
			foreach($this->required as $req)
			{
				if($count>1)
					$return.=", ".$req;
				else
					$return.=$req;
				$count++;
			}
			$return.= " @@".NEW_LINE;
		}
		$count=1;
		if(count($this->records)>0)
		{
			$return.= NEW_LINE."@@ Record Fields @@";
			foreach($this->records as $key => $value)
				$return.= NEW_LINE."    ".$count++.". ".$key." = ".$value;
			$return.= NEW_LINE;
		}
		if(count($this->alterIndex)>0)
		{
			$return.= NEW_LINE."@@ Field properties @@";
			$count=1;
			foreach($this->alterIndex as $key => $value)
			{
				$return.= NEW_LINE."    ".$count++.". ".$key;
				foreach($value as $anotherValue)
					$return.= "  ".$anotherValue;
			}
			$return.= NEW_LINE;
		}
		if(count($this->indexes)>0)
		{
			$return.= NEW_LINE."@@ Foreign Key Constraints @@";
			$count=1;
			foreach($this->indexes as $fKey => $fTable)
			{
				
				$return.= NEW_LINE."  FOREIGN KEY (".$fKey.") REFERENCES ".$fTable."(".$fKey.") ON DELETE RESTRICT ON UPDATE RESTRICT";
			}
		}
		if(count($this->garbage)>0)
		{
			$return.= NEW_LINE."@@  Error/s @@";
			$count=1;
			foreach($this->garbage as $key)
				$return.= NEW_LINE."    ".$count++.". ".$key;
		}
		$return.=NEW_LINE."@@ Table Declaration finish @".NEW_LINE.NEW_LINE.NEW_LINE;
		//$return.= NEW_LINE."##&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&##".NEW_LINE;
		return $return;
	}
	
}
?>
