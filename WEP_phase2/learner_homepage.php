
<?php
include 'db_connection.php';

session_start();


// (a) & (g) Check login + user type
if (!isset($_SESSION['userID'])) {
    /*header("Location: System_Homepage.php?error=unauthorized");*/
     $errorMsg = "not logged-in. Redirecting to Homepage...";
    
    // Show the error message and stop execution
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'System_Homepage.php?error= not logged-in';
            }, 3000); // wait 3 seconds before redirect
          </script>";
    exit();
}// end me 



if ( $_SESSION['userType'] !== 'learner') {
    /*header("Location: Log-in.php?error=unauthorized");*/
     $errorMsg = "not a learner. Redirecting to login...";
    
    // Show the error message and stop execution
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'Log-in.php?error=not a learner';
            }, 3000); // wait 3 seconds before redirect
          </script>";
    exit();
}
 
//if (!isset($_SESSION['userID']) || $_SESSION['userType'] !== 'learner') {
//    header("Location: Log-in.php?error=unauthorized");
 //   exit();
//}
// me 


$userId = $_SESSION['userID'];

// (b) Get user info
$userQuery = $conn->prepare("SELECT firstName, lastName, emailAddress, photoFileName FROM User WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

// (c) Get topics for filter form
$topics = $conn->query("SELECT id, topicName FROM Topic");

// Check if request is POST (filter by topic) or GET (all quizzes)
/*$filterTopicId = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['topic'])) {
    $filterTopicId = intval($_POST['topic']);
    
    $quizQuery = $conn->prepare("
        SELECT Quiz.id, Topic.topicName, User.firstName, User.lastName, User.photoFileName,
        (SELECT COUNT(*) FROM QuizQuestion WHERE QuizQuestion.quizID = Quiz.id) as questionCount
        FROM Quiz
        JOIN Topic ON Quiz.topicID = Topic.id
        JOIN User ON Quiz.educatorID = User.id
        WHERE Quiz.topicID = ?
    ");
    // me
    if($filterTopicId==0){
         $quizzes = $conn->query("
        SELECT Quiz.id, Topic.topicName, User.firstName, User.lastName, User.photoFileName,
        (SELECT COUNT(*) FROM QuizQuestion WHERE QuizQuestion.quizID = Quiz.id) as questionCount
        FROM Quiz
        JOIN Topic ON Quiz.topicID = Topic.id
        JOIN User ON Quiz.educatorID = User.id
    ");
    }
    else{
 
    $quizQuery->bind_param("i", $filterTopicId);
    $quizQuery->execute();
    $quizzes = $quizQuery->get_result();}// end me
} else {*/
    $quizzes = $conn->query("
        SELECT Quiz.id, Topic.topicName, User.firstName, User.lastName, User.photoFileName,
        (SELECT COUNT(*) FROM QuizQuestion WHERE QuizQuestion.quizID = Quiz.id) as questionCount
        FROM Quiz
        JOIN Topic ON Quiz.topicID = Topic.id
        JOIN User ON Quiz.educatorID = User.id
    ");
    
/*}*/

// (f) Recommended questions by learner
$recQuery = $conn->prepare("
    SELECT RecommendedQuestion.*, Topic.topicName, User.firstName, User.lastName, User.photoFileName
    FROM RecommendedQuestion
    JOIN Quiz ON RecommendedQuestion.quizID = Quiz.id
    JOIN Topic ON Quiz.topicID = Topic.id
    JOIN User ON Quiz.educatorID = User.id
    WHERE RecommendedQuestion.learnerID = ?
");
$recQuery->bind_param("i", $userId);
$recQuery->execute();
$recommended = $recQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Learner's Homepage</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    body {
      font-family:'Segoe UI', sans-serif;
      background-color:  #f0fdf4;
      margin: 0;
    }
    .container {
      padding: 20px;
      max-width: 1300px;
      margin: auto;
      margin-top: 20px;
      margin-bottom: 20px;
    }
	
	#profileImage {
	    width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #22c55e;
	}
    .WelcomeBox {
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: white;
      color: #14532d;
      padding: 12px 30px;
      text-align: center;
      height: 100px;
    }
     .WelcomeBox h2 {
      font-size: 23px;
      margin:auto;
      

    }
  
    .user-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px;
      margin: 15px 0;
      background-color: white;
      border-radius: 10px;
      color:  #14532d;
    }
    .user-info .details {
      flex: 1;
    }
    .user-info img {
      width: 90px;
      height: 90px;
      object-fit: cover;
      margin-right: 10px;
    }
    table {
      width: 100%;
      margin: 15px 0;
    }
  
    th {
      background:  #dcfce7;
      text-align: left;
      padding: 8px;
      color:#14532d;
      
    }
    td {
      padding: 8px;
      vertical-align: top;
      border-bottom: #00000020 solid 1px;
      color:#14532d;
      
    }
	
	tr:hover  {
	  background: #dcfce7; }

	
    .filter {
      text-align: right;
      margin: 10px 0;
    }
    .filter select, .filter button {
      padding: 5px 10px;
    }
   
    .recommend-link {
      text-align: right;
      margin: 10px 0;
    }
    .recommend-link a {
      font-weight: bold;
       color:#22c55e;
    
      
      
    }
	 .correct {
      font-weight: bold;
      color: #22c55e;
    }
    a{
      color: #6F8F72;
    }

    .EducatorImage{
        width: 60px;
        height: 70px;
    }
    .tableTow, .tableOne{
      background-color: white;
      border-radius: 10px;
      padding: 10px;
    }


    h3{
      color:#14532d;
    }

    hr{
      border: none;
      height: 2px;
      background-color: #22c55e;
    }

  </style>
</head>
<body>
  <header class="header">
    <img class="logo" src="Image/Logo3.png" alt="FitMind Logo">
    <h1>FitMind</h1>
     <a href="logout.php" class="logout-btn">Logout</a>
  </header>     

  <!-- Welcome -->
  <div class="WelcomeBox">
      <h2>Welcome <?= htmlspecialchars($user['firstName']) ?>!</h2>
  </div>

  <div class="container">
    <!-- Learner Info -->
    <div class="user-info">
        <img id="profileImage" alt="user photo" src="uploads/<?= htmlspecialchars($user['photoFileName']) ?>">
        <div class="details">
          <p><strong>Name: </strong><?= htmlspecialchars($user['firstName'] . " " . $user['lastName']) ?></p>
          <p><strong>Email: </strong><?= htmlspecialchars($user['emailAddress']) ?></p>
        </div>
    </div>

    <!-- Available Quizzes -->
    <div class="tableOne">
    <h3>All Available Quizzes</h3>
    <hr>
    <div class="filter">
  <select id="topicSelect">
    <option value="0">All Topics</option>
    <?php while($row = $topics->fetch_assoc()): ?>
      <option value="<?= $row['id'] ?>">
        <?= htmlspecialchars($row['topicName']) ?>
      </option>
    <?php endwhile; ?>
  </select>
</div>

    <table id="quizTable">
  <thead>
    <tr>
      <th>Topic</th>
      <th>Educator</th>
      <th>Number of Questions</th>
      <th>Quiz Link</th>
    </tr>
  </thead>
  <tbody>
    <?php while($quiz = $quizzes->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($quiz['topicName']) ?></td>
      <td>
        <?= htmlspecialchars($quiz['firstName'] . " " . $quiz['lastName']) ?><br>
        <img src="uploads/<?= htmlspecialchars($quiz['photoFileName']) ?>" class="EducatorImage">
      </td>
      <td><?= $quiz['questionCount'] ?></td>
      <td>
        <?php if ($quiz['questionCount'] > 0){ ?>
          <a href="Take_quiz.php?quizId=<?= $quiz['id'] ?>" style="color:#22c55e;">Take Quiz</a>
        <?php } else{?>
          <p style="color: gray; font-style: italic;">no questions for this quiz yet</p>
        <?php } ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

    </div>

    <!-- Recommended Questions -->
    <div class="tableTow">
    <h3>Recommended Questions</h3>
    <hr>
    <div class="recommend-link">
      <a href="recommend_question.php">Recommend a Question</a>
    </div>
    <table>
        <thead>
      <tr>
        <th>Topic</th>
        <th>Educator</th>
        <th>Question</th>
        <th>Status</th>
        <th>Comments</th>
      </tr>
      </thead>
      <tbody>
      <?php while($rec = $recommended->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($rec['topicName']) ?></td>
        <td>
          <?= htmlspecialchars($rec['firstName'] . " " . $rec['lastName']) ?><br>
          <img src="uploads/<?= htmlspecialchars($rec['photoFileName']) ?>" class="EducatorImage">
        </td>
        <td>
          <?php if (!empty($rec['questionFigureFileName'])): ?>
            <img src="uploads/<?= htmlspecialchars($rec['questionFigureFileName']) ?>" width="80"><br>
          <?php endif; ?>
          <?= htmlspecialchars($rec['question']) ?><br>
          A. <?= htmlspecialchars($rec['answerA']) ?><br>
          B. <?= htmlspecialchars($rec['answerB']) ?><br>
          C. <?= htmlspecialchars($rec['answerC']) ?><br>
          D. <?= htmlspecialchars($rec['answerD']) ?><br>
          <p class="correct">Correct: <?= htmlspecialchars($rec['correctAnswer']) ?></p>
        </td>
        <td><?= htmlspecialchars($rec['status']) ?></td>
        <?php if($rec['comments'] != null) {   ?>
        <td><?= htmlspecialchars($rec['comments']) ?></td>
        <?php }  else {?>
        <td style="color: gray; font-style: italic;">no comment</td>
        <?php }?>
      
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
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
  
  
  
  
  
  <script>
$(document).ready(function(){
  $('#topicSelect').on('change', function(){
    var topicID = $(this).val();

    $.ajax({
      url: 'get_quizzes.php',
      type: 'GET',
      data: { topicID: topicID },
      dataType: 'json',
      success: function(data) {
        var tableBody = $('#quizTable tbody');
        tableBody.empty();

        if (data.length === 0) {
          tableBody.append('<tr><td colspan="4" style="text-align:center;">No quizzes found</td></tr>');
        } else {
          $.each(data, function(index, quiz) {
            var quizRow = '<tr>' +
              '<td>' + quiz.topicName + '</td>' +
              '<td>' + quiz.firstName + ' ' + quiz.lastName + '<br>' +
              '<img src="uploads/' + quiz.photoFileName + '" class="EducatorImage"></td>' +
              '<td>' + quiz.questionCount + '</td>' +
              '<td>';

            if (quiz.questionCount > 0)
              quizRow += '<a href="Take_quiz.php?quizId=' + quiz.id + '" style="color:#22c55e;">Take Quiz</a>';
            else
              quizRow += '<p style="color: gray; font-style: italic;">no questions for this quiz yet</p>';

            quizRow += '</td></tr>';

            tableBody.append(quizRow);
          });
        }
      },
      error: function(xhr, status, error) {
        alert("Error loading quizzes: " + error);
      }
    });
  });
});
</script>

</body>
</html>




