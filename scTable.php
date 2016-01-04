<?php
include_once("scDBCon.php"); // DB connection File
include_once("scInit.php");


// These three files are extenction on program to add UI Layer and will be implemented soon. 
// Once UI layer is completed, will be uploaded here

//include_once("scHTML.php");
//include_once("scHTMLTable.php");


define("NEW_LINE", "</BR>");
define("TAB","&nbsp;&nbsp;&nbsp;&nbsp;");

class table
{
	protected $name;			// name of the table
	protected $records;			// fields in table
	protected $indexes;			// fields in the table with references
	protected $required;		// fields that the value are scanned in the form, required by 
								// default to insert records in the table.
	protected $postIntoForm;	// Field that the values are display to fill by user
	protected $alterIndex;		// Special fields defination. eg. PRIMARY, UNIQUE, etc
	protected $con;				// connection class to database
	protected $garbage;			// error during the class emplimentation. 
	
	// constructor to define table name 
	public function __construct($tableName)
	{
		$this->setName($tableName);
		$this->records			=	array();
		$this->indexes			= array();
		$this->required			= array();
		$this->postIntoForm	=	array();
		$this->alterIndex		=	array();
		$dbHandler 					= scDBCon::getInstance();
		$this->con					=	$dbHandler->getConnection();
		$this->garbage			=	array(); 
		$this->addTableProperties();
	}
	
	// check if field in record is already exist
	private function isFieldExist($field)
	{
		return array_key_exists($field,$this->records)? true: false;
	}
	
	/*/ add new field in record list
		where, field is name of field,
		default is default value, isRequired 
		is if it is required during 
		form scan and is postIntoForm
		is whether to post value into 
		form
	*/
	public function addRecordWithOptions($fieldName,$defaultValue,$isRequiredWhileScanningForm,$isPostIntoFormToDisplayToUser)
	{
		if(!$this->isFieldExist($fieldName))
		{
			$this->records[$fieldName]=$defaultValue;
			if($isRequiredWhileScanningForm)
				$this->required[]=$fieldName;
			if($isPostIntoFormToDisplayToUser)
				$this->postIntoForm[]=$fieldName;
			return true;
		}
		$this->addGarbage("Error!: Duplicate record found in function addRecord() for field: ".$field);
		return false;
	}
	
	// add field and record value into  record list.
	public function addRecord($field,$value)
	{
		return $this->addRecordWithOptions($field,$value,false,false);
	}
	
	// get field value by field name
	public function getFieldValue($field)
	{
		if($this->isFieldExist($field))
			return $this->records[$field];
		$this->addGarbage("Error: Record not found in function getFieldValue($field) for:".$field);
		return false;
	}
	
	// Update filed value in record list
	public function updateRecord($field,$newValue)
	{
		if($this->isFieldExist($field))
		{
			$this->records[$field]=$newValue;
			return true;
		}
		$this->addGarbage("Error: Unidentified field:$field tried to update to:$newValue in function updateRecord()");
		return false;
	}
	
	//for alter index
	
	public function addAlterIndex($field,$value)
	{
		//if($this->isFieldExist($field)||$field=="TABLE_PROPERTIES")
		{
			$this->alterIndex[$field][]=$value;
			return true;
		}
		$this->addGarbage("Error: Properties added to Unidentified field: \"$field\" with properties: \"$value\" function addAlterIndex()");
		return false;
	}
	public function removeAlterIndexByField($field)
	{
		unset($this->alterIndex[$field]);
	}
	//add primery key
	public function addPrimeryKey($field)
	{
		$this->addNotNullKey($field,datasets::bigint_20);
		$this->addAlterIndex($field,datasets::auto_increment);
		$this->addAlterIndex($field,datasets::primery_key);
	}
	public function addUniqueKey($field,$value)
	{
		$this->addNotNullKey($field,$value);
		$this->addAlterIndex($field,datasets::unique_key);
	}
	public function addNotNullKey($key,$value)
	{
		$return =true;
		if(!$this->addkey($key,$value))
			$return=false;
		if(!$this->addAlterIndex($key,datasets::not_null))
			$return=false;
		return $return;
	}
	public function addkey($key,$value)
	{
		return $this->addAlterIndex($key,$value);
	}
	public function addTableProperties()
	{
		return $this->addkey("TABLE_PROPERTIES","ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1000000000001");
	}
	// add foreign constraint 
	public function addForeighKey($key,$foreignTable)
	{
		//if($this->isFieldExist($key))
		{
			$this->addNotNullKey($key,datasets::bigint_20);
			$this->indexes[$key]=$foreignTable;
			return true;
		}
		//else
			//$this->addGarbage("Error!: Foreign key constraint on undefined field $key on addForeignKey()");
		//return false;
	}
	public function getReferences()
	{
		return $this->indexes;
	}
	
	public function runQuery($query)
	{
		$result=NULL;
		$return =true;
		try
		{
		//echo "query on runQuery(): ".$query;
			$result=$this->con->exec($query); 
			return $result; 
		}
		catch (Exception $e) 
		{
			$this->addGarbage($e->getCode().": ".$e->getMessage());
			echo $this;
			
			$return=false;
		}
		return $return;
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
		if(!$primeryKey)
			return false;
		else
			return $primeryKey;
	}
	public function createTable()
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
					$query.= ", FOREIGN KEY (".$fKey.") REFERENCES ".$fTable."(".$fKey.") ON DELETE RESTRICT ON UPDATE RESTRICT";
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
		return "TRUNCATE TABLE ".$this->getName().";";
		//SET FOREIGN_KEY_CHECKS = 1;
	}
	
	public function dropTable()
	{
		return "DROP TABLE ".$this->getName().";";
	}
	
	public function valueAsJsonString()
	{
		$json='{ "table":"'.$this->getName().'"';
		foreach($this->records as $key => $value)
			$json.=', "'.$key.'":"'.$value.'"';
		$json.=' }';
		return $json;
		
	}

	public function returnResultsAsArray( $table,$search)
	{
		$count=0;
	 	$return= array();
		$query="SELECT * FROM ".$table->getName();
		if(count($search)>0)
		{
			$query.=" WHERE ";
			foreach($search as $aKey => $aValue)
			{
				if($count>0)
					$query.= " AND ";
				$query.= $aKey." = '".$aValue."'";
				$count++;
			}
		}
		$query.=" ORDER BY ".$table->getPrimeryKey()." DESC";
		$query.=";";
		$dbcon= scDBCon::getInstance();
		$con=$dbcon->getConnection();
		if($result=$con->query($query))
		{
			if($result->rowCount()>0)
			{
				while($row = $result->fetch(PDO::FETCH_ASSOC)) 
				{
    				$return[]=$row;
				}
			}	
		}
		return $return;
	}
	
	public function search($search)
	{
		$count=0;
		$query="SELECT * FROM ".$this->getName();
		if(count($search)>0)
		{
			$query.=" WHERE ";
			foreach($search as $aKey => $aValue)
			{
				if($count>0)
					$query.= " AND ";
				$query.= $aKey." = '".$aValue."'";
				$count++;
			}
		}
		$query.=" ORDER BY ".$this->getPrimeryKey()." DESC;";
		//echo $query;
		$dbcon= scDBCon::getInstance();
		$con=$dbcon->getConnection();
		
		if($result=$con->query($query))
		{
			if($result->rowCount()>0)
			{
				$row = $result->fetch(PDO::FETCH_ASSOC);
				foreach($this->records as $aKey => $aValue)
				{
					$this->updateRecord($aKey,$row[$aKey]);
				}
				return true;
			}	
			else
				$this->addGarbage($result->rowCount()." record found in search() for query: ".$query);
		}
		else
			$this->addGarbage("Couldnt run select query on search() ");
		return false;
	}
	public function insertValues()
	{
		$return=false;
		$query="";
		if(($numOfRecords=count($this->records))>0)
		{
			$primeryKey=$this->getPrimeryKey();
			//echo'Primery Key:'.$primeryKey.'. numOfRecords:'.$numOfRecords;
			if($this->runQuery($this->insert()))
			{
				$search=NULL;
				foreach($this->records as $aKey => $aValue)
				{
					if($aKey!=$primeryKey)
					{
						$search[$aKey]=$aValue;
					}
				}
				$return = $this->search($search);
			}
			else
				$this->addGarbage("couldn't insert record ion insertAndUpdateValues()");
		}
		else
			$this->addGarbage("record number is less then 1 in insertAndUpdateValues()");
		return $return;
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
			$this->addGarbage("Error!: no records found to create query in insert()");
			return false;
		}
		
	}

	public function updateWithPrimeryKey($update)
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
		$query.=" WHERE ".$primeryKey." = ".$this->getFieldValue($primeryKey).";";
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
				return $this->search($search);
		}
		return $return;
	}
	public function __toString()
	{
		
		$return = NEW_LINE.'<table width="95%" style="table-layout:fixed;" cellpadding="5" cellspacing="0" border="1" bordercolor="#DDDDDD" align="center">';
		$return.= '<tr><th width="200px"> tableName</th> <th> '.$this->getName().'</th></tr>';
		$return.= '<tr><td> Primery Key</td> <td> '.$this->getPrimeryKey().'</td></tr>';
		$return.= '<tr><td colspan="2"> Queries</td> </tr> ';
		$return.= '<tr><td>Create </td> <td> '.$this->createTable().'</td></tr>';
		$return.= '<tr><td>Insert </td> <td> '.$this->insert().'</td></tr>';
		$return.= '<tr><td>Turncate </td> <td> '.$this->turncateTable().'</td></tr>';
		$return.= '<tr><td>Drop </td> <td> '.$this->dropTable().'</td></tr>';
		$return.= '<tr><td> JSON Value</td> <td> '.$this->valueAsJsonString().'</td></tr>';
		$count=1;
		if(count($this->required)>0)
		{
			$return.= '<tr><td> Requested field on form scan</td> <td> ';
			foreach($this->required as $req)
			{
				if($count>1)
					$return.=", ".$req;
				else
					$return.=$req;
				$count++;
			}
			$return.= '</td></tr>';
		}
		$count=1;
		if(count($this->records)>0)
		{
			$return.= '<tr><td> Field\'s Values </td> <td> ';
			foreach($this->records as $key => $value)
				$return.= NEW_LINE.$count++.". ".$key." = ".$value;
			$return.= '</td></tr>';
		}
		if(count($this->alterIndex)>0)
		{
			$return.= '<tr><td> Field properties </td> <td> ';
			$count=1;
			foreach($this->alterIndex as $key => $value)
			{
				$return.= NEW_LINE.$count++.". ".$key;
				foreach($value as $anotherValue)
					$return.= "  ".$anotherValue;
			}
			$return.= '</td></tr>';
		}
		if(count($this->indexes)>0)
		{
			$return.= '<tr><td> Foreign Key Constraints </td> <td> ';
			$count=1;
			foreach($this->indexes as $fKey => $fTable)
			{
				
				$return.= NEW_LINE."  FOREIGN KEY (".$fKey.") REFERENCES ".$fTable."(".$fKey.") ON DELETE RESTRICT ON UPDATE RESTRICT";
			}
		}
		if(count($this->garbage)>0)
		{
			$return.= '<tr><td>  Error/s </td> <td> ';
			$count=1;
			foreach($this->garbage as $key)
				$return.= NEW_LINE.$count++.". ".$key;
			$return.='</td></tr>';
		}
		$return.='<tr><td colspan="2"> Table Declaration finish </td> <tr> ';
		$return.= '</table>'.NEW_LINE;
		return $return;
	}	
}
?>
