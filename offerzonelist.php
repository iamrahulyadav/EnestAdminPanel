<?php
require 'include/header.php';
?>

<body data-col="2-columns" class=" 2-columns ">
    <div class="layer"></div>
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
                                        <h4 class="card-title">Offer Zone</h4>
                                        NOTE : Before deleting any Offer Subcategory, please make sure all the Offer Products in that Subcategory are deleted first.
                                    </div>
                                    <div class="card-body collapse show">
                                        <div class="card-block card-dashboard">
                                            <?php
                                            $sel = $con->query("select * from subcategory where cat_id=16");
                                            $i = 0;
                                            while ($row = $sel->fetch_assoc()) {
                                                $i = $i + 1;
                                            ?>
                                            <div class="card-header">
                                                <h5 class="card-title"><?php echo $i . ") " . $row['name'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; ?>
                                                    <img class="media-object round-media"
                                                        src="<?php echo $row['img']; ?>" alt="Generic placeholder image"
                                                        style="height: 75px;">
                                                    &nbsp;&nbsp;
                                                    <a class="primary"
                                                        href="subcategory.php?edit=<?php echo $row['id']; ?>"
                                                        data-original-title="" title="">
                                                        <i class="ft-edit font-medium-3"></i>
                                                    </a>
                                                    &nbsp;&nbsp;
                                                    <a class="danger" data-original-title=""
                                                        href="?subdele=<?php echo $row['id']; ?>" title="">
                                                        <i class="ft-trash font-medium-3"></i>
                                                    </a>
                                                </h5>
                                            </div>
                                            <table class="table table-striped table-bordered dom-jQuery-events">
                                                <thead>
                                                    <tr>
                                                        <th>Sr No.</th>
                                                        <th>Product Name</th>
                                                        <th>Product Image</th>
                                                        <th>Product Related Image</th>
                                                        <th>Seller Name</th>
                                                        <th>Small Description</th>
                                                        <th>Product Range</th>
                                                        <th>Product Price</th>
                                                        <th>In Stock ?</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $jj = $con->query("select * from product where sid=" . $row['id'] . "");
                                                        $j = 0;
                                                        while ($rkl = $jj->fetch_assoc()) {
                                                            $j = $j + 1;
                                                        ?>
                                                    <tr>
                                                        <td><?php echo $j; ?></td>
                                                        <td><?php echo $rkl['pname']; ?></td>
                                                        <td><img src="<?php echo $rkl['pimg']; ?>" width="100"
                                                                height="100" /></td>
                                                        <td><?php $sb = explode(',', $rkl['prel']);


                                                                    foreach ($sb as $bb) {
                                                                        if ($bb == '') {
                                                                        } else {
                                                                    ?>
                                                            <img src="<?php echo $bb; ?>" width="100" height="100" />
                                                            <?php
                                                                        }
                                                                    }
                                                                    ?>
                                                        </td>
                                                        <td><?php echo $rkl['sname']; ?></td>
                                                        <td><?php echo substr($rkl['psdesc'], 0, 100) . '....'; ?>
                                                        </td>
                                                        <td><?php echo $rkl['pgms']; ?></td>
                                                        <td><?php echo $rkl['pprice']; ?></td>
                                                        <td><?php if ($rkl['stock'] == 1) {
                                                                        echo 'Yes';
                                                                    } else {
                                                                        echo 'No';
                                                                    } ?></td>
                                                        <td><?php if ($rkl['status'] == 1) {
                                                                        echo 'Published';
                                                                    } else {
                                                                        echo 'Unpublished';
                                                                    } ?></td>
                                                        <td>
                                                            <a class="primary"
                                                                href="product.php?edit=<?php echo $rkl['id']; ?>"
                                                                data-original-title="" title="">
                                                                <i class="ft-edit font-medium-3"></i>
                                                            </a>

                                                            <a class="danger" data-original-title=""
                                                                href="?dele=<?php echo $rkl['id']; ?>" title="">
                                                                <i class="ft-trash font-medium-3"></i>
                                                            </a>

                                                        </td>

                                                    </tr>
                                                    <?php } ?>
                                                </tbody>

                                            </table>
                                            <?php } ?>
                                            </tbody>

                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php
                    if (isset($_GET['subdele'])) {
                        $con->query("delete from subcategory  where id=" . $_GET['subdele'] . "");
                    ?>
                    <script type="text/javascript">
                    $(document).ready(function() {
                        toastr.options.timeOut = 4500; // 1.5s

                        toastr.error('Selected Offer subcategory Deleted Successfully.');
                        setTimeout(function() {
                            window.location.href = "offerzonelist.php";
                        }, 1500);
                    });
                    </script>
                    <?php
                    }
                    ?>
                    <?php
                    if (isset($_GET['dele'])) {
                        $con->query("delete from product  where id=" . $_GET['dele'] . "");
                    ?>
                    <script type="text/javascript">
                    $(document).ready(function() {
                        toastr.options.timeOut = 4500; // 1.5s

                        toastr.error('Selected Offer Product Deleted Successfully.');
                        setTimeout(function() {
                            window.location.href = "offerzonelist.php";
                        }, 1500);
                    });
                    </script>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    require 'include/js.php';
    ?>
    <style>
    table {
        font-size: 13px;
    }
    </style>
    <!-- END PAGE LEVEL JS-->
</body>

</html>