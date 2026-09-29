<?php
include 'db_connection.php';

session_start();


if (!isset($_SESSION['userID'])) {
      $errorMsg = "You are not logged in. Redirecting to Homepage...";
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'System_Homepage.html?error=not_logged_in';
            }, 3000);
          </script>";
    exit();
}


if ($_SESSION['userType'] !== 'learner') {
    $errorMsg = "You are not a learner. Redirecting to login...";
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'Log-in.php';
            }, 3000);
          </script>";
    exit();
}

if (!isset($_GET['quizId']) || !is_numeric($_GET['quizId'])) {
    header('Location: System_Homepage.php'); // Or an error page
    exit();
}

$quizId = intval($_GET['quizId']);

$stmt = $conn->prepare("SELECT q.id, t.topicName, u.firstName, u.lastName, u.photoFileName 
                        FROM Quiz q 
                        JOIN Topic t ON q.topicID = t.id 
                        JOIN User u ON q.educatorID = u.id 
                        WHERE q.id = ?");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$quiz_result = $stmt->get_result();


if (!$quiz_result || $quiz_result->num_rows == 0) {

    header('Location: System_Homepage.php');
    exit();
}

$quiz_row = $quiz_result->fetch_assoc();
$educator_name = $quiz_row['firstName'] . ' ' . $quiz_row['lastName'];
$educator_image = $quiz_row['photoFileName'] ?? 'person.png'; 
$stmt->close();


$stmt = $conn->prepare("SELECT * FROM QuizQuestion WHERE quizID = ?");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$all_questions_result = $stmt->get_result();

if (!$all_questions_result) {
    die('Database error: ' . $conn->error);
}


$all_questions = [];
while ($q = $all_questions_result->fetch_assoc()) {
    $all_questions[] = $q;
}

shuffle($all_questions);

$selected_questions = array_slice($all_questions, 0, 5);


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Take Quiz</title>
  <link rel="stylesheet" href="Style.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,sans-serif;}
body{background-color:#f0fdf4;color:#14532d;min-height:100vh;display:flex;flex-direction:column;}
.author-info{display:flex;align-items:center;justify-content:center;gap:8px;font-weight:bold;}
.author-pic{width:32px;height:32px;border-radius:50%;object-fit:cover;}
.quiz-banner{padding:1.5rem 1rem;text-align:center;margin-bottom:1.5rem;}
.quiz-banner h2{font-size:1.6rem;margin-bottom:.5rem;}
.quiz-banner p{color:#14532d;opacity:.9;font-size:.9rem;}
.quiz-box{max-width:1500px;margin:0 auto;padding:1rem;flex-grow:1;}
.q-card{width:100%;background:#fff;border-left:5px solid #22c55e;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.08);padding:2rem;margin-bottom:2rem;}
.q-header{display:grid;grid-template-columns:1fr 260px;align-items:flex-start;gap:25px;}
.q-title{font-size:1.3rem;font-weight:600;margin-bottom:1rem;color:#14532d;line-height:1.6;}
.q-img-container{width:260px;height:200px;display:flex;justify-content:center;align-items:center;}
.q-img{max-width:100%;max-height:100%;object-fit:contain;border-radius:10px;border:2px solid #22c55e;background-color:#f0fdf4;padding:8px;box-shadow:0 2px 8px rgba(34,197,94,0.3);}
.q-img[src=""],.q-img:not([src]){visibility:hidden;}
.options{list-style:none;margin-top:10px;}
.option{border:1px solid #e5e7eb;border-radius:6px;margin-bottom:.75rem;transition:.2s;}
.option:hover{background-color:#dcfce7;}
.option label{display:flex;align-items:center;padding:.75rem;cursor:pointer;width:100%;}
.option input{margin-right:.75rem;cursor:pointer;}
.btn-submit{background:#22c55e;color:#fff;border:none;border-radius:6px;padding:.75rem 2rem;font-size:1rem;font-weight:500;cursor:pointer;transition:.2s;margin:1.5rem auto;display:block;min-width:200px;}
.btn-submit:hover{background:#14532d;}
@media(max-width:640px){
.quiz-banner h2{font-size:1.3rem;}
.q-title{font-size:1rem;}
.option label{padding:.6rem;}
.q-header{grid-template-columns:1fr;}
.q-img-container{margin:auto;}
}
</style>



</head>
<body>
 <header class="header">
  <img class="logo" src="Image/Logo3.png" alt="FitMind Logo" style="height:60px;margin-bottom:10px">
  <h1>FitMind</h1>
  <a href="logout.php" class="logout-btn">Logout</a> 
</header>

 <div class="quiz-banner">
  <h2><?php echo htmlspecialchars($quiz_row['topicName']); ?> Quiz</h2>
  <p class="author-info">
    <img src="uploads/<?php echo htmlspecialchars($educator_image); ?>" class="author-pic" alt="Educator Photo">
    Created by <?php echo htmlspecialchars($educator_name); ?>
  </p>
</div>


  <main class="quiz-box">
      <?php if (count($selected_questions) === 0): ?>
  <div class="q-card" style="text-align:center;font-weight:600;">
    This quiz has no questions yet.
  </div>
<?php else: ?>

   <form action="Quiz_score_and_feedback.php" method="post">
    <input type="hidden" name="quizID" value="<?php echo $quizId; ?>">
    <div id="quiz-questions">
    <?php foreach ($selected_questions as $index => $question): ?>
      <?php $question_num = $index + 1; ?>
      <div class="q-card">
	    <div class="q-header">
          <div class="q-title"><?php echo $question_num; ?>. <?php echo htmlspecialchars($question['question']); ?></div>
		  <?php $img_src = !empty($question['questionFigureFileName']) ? 'uploads/' . htmlspecialchars($question['questionFigureFileName']) : ''; ?>
		  <?php if ($img_src): ?>
		  <img src="<?php echo $img_src; ?>" class="q-img" alt="Question Image">
		  <?php endif; ?>
		  <input type="hidden" name="selected_qids[]" value="<?php echo $question['id']; ?>">
		</div>
        <ul class="options">
         
  <li class="option"><label><input type="radio" name="answer_<?php echo (int)$question['id']; ?>" value="A" required> <?php echo htmlspecialchars($question['answerA']); ?></label></li>
  <li class="option"><label><input type="radio" name="answer_<?php echo (int)$question['id']; ?>" value="B" required> <?php echo htmlspecialchars($question['answerB']); ?></label></li>
  <li class="option"><label><input type="radio" name="answer_<?php echo (int)$question['id']; ?>" value="C" required> <?php echo htmlspecialchars($question['answerC']); ?></label></li>
  <li class="option"><label><input type="radio" name="answer_<?php echo (int)$question['id']; ?>" value="D" required> <?php echo htmlspecialchars($question['answerD']); ?></label></li>
</ul>
        
      </div>
    <?php endforeach; ?>
      <button type="submit" class="btn-submit">Submit Quiz</button>
    </div>
	</form>
      <?php endif; ?>
  </main>

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