<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "QUIZ");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// only educator can perform reviews
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'educator') {
      $errorMsg = "not logged-in. Redirecting to Homepage...";
    
    // Show the error message and stop execution
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'System_Homepage.html?error= not logged-in';
            }, 3000); // wait 3 seconds before redirect
          </script>";
    exit();
}// end me 



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recID = (int)($_POST['recID'] ?? 0);
    $status = ($_POST['status'] ?? 'disapproved') === 'approved' ? 'approved' : 'disapproved';
    $comment = trim($_POST['comment'] ?? '');

    // Update RecommendedQuestion status and save educator comment
    $up = $conn->prepare("UPDATE `RecommendedQuestion` SET status = ?, comments = ? WHERE id = ?");
    $up->bind_param("ssi", $status, $comment, $recID);
    $up->execute();

    // If approved -> copy the recommended question into QuizQuestion
    if ($status === 'approved') {
        $sel = $conn->prepare("SELECT quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer FROM `RecommendedQuestion` WHERE id = ?");
        $sel->bind_param("i", $recID);
        $sel->execute();
        $row = $sel->get_result()->fetch_assoc();
        if ($row) {
            $iq = $conn->prepare("INSERT INTO `QuizQuestion` (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $iq->bind_param(
                "isssssss",
                $row['quizID'],
                $row['question'],
                $row['questionFigureFileName'],
                $row['answerA'],
                $row['answerB'],
                $row['answerC'],
                $row['answerD'],
                $row['correctAnswer']
            );
            $iq->execute();
        }
    }

    header("Location: educator_homepage.php");
    exit();
} else {
    header("Location: educator_homepage.php");
    exit();
}
?>

