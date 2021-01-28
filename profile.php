<?php
require 'include/header.php';
$getkey = $con->query("select * from admin")->fetch_assoc();
?>
<body data-col="2-columns" class=" 2-columns ">
	<div class="layer"></div>
	<!-- ////////////////////////////////////////////////////////////////////////////-->
	<div class="wrapper">
		<?php include('main.php'); ?>
		<!-- Navbar (Header) Ends-->

		<div class="main-panel">
			<div class="main-content">
				<div class="content-wrapper">
					<!--Statistics cards Starts-->

					<div class="row">
						<div class="col-md-12">
							<div class="card">
								<div class="card-header">
									<h4 class="card-title" id="basic-layout-form">Update Profile</h4>

								</div>
								<div class="card-body">
									<div class="px-3">
										<form class="form" method="post" enctype="multipart/form-data">
											<div class="form-body">

												<div class="form-group">
													<label for="cname">Username</label>
													<input type="text" class="form-control" placeholder="Enter Username" name="email" value="<?php echo $getkey['username']; ?>" required>
												</div>

												<div class="form-group">
													<label for="cname">Password</label>
													<input type="text" class="form-control" placeholder="Enter Password" name="password" value="<?php echo $getkey['password']; ?>" required>
												</div>

											</div>

											<div class="form-actions">

												<button type="submit" name="sub_cat" class="btn btn-raised btn-raised btn-primary">
													<i class="fa fa-check-square-o"></i> Update Profile
												</button>
											</div>

											<?php
											if (isset($_POST['sub_cat'])) {
												$password = $_POST['password'];
												$email = $_POST['email'];
												$con->query("update admin set username='" . $email . "',password='" . $password . "' where id=1");
											?>

												<script type="text/javascript">
													$(document).ready(function() {
														toastr.options.timeOut = 4500; // 1.5s

														toastr.info('Profile Updated Successfully!!!');

														setTimeout(function() {
															window.location.href = "profile.php";
														}, 1500);

													});
												</script>
											<?php
											}
											?>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	require 'include/js.php';
	?>
</body>
</html>