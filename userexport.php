<?php
require 'include/header.php';
$getkey = $con->query("select * from setting")->fetch_assoc();
define('ONE_KEY', $getkey['one_key']);
define('ONE_HASH', $getkey['one_hash']);
define('r_key', $getkey['r_key']);
define('r_hash', $getkey['r_hash']);
?>

<body data-col="2-columns" class=" 2-columns ">
    <div class="layer"></div>
    <!-- ////////////////////////////////////////////////////////////////////////////-->
    <div class="wrapper">



        <?php include('main.php'); ?>


        <div class="main-panel">
            <div class="main-content">
                <div class="content-wrapper">
                    <!--Statistics cards Starts-->

                    <section id="dom">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">

                                    <div class="card-header">
                                        <h4 class="card-title">Export Customer Details</h4>
                                    </div>
                                    <div class="card-body collapse show">
                                        <div class="card-block card-dashboard">
                                            <div>
                                                <form method="post">
                                                    <div class="form-body row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <input type="submit" name="sub_filter" class="btn btn-primary" value="Export to Excel" />

                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <?php if (isset($_POST["sub_filter"])) {
                                            ?>
                                                <table class="table table-striped" id="example">
                                                    <thead>
                                                        <tr>
                                                            <th>Sr No.</th>
                                                            <th>Customer ID</th>
                                                            <th>Customer Name</th>
                                                            <th>Customer Email</th>
                                                            <th>Customer Mobile</th>
                                                            <th>Password</th>
                                                            <th>Wallet Balance</th>
                                                            <th>Customer Address</th>
                                                            <th>Total Orders Received</th>
                                                            <th>Total Purchase Amount (in ₹)</th>
                                                            <th>Account Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $sel = $con->query("SELECT user.id, user.name, user.email, user.ccode, user.mobile, user.password, user.wallet, user.status FROM user order by user.id asc");
                                                        $i = 0;
                                                        while ($row = $sel->fetch_assoc()) {
                                                            $i = $i + 1;
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $i; ?></td>
                                                                <td><?php echo $row['id']; ?></td>
                                                                <td><?php echo $row['name']; ?></td>
                                                                <td><a href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></a></td>
                                                                <td><a href="tel:<?php echo $row['ccode'] . $row['mobile']; ?>"><?php echo $row['ccode'] . $row['mobile']; ?></a></td>
                                                                <td><?php echo $row['password']; ?></td>
                                                                <td><?php echo $row['wallet']; ?></td>
                                                                <td>
                                                                    <?php
                                                                    $qry = $con->query("SELECT address.hno, address.society, address.area, address.landmark, address.pincode FROM  address where address.uid=" . $row['id']);
                                                                    $j = 0;
                                                                    while ($rkl = $qry->fetch_assoc()) {
                                                                        $j = $j + 1;
                                                                        echo "Address No." . $j . ": " . $rkl['hno'] . ",\n" . $rkl['society'] . ",\n" . $rkl['area'] . ",\nNear " . $rkl['landmark'] . ",\nPincode: " . $rkl['pincode'] . "\n";
                                                                    }
                                                                    ?>

                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    $qry = $con->query("SELECT COUNT(id) as total FROM orders where uid=" . $row['id'])->fetch_assoc();
                                                                    echo $qry['total'];
                                                                    ?>
                                                                </td>
                                                                <td style="min-width:100px;">
                                                                    <?php
                                                                    $qry = $con->query("SELECT SUM(total) as total FROM orders where uid=" . $row['id'] . " and status = 'completed'")->fetch_assoc();
                                                                    if ($qry['total'] != null)
                                                                        echo number_format($qry['total'], 2, '.', '');
                                                                    else
                                                                        echo "0";
                                                                    ?>
                                                                </td>
                                                                <td><?php
                                                                    if ($row['status'] == 1) echo "Active";
                                                                    else echo "Deactive" ?></td>
                                                            </tr>
                                                        <?php  } ?>
                                                    </tbody>

                                                </table>
                                            <?php } ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </section>




                </div>
            </div>



        </div>
    </div>

    <?php require 'include/js.php'; ?>

</body>



<script>
    $('#example').DataTable({
        dom: 'Bfrtip',
        buttons: [

            'excelHtml5'

        ]
    });
</script>

<style>
    #example_wrapper {
        overflow: auto;
    }

    td p {
        /* border-bottom: 1px solid #dee2e6;*/
        /* padding: 0% !important; */
        margin: 0px;
        /* font-size:11px;*/
    }

    td.manage_td {
        padding: 0% !important;
    }

    table {
        font-size: 12px;
    }
</style>

</html>