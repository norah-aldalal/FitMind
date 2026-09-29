<?php
include 'db_connection.php';

session_start();

// only educator
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'educator') {
    echo "false";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $recID = (int)($_POST['recID'] ?? 0);
    $status = ($_POST['status'] ?? 'disapproved') === 'approved' ? 'approved' : 'disapproved';
    $comment = trim($_POST['comment'] ?? '');

    // Update recommended question
    $up = $conn->prepare("UPDATE recommendedquestion SET status = ?, comments = ? WHERE id = ?");
    $up->bind_param("ssi", $status, $comment, $recID);
    $up->execute();

    // If approved → copy into QuizQuestion
    if ($status === 'approved') {
        $sel = $conn->prepare("SELECT quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer 
                               FROM recommendedquestion WHERE id = ?");
        $sel->bind_param("i", $recID);
        $sel->execute();
        $row = $sel->get_result()->fetch_assoc();

        if ($row) {
            $iq = $conn->prepare("INSERT INTO quizquestion 
                (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $iq->bind_param(
                "isssssss",
                $row['quizID'], $row['question'], $row['questionFigureFileName'],
                $row['answerA'], $row['answerB'], $row['answerC'], $row['answerD'], $row['correctAnswer']
            );
            $iq->execute();
        }
    }

    echo "true"; 
    exit();
}

echo "false";
exit();
?>
