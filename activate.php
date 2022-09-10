<?php include 'includes/session.php'; ?>
<?php

	if(!isset($_GET['code']) OR !isset($_GET['user'])){
		$_SESSION['error'] = '<center><h2><b>Code to activate account not found😐😐</b></h2></center>';
		header('location:login.php');         
	}
	else{
		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users WHERE activate_code=:code AND id=:id");
		$stmt->execute(['code'=>$_GET['code'], 'id'=>$_GET['user']]);
		$row = $stmt->fetch();

		if($row['numrows'] > 0){
			if($row['status']){
				$_SESSION['success'] = '<center><h2><b>Account already activated🥰</b></h2></center>';
				header('location:login.php');
			}
			else{
				try{
					$stmt = $conn->prepare("UPDATE users SET status=:status WHERE id=:id");
					$stmt->execute(['status'=>1, 'id'=>$row['id']]);

					$_SESSION['success'] = '<center><h3> Success 😁😁
											<br>Account activated - Email: <b>'.$row['email'].'</b></br></h3></center>
											';
					header('location:login.php');
					
				}
				catch(PDOException $e){
					$output .= '
						<div class="alert alert-danger">
			                <h4><i class="icon fa fa-warning"></i> Error!</h4>
			                '.$e->getMessage().'
			            </div>
			            <h4>You may <a href="signup.php">Signup</a> or back to <a href="index.php">Homepage</a>.</h4>
					';
				}

			}
			
		}
		else{
			$_SESSION['error'] = '<center><h4><b>Cannot activate account.
										<br>Wrong code😬😬</b></b></h4></center>';
			header('location:signup.php');
		}
		
		$pdo->close();
		
	}
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

	<?php include 'includes/navbar.php'; ?>
	 
	  <div class="content-wrapper">
	    <div class="container">

	      <!-- Main content -->
	      <section class="content">
	        <div class="row">
	        	<div class="col-sm-9">
	        		<?php echo $output; ?>
	        	</div>
	        	<div class="col-sm-3">
	        		<?php include 'includes/sidebar.php'; ?>
	        	</div>
	        </div>
	      </section>
	     
	    </div>
	  </div>
  
  	<?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
</body>
</html>