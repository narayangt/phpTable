<?php
include_once("scTable.php");

class tables
{
	protected $tables;
	
	public function __construct()
	{
		$this->tables= array();
	}
	public function addTable(table $table)
	{
		$flag=true;
		$foreignKeys= $table->getReferences();
		if(count($foreignKeys)>0)
			foreach($foreignKeys as $key => $foreignTable)
				if(!$this->isTableExist($foreignTable))
					$flag=false;
		if($flag)
		{
			$this->tables[$table->getName()]=$table;
			//echo '<br />'.$table->getName().' added to table list';
		}
		return $flag;
	}
	public function removeTable($name)
	{
		if($this->isTableExist($name))
		{
			unset($this->tables[$name]);
			return true;
		}
		return false;
	}
	public function getTable($name)
	{
		if($this->isTableExist($name))
			return $this->tables[$name];
		return false;
	}
	
	public function populateTables()
	{
		$return=true;
		foreach($this->tables as $table)
		{
			if(!$table->runQuery($table->createTable()))
			{
				$return=false;
				echo'<br />can not create table :'.$table->getName();
			}
			else
				echo'<br />table: '.$table->getName().' created';
		}
		return $return;
	}
	
	public function turncateTables()
	{
		$return=true;
		foreach($this->tables as $table)
		{
			if(!$table->runquery($table->turncate()))
				$return=false;
		}
		
		return $return;
	}
	
	public function dropTables()
	{
		$return=true;
		for($i=count($this->tables);$i>0;$i--)
		{
			$count=1;
			foreach($this->tables as $table)
			{
				if($count==$i)
				{
					$table->runQuery($table->dropTable());
					echo'<br />table : '.$table->getname().' Dropped';
				}
				$count++;
			}
		}
		
		return $return;
	}
	
	
	private function sortTables($action)
	{
		
	}
	
	private function swapTables($aTable, $bTable)
	{
		$return=false;
		if($this->isTableExist($aTable) && $this->isTableExist($bTable))
		{
		    
			$return=true;
		}
	}
	
	
	
	
	
	
	private function isTableExist($name)
	{
		return isset($this->tables[$name]);
	}
	
	public function __toString()
	{
		$return = NULL;
		foreach($this->tables as $table)
			$return.= $table->__toString();
		return $return;
		
	}
	
}


?>
