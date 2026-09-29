<?php
include 'db_connection.php';

session_start();
if (!isset($_SESSION['userID']) || $_SESSION['userType'] !== 'educator') {
    header("Location: System_Homepage.html?error=not logged-in");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $questionID = intval($_POST['questionID']);
    $quizID = intval($_POST['quizID']);
    $questionText = $conn->real_escape_string($_POST['question']);
    $answerA = $conn->real_escape_string($_POST['answerA']);
    $answerB = $conn->real_escape_string($_POST['answerB']);
    $answerC = $conn->real_escape_string($_POST['answerC']);
    $answerD = $conn->real_escape_string($_POST['answerD']);
    $correctAnswer = $conn->real_escape_string($_POST['correctAnswer']);
    
    // Get current question data
    $currentQuery = "SELECT questionFigureFileName FROM QuizQuestion WHERE id = ?";
    $stmt = $conn->prepare($currentQuery);
    $stmt->bind_param("i", $questionID);
    $stmt->execute();
    $currentResult = $stmt->get_result();
    $currentQuestion = $currentResult->fetch_assoc();
    
    if (!$currentQuestion) {
        header("Location: edit.php?qid=$questionID&error=Question+not+found");
        exit();
    }
    
    $newImageFileName = $currentQuestion['questionFigureFileName'];
    $removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';
    
    // Handle image removal
    if ($removeImage && !empty($currentQuestion['questionFigureFileName'])) {
        if (file_exists("uploads/" . $currentQuestion['questionFigureFileName'])) {
            unlink("uploads/" . $currentQuestion['questionFigureFileName']);
        }
        $newImageFileName = "";
    }
    
    // Handle new image upload
    if (isset($_FILES['newImage']) && $_FILES['newImage']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['newImage']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            // Delete old image if exists
            if (!empty($currentQuestion['questionFigureFileName']) && file_exists("uploads/" . $currentQuestion['questionFigureFileName'])) {
                unlink("uploads/" . $currentQuestion['questionFigureFileName']);
            }
            
            $ext = pathinfo($_FILES['newImage']['name'], PATHINFO_EXTENSION);
            $newImageFileName = "question_" . time() . "_" . uniqid() . "." . $ext;
            $targetDir = "uploads/";
            
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            if (!move_uploaded_file($_FILES['newImage']['tmp_name'], $targetDir . $newImageFileName)) {
                header("Location: edit.php?qid=$questionID&error=Failed+to+upload+new+image");
                exit();
            }
        } else {
            header("Location: edit.php?qid=$questionID&error=Invalid+image+format.+Allowed:+JPEG,+PNG,+GIF,+WEBP");
            exit();
        }
    }
    
    // Verify the question belongs to the current educator
    $verifyStmt = $conn->prepare("SELECT q.id FROM Quiz q WHERE q.id = ? AND q.educatorID = ?");
    $verifyStmt->bind_param("ii", $quizID, $_SESSION['userID']);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows === 0) {
        header("Location: edit.php?qid=$questionID&error=Access+denied");
        exit();
    }
    
    // Update question in database
    $updateQuery = "UPDATE QuizQuestion SET question = ?, questionFigureFileName = ?, answerA = ?, answerB = ?, answerC = ?, answerD = ?, correctAnswer = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssssssi", $questionText, $newImageFileName, $answerA, $answerB, $answerC, $answerD, $correctAnswer, $questionID);
    
    if ($stmt->execute()) {
        header("Location: quiz.php?quizID=$quizID&success=Question+updated+successfully");
        exit();
    } else {
        header("Location: edit.php?qid=$questionID&error=Error+updating+question");
        exit();
    }
} else {
    header("Location: educator_dashboard.php");
    exit();
}

$conn->close();
?>