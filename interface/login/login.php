<?php
$ignoreAuth=true;
include_once("../globals.php");
include_once("$srcdir/md5.js");
include_once("$srcdir/sql.inc");
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<script language='JavaScript'>

function imsubmitted() {
<?php if (!empty($GLOBALS['restore_sessions'])) { ?>
 // Delete the session cookie by setting its expiration date in the past.
 // This forces the server to create a new session ID.
 var olddate = new Date();
 olddate.setFullYear(olddate.getFullYear() - 1);
 document.cookie = '<?php echo session_name() . '=' . session_id() ?>; path=/; expires=' + olddate.toGMTString();
<?php } ?>
 return true;
}

</script>

</head>
<body <?echo $login_body_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0 onload="javascript:document.login_form.authUser.focus();" >

<span class="text"></span>

<center>

<form method="POST" action="../main/main_screen.php?auth=login" target="_top"
 name="login_form" onsubmit="return imsubmitted();">

<?php
$res = sqlStatement("select distinct name from groups");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
	$result[$iter] = $row;
if (count($result) == 1) {
	$resvalue = $result[0]{"name"};
	echo "<input type='hidden' name='authProvider' value='$resvalue' />\n";
}
?>

<table width=100% height="90%">
<tr>
<td valign=middle width=33%>
<?echo $logocode;?>
</td>
<td align='center' valign='middle' width=34%>
<table>
<?php
if (count($result) != 1) {
?>
<tr>
<td><span class="text"><? xl('Group:','e'); ?></span></td>
<td>
<select name=authProvider>
<?php
	foreach ($result as $iter) {
		echo "<option value='".$iter{"name"}."'>".$iter{"name"}."</option>\n";
	}
?>
</select>
</td></tr>
<?php
}
?>
<tr>
<td><span class="text"><? xl('Username:','e'); ?></span></td>
<td>
<input type="entry" size=10 name=authUser>
</td></tr><tr>
<td><span class="text"><? xl('Password:','e'); ?></span></td>
<td>
<input type="password" size=10 name=clearPass>
</td></tr>
<tr><td>&nbsp;</td><td>
<input type="hidden" name="authPass">
<input type="submit" onClick="javascript:this.form.authPass.value=MD5(this.form.clearPass.value);this.form.clearPass.value='';" value=<? xl('Login','e');?>>
</td></tr>
</table>

</td>
<td width=33%>

<!-- Uncomment this for the OpenEMR demo installation
<p><center>login = admin
<br>password = pass
-->

</center></p>

</td>
</table>

</form>

<address>
<a href="copyright_notice.html" target="main"><? xl('Copyright Notice','e'); ?></a><br />
</address>

</center>
</body>
</html>
