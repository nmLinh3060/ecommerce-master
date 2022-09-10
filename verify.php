<?php
	include 'includes/session.php';
	$conn = $pdo->open();

	if(isset($_POST['login'])){
		
		$email = $_POST['email'];
		$password = $_POST['password'];

		try{

			$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users WHERE email = :email");
			$stmt->execute(['email' => $email]);
			$row = $stmt->fetch();
			if($row['numrows'] > 0){
				if($row['status']){
					if(password_verify($password, $row['password'])){
						if($row['type']){
							$_SESSION['admin'] = $row['id'];
						}
						else{
							$_SESSION['user'] = $row['id'];
						}
					}
					else{
						$_SESSION['error'] = '<center><h2><b>Incorrect Password😡</b></h2></center>';
						header('location: login.php');
					}
				}
				else{
					$_SESSION['error'] = '<center><h2><b>Account not activated😐</b></h2></center>';
					header('location: signup.php');
				}
			}
			else{
				$_SESSION['error'] = '<center><h4><br>Email not found
										<br>Please register for an account😉</br></b></h4></center>';
				header('location: signup.php');
			}
		}
		catch(PDOException $e){
			echo "There is some problem in connection: " . $e -> getMessage();
		}

	}
	else{
		$_SESSION['error'] = '<center><h2><br>Input login credentials first</b></h2></center>';
		header('location: login.php');
	}

	$pdo->close();
	header('location: login.php');
?>