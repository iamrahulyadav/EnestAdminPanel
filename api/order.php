<?php
require 'db.php';
require dirname(dirname(__FILE__)) . '/app-assets/vendors/autoload.php';

$getkey = $con->query("select * from setting")->fetch_assoc();
define('API_KEY', $getkey['sendgrid_key']);
define('API_EMAIL', $getkey['sendgrid_email']);
define('CALL', $getkey['callsupport']);

$data = json_decode(file_get_contents('php://input'), true);
if ($data['uid'] == '') {
    $returnArr = array("ResponseCode" => "401", "Result" => "false", "ResponseMsg" => "Something Went Wrong!");
} else {

    $uid =  $data['uid'];
    $ddate = $data['ddate'];
    $a = explode('/', $ddate);
    $ddate = $a[2] . '-' . $a[1] . '-' . $a[0];
    $timesloat = $data['timesloat'];
    $oid = '#' . uniqid();
    $pname = $data['pname'];
    $status = 'pending';
    $p_method = $data['p_method'];
    $address_id = $data['address_id'];
    $tax = $data['tax'];
    $coupon_id = $data['coupon_id'];
    $cou_amt = $data['cou_amt'];
    $wal_amt = $data['wal_amt'];
    $timestamp = date("Y-m-d");
    $tid = $data['tid'];
    $total = number_format((float)$data['total'], 2, '.', '');
    $e = array();
    $p = array();
    $w = array();
    $pp = array();
    $q = array();
    $subtotal = 0;
    for ($i = 0; $i < count($pname); $i++) {
        $e[] = mysqli_real_escape_string($con, $pname[$i]['title']);
        $p[] = $pname[$i]['pid'];
        $temp = $con->query("select * from product where id=" . $pname[$i]['pid'] . "")->fetch_assoc();
        $w[] = $pname[$i]['weight'];
        $pp[] = $pname[$i]['cost'];
        $q[] = $pname[$i]['qty'];
        $subtotal = $subtotal + ($pname[$i]['cost'] * (1 - $temp['discount'] / 100) * $pname[$i]['qty']);
    }
    $count = count($pname);
    $pname = implode('$;', $e);
    $pid = implode('$;', $p);
    $ptype = implode('$;', $w);
    $pprice = implode('$;', $pp);
    $qty = implode('$;', $q);

    $con->query("insert into orders(`oid`,`uid`,`pname`,`pid`,`ptype`,`pprice`,`ddate`,`timesloat`,`order_date`,`status`,`qty`,`total`,`p_method`,`address_id`,`tax`,`tid`,`cou_amt`,`coupon_id`,`wal_amt`)values('" . $oid . "'," . $uid . ",'" . $pname . "','" . $pid . "','" . $ptype . "','" . $pprice . "','" . $ddate . "','" . $timesloat . "','" . $timestamp . "','" . $status . "','" . $qty . "'," . $total . ",'" . $p_method . "'," . $address_id . "," . $tax . ",'" . $tid . "'," . $cou_amt . "," . $coupon_id . "," . $wal_amt . ")");

    $con->query("update user set wallet=wallet-" . $wal_amt . " where id=" . $uid . "");

    $returnArr = array("ResponseCode" => "200", "Result" => "true", "ResponseMsg" => "Order Placed Successfully!!!");

    $c = $con->query("select * from user where id=" . $uid . "")->fetch_assoc();

    $delivery = $total - (($subtotal + $tax) - ($cou_amt + $wal_amt));
    $day = date('l', strtotime($timestamp));
    $month = date("F", strtotime($timestamp));
    $year = date("Y", strtotime($timestamp));
    $d = substr($timestamp, 8, 2);

    // Sendgrid Mail Integration
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom(API_EMAIL, "E-Nest");
    $email->setSubject("Your E-Nest order " . $oid . " of " . $count . " item(s) has been placed.");
    $email->addTo($c['email'], $c['name']);
    $email->addContent(
        "text/html",
        "
        <!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
        <html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\" style=\"width:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0\">
         <head> 
          <meta charset=\"UTF-8\"> 
          <meta content=\"width=device-width, initial-scale=1\" name=\"viewport\"> 
          <meta name=\"x-apple-disable-message-reformatting\"> 
          <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> 
          <meta content=\"telephone=no\" name=\"format-detection\"> 
          <title>Order Confirmation</title> 
          <style type=\"text/css\">
        #outlook a {
            padding:0;
        }
        .ExternalClass {
            width:100%;
        }
        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
            line-height:100%;
        }
        .es-button {
            mso-style-priority:100!important;
            text-decoration:none!important;
        }
        a[x-apple-data-detectors] {
            color:inherit!important;
            text-decoration:none!important;
            font-size:inherit!important;
            font-family:inherit!important;
            font-weight:inherit!important;
            line-height:inherit!important;
        }
        .es-desk-hidden {
            display:none;
            float:left;
            overflow:hidden;
            width:0;
            max-height:0;
            line-height:0;
            mso-hide:all;
        }
        @media only screen and (max-width:600px) {p, ul li, ol li, a { font-size:16px!important; line-height:150%!important } h1 { font-size:30px!important; text-align:center; line-height:120% } h2 { font-size:26px!important; text-align:center; line-height:120% } h3 { font-size:20px!important; text-align:center; line-height:120% } h1 a { font-size:30px!important } h2 a { font-size:26px!important } h3 a { font-size:20px!important } .es-menu td a { font-size:16px!important } .es-header-body p, .es-header-body ul li, .es-header-body ol li, .es-header-body a { font-size:16px!important } .es-footer-body p, .es-footer-body ul li, .es-footer-body ol li, .es-footer-body a { font-size:16px!important } .es-infoblock p, .es-infoblock ul li, .es-infoblock ol li, .es-infoblock a { font-size:12px!important } *[class=\"gmail-fix\"] { display:none!important } .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3 { text-align:right!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-button-border { display:block!important } .es-btn-fw { border-width:10px 0px!important; text-align:center!important } .es-adaptive table, .es-btn-fw, .es-btn-fw-brdr, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .es-adapt-td { display:block!important; width:100%!important } .adapt-img { width:100%!important; height:auto!important } .es-m-p0 { padding:0px!important } .es-m-p0r { padding-right:0px!important } .es-m-p0l { padding-left:0px!important } .es-m-p0t { padding-top:0px!important } .es-m-p0b { padding-bottom:0!important } .es-m-p20b { padding-bottom:20px!important } .es-mobile-hidden, .es-hidden { display:none!important } tr.es-desk-hidden, td.es-desk-hidden, table.es-desk-hidden { width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } tr.es-desk-hidden { display:table-row!important } table.es-desk-hidden { display:table!important } td.es-desk-menu-hidden { display:table-cell!important } .es-menu td { width:1%!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } table.es-social { display:inline-block!important } table.es-social td { display:inline-block!important } a.es-button, button.es-button { font-size:20px!important; display:block!important; border-width:10px 0px 10px 0px!important } }
        </style> 
         </head> 
         <body style=\"width:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0\"> 
          <div class=\"es-wrapper-color\" style=\"background-color:#F6F6F6\"> 
           <table class=\"es-wrapper\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top\"> 
             <tr style=\"border-collapse:collapse\"> 
              <td valign=\"top\" style=\"padding:0;Margin:0\"> 
               <table class=\"es-header\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top\"> 
                 <tr style=\"border-collapse:collapse\"> 
                  <td align=\"center\" style=\"padding:0;Margin:0\"> 
                   <table class=\"es-header-body\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px\"> 
                     <tr style=\"border-collapse:collapse\"> 
                      <td style=\"padding:40px;Margin:0;background-color:#FFFFFF\" bgcolor=\"#ffffff\" align=\"left\"> 
                       <table class=\"es-left\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:left\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td class=\"es-m-p20b\" align=\"center\" style=\"padding:0;Margin:0;width:204px\"> 
                           <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td class=\"es-m-txt-l\" align=\"left\" style=\"padding:0;Margin:0;font-size:0px\"><img src=\"https://owrnhh.stripocdn.email/content/guids/CABINET_37be19f93fd1833ef516462722e25083/images/56461611226659343.png\" alt style=\"display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic\" width=\"103\" height=\"64\"></td> 
                             </tr> 
                           </table></td> 
                          <td class=\"es-hidden\" style=\"padding:0;Margin:0;width:5px\"></td> 
                         </tr> 
                       </table> 
                       <table class=\"es-left\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:left\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td class=\"es-m-p20b\" align=\"left\" style=\"padding:0;Margin:0;width:141px\"> 
                           <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td valign=\"top\" align=\"right\" style=\"padding:0;Margin:0;padding-right:10px;padding-top:20px;width:30px;font-size:0px\"><img src=\"https://owrnhh.stripocdn.email/content/guids/CABINET_b07b88d99bde76e46a2396d11f306432/images/26531551864324009.png\" alt style=\"display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic\" width=\"26\" height=\"26\"></td> 
                              <td align=\"left\" style=\"padding:0;Margin:0\"> 
                               <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td align=\"left\" style=\"padding:0;Margin:0;padding-top:20px\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><a href=\"tel:" . CALL . "\" style=\"-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-size:14px;text-decoration:underline;color:#1376C8\">" . CALL . "</a></p></td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table> 
                       <table class=\"es-right\" cellspacing=\"0\" cellpadding=\"0\" align=\"right\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:right\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td align=\"left\" style=\"padding:0;Margin:0;width:165px\"> 
                           <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td valign=\"top\" align=\"right\" style=\"padding:0;Margin:0;padding-right:10px;padding-top:20px;width:30px;font-size:0px\"><img src=\"https://owrnhh.stripocdn.email/content/guids/CABINET_b07b88d99bde76e46a2396d11f306432/images/4801551865294269.png\" alt style=\"display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic\" width=\"26\" height=\"26\"></td> 
                              <td align=\"left\" style=\"padding:0;Margin:0\"> 
                               <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td align=\"left\" style=\"padding:0;Margin:0;padding-top:20px\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><a target=\"_blank\" href=\"mailto:support@enest.store\" style=\"-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-size:14px;text-decoration:underline;color:#1376C8\">support@enest.store</a></p></td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table> 
                      </td> 
                     </tr> 
                     <tr style=\"border-collapse:collapse\"> 
                      <td align=\"left\" style=\"padding:0;Margin:0;padding-left:40px;padding-right:40px\"> 
                       <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td align=\"center\" valign=\"top\" style=\"padding:0;Margin:0;width:520px\"> 
                           <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"center\" style=\"padding:0;Margin:0;padding-bottom:15px;font-size:0\"> 
                               <table border=\"0\" width=\"100%\" height=\"100%\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;border-bottom:1px solid #CCCCCC;background:none;height:1px;width:100%;margin:0px\"></td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table></td> 
                     </tr> 
                     <tr style=\"border-collapse:collapse\"> 
                      <td align=\"left\" style=\"padding:0;Margin:0;padding-left:40px;padding-right:40px\"> 
                       <table cellpadding=\"0\" cellspacing=\"0\" class=\"es-left\" align=\"left\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:left\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td class=\"es-m-p20b\" align=\"left\" style=\"padding:0;Margin:0;width:250px\"> 
                           <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"left\" style=\"padding:0;Margin:0;padding-top:40px\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><strong>Hello " . strtok($c['name'], " ") . ",</strong></p></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table> 
                       <table cellpadding=\"0\" cellspacing=\"0\" class=\"es-right\" align=\"right\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:right\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td align=\"left\" style=\"padding:0;Margin:0;width:250px\"> 
                           <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"right\" style=\"padding:0;Margin:0\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><span style=\"font-size:27px\"><strong>Order Confirmation</strong></span><br><br>Order " . $oid . "</p></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table> 
                      </td> 
                     </tr> 
                   </table></td> 
                 </tr> 
               </table> 
               <table class=\"es-content\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%\"> 
                 <tr style=\"border-collapse:collapse\"> 
                  <td align=\"center\" style=\"padding:0;Margin:0\"> 
                   <table class=\"es-content-body\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px\"> 
                     <tr style=\"border-collapse:collapse\"> 
                      <td align=\"left\" style=\"padding:0;Margin:0;padding-left:40px;padding-right:40px\"> 
                       <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td align=\"center\" valign=\"top\" style=\"padding:0;Margin:0;width:520px\"> 
                           <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"left\" style=\"padding:0;Margin:0;padding-top:20px\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:12px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br>Thank you for your order. We thought you'd like to know that E-Nest is processing your item(s). We’ll send a confirmation once your order is ready. If you need to cancel&nbsp;an item from this order or manage other orders, please open E-Nest App.<br><br>Please note that a signature may be required for the delivery of the package.</p></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table></td> 
                     </tr> 
                     <tr style=\"border-collapse:collapse\"> 
                      <td align=\"left\" style=\"padding:0;Margin:0;padding-left:40px;padding-right:40px\"> 
                       <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td align=\"center\" valign=\"top\" style=\"padding:0;Margin:0;width:520px\"> 
                           <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"left\" style=\"padding:0;Margin:0;padding-top:15px\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:27px;color:#212C95\"><strong>Order Summary</strong></p></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table></td> 
                     </tr> 
                     <tr style=\"border-collapse:collapse\"> 
                      <td align=\"left\" style=\"padding:0;Margin:0;padding-left:40px;padding-right:40px\"> 
                       <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td align=\"center\" valign=\"top\" style=\"padding:0;Margin:0;width:520px\"> 
                           <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"center\" style=\"padding:0;Margin:0;padding-bottom:20px;font-size:0\"> 
                               <table border=\"0\" width=\"100%\" height=\"100%\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;border-bottom:1px solid #CCCCCC;background:none;height:1px;width:100%;margin:0px\"></td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                           </table></td> 
                         </tr>
                       </table></td> 
                     </tr> 
                     <tr style=\"border-collapse:collapse\"> 
                      <td align=\"left\" style=\"padding:0;Margin:0;padding-left:40px;padding-right:40px\"> 
                       <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td align=\"center\" valign=\"top\" style=\"padding:0;Margin:0;width:520px\"> 
                           <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"left\" style=\"padding:0;Margin:0\"> 
                               <table border=\"0\" cellspacing=\"1\" cellpadding=\"1\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:500px\" class=\"cke_show_border\" role=\"presentation\"> 
                               <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;font-size:10px\">Order " . $oid . "<br><br></td> 
                                  <td style=\"padding:0;Margin:0;font-size:10px\"><br><br></td> 
                                </tr>
                                <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;font-size:10px\">Placed on " . $day . ", " . $month . " " . $d . ", " . $year . "<br><br></td> 
                                  <td style=\"padding:0;Margin:0;font-size:10px\"><br><br></td> 
                                </tr>   
                               <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\">Item Subtotal<br><br></td> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\">Rs. " . $subtotal . "<br><br></td> 
                                 </tr> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\">Delivery Charge<br><br></td> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\">Rs. " . $delivery . "<br><br></td> 
                                 </tr> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\">Coupon Discount<br><br></td> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\">Rs. " . $cou_amt . "<br><br></td> 
                                 </tr> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\">Tax<br><br></td> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\">Rs. " . $tax . "<br><br></td> 
                                 </tr> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;font-size:15px\">Order Total</td> 
                                  <td style=\"padding:0;Margin:0;font-size:15px\">Rs. " . $total . "</td> 
                                 </tr> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\"><br></td> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\"><br></td> 
                                 </tr> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\">Payment Pending</td> 
                                  <td style=\"padding:0;Margin:0;font-size:12px\">Rs. " . $total . "</td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table></td> 
                     </tr> 
                     <tr style=\"border-collapse:collapse\"> 
                      <td align=\"left\" style=\"padding:0;Margin:0;padding-left:40px;padding-right:40px\"> 
                       <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td align=\"center\" valign=\"top\" style=\"padding:0;Margin:0;width:520px\"> 
                           <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"left\" style=\"padding:0;Margin:0\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br><br>Track your order with the&nbsp;<a target=\"_blank\" style=\"-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-size:14px;text-decoration:underline;color:#212C95\" href=\"https://play.google.com/store/apps/details?id=com.enestcustomerapp\">E-Nest&nbsp;App</a>.<br><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\">We hope to see you again soon!<br><br><b>E-Nest</b><br><br></p></td> 
                             </tr> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"center\" style=\"padding:20px;Margin:0;font-size:0\"> 
                               <table border=\"0\" width=\"100%\" height=\"100%\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td style=\"padding:0;Margin:0;border-bottom:1px solid #CCCCCC;background:none;height:1px;width:100%;margin:0px\"></td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table></td> 
                     </tr> 
                   </table></td> 
                 </tr> 
               </table> 
               <table class=\"es-footer\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top\"> 
                 <tr style=\"border-collapse:collapse\"> 
                  <td align=\"center\" style=\"padding:0;Margin:0\"> 
                   <table class=\"es-footer-body\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px\"> 
                     <tr style=\"border-collapse:collapse\"> 
                      <td align=\"left\" style=\"padding:0;Margin:0;padding-left:40px;padding-right:40px\"> 
                       <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                         <tr style=\"border-collapse:collapse\"> 
                          <td align=\"center\" valign=\"top\" style=\"padding:0;Margin:0;width:520px\"> 
                           <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"left\" style=\"padding:0;Margin:0\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:9px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br>This email was sent from a notification-only address that cannot accept incoming email. Please do not reply to this message.<br></p></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table></td> 
                     </tr> 
                   </table></td> 
                 </tr> 
               </table></td> 
             </tr> 
           </table> 
          </div>  
         </body>
        </html>"
    );
    $sendgrid = new \SendGrid(API_KEY);
    try {
        $response = $sendgrid->send($email);
    } catch (Exception $e) {
    }
}

echo json_encode($returnArr);
