<?php
include 'db_connection.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userID']) || $_SESSION['userType'] !== 'educator') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}


if (isset($_POST['questionID']) && isset($_POST['quizID'])) {
    $questionID = intval($_POST['questionID']);
    $quizID = intval($_POST['quizID']);
    
    // Verify the question belongs to the current educator
    $verifyStmt = $conn->prepare("
        SELECT qq.questionFigureFileName 
        FROM QuizQuestion qq 
        JOIN Quiz q ON qq.quizID = q.id 
        WHERE qq.id = ? AND q.educatorID = ?
    ");
    $verifyStmt->bind_param("ii", $questionID, $_SESSION['userID']);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Question not found or access denied']);
        exit();
    }
    
    $question = $verifyResult->fetch_assoc();
    
    // Delete the question
    $deleteQuery = "DELETE FROM QuizQuestion WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $questionID);
    
    if ($stmt->execute()) {
        // Delete the image file if it exists
        if (!empty($question['questionFigureFileName']) && file_exists("uploads/" . $question['questionFigureFileName'])) {
            unlink("uploads/" . $question['questionFigureFileName']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Question deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
}

$conn->close();
?>