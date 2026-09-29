<?php
include 'db_connection.php';

session_start();

if (!isset($_SESSION['userID']) || $_SESSION['userType'] !== 'learner') {
     $errorMsg = "You are not logged in. Redirecting to Homepage...";
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'System_Homepage.html?error=not_logged_in';
            }, 3000);
          </script>";
    exit();
}


$userID = intval($_SESSION['userID']);

$topicID = intval($_POST['topic']);
$educatorID = intval($_POST['educator']);
$question = trim($_POST['questionText']);
$answerA = trim($_POST['optionA']);
$answerB = trim($_POST['optionB']);
$answerC = trim($_POST['optionC']);
$answerD = trim($_POST['optionD']);
$correctAnswer = strtoupper(trim($_POST['correctAnswer']));

if ($topicID <= 0 || $educatorID <= 0 || $question === '' || 
    $answerA === '' || $answerB === '' || $answerC === '' || 
    $answerD === '' || !in_array($correctAnswer, ['A','B','C','D'])) {

    echo "<script>alert('Please complete the form correctly.'); window.history.back();</script>";
    exit();
}

$fileName = '';
if (isset($_FILES['questionFile']) && is_uploaded_file($_FILES['questionFile']['tmp_name'])) {

    $file = $_FILES['questionFile'];
    $allowedExt = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

 
    if (!in_array($ext, $allowedExt)) {
        echo "<script>alert('Only JPG, PNG, GIF, and WEBP formats are allowed.'); window.history.back();</script>";
        exit();
    }

    
    if ($file['size'] > 2 * 1024 * 1024) {
        echo "<script>alert('Image must be less than 2MB.'); window.history.back();</script>";
        exit();
    }

   
    $fileName = uniqid("QIMG_", true) . "." . $ext;
    move_uploaded_file($file['tmp_name'], "uploads/" . $fileName);
}


$stmt = $conn->prepare("SELECT id FROM Quiz WHERE topicID = ? AND educatorID = ? LIMIT 1");
$stmt->bind_param("ii", $topicID, $educatorID);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$quiz) {
    echo "<script>alert('No quiz found for the selected topic and educator.'); window.history.back();</script>";
    exit();
}

$quizID = intval($quiz['id']);

$insert = $conn->prepare("
INSERT INTO RecommendedQuestion
(quizID, learnerID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer, status, comments)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', '')
");
$insert->bind_param("iisssssss", $quizID, $userID, $question, $fileName, $answerA, $answerB, $answerC, $answerD, $correctAnswer);

if ($insert->execute()) {
    echo "<script>alert('Your recommendation has been submitted!'); window.location.href='learner_homepage.php';</script>";
} else {
    echo "<script>alert('Submission failed. Please try again.'); window.history.back();</script>";
}

$insert->close();
$conn->close();
?>
