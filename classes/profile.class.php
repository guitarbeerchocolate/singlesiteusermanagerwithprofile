<?php
class profile
{
	private $db = NULL;
	public $name = NULL;
	public $photo = NULL;
	public $phototype = NULL;
	public $tags = NULL;
	public $hasProfile = FALSE;
	function __construct()
	{
		$this->db = new database;
	}

	function getProfile($id)
	{
		$p = $this->db->singleRow("SELECT * FROM `profiles` WHERE `userid`='{$id}'");
		if(isset($p->id))
		{
			$this->name = $p->name;
			$this->photo = $p->photo;
			$this->phototype = $p->phototype;
			$this->tags = $p->tags;
			$this->hasProfile = TRUE;
		}
	}

	function update($po, $photo, $type)
	{
		$result = $this->db->singleRow("SELECT * FROM `profiles` WHERE `userid`='{$po->userid}'");
		if(isset($result->id))
		{
			$q = "UPDATE `profiles` SET `userid`='{$po->userid}', `name`='{$po->name}' ";
			if(isset($photo))
			{
				$q .= ", `photo`='{$photo}', `type`='{$type}'";
			}
			$q .= ", `tags`='";
			$q .= $this->tagManagement($po->tags);
			$q .= "' WHERE `id`='{$result->id}'";
		}
		else
		{
			$tags = $this->tagManagement($po->tags);
			$q = "INSERT INTO `profiles` VALUES (NULL, '{$po->userid}','{$po->name}','{$photo}','{$type}','{$tags}')";
		}
		// echo $q;
		$this->db->singleRow($q);
	}

	function tagManagement($t)
	{
		$id = NULL;
		$idArr = array();
		$tagObj = new tags;
		$tagArr = explode(',',$t);
		foreach($tagArr as $tagItem)
		{
			$id = $tagObj->getTagId(strtolower($tagItem));
			if(isset($id))
			{
				array_push($idArr, $id);
			}
			else
			{
				$tagObj->update($tagItem);
				$id = $tagObj->getTagId(strtolower($tagItem));
				array_push($idArr, $id);
			}
			$id = NULL;
		}
		return implode(',',$idArr);
	}

	function __destruct()
	{

	}
}
?>