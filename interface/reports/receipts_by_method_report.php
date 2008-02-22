<?php
  // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

  // This is a report of receipts by payment method.  It's most useful for
  // sites using pos_checkout.php (e.g. weight loss clinics) because this
  // plugs a payment method like Cash, Check, VISA, etc. into the "source"
  // column of the SQL-Ledger acc_trans table.

  include_once("../globals.php");
  include_once("../../library/patient.inc");
  include_once("../../library/sql-ledger.inc");
  include_once("../../library/acl.inc");

  function bucks($amount) {
    if ($amount)
      printf("%.2f", $amount);
  }

  if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

  SLConnect();

  $form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
  $form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
  $form_use_edate = $_POST['form_use_edate'];
?>
<html>
<head>
<? html_header_show();?>
<title><?xl('Receipts by Payment Method','e')?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?xl('Receipts by Payment Method','e')?></h2>

<form method='post' action='receipts_by_method_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   &nbsp;<select name='form_use_edate'>
    <option value='0'><?php xl('Payment Date','e'); ?></option>
    <option value='1'<?php if ($form_use_edate) echo ' selected' ?>><?php xl('Invoice Date','e'); ?></option>
   </select>
   &nbsp;<?xl('From:','e')?>
   <input type='text' name='form_from_date' size='10' value='<? echo $form_from_date; ?>' title='MM/DD/YYYY'>
   &nbsp;<?php xl('To:','e'); ?>
   <input type='text' name='form_to_date' size='10' value='<? echo $form_to_date; ?>' title='MM/DD/YYYY'>
   &nbsp;
   <input type='checkbox' name='form_details' value='1'<? if ($_POST['form_details']) echo " checked"; ?>><?xl('Details','e')?>
   &nbsp;
   <input type='checkbox' name='form_checkno' value='1'<? if ($_POST['form_checkno']) echo " checked"; ?>><?xl('Check#','e')?>
   &nbsp;
   <input type='submit' name='form_refresh' value="<?xl('Refresh','e')?>">
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   <?xl('Method','e')?>
  </td>
  <td class="dehead">
   <?xl('Date','e')?>
  </td>
  <td class="dehead">
   <?xl('Invoice','e')?>
  </td>
  <td class="dehead">
   <?xl('Procedure','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Adjustments','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Payments','e')?>
  </td>
 </tr>
<?
  $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
  if ($sl_err) die($sl_err);
  $chart_id_income = SLQueryValue("select id from chart where accno = '$sl_income_acc'");
  if ($sl_err) die($sl_err);

  if ($_POST['form_refresh']) {
    $from_date = $form_from_date;
    $to_date   = $form_to_date;

    /*****************************************************************
    $query = "select acc_trans.amount, acc_trans.transdate, acc_trans.memo, " .
      "acc_trans.source, ar.invnumber, ar.employee_id from acc_trans, ar where " .
      "acc_trans.chart_id = $chart_id_cash and " .
      "ar.id = acc_trans.trans_id and ";
    *****************************************************************/
    $query = "SELECT acc_trans.amount, acc_trans.transdate, acc_trans.memo, " .
      "replace(acc_trans.source, 'InvAdj ', '') AS source, " .
      "acc_trans.chart_id, ar.invnumber, ar.employee_id " .
      "FROM acc_trans, ar WHERE " .
      "( acc_trans.chart_id = $chart_id_cash OR " .
      "( acc_trans.chart_id = $chart_id_income AND " .
      "acc_trans.source LIKE 'InvAdj %' ) ) AND " .
      "ar.id = acc_trans.trans_id AND ";

    if ($form_use_edate) {
      $query .= "ar.transdate >= '$from_date' AND " .
      "ar.transdate <= '$to_date'";
    } else {
      $query .= "acc_trans.transdate >= '$from_date' AND " .
      "acc_trans.transdate <= '$to_date'";
    }

    // $query .= " order by acc_trans.source, acc_trans.transdate, ar.invnumber, acc_trans.memo";
    $query .= " ORDER BY source, acc_trans.transdate, ar.invnumber, acc_trans.memo";

    echo "<!-- $query -->\n";

    $t_res = SLQuery($query);
    if ($sl_err) die($sl_err);

    $paymethod   = "";
    $paymethodleft = "";
    $methodpaytotal = 0;
    $grandpaytotal  = 0;
    $methodadjtotal  = 0;
    $grandadjtotal  = 0;

    for ($irow = 0; $irow < SLRowCount($t_res); ++$irow) {
      $row = SLGetRow($t_res, $irow);
      $rowpayamount = 0 - $row['amount'];
      $rowadjamount = 0;
      if ($row['chart_id'] == $chart_id_income) {
        $rowadjamount = $rowpayamount;
        $rowpayamount = 0;
      }

      $rowmethod = trim($row['source']);
      if (!$_POST['form_checkno']) {
        // Extract only the first word as the payment method because any
        // following text will be some petty detail like a check number.
        $rowmethod = substr($rowmethod, 0, strcspn($rowmethod, ' /'));
      }

      if (! $rowmethod) $rowmethod = 'Unknown';

      if ($paymethod != $rowmethod) {
        if ($paymethod) {
          // Print method total.
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="4">
   <? echo xl('Total for ') . $paymethod ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($methodadjtotal) ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($methodpaytotal) ?>
  </td>
 </tr>
<?php
        }
        $methodpaytotal = 0;
        $methodadjtotal  = 0;
        $paymethod = $rowmethod;
        $paymethodleft = $paymethod;
      }

      if ($_POST['form_details']) {
?>

 <tr>
  <td class="detail">
   <?php echo $paymethodleft; $paymethodleft = "&nbsp;" ?>
  </td>
  <td class="dehead">
   <?php echo $row['transdate'] ?>
  </td>
  <td class="detail">
   <?php echo $row['invnumber'] ?>
  </td>
  <td class="dehead">
   <?php echo $row['memo'] ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($rowadjamount) ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($rowpayamount) ?>
  </td>
 </tr>
<?php
      }
      $methodpaytotal += $rowpayamount;
      $grandpaytotal  += $rowpayamount;
      $methodadjtotal += $rowadjamount;
      $grandadjtotal  += $rowadjamount;
    }
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="4">
   <?echo xl('Total for ') . $paymethod ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($methodadjtotal) ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($methodpaytotal) ?>
  </td>
 </tr>

 <tr bgcolor="#ffdddd">
  <td class="detail" colspan="4">
   <?php xl('Grand Total','e') ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($grandadjtotal) ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($grandpaytotal) ?>
  </td>
 </tr>

<?php
  }
  SLClose();
?>

</table>
</form>
</center>
</body>
</html>
