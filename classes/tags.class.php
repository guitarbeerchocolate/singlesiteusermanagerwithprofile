<?php
class tags
{
	private $db;
	function __construct()
	{
		$this->db = new database;
	}

	function update($name)
	{
		$q = "INSERT INTO `tags` VALUES (NULL, '{$name}')";
    	$this->db->singleRow($q);
	}

	function getTags()
	{
		return $this->db->query("SELECT * FROM `tags`");
	}

	function getTagId($t)
	{
		$result = NULL;
		$result = $this->db->singleRow("SELECT * FROM `tags` WHERE `name`='{$t}'");
		return $result->id;
	}

	function getTagString($t)
	{
		$tArr = explode(',',$t);
		$tempArr = array();
		foreach($tArr as $tag)
		{
			$result = $this->db->singleRow("SELECT * FROM `tags` WHERE `id`='{$tag}'");
			array_push($tempArr, $result->name);
		}
		return implode(',',$tempArr);
	}

	function __destruct()
	{

	}
}
?>