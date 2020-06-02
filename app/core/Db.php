<?php

class Db
{
	public $conn;
	
	public function __construct()
	{
		$this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	}
	
	private function runQuery($sql)
	{
		if ($this->conn->query($sql) === TRUE) {
			$insert_id = $this->conn->insert_id;
			//echo "New record created successfully";
		} else {
			throw new Exception("Error: " . $sql . "<br>" . $this->conn->error );
		}

		return $insert_id;
	}
	
	private function generateInsertValuesString($values)
	{
		$return_str = "NULL";
		
		foreach($values as $key => $value)
		{
			switch(gettype($value))
			{
				case 'boolean':
				case 'string':
				case 'integer':
				case 'double':
					$return_str .= ", '{$value}'";
					break;
				default:
					throw new Exception('Invalid variable value: (' . $value . ' for column ' . $key . ')' );
			}
		}
		
		return $return_str;
	}
	
	public function create($table, $columns)
	{
		$columns_str = "id, " . implode(',', array_keys($columns));
		
		$values_str = $this->generateInsertValuesString(array_values($columns));
		
		$sql = "INSERT INTO {$table} ({$columns_str})
				VALUES ({$values_str});";
		
		return $this->runQuery($sql);
	}
	
	public function read($table, $columns, $conditions = NULL, $join_tables = NULL, $group_by = NULL)
	{
		if(is_array($columns))
		{
			$columns = implode(',', $columns);
		}
		$sql = "SELECT {$columns} FROM {$table}";
		
		if(!is_null($join_tables))
		{
			foreach($join_tables as $join_table)
			{
				$sql .= " {$join_table['type']} {$join_table['name']} ON {$join_table['condition']} ";
			}
		}
		
		if(!is_null($conditions))
		{
			$sql .= " WHERE ";
			
			$lastCondition = end($conditions);
			foreach($conditions as $condition)
			{
				$sql .= "{$condition['column']} = '{$condition['value']}'";
				
				if($lastCondition != $condition)
				{
					$sql .= " AND ";
				}
			}
		}
		
		if(!is_null($group_by))
		{
			$sql .= " GROUP BY {$group_by}";
		}
		
		$sql .= ";";
		
		if ($result = $this->conn->query($sql)) {
			return $result->fetch_all(MYSQLI_ASSOC);
		} else {
			throw new Exception("Error: " . $sql . "<br>" . $this->conn->error );
		}
	}
	
	public function update($table, $columns, $conditions)
	{
		$sql = "UPDATE {$table} SET ";
		
		$last_col = end($columns);
		foreach($columns as $key => $value)
		{
			$sql .= "{$key} = '{$value}'";
			if($value != $last_col){
				$sql .= ", ";
			}
		}
		
		$last_condition = end($conditions);
		if(is_array($conditions))
		{
			$sql .= " WHERE ";
			foreach($conditions as $key => $value)
			{
				$sql .= "{$key} = '{$value}'";
				if($value != $last_condition){
					$sql .= " AND ";
				}
			}
		}
		return $this->runQuery($sql);
	}
	
	public function delete($table, $conditions)
	{
		$sql = "DELETE FROM {$table} WHERE ";
		$last_condition = end($conditions);
		foreach($conditions as $key => $value)
		{
			$sql .= "{$key} = '{$value}'";
			if($value != $last_condition){
				$sql .= " AND ";
			}
		}
		return $this->runQuery($sql);
	}
}