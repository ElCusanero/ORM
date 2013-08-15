<?php

class DB {
	
	public $db = null;
	private $queries = array();
	private $where = array();
	private $select = '*';
	private $order = null;
	private $limit = null;
	private $offset = null;
	
	public static function Instance($host = null, $user = null, $pass = null, $database = null)
	{
		static $instance = null;
		if ($instance === null)
		{
			$instance = new DB($host, $user, $pass, $database);
		}
		return $instance;
	}
	
	private function __construct($host, $user, $pass, $database)
	{
		$db = new mysqli($host, $user, $pass, $database);
		$this->db = $db;
		$this->where = array();
	}
	
	private function reset()
	{
		$this->where = array();
		$this->select = '*';
		$this->order = null;
		$this->limit = null;
		$this->offset = null;
	}
	
	//$query can be either a field name or an associative array of key/value pairs
	public function where($query, $value = null)
	{
		if (!is_array($query) && $value)
		{
			$query = array(
				$query => $value
			);
		}
		if (is_array($query))
		{
			foreach ($query as $key => $value)
			{
				$this->where[] = array($key, $value);
			}
		}
		else
		{
			$this->where[] = array($query);
		}
		
		return $this;
	}
	
	private function build_where()
	{
		if (count($this->where) > 0)
		{
			$query = ' WHERE ';
			$sets = array();
			foreach ($this->where as $where)
			{
				if (count($where) == 2)
				{
					$sets[] = $where[0] . ' = ' . $where[1];
				}
				else
				{
					$sets[] = $where[0];
				}
			}
			$query .= implode(' AND ', $sets);
			
			return $query;
		}
		return '';
	}
	
	public function select($select)
	{
		$this->select = $select;
		
		return $this;
	}
	
	public function order($field, $order = 'ASC')
	{
		$this->order = $field . ' ' . $order;
		
		return $this;
	}
	
	private function build_order()
	{
		if ($this->order !== null)
		{
			return ' ORDER BY ' . $this->order;
		}
		return '';
	}
	
	public function limit($limit, $offset = 0)
	{
		$this->limit = $limit;
		$this->offset = $offset;
		
		return $this;
	}
	
	private function build_limit()
	{
		if ($this->limit !== null)
		{
			$limit = ' LIMIT ';
			if ($this->offset !== null)
			{
				$limit .= $this->offset . ', ';
			}
			$limit .= $this->limit;
			
			return $limit;
		}
		return '';
	}
	
	public function get($table_name, $where = null)
	{
		if ($where !== null)
		{
			$this->where($where);
		}
		
		$query = 'SELECT ' . $this->select . ' FROM ' . $table_name . $this->build_where() . $this->build_order() . $this->build_limit();
		
		$this->reset();
		
		return $this->query($query);
	}
	
	public function update($table_name, $data, $where = null)
	{
		if ($where !== null)
		{
			$this->where($where);
		}
		
		$query = 'UPDATE ' . $table_name . ' SET ';
		
		$set = array();
		foreach ($data as $key => $value)
		{
			$set[] = $key . ' = "' . $this->db->real_escape_string($value) . '"';
		}
		$set = implode(', ', $set);
		
		$query .= $set . $this->build_where() . $this->build_order() . $this->build_limit();
		
		$this->reset();
		
		return $this->query($query);
	}
	
	public function destroy($table_name, $where = null)
	{
		if ($where !== null)
		{
			$this->where($where);
		}
		
		$query = 'DELETE FROM ' . $table_name . $this->build_where() . $this->build_order() . $this->build_limit();
		
		$this->reset();
		
		return $this->query($query);
	}
	
	public function query($sql)
	{
		$this->queries[] = $sql;
		return $this->db->query($sql);
	}
	
	public function runtime_info()
	{
		return array(
			'total' => count($this->queries),
			'queries' => $this->queries
		);
	}
	
}