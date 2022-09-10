<?php

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	require 'plugins/PHPMailer/src/Exception.php';
	require 'plugins/PHPMailer/src/PHPMailer.php';
	require 'plugins/PHPMailer/src/SMTP.php';
	
	include 'includes/session.php';

	if(isset($_POST['signup'])){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$repassword = $_POST['repassword'];

		$_SESSION['firstname'] = $firstname;
		$_SESSION['lastname'] = $lastname;
		$_SESSION['email'] = $email;

		$uppercase = preg_match('@[A-Z]@', $password);
		$lowercase = preg_match('@[a-z]@', $password);
		$number    = preg_match('@[0-9]@', $password);

		$output='';

		if(isset($_POST['g-recaptcha-response'])){
			//require('recaptcha/src/autoload.php');		
			$recaptcha = '6LdGuKQhAAAAAG74dsPAQKh-r23pGMq6kJn0tS_U';
			$response = $_POST['g-recaptcha-response'];
			$remoteip = $_SERVER['REMOTE_ADDR'];
			$url = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha&response=$response&remoteip=$remoteip";
			$resp = file_get_contents($url);
			
			
			$data = json_decode($resp);

			if (!$data->success){
		  		$_SESSION['error'] = '<center><b>Oops you are a robot 😡. Please answer recaptcha</b></center>';
		  		header('location: signup.php');	
		  		exit();	
		  	} else {
		  		$_SESSION['captcha'] = time() + (10*60);
				if ($password == $repassword){
					if (!$uppercase || !$lowercase || !$number || strlen($password) < 8) {
						$_SESSION['error'] = '<center><b>Passwords must be contain minimum of 8 characters, number
											uppercase and lowercase character</b><center>';
						header('location: signup.php');	
					} else {
						$conn = $pdo->open();

						$stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM users WHERE email=:email");
						$stmt->execute(['email'=>$email]);
						$row = $stmt->fetch();
						if($row['numrows'] > 0) {
							$_SESSION['error'] = '<center><h3><b>Email already taken</b></h3><center>';	
							header('location: signup.php');
						} else {
							$now = date('Y-m-d');
							$password = password_hash($password, PASSWORD_DEFAULT);
			
							//generate code
							$set='123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
							$code=substr(str_shuffle($set), 0, 12);
			
							try{
								$stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, activate_code, created_on) VALUES (:email, :password, :firstname, :lastname, :code, :now)");
								$stmt->execute(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'code'=>$code, 'now'=>$now]);				
								$userid = $conn->lastInsertId();
								$passd_hash = hash('ripemd160', $password);										
								$message = "
									<h2>Thank you for Registering.</h2>
									<p>Your Account:</p>
									<p>Email: ".$email."</p>
									<p>Password: $passd_hash</p>
									<p><h3><b>Please click the link below to activate your account.</b></h3></p>
									<a href='http://localhost:8080/ecommerce/activate.php?code=".$code."&user=".$userid."'><h3><b>Activate Account</b></h3></a>";
			
								//Load phpmailer
								require 'vendor/autoload.php';
			
								$mail = new PHPMailer();                             
								try {
									//Server settings
									$mail -> SMTPDebug = 0;
									$mail->isSMTP(); 
									$mail-> CharSet = 'utf-8';
									$mail -> Mailer = 'smtp';                          
									$mail->Host = 'smtp.gmail.com';                      
									$mail->SMTPAuth = true;                               
									$mail->Username = 'manhmanhln@gmail.com';     
									$mail->Password = 'nhtdszpeslsbbjdf';                    
									$mail->SMTPOptions = array(
										'tsl' => array(
										'verify_peer' => false,
										'verify_peer_name' => false,
										'allow_self_signed' => true
										)
									);                         
									$mail->SMTPSecure = 'tsl';                           
									$mail->Port = 587;                                   
			
									$mail->setFrom('no-reply@mlgearsaling.com', 'MLGear');
									
									//Recipients
									$mail->addAddress($email);              
									$mail->addReplyTo('no-reply@mlgearsaling.com', 'MLGear');
								
									//Content
									$mail->isHTML(true);                                  
									$mail->Subject = 'MLGear Site Sign Up';
									// $mailContent = "<h1>Send HTML Email using SMTP in PHP</h1>
									// 				<p>This is a test email I’m sending using SMTP mail server with PHPMailer.</p>";
									// $mail->Body = $mailContent;
									$mail->Body    = $message;
			
									$mail->send();
			
									unset($_SESSION['firstname']);
									unset($_SESSION['lastname']);
									unset($_SESSION['email']);
									
									$output .= '
										<div class="alert alert-success">
											<h1><i class="icon fa fa-check"></i><br> Success!</b></h1>
											<h2><b>Account created. Check your email to activate.</b></h2>
										</div>
										<h1>You may <a href="login.php">Login</a> or back to <a href="index.php">Homepage</a>.</h1>
									';
									//$_SESSION['success'] = 'Account created. Check your email to activate.';
									//header('location: login.php');
			
								} catch (Exception $e) {
									$_SESSION['error'] = 'Message could not be sent. Mailer Error: '.$mail->ErrorInfo;
									header('location: signup.php');
								}
							} catch(PDOException $e){
								$_SESSION['error'] = $e->getMessage();
								header('location: register.php');
							}
							$pdo->close();						
						}
						
					}
				} else {
					$_SESSION['error'] = '<center><h3><b>Passwords did not match</b></h3></center>';
					header('location: signup.php');
				}
			}
		}
	} else {
		$_SESSION['error'] = 'Fill up signup form first';
		header('location: signup.php');
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
