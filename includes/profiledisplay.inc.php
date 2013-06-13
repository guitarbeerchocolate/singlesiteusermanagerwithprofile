<?php
$p = new profile;
$p->getProfile($session->userid);
echo '<span id="displayname">'.$p->name.'</span><br />';
echo '<img id="userphoto" src="data:'.$p->phototype.';base64,'.base64_encode($p->photo).'" /><br />';
$t = new tags;
echo '<span id="usertags">'.$t->getTagString($p->tags).'</span>';
?>