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
$topics = $conn->query("SELECT id, topicName FROM Topic ORDER BY topicName ASC");
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>FitMind - Recommend a Question</title>
  <link rel="stylesheet" href="Style.css">
  <style>
body{font-family:system-ui,Segoe UI,Arial;background:#f0fdf4;color:#14532d;margin:0}
.mainContainer{max-width:800px;margin:auto;padding:2rem 1rem;min-height:100vh}
.titleLarge{font-size:1.875rem;font-weight:700}
.titleSmall{font-size:1.125rem;margin-top:.5rem}
.card{background:#fff;padding:1.5rem;border-radius:.75rem;box-shadow:0 1px 2px rgba(0,0,0,.05);margin-bottom:1.5rem}
label{font-size:.875rem;font-weight:500;margin-bottom:.25rem;display:block}
input,select,textarea{width:100%;border:1px solid #d1d5db;border-radius:.375rem;padding:.5rem}
.flexRow{display:flex;align-items:center;justify-content:space-between}
.verticalSpace>*+*{margin-top:.5rem}
.smallWidth{width:2rem}
.mainButton{background:#16a34a;color:#fff;font-weight:600;padding:.75rem 1.5rem;border-radius:.5rem;cursor:pointer;border:none;transition:.2s}
.mainButton:hover{background:#15803d;transform:translateY(-2px);box-shadow:0 4px 8px rgba(0,0,0,.1)}
@media(max-width:640px){.mainContainer{padding:1rem}.mainButton{width:100%}}
a{color:#14532d}
  </style>
</head>
<body>
<header class="header" style="padding:1rem;display:flex;align-items:center;gap:1rem">
  <img class="logo" src="Image/Logo3.png" alt="FitMind Logo" style="height:60px">
  <h1 style="margin:0">FitMind</h1>
  <a href="logout.php" class="logout-btn">Logout</a> 
</header>

<div class="mainContainer">
  <div class="page active">
    <header>
      <h1 class="titleLarge">Recommend a Question</h1>
      <p class="titleSmall">Recommend a question for future quizzes</p>
    </header>

    <div class="recommendationForm card">
      <form id="RecommendQuestion" method="POST" action="recommendQuestionProcess.php" enctype="multipart/form-data" novalidate>
        <div class="flexRow" style="gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
          <div style="flex:1;min-width:200px">
            <label for="rec-topic">Topic</label>
            <select id="rec-topic" name="topic" required>
              <option value="">Select Topic</option>
              <?php while($t = $topics->fetch_assoc()): ?>
                <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['topicName'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div style="flex:1;min-width:200px">
            <label for="rec-educator">Educator</label>
            <select id="rec-educator" name="educator" required>
    <option value="">Select Educator</option>
</select>


          </div>
        </div>

        <div style="margin-bottom:1.5rem">
          <label for="question-text">Question Text</label>
          <textarea id="question-text" name="questionText" rows="2" required></textarea>
        </div>

        <div style="margin-bottom:1.5rem">
          <label for="question-file">Attach File (Image)</label>
          <input type="file" id="question-file" name="questionFile" accept="image/*">
        </div>

        <div style="margin-bottom:1.5rem">
          <label>Answer Options</label>
          <div class="verticalSpace">
            <div class="flexRow"><span class="smallWidth">A:</span><input type="text" name="optionA" required></div>
            <div class="flexRow"><span class="smallWidth">B:</span><input type="text" name="optionB" required></div>
            <div class="flexRow"><span class="smallWidth">C:</span><input type="text" name="optionC" required></div>
            <div class="flexRow"><span class="smallWidth">D:</span><input type="text" name="optionD" required></div>
          </div>
        </div>

        <div style="margin-bottom:1.5rem">
          <label for="correct-answer">Correct Answer</label>
          <select id="correct-answer" name="correctAnswer" required>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
          </select>
        </div>

       <div class="flexRow" style="margin-top:1.5rem">
         <a href="learner_homepage.php" style="text-decoration:none;color:#14532d">Cancel</a>
         <button id="submitBtn" type="submit" class="mainButton">Submit Recommendation</button>
       </div>
      </form>
    </div>
  </div>
</div>

<footer class="footer" style="padding:1rem;text-align:center">
  <div>
    <img src="Image/Logo3.png" alt="FitMind Logo" style="height:30px;margin-bottom:10px">
    <p style="margin:0">Contact: support@fitmind.com | (555) 123-4567</p>
    <p style="margin:0">&copy; 2025 FitMind</p>
  </div>
</footer>


</body>
<script>
document.getElementById("rec-topic").addEventListener("change", function() {
    let topicID = this.value;
    let educatorDropdown = document.getElementById("rec-educator");

   
    educatorDropdown.innerHTML = `<option value="">Loading...</option>`;

    if (topicID === "") {
        educatorDropdown.innerHTML = `<option value="">Select Educator</option>`;
        return;
    }

    
    fetch("getEducatorsByTopic.php?topicID=" + topicID)
        .then(response => response.json())
        .then(data => {
            educatorDropdown.innerHTML = "";

            if (data.length === 0) {
                educatorDropdown.innerHTML = `<option value="">No educators found</option>`;
                return;
            }

            educatorDropdown.innerHTML = `<option value="">Select Educator</option>`;

            data.forEach(edu => {
                educatorDropdown.innerHTML += `<option value="${edu.id}">${edu.name}</option>`;
            });
        })
        .catch(error => {
            educatorDropdown.innerHTML = `<option value="">Error loading list</option>`;
        });
});
</script>

</html>
