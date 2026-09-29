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


if (!isset($_GET['topicID'])) {
    echo json_encode([]);
    exit();
}

$topicID = intval($_GET['topicID']);

$query = "
    SELECT u.id, u.firstName, u.lastName
    FROM quiz q
    INNER JOIN user u ON q.educatorID = u.id
    WHERE q.topicID = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $topicID);
$stmt->execute();
$result = $stmt->get_result();

$educators = [];
while ($row = $result->fetch_assoc()) {
    $educators[] = [
        "id" => $row["id"],
        "name" => $row["firstName"] . " " . $row["lastName"]
    ];
}
header('Content-Type: application/json');

echo json_encode($educators);
?>
