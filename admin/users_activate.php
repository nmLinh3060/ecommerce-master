<?php
	include 'includes/session.php';

	if(isset($_POST['activate'])){
		$id = $_POST['id'];
		
		$conn = $pdo->open();

		// $stmt = $conn->prepare("SELECT status FROM users WHERE id=:id");
		// $stmt->execute(['id' => $id]);
		// $row = $stmt->fetch();
		// if ($row['status'] == 1) {
		// 	$_SESSION['error'] = 'User already activated';
		// } else {
			try{
				$stmt1 = $conn->prepare("UPDATE users SET status=:status WHERE id=:id");
				$stmt1->execute(['status'=>1, 'id'=>$id]);
				$_SESSION['success'] = 'User activated successfully';
			}
			catch(PDOException $e){
				$_SESSION['error'] = $e->getMessage();
			}
	
		// }	
		$pdo->close();

	}
	else{
		$_SESSION['error'] = 'Select user to activate first';
	}

	header('location: users.php');
?>
