<?php
@session_start();
include_once 'classes/autoload.php';
include_once 'includes/privatesetup.inc.php';
$p = new profile;
$p->getProfile($session->userid);
?>
<form id="loginform" class="form-signin form-horizontal" enctype="multipart/form-data" action="httphandler.class.php" method="POST">
	<fieldset>
		<legend>Please complete your profile</legend>
		<input name="method" type="hidden" value="profile" />
		<input name="username" type="hidden" value="<?php echo $session->username; ?>" />
		<input name="userid" type="hidden" value="<?php echo $session->userid; ?>" />
		<input name="sessid" type="hidden" value="<?php echo $session->sessid; ?>" />
		<label for="name">Name</label>
		<input type="text" name="name"
		<?php
		if($p->hasProfile == TRUE)
		{
			echo 'value="'.$p->name.'"';
		}
		else
		{
			echo 'placeholder="Joe Bloggs"';
		}
		?>
		 required />
		<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
		<label for="photo">Photo</label>
		<?php
		if(!empty($p->photo))
		{
			echo '<p>Your current photo is</p>';
			echo '<img src="data:'.$p->phototype.';base64,'.base64_encode($p->photo).'" /><br />';
		}
		?>
		<label for="photo">Choose a new or different photo</label>
		<input type="file" name="photo" />
		<label for="tags">Tags (Separated by comma)</label>
		<input type="text" name="tags"list="tag_list"
		<?php
		$t = new tags;
		if($p->hasProfile == TRUE)
		{
			echo 'value="'.$t->getTagString($p->tags).'"';
		}
		else
		{
			echo 'placeholder="tags"';
		}
		?>
		 required multiple />
		<datalist id="tag_list">
		<?php
		$result = $t->getTags();
		if(isset($result))
		{
			foreach($result as $row)
			{
				echo '<option value="'.$row->id.'" label="'.trim($row->name).'">'.PHP_EOL;
			}
		}
		?>
		</datalist>
		<button class="btn" type="submit">Submit</button>
	</fieldset>
</form>