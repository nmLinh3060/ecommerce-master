<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$address = $_POST['address'];
		$contact = $_POST['contact'];

		$uppercase = preg_match('@[A-Z]@', $password);
		$lowercase = preg_match('@[a-z]@', $password);
		$number    = preg_match('@[0-9]@', $password);

		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users WHERE email=:email");
		$stmt->execute(['email'=>$email]);
		$row = $stmt->fetch();

		if($row['numrows'] > 0){
			$_SESSION['error'] = 'Email already taken';
		}
		else{
			if (!$uppercase || !$lowercase || !$number || strlen($password) < 8) {
				$_SESSION['error'] = 'Passwords must be contain minimum of 8 characters, number
									uppercase and lowercase character';
				//header('location: includes/users_modal.php');	
			} else {
				$password = password_hash($password, PASSWORD_DEFAULT);
				$filename = $_FILES['photo']['name'];
				$now = date('Y-m-d');
				if(!empty($filename)){
					move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);	
				}
				try{
					$stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, address, contact_info, photo, status, created_on) VALUES (:email, :password, :firstname, :lastname, :address, :contact, :photo, :status, :created_on)");
					$stmt->execute(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'address'=>$address, 'contact'=>$contact, 'photo'=>$filename, 'status'=>1, 'created_on'=>$now]);
					$_SESSION['success'] = 'User added successfully';

				}
				catch(PDOException $e){
					$_SESSION['error'] = $e->getMessage();
				}
			}
		}
		$pdo->close();
	} else{
		$_SESSION['error'] = 'Fill up user form first';
	}

	header('location: users.php');

?>