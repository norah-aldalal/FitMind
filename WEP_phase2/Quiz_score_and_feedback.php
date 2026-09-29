<?php
include 'db_connection.php';

session_start();


if (!isset($_SESSION['userID']) || $_SESSION['userType'] !== 'learner') {
    $errorMsg = "You are not logged in. Redirecting to homepage...";
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'System_Homepage.html';
            }, 3000);
          </script>";
    exit();
}

$userID = $_SESSION['userID'];



if (!isset($_POST['quizID']) || !isset($_POST['selected_qids'])) {
    header("Location: System_Homepage.html");
    exit();
}

$quizID = intval($_POST['quizID']);
$selected_qids = $_POST['selected_qids'] ?? [];

$stmt = $conn->prepare("SELECT q.id, t.topicName, u.firstName, u.lastName, u.photoFileName 
                        FROM Quiz q 
                        JOIN Topic t ON q.topicID = t.id 
                        JOIN User u ON q.educatorID = u.id 
                        WHERE q.id = ?");
$stmt->bind_param("i", $quizID);
$stmt->execute();
$quiz_result = $stmt->get_result();
$quiz_row = $quiz_result->fetch_assoc();
$stmt->close();

if (!$quiz_row) {
    header("Location: System_Homepage.html");
    exit();
}

$topic_name = $quiz_row['topicName'] ?? '';
$educator_name = trim(($quiz_row['firstName'] ?? '') . ' ' . ($quiz_row['lastName'] ?? ''));
$educator_image = $quiz_row['photoFileName'] ?? 'default.jpg';

$score = 0;
$totalQuestions = count($selected_qids);


if ($totalQuestions > 0) {
    foreach ($selected_qids as $questionID) {
        $questionID = (int)$questionID;
        if ($questionID <= 0) continue;

        $user_answer = isset($_POST["answer_" . $questionID]) ? strtoupper(trim($_POST["answer_" . $questionID])) : '';

        $stmt = $conn->prepare("SELECT correctAnswer FROM QuizQuestion WHERE id = ?");
        $stmt->bind_param("i", $questionID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

if ($row) {
    $correct = strtoupper(trim($row['correctAnswer']));
    if ($user_answer === $correct) {
        $score++;
    }
}

    }

    $denominator = max(1, $totalQuestions);
    $percentage = round(($score / $denominator) * 100);
} else {
    $percentage = 0;
    $score = 0;
    $totalQuestions = 0;
}

$stmt = $conn->prepare("INSERT INTO TakenQuiz (quizID, score) VALUES (?, ?)");
$score_to_store = intval($percentage);
$stmt->bind_param("ii", $quizID, $score_to_store);
$stmt->execute();
$stmt->close();

$videoFile = ($percentage >= 60) ? "success.mp4" : "try_again.mp4";

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FitMind - Quiz Feedback</title>
  <link rel="stylesheet" href="Style.css">
  <style>
    body { 
      font-family: system-ui, sans-serif;
      background: #f0fdf4; 
      color: #14532d;
    }
    .author-info {
      display: flex;
      align-items: center;
      justify-content: center; 
      gap: 8px; 
      font-weight: bold; 
    }
    .author-pic {
      width: 40px;  
      height: 40px;
      border-radius: 50%; 
      object-fit: cover;
    }
    .btn-primary { 
      background: #16a34a;  
      color: #fff; 
      padding: 0.75rem 1.5rem; 
      border-radius: 0.5rem; 
      cursor: pointer; 
      border: none; 
      display: inline-block; 
      text-decoration: none; 
      font-size: 1rem;
      transition: background 0.3s;
    }
    .btn-primary:hover { 
      background: #14532d;  
      color: #fff;
    }
    .score-display { 
      font-size: 1.5rem; 
      text-align: center; 
      margin-bottom: 2rem; 
      color: #14532d; 
      font-weight: bold; 
      padding: 1rem;
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .score-pass { color: #16a34a; }
    .score-fail { color: #dc2626; }
    .score-breakdown { font-size: 1.2rem; margin-top: 0.5rem; opacity: 0.8; }
    .feedback-box { 
      background: #fff; 
      max-width: 700px;
      margin: 2rem auto;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .feedback-box h2 { 
      color: #14532d; 
      margin-bottom: 1.5rem; 
      text-align: center;
    }
    .feedback-box label { 
      display: block; 
      margin-bottom: 0.5rem; 
      font-weight: 600; 
    }
    .feedback-box select, 
    .feedback-box textarea { 
      width: 100%; 
      margin-bottom: 1.5rem; 
      padding: 1rem; 
      border: 1px solid #ccc; 
      border-radius: 8px; 
      font-size: 1rem;
      box-sizing: border-box;
    }
    .feedback-box textarea { resize: vertical; min-height: 100px; }
    .action-buttons {
      margin-top: 2rem;
      display: flex;
      justify-content: center;
      gap: 2rem; 
    }
   .video-container {
  position: relative;
  width: 100%;
  max-width: 700px;
  aspect-ratio: 16 / 9;
  margin: 2rem auto;
  border-radius: 16px;
  overflow: hidden;
  background: #f0fdf4; 
  display: flex;
  justify-content: center;
  align-items: center;
}

.video-container video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  background-color: transparent;
  display: block;
}

    .video-fallback {
      padding: 2rem;
      color: #fff;
      font-size: 1.2rem;
    }
    @media (max-width: 640px) {
      .action-buttons { flex-direction: column; align-items: center; }
      .score-display { font-size: 1.2rem; }
    }
  </style>
  <script>
    function validateFeedback() {
      const rating = document.getElementById('rating').value;
      const comments = document.getElementById('comments').value.trim();
      if (!rating || !comments) {
        alert('Please select a rating and add comments.');
        return false;
      }
      return true;
    }
  </script>
</head>
<body>

<header class="header">
  <img class="logo" src="Image/Logo3.png" alt="FitMind Logo" style="height:60px;margin-bottom:10px">
  <h1>FitMind</h1>
  <a href="logout.php" class="logout-btn">Logout</a> 
</header>

<p class="author-info">
  <img src="uploads/<?php echo htmlspecialchars($educator_image); ?>" alt="Educator" class="author-pic">
  <?php echo htmlspecialchars($educator_name); ?>, Topic: <?php echo htmlspecialchars($topic_name); ?>
</p>

<div id="score-box" class="score-display <?php echo $percentage >= 60 ? 'score-pass' : 'score-fail'; ?>">
  Quiz Score: <?php echo htmlspecialchars($percentage); ?>%
  <div class="score-breakdown"><?php echo $score; ?> out of <?php echo $totalQuestions; ?> correct</div>
</div>

<div class="video-container">
  <video autoplay muted loop playsinline preload="auto">
    <source src="Image/<?php echo htmlspecialchars($videoFile); ?>" type="video/mp4">
  </video>
</div>


<div class="feedback-box">
  <h2>We value your feedback</h2>
  <form id="userFeedbackForm" method="POST" action="addFeedbackProcess.php" onsubmit="return validateFeedback();"> 
    <input type="hidden" name="quizID" value="<?php echo htmlspecialchars($quizID); ?>">
    
    <label for="rating">Rate your experience:</label>
    <select id="rating" name="rating" required aria-required="true">
      <option value="">Select rating</option>
      <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
      <option value="4">⭐⭐⭐⭐ Very Good</option>
      <option value="3">⭐⭐⭐ Good</option>
      <option value="2">⭐⭐ Fair</option>
      <option value="1">⭐ Poor</option>
    </select>

    <label for="comments">Additional comments:</label>
    <textarea id="comments" name="comments" rows="4" placeholder="Write your feedback..." required aria-required="true"></textarea>

    <div class="action-buttons">
      <button type="submit" class="btn-primary">Submit Feedback</button>
      <a href="learner_homepage.php" class="btn-primary">Return to Homepage</a> 
    </div>
  </form>
</div>

<footer class="footer">
  <div>
    <img src="Image/Logo3.png" alt="FitMind Logo" style="height:30px;margin-bottom:10px">
    <p>Contact: support@fitmind.com | (555) 123-4567</p>
    <p>&copy; 2025 FitMind</p>
    <span>Privacy Policy</span> | <span>Terms of Service</span>
  </div>
</footer>

</body>
</html>