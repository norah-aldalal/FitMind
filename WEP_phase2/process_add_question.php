<?php
include 'db_connection.php';

session_start();
if (!isset($_SESSION['userID']) || $_SESSION['userType'] !== 'educator') {
    header("Location: System_Homepage.html?error=not logged-in");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quizID = intval($_POST['quizID']);
    $question = $conn->real_escape_string($_POST['question']);
    $answerA = $conn->real_escape_string($_POST['answerA']);
    $answerB = $conn->real_escape_string($_POST['answerB']);
    $answerC = $conn->real_escape_string($_POST['answerC']);
    $answerD = $conn->real_escape_string($_POST['answerD']);
    $correctAnswer = $conn->real_escape_string($_POST['correctAnswer']);
    
    $imageFileName = "";
    
    // Handle image upload
    if (isset($_FILES['questionImage']) && $_FILES['questionImage']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['questionImage']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $ext = pathinfo($_FILES['questionImage']['name'], PATHINFO_EXTENSION);
            $imageFileName = "question_" . time() . "_" . uniqid() . "." . $ext;
            $targetDir = "uploads/";
            
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            if (!move_uploaded_file($_FILES['questionImage']['tmp_name'], $targetDir . $imageFileName)) {
                header("Location: add.php?quizID=$quizID&error=Failed+to+upload+image");
                exit();
            }
        } else {
            header("Location: add.php?quizID=$quizID&error=Invalid+image+format.+Allowed:+JPEG,+PNG,+GIF,+WEBP");
            exit();
        }
    }
    
    // Verify quiz belongs to educator
    $verifyStmt = $conn->prepare("SELECT id FROM Quiz WHERE id = ? AND educatorID = ?");
    $verifyStmt->bind_param("ii", $quizID, $_SESSION['userID']);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows === 0) {
        header("Location: add.php?quizID=$quizID&error=Quiz+not+found+or+access+denied");
        exit();
    }
    
    // Insert question into database
    $insertQuery = "INSERT INTO QuizQuestion (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("isssssss", $quizID, $question, $imageFileName, $answerA, $answerB, $answerC, $answerD, $correctAnswer);
    
    if ($stmt->execute()) {
        header("Location: quiz.php?quizID=$quizID&success=Question+added+successfully");
        exit();
    } else {
        header("Location: add.php?quizID=$quizID&error=Error+adding+question");
        exit();
    }
} else {
    header("Location: educator_dashboard.php");
    exit();
}

$conn->close();
?>