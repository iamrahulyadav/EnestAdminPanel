<?php
require 'db.php';
require dirname(dirname(__FILE__)) . '/app-assets/vendors/autoload.php';
$getkey = $con->query("select * from setting")->fetch_assoc();
define('ONE_KEY', $getkey['one_key']);
define('ONE_HASH', $getkey['one_hash']);
define('API_KEY', $getkey['sendgrid_key']);
define('API_EMAIL', $getkey['sendgrid_email']);
define('CALL', $getkey['callsupport']);

header('Content-Type: text/html; charset=utf-8');
$data = json_decode(file_get_contents('php://input'), true);

$oid = $data['oid'];
$status = $data['status'];
$rid = $data['rid'];
if ($oid == '' or $status == '' or $rid == '') {
	$returnArr = array("ResponseCode" => "401", "Result" => "false", "ResponseMsg" => "Something Went wrong  try again !");
} else {

	$oid = strip_tags(mysqli_real_escape_string($con, $oid));
	$rid = strip_tags(mysqli_real_escape_string($con, $rid));
	$status = strip_tags(mysqli_real_escape_string($con, $status));
	$check = $con->query("select *  from orders where rid=" . $rid . " and id=" . $oid . "")->num_rows;
	if ($check != 0) {
		if ($status == 'accept') {

			$con->query("update orders set status='processing',a_status=2,r_status='Accepted' where id=" . $oid . "");
			$con->query("update rider set accept = accept+1 where id=" . $rid . "");
			$checks = $con->query("select * from orders where id=" . $oid . "")->fetch_assoc();
			$heading = array(
				"en" => 'Your Order Has Been Accepted 🔔' //mesaj burasi
			);
			$content = array(
				"en" => 'Order No.: ' . $oid . ' is on the way.'
			);
			$fields = array(
				'app_id' => ONE_KEY,
				'included_segments' =>  array("Subscribed Users"),
				'filters' => array(array('field' => 'tag', 'key' => 'userId', 'relation' => '=', 'value' => $checks['uid'])),
				'headings' => $heading,
				'contents' => $content
			);
			$fields = json_encode($fields);


			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json; charset=utf-8',
					'Authorization: Basic ' . ONE_HASH
				)
			);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

			$response = curl_exec($ch);
			curl_close($ch);

			$returnArr = array("ResponseCode" => "200", "Result" => "true", "ResponseMsg" => "Order Accepted Successfully!!!!!");
		} else if ($status == 'reject') {
			$con->query("update orders set a_status=5,r_status='Rejected',rid=0 where id=" . $oid . "");
			$con->query("update rider set reject = reject+1 where id=" . $rid . "");
			$returnArr = array("ResponseCode" => "200", "Result" => "false", "ResponseMsg" => "Order Rejected Successfully!!!!!");
		} else if ($status == 'cancle') {
			$comment = $data['comment'];
			$con->query("update orders set a_status=4,r_status='Cancelled',status='cancelled',s_photo='" . $comment . "' where id=" . $oid . "");

			$checks = $con->query("select * from orders where id=" . $oid . "")->fetch_assoc();
			$heading = array(
				"en" => 'Your Order Has Been Cancelled 🔔'
			);
			$content = array(
				"en" => 'Order No.: ' . $oid //mesaj burasi
			);
			$fields = array(
				'app_id' => ONE_KEY,
				'included_segments' =>  array("Subscribed Users"),
				'filters' => array(array('field' => 'tag', 'key' => 'userId', 'relation' => '=', 'value' => $checks['uid'])),
				'headings' => $heading,
				'contents' => $content
			);
			$fields = json_encode($fields);


			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json; charset=utf-8',
					'Authorization: Basic ' . ONE_HASH
				)
			);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

			$response = curl_exec($ch);
			curl_close($ch);


			$returnArr = array("ResponseCode" => "200", "Result" => "true", "ResponseMsg" => "Order Cancelled Successfully!!!");

			// Sendgrid Mail Integration for cancellation
			$order = $con->query("select * from orders where  id=" . $oid . "")->fetch_assoc();
			$day = date('l', strtotime($order['order_date']));
			$month = date("F", strtotime($order['order_date']));
			$year = date("Y", strtotime($order['order_date']));
			$d = substr($order['order_date'], 8, 2);
			$c = $con->query("select * from user where id=" . $order['uid'] . "")->fetch_assoc();
			$email = new \SendGrid\Mail\Mail();
			$email->setFrom(API_EMAIL, "E-Nest");
			$email->setSubject("Your E-Nest order " . $order['oid'] . " has been cancelled.");
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
				  <title>Order Cancelled</title> 
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
									  <td align=\"right\" style=\"padding:0;Margin:0\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><span style=\"font-size:27px\"><strong>Order Cancelled</strong></span><br><br>Order " . $order['oid'] . "</p></td> 
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
									  <td align=\"left\" style=\"padding:0;Margin:0;padding-top:20px\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:12px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br>Your Order has been cancelled. To manage other orders, please open E-Nest App.<br><br></p></td> 
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
									  <td align=\"left\" style=\"padding:0;Margin:0;padding-top:15px\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:27px;color:#212C95\"><strong>Cancelled Order Details</strong></p></td> 
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
										  <td style=\"padding:0;Margin:0;font-size:12px\">Order " . $order['oid'] . "<br><br></td> 
										  <td style=\"padding:0;Margin:0;font-size:12px\"><br><br></td> 
										 </tr> 
										 <tr style=\"border-collapse:collapse\"> 
										  <td style=\"padding:0;Margin:0;font-size:12px\">Placed on " . $day . ", " . $month . " " . $d . ", " . $year . "<br><br></td> 
										  <td style=\"padding:0;Margin:0;font-size:12px\"><br><br></td> 
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
									  <td align=\"left\" style=\"padding:0;Margin:0\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:12px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\"><br><br>Manage other orders with the&nbsp;<a target=\"_blank\" style=\"-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-size:14px;text-decoration:underline;color:#212C95\" href=\"https://play.google.com/store/apps/details?id=com.enestcustomerapp\">E-Nest&nbsp;App</a>.<br><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333\">We hope to see you again soon!<br><br><b>E-Nest</b><br><br></p></td> 
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
		} else if ($status == 'complete') {
			$sign = $data['sign'];
			$con->query("update orders set a_status=3,r_status='Delivered',status='completed',photo='" . $sign . "' where id=" . $oid . "");
			$con->query("update rider set complete = complete + 1 where id=" . $rid . "");

			$checks = $con->query("select * from orders where id=" . $oid . "")->fetch_assoc();
			$heading = array(
				"en" => 'Your Order has been delivered successfully 🔔'
			);
			$content = array(
				"en" => 'Order No.: ' . $oid //mesaj burasi
			);
			$fields = array(
				'app_id' => ONE_KEY,
				'included_segments' =>  array("Subscribed Users"),
				'filters' => array(array('field' => 'tag', 'key' => 'userId', 'relation' => '=', 'value' => $checks['uid'])),
				'headings' => $heading,
				'contents' => $content
			);
			$fields = json_encode($fields);


			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json; charset=utf-8',
					'Authorization: Basic ' . ONE_HASH
				)
			);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

			$response = curl_exec($ch);
			curl_close($ch);


			$returnArr = array("ResponseCode" => "200", "Result" => "true", "ResponseMsg" => "Order Completed Successfully!!!");

			$c = $con->query("select * from user where id=" . $checks['uid'] . "")->fetch_assoc();

			// Sendgrid Mail Integration
			$email = new \SendGrid\Mail\Mail();
			$email->setFrom(API_EMAIL, "E-Nest");
			$email->setSubject("Delivered: Your E-Nest package has been delivered.");
			$email->addTo($c['email'], $c['name']);
			$email->addContent(
				"text/html",
				"<!DOCTYPE html
				PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
			  <html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\">
			  
			  <head>
				<meta charset=\"UTF-8\">
				<meta content=\"width=device-width, initial-scale=1\" name=\"viewport\">
				<meta name=\"x-apple-disable-message-reformatting\">
				<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
				<meta content=\"telephone=no\" name=\"format-detection\">
				<title>Order Delivered</title>
				<style type=\"text/css\">
				  /* CONFIG STYLES Please do not delete and edit CSS styles below */
				  /* IMPORTANT THIS STYLES MUST BE ON FINAL EMAIL */
				  #outlook a {
					padding: 0;
				  }
			  
				  .ExternalClass {
					width: 100%;
				  }
			  
				  .ExternalClass,
				  .ExternalClass p,
				  .ExternalClass span,
				  .ExternalClass font,
				  .ExternalClass td,
				  .ExternalClass div {
					line-height: 100%;
				  }
			  
				  .es-button {
					mso-style-priority: 100 !important;
					text-decoration: none !important;
				  }
			  
				  a[x-apple-data-detectors] {
					color: inherit !important;
					text-decoration: none !important;
					font-size: inherit !important;
					font-family: inherit !important;
					font-weight: inherit !important;
					line-height: inherit !important;
				  }
			  
				  .es-desk-hidden {
					display: none;
					float: left;
					overflow: hidden;
					width: 0;
					max-height: 0;
					line-height: 0;
					mso-hide: all;
				  }
			  
				  /*
			  END OF IMPORTANT
			  */
				  s {
					text-decoration: line-through;
				  }
			  
				  html,
				  body {
					width: 100%;
					font-family: arial, 'helvetica neue', helvetica, sans-serif;
					-webkit-text-size-adjust: 100%;
					-ms-text-size-adjust: 100%;
				  }
			  
				  table {
					mso-table-lspace: 0pt;
					mso-table-rspace: 0pt;
					border-collapse: collapse;
					border-spacing: 0px;
				  }
			  
				  table td,
				  html,
				  body,
				  .es-wrapper {
					padding: 0;
					Margin: 0;
				  }
			  
				  .es-content,
				  .es-header,
				  .es-footer {
					table-layout: fixed !important;
					width: 100%;
				  }
			  
				  img {
					display: block;
					border: 0;
					outline: none;
					text-decoration: none;
					-ms-interpolation-mode: bicubic;
				  }
			  
				  table tr {
					border-collapse: collapse;
				  }
			  
				  p,
				  hr {
					Margin: 0;
				  }
			  
				  h1,
				  h2,
				  h3,
				  h4,
				  h5 {
					Margin: 0;
					line-height: 120%;
					mso-line-height-rule: exactly;
					font-family: arial, 'helvetica neue', helvetica, sans-serif;
				  }
			  
				  p,
				  ul li,
				  ol li,
				  a {
					-webkit-text-size-adjust: none;
					-ms-text-size-adjust: none;
					mso-line-height-rule: exactly;
				  }
			  
				  .es-left {
					float: left;
				  }
			  
				  .es-right {
					float: right;
				  }
			  
				  .es-p5 {
					padding: 5px;
				  }
			  
				  .es-p5t {
					padding-top: 5px;
				  }
			  
				  .es-p5b {
					padding-bottom: 5px;
				  }
			  
				  .es-p5l {
					padding-left: 5px;
				  }
			  
				  .es-p5r {
					padding-right: 5px;
				  }
			  
				  .es-p10 {
					padding: 10px;
				  }
			  
				  .es-p10t {
					padding-top: 10px;
				  }
			  
				  .es-p10b {
					padding-bottom: 10px;
				  }
			  
				  .es-p10l {
					padding-left: 10px;
				  }
			  
				  .es-p10r {
					padding-right: 10px;
				  }
			  
				  .es-p15 {
					padding: 15px;
				  }
			  
				  .es-p15t {
					padding-top: 15px;
				  }
			  
				  .es-p15b {
					padding-bottom: 15px;
				  }
			  
				  .es-p15l {
					padding-left: 15px;
				  }
			  
				  .es-p15r {
					padding-right: 15px;
				  }
			  
				  .es-p20 {
					padding: 20px;
				  }
			  
				  .es-p20t {
					padding-top: 20px;
				  }
			  
				  .es-p20b {
					padding-bottom: 20px;
				  }
			  
				  .es-p20l {
					padding-left: 20px;
				  }
			  
				  .es-p20r {
					padding-right: 20px;
				  }
			  
				  .es-p25 {
					padding: 25px;
				  }
			  
				  .es-p25t {
					padding-top: 25px;
				  }
			  
				  .es-p25b {
					padding-bottom: 25px;
				  }
			  
				  .es-p25l {
					padding-left: 25px;
				  }
			  
				  .es-p25r {
					padding-right: 25px;
				  }
			  
				  .es-p30 {
					padding: 30px;
				  }
			  
				  .es-p30t {
					padding-top: 30px;
				  }
			  
				  .es-p30b {
					padding-bottom: 30px;
				  }
			  
				  .es-p30l {
					padding-left: 30px;
				  }
			  
				  .es-p30r {
					padding-right: 30px;
				  }
			  
				  .es-p35 {
					padding: 35px;
				  }
			  
				  .es-p35t {
					padding-top: 35px;
				  }
			  
				  .es-p35b {
					padding-bottom: 35px;
				  }
			  
				  .es-p35l {
					padding-left: 35px;
				  }
			  
				  .es-p35r {
					padding-right: 35px;
				  }
			  
				  .es-p40 {
					padding: 40px;
				  }
			  
				  .es-p40t {
					padding-top: 40px;
				  }
			  
				  .es-p40b {
					padding-bottom: 40px;
				  }
			  
				  .es-p40l {
					padding-left: 40px;
				  }
			  
				  .es-p40r {
					padding-right: 40px;
				  }
			  
				  .es-menu td {
					border: 0;
				  }
			  
				  .es-menu td a img {
					display: inline-block !important;
				  }
			  
				  /* END CONFIG STYLES */
				  a {
					font-family: arial, 'helvetica neue', helvetica, sans-serif;
					font-size: 14px;
					text-decoration: underline;
				  }
			  
				  h1 {
					font-size: 30px;
					font-style: normal;
					font-weight: normal;
					color: #333333;
				  }
			  
				  h1 a {
					font-size: 30px;
				  }
			  
				  h2 {
					font-size: 24px;
					font-style: normal;
					font-weight: normal;
					color: #333333;
				  }
			  
				  h2 a {
					font-size: 24px;
				  }
			  
				  h3 {
					font-size: 20px;
					font-style: normal;
					font-weight: normal;
					color: #333333;
				  }
			  
				  h3 a {
					font-size: 20px;
				  }
			  
				  p,
				  ul li,
				  ol li {
					font-size: 14px;
					font-family: arial, 'helvetica neue', helvetica, sans-serif;
					line-height: 150%;
				  }
			  
				  ul li,
				  ol li {
					Margin-bottom: 15px;
				  }
			  
				  .es-menu td a {
					text-decoration: none;
					display: block;
				  }
			  
				  .es-wrapper {
					width: 100%;
					height: 100%;
					background-image: ;
					background-repeat: repeat;
					background-position: center top;
				  }
			  
				  .es-wrapper-color {
					background-color: #f6f6f6;
				  }
			  
				  .es-content-body {
					background-color: #ffffff;
				  }
			  
				  .es-content-body p,
				  .es-content-body ul li,
				  .es-content-body ol li {
					color: #333333;
				  }
			  
				  .es-content-body a {
					color: #2cb543;
				  }
			  
				  .es-header {
					background-color: transparent;
					background-image: ;
					background-repeat: repeat;
					background-position: center top;
				  }
			  
				  .es-header-body {
					background-color: #ffffff;
				  }
			  
				  .es-header-body p,
				  .es-header-body ul li,
				  .es-header-body ol li {
					color: #333333;
					font-size: 14px;
				  }
			  
				  .es-header-body a {
					color: #1376c8;
					font-size: 14px;
				  }
			  
				  .es-footer {
					background-color: transparent;
					background-image: ;
					background-repeat: repeat;
					background-position: center top;
				  }
			  
				  .es-footer-body {
					background-color: #ffffff;
				  }
			  
				  .es-footer-body p,
				  .es-footer-body ul li,
				  .es-footer-body ol li {
					color: #333333;
					font-size: 14px;
				  }
			  
				  .es-footer-body a {
					color: #ffffff;
					font-size: 14px;
				  }
			  
				  .es-infoblock,
				  .es-infoblock p,
				  .es-infoblock ul li,
				  .es-infoblock ol li {
					line-height: 120%;
					font-size: 12px;
					color: #cccccc;
				  }
			  
				  .es-infoblock a {
					font-size: 12px;
					color: #cccccc;
				  }
			  
				  .es-button-border {
					border-style: solid solid solid solid;
					border-color: #2cb543 #2cb543 #2cb543 #2cb543;
					background: #2cb543;
					border-width: 0px 0px 2px 0px;
					display: inline-block;
					border-radius: 30px;
					width: auto;
				  }
			  
				  /* RESPONSIVE STYLES Please do not delete and edit CSS styles below. If you don't need responsive layout, please delete this section. */
				  @media only screen and (max-width: 600px) {
			  
					p,
					ul li,
					ol li,
					a {
					  font-size: 16px !important;
					  line-height: 150% !important;
					}
			  
					h1 {
					  font-size: 30px !important;
					  text-align: center;
					  line-height: 120%;
					}
			  
					h2 {
					  font-size: 26px !important;
					  text-align: center;
					  line-height: 120%;
					}
			  
					h3 {
					  font-size: 20px !important;
					  text-align: center;
					  line-height: 120%;
					}
			  
					h1 a {
					  font-size: 30px !important;
					}
			  
					h2 a {
					  font-size: 26px !important;
					}
			  
					h3 a {
					  font-size: 20px !important;
					}
			  
					.es-menu td a {
					  font-size: 16px !important;
					}
			  
					.es-header-body p,
					.es-header-body ul li,
					.es-header-body ol li,
					.es-header-body a {
					  font-size: 16px !important;
					}
			  
					.es-footer-body p,
					.es-footer-body ul li,
					.es-footer-body ol li,
					.es-footer-body a {
					  font-size: 16px !important;
					}
			  
					.es-infoblock p,
					.es-infoblock ul li,
					.es-infoblock ol li,
					.es-infoblock a {
					  font-size: 12px !important;
					}
			  
					*[class=\"gmail-fix\"] {
					  display: none !important;
					}
			  
					.es-m-txt-c,
					.es-m-txt-c h1,
					.es-m-txt-c h2,
					.es-m-txt-c h3 {
					  text-align: center !important;
					}
			  
					.es-m-txt-r,
					.es-m-txt-r h1,
					.es-m-txt-r h2,
					.es-m-txt-r h3 {
					  text-align: right !important;
					}
			  
					.es-m-txt-l,
					.es-m-txt-l h1,
					.es-m-txt-l h2,
					.es-m-txt-l h3 {
					  text-align: left !important;
					}
			  
					.es-m-txt-r img,
					.es-m-txt-c img,
					.es-m-txt-l img {
					  display: inline !important;
					}
			  
					.es-button-border {
					  display: block !important;
					}
			  
					.es-btn-fw {
					  border-width: 10px 0px !important;
					  text-align: center !important;
					}
			  
					.es-adaptive table,
					.es-btn-fw,
					.es-btn-fw-brdr,
					.es-left,
					.es-right {
					  width: 100% !important;
					}
			  
					.es-content table,
					.es-header table,
					.es-footer table,
					.es-content,
					.es-footer,
					.es-header {
					  width: 100% !important;
					  max-width: 600px !important;
					}
			  
					.es-adapt-td {
					  display: block !important;
					  width: 100% !important;
					}
			  
					.adapt-img {
					  width: 100% !important;
					  height: auto !important;
					}
			  
					.es-m-p0 {
					  padding: 0px !important;
					}
			  
					.es-m-p0r {
					  padding-right: 0px !important;
					}
			  
					.es-m-p0l {
					  padding-left: 0px !important;
					}
			  
					.es-m-p0t {
					  padding-top: 0px !important;
					}
			  
					.es-m-p0b {
					  padding-bottom: 0 !important;
					}
			  
					.es-m-p20b {
					  padding-bottom: 20px !important;
					}
			  
					.es-mobile-hidden,
					.es-hidden {
					  display: none !important;
					}
			  
					tr.es-desk-hidden,
					td.es-desk-hidden,
					table.es-desk-hidden {
					  width: auto !important;
					  overflow: visible !important;
					  float: none !important;
					  max-height: inherit !important;
					  line-height: inherit !important;
					}
			  
					tr.es-desk-hidden {
					  display: table-row !important;
					}
			  
					table.es-desk-hidden {
					  display: table !important;
					}
			  
					td.es-desk-menu-hidden {
					  display: table-cell !important;
					}
			  
					.es-menu td {
					  width: 1% !important;
					}
			  
					table.es-table-not-adapt,
					.esd-block-html table {
					  width: auto !important;
					}
			  
					table.es-social {
					  display: inline-block !important;
					}
			  
					table.es-social td {
					  display: inline-block !important;
					}
			  
					a.es-button,
					button.es-button {
					  font-size: 20px !important;
					  display: block !important;
					  border-width: 10px 0px 10px 0px !important;
					}
				  }
			  
				  /* END RESPONSIVE STYLES */
				  a.es-button,
				  button.es-button {
					border-style: solid;
					border-color: #31cb4b;
					border-width: 10px 20px 10px 20px;
					display: inline-block;
					background: #31cb4b;
					border-radius: 30px;
					font-size: 18px;
					font-family: arial, 'helvetica neue', helvetica, sans-serif;
					font-weight: normal;
					font-style: normal;
					line-height: 120%;
					color: #ffffff;
					text-decoration: none;
					width: auto;
					text-align: center;
				  }
			  
				  .es-p-default {
					padding-top: 20px;
					padding-right: 40px;
					padding-bottom: 20px;
					padding-left: 40px;
				  }
			  
				  .es-p-all-default {
					padding: 0px;
				  }
				</style>
			  </head>
			  
			  <body>
				<div class=\"es-wrapper-color\">
				  <table class=\"es-wrapper\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">
					<tbody>
					  <tr>
						<td class=\"esd-email-paddings\" valign=\"top\">
						  <table class=\"es-header esd-footer-popover\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">
							<tbody>
							  <tr>
								<td class=\"esd-stripe\" align=\"center\">
								  <table class=\"es-header-body\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\"
									align=\"center\">
									<tbody>
									  <tr>
										<td class=\"esd-structure es-p20t es-p20b es-p40r es-p40l\" align=\"left\">
										  <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
											<tbody>
											  <tr>
												<td width=\"520\" class=\"esd-container-frame\" align=\"center\" valign=\"top\">
												  <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
													<tbody>
													  <tr>
														<td align=\"center\" class=\"esd-block-image es-p30t\" style=\"font-size: 0px;\"><a
															target=\"_blank\" href=\"https://www.enest.store\"><img
															  src=\"https://owrnhh.stripocdn.email/content/guids/CABINET_37be19f93fd1833ef516462722e25083/images/56461611226659343.png\"
															  alt style=\"display: block;\" width=\"130\"></a></td>
													  </tr>
													</tbody>
												  </table>
												</td>
											  </tr>
											</tbody>
										  </table>
										</td>
									  </tr>
									  <tr>
										<td class=\"esd-structure es-p20t es-p20b es-p40r es-p40l\" align=\"left\">
										  <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
											<tbody>
											  <tr>
												<td width=\"520\" class=\"esd-container-frame\" align=\"center\" valign=\"top\">
												  <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
													<tbody>
													  <tr>
														<td align=\"center\" class=\"esd-block-spacer es-p20r es-p20l\"
														  style=\"font-size:0\">
														  <table border=\"0\" width=\"100%\" height=\"100%\" cellpadding=\"0\"
															cellspacing=\"0\">
															<tbody>
															  <tr>
																<td
																  style=\"border-bottom: 1px solid #cccccc; background:none; height:1px; width:100%; margin:0px 0px 0px 0px;\">
																</td>
															  </tr>
															</tbody>
														  </table>
														</td>
													  </tr>
													</tbody>
												  </table>
												</td>
											  </tr>
											</tbody>
										  </table>
										</td>
									  </tr>
									  <tr>
										<td class=\"esd-structure es-p20b es-p40r es-p40l\" align=\"left\">
										  <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
											<tbody>
											  <tr>
												<td width=\"520\" class=\"esd-container-frame\" align=\"center\" valign=\"top\">
												  <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
													<tbody>
													  <tr>
														<td align=\"left\" class=\"esd-block-text es-p40r es-p40l\"
														  esd-links-color=\"#212c95\">
														  <p style=\"line-height: 200%;\"><span style=\"font-size:16px;\">Hi
															  " . strtok($c['name'], " ") . ",</span><br>Your package has been delivered!<br>Please rate
															your delivery experience on the Enest App.<br><br>To manage other orders,
															open the <a target=\"_blank\" style=\"line-height: 200%; color: #212c95;\"
															  href=\"https://play.google.com/store/apps/details?id=com.enestcustomerapp\">Enest
															  App</a>.</p>
														</td>
													  </tr>
													  <tr>
														<td align=\"center\" class=\"esd-block-spacer es-p20\" style=\"font-size:0\">
														  <table border=\"0\" width=\"100%\" height=\"100%\" cellpadding=\"0\"
															cellspacing=\"0\">
															<tbody>
															  <tr>
																<td
																  style=\"border-bottom: 1px solid #cccccc; background:none; height:1px; width:100%; margin:0px 0px 0px 0px;\">
																</td>
															  </tr>
															</tbody>
														  </table>
														</td>
													  </tr>
													  <tr>
														<td align=\"left\" class=\"esd-block-text es-p40b es-p40r es-p40l\">
														  <p style=\"line-height: 200%;\"><span style=\"font-size:12px;\">Order
															  &nbsp;<span style=\"color:#0000FF;\">" . $checks['oid'] . ".</span></span><br><span
															  style=\"font-size: 9px; line-height: 200%;\">This email was sent from an
															  email address that can't receive emails. Please don't reply to this
															  email.</span></p>
														</td>
													  </tr>
													</tbody>
												  </table>
												</td>
											  </tr>
											</tbody>
										  </table>
										</td>
									  </tr>
									</tbody>
								  </table>
								</td>
							  </tr>
							</tbody>
						  </table>
						</td>
					  </tr>
					</tbody>
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
			$returnArr = array("ResponseCode" => "401", "Result" => "false", "ResponseMsg" => "Something Went wrong  try again !");
		}
	} else {
		$returnArr = array("ResponseCode" => "401", "Result" => "false", "ResponseMsg" => "Sorry this Order is Assigned to Other Rider Or Cancelled!");
	}
}
echo json_encode($returnArr);
mysqli_close($con);
