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

if (!isset($_POST['quizID']) || !is_numeric($_POST['quizID'])) {
    header("Location: learner_homepage.php");
    exit();
}
$quizID = intval($_POST['quizID']);
$rating = intval($_POST['rating']);
$comments = trim($_POST['comments']);

$stmt = $conn->prepare("INSERT INTO QuizFeedback (quizID, rating, comments, date) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $quizID, $rating, $comments);

if ($stmt->execute()) {
    echo "<script>alert('Thank you for your feedback!'); window.location.href='learner_homepage.php';</script>";
} else {
    echo "<script>alert('Error submitting feedback. Please try again.'); window.location.href='learner_homepage.php';</script>";
}

$stmt->close();
$conn->close();
?>
