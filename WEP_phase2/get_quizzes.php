<?php
include 'db_connection.php';

header('Content-Type: application/json');

$topicId = isset($_GET['topicID']) ? intval($_GET['topicID']) : 0;

if ($topicId === 0) {
    $sql = "SELECT Quiz.id, Topic.topicName, User.firstName, User.lastName, User.photoFileName,
            (SELECT COUNT(*) FROM QuizQuestion WHERE QuizQuestion.quizID = Quiz.id) AS questionCount
            FROM Quiz
            JOIN Topic ON Quiz.topicID = Topic.id
            JOIN User ON Quiz.educatorID = User.id";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT Quiz.id, Topic.topicName, User.firstName, User.lastName, User.photoFileName,
            (SELECT COUNT(*) FROM QuizQuestion WHERE QuizQuestion.quizID = Quiz.id) AS questionCount
            FROM Quiz
            JOIN Topic ON Quiz.topicID = Topic.id
            JOIN User ON Quiz.educatorID = User.id
            WHERE Quiz.topicID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $topicId);
}

$stmt->execute();
$result = $stmt->get_result();

$quizzes = [];
while ($row = $result->fetch_assoc()) {
    $quizzes[] = $row;
}

echo json_encode($quizzes);
$stmt->close();
$conn->close();
?>

