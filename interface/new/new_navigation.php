<?php 
include_once("../globals.php");
?>

<html>
<head>
<? html_header_show();?>
<title><?php xl('Navigation','e'); ?></title>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

</head>
<body <?php echo $nav_bg_line; ?> topmargin=0 rightmargin=4 leftmargin=2 marginheight=0 bottommargin=0 link=#000000 vlink=#000000 alink=#000000>

<form border=0 method=post target="_top" name="find_patient" action="../main/finder/patient_finder.php">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td valign="middle" width="10%" nowrap>
<input type=entry size=10 name=patient>
  <select name="findBy" size=1> 
	<option value="Last" selected><?php xl('Last Name','e');?></option>
	<option value="ID"><?php xl('ID','e');?></option>
	<option value="SSN"><?php xl('SSN','e');?></option>
	<option value="DOB"><?php xl('DOB','e');?></option>
</select>
</td>

<td valign="middle" nowrap>
<a href="javascript:top.restoreSession();document.find_patient.action='../main/finder/patient_finder.php';document.find_patient.submit();" class=link>&nbsp;<?php xl('Find Patient','e');?></a>
</td>

<td valign="middle" align="right" nowrap>
<a href="../main/main_screen.php" target="_top" class="logout" onclick="top.restoreSession()"><?php xl('Back','e'); ?></a>&nbsp;&nbsp;
</td>
</tr>
</table>

</form>

</body>
</html>
