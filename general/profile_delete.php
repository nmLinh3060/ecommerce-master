<?php
    include 'includes/session.php';

    if(isset($_POST['delete'])){
        $id = $_POST['id'];
        
        $conn = $pdo->open();

        try{
            $stmt = $conn->prepare("DELETE FROM users WHERE id=:id");
            $stmt->execute(['id'=>$id]);
            $stmt = $conn->prepare("ALTER TABLE users DROP id");
            $stmt->execute();
            $stmt = $conn->prepare("ALTER TABLE users ADD id INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id)");
            $stmt->execute();
            
            $_SESSION['success'] = 'Your account deleted successfully';
        }
        catch(PDOException $e){
            $_SESSION['error'] = $e->getMessage();
        }

        $pdo->close();
    }
    header('location: logout.php');
?>