<?php
require 'db.php';
require dirname(dirname(__FILE__)) . '/app-assets/vendors/autoload.php';

$getkey = $con->query("select * from setting")->fetch_assoc();
define('API_KEY', $getkey['sendgrid_key']);
define('API_EMAIL', $getkey['sendgrid_email']);

$data = json_decode(file_get_contents('php://input'), true);
if ($data['mobile'] == ''  or $data['password'] == '') {
    $returnArr = array("ResponseCode" => "401", "Result" => "false", "ResponseMsg" => "Something Went Wrong!");
} else {
    $mobile = strip_tags(mysqli_real_escape_string($con, $data['mobile']));
    $imei = strip_tags(mysqli_real_escape_string($con, $data['imei']));
    $password = strip_tags(mysqli_real_escape_string($con, $data['password']));

    $chek = $con->query("select * from user where (mobile='" . $mobile . "' or email='" . $mobile . "') and status = 1 and password='" . $password . "'");
    $status = $con->query("select * from user where status = 1");
    if ($status->num_rows != 0) {
        if ($chek->num_rows != 0) {
            $c = $con->query("select * from user where (mobile='" . $mobile . "' or email='" . $mobile . "')  and status = 1 and password='" . $password . "'");
            $c = $c->fetch_assoc();
            $dc = $con->query("select * from area_db where name='" . $c['area'] . "'");
            $vb = $dc->fetch_assoc();
            $returnArr = array("user" => $c, "d_charge" => $vb['dcharge'], "ResponseCode" => "200", "Result" => "true", "ResponseMsg" => "Login successfully!");
            
            // Sendgrid Mail Integration
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(API_EMAIL, "E-Nest");
            $email->setSubject("New Sign-in on Enest App");
            $email->addTo($c['email'], $c['name']);
            $email->addContent(
                "text/html", "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
                <html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\" style=\"width:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0\">
                 <head> 
                  <meta charset=\"UTF-8\"> 
                  <meta content=\"width=device-width, initial-scale=1\" name=\"viewport\"> 
                  <meta name=\"x-apple-disable-message-reformatting\"> 
                  <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> 
                  <meta content=\"telephone=no\" name=\"format-detection\"> 
                  <title>New User Log-In</title> 
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
                              <td align=\"left\" style=\"padding:0;Margin:0;padding-top:20px;padding-left:20px;padding-right:20px\"> 
                               <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td align=\"center\" valign=\"top\" style=\"padding:0;Margin:0;width:560px\"> 
                                   <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                     <tr style=\"border-collapse:collapse\"> 
                                      <td align=\"center\" style=\"padding:0;Margin:0;font-size:0px\"><img class=\"adapt-img\" src=\"https://owrnhh.stripocdn.email/content/guids/CABINET_37be19f93fd1833ef516462722e25083/images/23001611226601593.png\" alt style=\"display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic\" width=\"560\" height=\"88\"></td> 
                                     </tr> 
                                   </table></td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"left\" style=\"padding:0;Margin:0;padding-top:20px;padding-left:20px;padding-right:20px\"> 
                               <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td align=\"center\" valign=\"top\" style=\"padding:0;Margin:0;width:560px\"> 
                                   <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                     <tr style=\"border-collapse:collapse\"> 
                                      <td align=\"center\" style=\"padding:0;Margin:0;font-size:0px\"><img class=\"adapt-img\" src=\"https://owrnhh.stripocdn.email/content/guids/CABINET_37be19f93fd1833ef516462722e25083/images/56461611226659343.png\" alt style=\"display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic\" width=\"100\" height=\"63\"></td> 
                                     </tr> 
                                   </table></td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"left\" style=\"padding:0;Margin:0;padding-top:20px;padding-left:20px;padding-right:20px\"> 
                               <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td align=\"center\" valign=\"top\" style=\"padding:0;Margin:0;width:560px\"> 
                                   <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                     <tr style=\"border-collapse:collapse\"> 
                                      <td align=\"left\" style=\"padding:40px;Margin:0\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><strong><span>Hi ".$c['name'].",</span></strong><br><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\">Greetings from Enest!</p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\">We've received a log-in request from your account in the E-Nest Application.</p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\">If not done by you, please call us at <a href=\"tel:+917602630763\" target=\"_blank\" style=\"-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-size:14px;text-decoration:underline;color:#1376C8\">+917602630763</a> or email us your issue at <a href=\"mailto:support@enest.store?subject=Unusual%20Log-in%20request%20from%20my%20account.\" target=\"_blank\" style=\"-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-size:14px;text-decoration:underline;color:#1376C8\">support@enest.store</a></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\">Best Wishes,</p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\">Team Enest.</p></td> 
                                     </tr> 
                                   </table></td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                             <tr style=\"border-collapse:collapse\"> 
                              <td align=\"left\" style=\"padding:0;Margin:0;padding-top:20px;padding-left:20px;padding-right:20px\"> 
                               <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                 <tr style=\"border-collapse:collapse\"> 
                                  <td align=\"center\" valign=\"top\" style=\"padding:0;Margin:0;width:560px\"> 
                                   <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" role=\"presentation\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px\"> 
                                     <tr style=\"border-collapse:collapse\"> 
                                      <td align=\"center\" style=\"padding:0;Margin:0;font-size:0px\"><img class=\"adapt-img\" src=\"https://owrnhh.stripocdn.email/content/guids/CABINET_37be19f93fd1833ef516462722e25083/images/52021611227226067.png\" alt style=\"display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic\" width=\"560\" height=\"88\"></td> 
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
        } else {
            $returnArr = array("ResponseCode" => "401", "Result" => "false", "ResponseMsg" => "Invalid Email/Mobile No or Password!!!");
        }
    } else {
        $returnArr = array("ResponseCode" => "401", "Result" => "false", "ResponseMsg" => "Your Status Deactivate!!!");
    }
}

echo json_encode($returnArr);