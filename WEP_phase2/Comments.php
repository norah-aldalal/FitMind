<?php
include 'db_connection.php';

session_start();

// Check that the user is an educator
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'educator') {
     $errorMsg = "You are not logged in. Redirecting to homepage...";
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'System_Homepage.html';
            }, 3000);
          </script>";
    exit();
}


// Retrieve quizID from request
$quizID = $_GET['quizID'] ?? null;
if (!$quizID) {
    header("Location: educator_homepage.php?error=missingQuiz");
    exit();
}

// Get topic name
$stmt = $conn->prepare("
    SELECT Topic.topicName 
    FROM Quiz 
    JOIN Topic ON Quiz.topicID = Topic.id 
    WHERE Quiz.id = ?
");
$stmt->bind_param("i", $quizID);
$stmt->execute();
$result = $stmt->get_result();



if ($result->num_rows === 0) {
    die("<h2 style='text-align:center;color:red;'>Quiz not found!</h2>");
}
$quiz = $result->fetch_assoc();
$topicName = htmlspecialchars($quiz['topicName']);

// Retrieve feedbacks for this quiz
$stmt = $conn->prepare("
    SELECT comments, rating, date
    FROM QuizFeedback
    WHERE quizID = ?
    ORDER BY date DESC
");
$stmt->bind_param("i", $quizID);
$stmt->execute();
$feedbacks = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Quiz Comments</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f0fdf4;
      color: #14532d;
    }
    .quiz-info-card {
      border: 1.5px solid #c4f0d2;
      padding: 20px;
      border-radius: 10px;
      margin: 20px auto;
      text-align: center;
      max-width: 700px;
    }
    .quiz-info-card h2 { color: #14532d; font-size: 1.6rem; margin-bottom: 10px; }
    .quiz-info-card .subtitle { font-size: 1rem; color: #256f42; margin-bottom: 15px; }
    .alert {
      background-color: #dcfce7; color: #14532d;
      padding: 10px; border-radius: 8px;
      font-size: 0.95rem; font-weight: bold;
      border-left: 4px solid #22c55e;
    }
    .container { max-width: 800px; margin: 2rem auto; padding: 1rem; }
    .comment-card {
      background: #ffffff;
      border-left: 5px solid #22c55e;
      border-radius: 10px;
      padding: 1rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .comment-date { font-size: 0.9rem; color: #666; margin-bottom: 0.5rem; }
    .comment-text { font-size: 1rem; line-height: 1.6; }
    .rating { color: #facc15; margin-top: 0.5rem; }
    .back-btn { display: block; text-align: center; margin-top: 2rem; }
    .back-btn a {
      background-color: #22c55e; color: white;
      padding: 10px 20px; border-radius: 8px;
      text-decoration: none; font-weight: bold;
    }
    .back-btn a:hover { background-color: #14532d; }
  </style>
</head>
<body>

<header class="header">
  <img class="logo" src="Image/Logo3.png" alt="FitMind Logo">
  <h1>FitMind</h1>
  <a href="logout.php" class="logout-btn">Logout</a>
</header>

<div class="quiz-info-card">
  <h2><?php echo $topicName; ?> - Feedback</h2>
  <p class="subtitle">Learner comments and ratings (displayed anonymously)</p>
  <div class="alert">🔒 All comments are displayed anonymously – Learner identities are protected.</div>
</div>

<div class="container">
  <?php if ($feedbacks->num_rows > 0): ?>
    <?php while ($fb = $feedbacks->fetch_assoc()): ?>
      <div class="comment-card">
        <div class="comment-date">
          <?php echo date("F d, Y - h:i A", strtotime($fb['date'])); ?>
        </div>
        <div class="comment-text">
          <?php echo htmlspecialchars($fb['comments']); ?>
        </div>
        <div class="rating">
          <?php
          $stars = intval($fb['rating']);
          echo str_repeat("★", $stars) . str_repeat("☆", 5 - $stars);
          ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center;">No feedback available for this quiz yet.</p>
  <?php endif; ?>

  <div class="back-btn">
    <a href="educator_homepage.php">← Back to Homepage</a>
  </div>
</div>

<footer class="footer">
  <div>
    <img src="Image/Logo3.png" alt="FitMind Logo" style="height: 60px; margin-bottom: 10px;">
    <p>Contact: support@fitmind.com | (555) 123-4567</p>
    <p>&copy; 2025 FitMind. All rights reserved.</p>
    <span>Privacy Policy</span> | <span>Terms of Service</span>
  </div>
</footer>

</body>
</html>
