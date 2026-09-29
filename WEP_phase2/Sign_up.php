<?php
include 'db_connection.php';

    
?>

<?php

session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName  = mysqli_real_escape_string($conn, $_POST['lastName']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $password  = $_POST['password'];
    $userType  = $_POST['userType'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM User WHERE emailAddress=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

   
    if ($stmt->num_rows > 0) {
    $errorMsg = "Email already exists. Redirecting to Sign Up...";
    
    
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'Sign_up.html?error=Email already exists';
            }, 3000); // wait 3 seconds before redirect
          </script>";
    exit(); 
}




    // Handle image
    $photoFileName = "person.png";
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === 0) {
        $ext = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid("user_", true) . "." . $ext;
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetDir . $uniqueName);
        $photoFileName = $uniqueName;
    }
    
    

    // !!!2!!! Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // !!!3!!! Insert user
    $insert = $conn->prepare("INSERT INTO User (firstName,lastName,emailAddress,password,photoFileName,userType) VALUES (?,?,?,?,?,?)");
    $insert->bind_param("ssssss", $firstName, $lastName, $email, $hashedPassword, $photoFileName, $userType);
    $insert->execute();
    $userID = $insert->insert_id;

    // Store session
    $_SESSION['userID'] = $userID;
    $_SESSION['userType'] = $userType;
    
    

    // If educator → insert quizzes for each topic
    if ($userType === "educator" && isset($_POST['topics'])) {
        foreach ($_POST['topics'] as $topicID) {
            $q = $conn->prepare("INSERT INTO Quiz (educatorID, topicID) VALUES (?,?)");
            $q->bind_param("ii", $userID, $topicID);
            $q->execute();
        }
    }

    // Redirect
    if ($userType === "learner") {
        header("Location: learner_homepage.php");
    } else {
        header("Location: educator_homepage.php");
    }
    exit();
}
?>




