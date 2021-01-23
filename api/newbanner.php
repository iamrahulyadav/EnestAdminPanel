<?php
require 'db.php';
$sel = $con->query("select * from new_banner");
while ($row = $sel->fetch_assoc()) {
    $myarray[] = $row;
}
$returnArr = array("data" => $myarray, "ResponseCode" => "200", "Result" => "true", "ResponseMsg" => "Banner List Founded!");
echo json_encode($returnArr);
?>