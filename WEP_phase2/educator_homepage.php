
<?php
include 'db_connection.php';

session_start();

// Case 1: No session at all
if (!isset($_SESSION['userID']) || !isset($_SESSION['userType'])) {
    $errorMsg = "You are not logged in. Redirecting to Homepage...";
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'System_Homepage.html?error=not_logged_in';
            }, 3000);
          </script>";
    exit();
}

// Case 2: Logged in, but wrong type
if ($_SESSION['userType'] !== 'educator') {
    $errorMsg = "You are not an educator. Redirecting to login...";
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'Log-in.php?error=not_educator';
            }, 3000);
          </script>";
    exit();
}



$userID = $_SESSION['userID'];


$userQuery = $conn->prepare("SELECT firstName, lastName, emailAddress, photoFileName FROM user WHERE id = ?");
$userQuery->bind_param("i", $userID);
$userQuery->execute();
$user = $userQuery->get_result()->fetch_assoc();

$educatorName = $user['firstName'] . ' ' . $user['lastName'];
$educatorEmail = $user['emailAddress'];
$educatorPhoto = $user['photoFileName'] ?: 'person.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Educator Home - FitBrain</title>
  <link rel="stylesheet" href="style.css">
 <style>
    body.edu-page {
      font-family: 'Segoe UI', sans-serif;
      background: #f0fdf4; 
      margin: 0;
      padding: 0;
      color: #14532d; 
    }

    .edu-Welcome {
      background: #fff;
      color: #14532d;
      padding: 12px 30px;
      font-size: 23px;
      text-align: center;
      height: 100px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

 
    .edu-container {
      width: 90%;
      margin: 20px auto;
    }


    .edu-info {
      display: flex;
      gap: 20px;
      background: #fff;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .edu-photo {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #22c55e;
    }


    .edu-section {
      background: white;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    .edu-section h3 {
      margin-top: 0;
      color: #14532d;
      border-bottom: 2px solid #22c55e;
      padding-bottom: 8px;
    }

 
    .edu-table {
      width: 100%;
    }
    .edu-table th {
      background: #dcfce7;
      color: #14532d;
      text-align: left;
      padding: 10px;
    }
    .edu-table td {
      padding: 10px;
      border-bottom: 1px solid #dcfce7;
      vertical-align: top;
    }
    .edu-table tr:hover { background: #dcfce7; }

  
    a {
      color: #22c55e;
      font-weight: bold;
      text-decoration: none;
    }
    a:hover { text-decoration: underline; }

  
    .correct {
      font-weight: bold;
      color: #22c55e;
    }
    .question-image {
      width: 120px;
      height: auto;
      display: block;
      margin-bottom: 8px;
      border-radius: 5px;
      object-fit: cover;
    }

    textarea, input[type=text] {
      width: 100%;
      min-height: 40px;
      margin-bottom: 8px;
      padding: 6px;
      border: 1px solid #22c55e;
      border-radius: 4px;
     
    }

    .submit-btn {
      border: none;
      padding: 6px 12px;
      border-radius: 5px;
      color: white;
      cursor: pointer;
      background: #22c55e;
    }
    .submit-btn:hover { background: #14532d; }


    .learner-info {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .learner-info img {
      width: 60px;
      height: 70px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #22c55e;
    }
    

    .no-data {
      font-style: italic;
      color: #757575;
    }
    
  </style>
</head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



<body class="edu-page">

<header class="header">
  <img class="logo" src="Image/Logo3.png" alt="FitMind Logo">
  <h1>FitMind</h1>
  <a href="logout.php" class="logout-btn">Logout</a>
</header>

<div class="edu-Welcome">
  <h2>Welcome, Dr. <?php echo htmlspecialchars($user['firstName']); ?>!</h2>
</div>

<div class="edu-container">

  
  <section class="edu-info">
    <img src="uploads/<?php echo htmlspecialchars($educatorPhoto); ?>" class="edu-photo" alt="Educator photo">
    <div>
      <p><strong>Name:</strong> <?php echo htmlspecialchars($educatorName); ?></p>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($educatorEmail); ?></p>
    </div>
  </section>

  <!-- the quizzes-->
  <section class="edu-section">
    <h3>My Quizzes</h3>
    <table class="edu-table">
      <thead>
        <tr>
          <th>Topic</th>
          <th>Questions</th>
          <th>Statistics</th>
          <th>Feedback</th>
        </tr>
      </thead>
      <tbody>
        <?php
       
        $quizQuery = "
          SELECT q.id AS quizID, t.topicName 
          FROM quiz q 
          JOIN topic t ON q.topicID = t.id
          WHERE q.educatorID = ?";
        $stmt = $conn->prepare($quizQuery);
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $quizzes = $stmt->get_result();

        if ($quizzes->num_rows > 0) {
          while ($quiz = $quizzes->fetch_assoc()) {
            $quizID = $quiz['quizID'];
            $topicName = $quiz['topicName'];

            
            $countQ = $conn->query("SELECT COUNT(*) AS totalQ FROM quizquestion WHERE quizID = $quizID")->fetch_assoc()['totalQ'];

         
            $taken = $conn->query("SELECT COUNT(*) AS totalTaken, AVG(score) AS avgScore FROM takenquiz WHERE quizID = $quizID")->fetch_assoc();
            $takenText = ($taken['totalTaken'] > 0)
              ? "{$taken['totalTaken']} takers, Avg: " . round($taken['avgScore'], 1) . "%"
              : "<span class='no-data'>Quiz not taken yet</span>";

           
            $feedback = $conn->query("SELECT AVG(rating) AS avgRating FROM quizfeedback WHERE quizID = $quizID")->fetch_assoc();
            if ($feedback['avgRating'] != null) {
              $feedbackText = "Avg Rating " . round($feedback['avgRating'], 1) . "/5 (<a href='Comments.php?quizID=$quizID'>Comments</a>)";
            } else {
              $feedbackText = "<span class='no-data'>No feedback yet</span>";
            }

            echo "
            <tr>
              <td><a href='quiz.php?quizID=$quizID'>$topicName</a></td>
              <td>$countQ</td>
              <td>$takenText</td>
              <td>$feedbackText</td>
            </tr>";
          }
        } else {
          echo "<tr><td colspan='4' class='no-data'>No quizzes found</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </section>

  
  <section class="edu-section">
    <h3>Recommended Questions</h3>
    <table class="edu-table">
      <thead>
        <tr>
          <th>Topic</th>
          <th>Learner</th>
          <th>Question</th>
          <th>Review</th>
        </tr>
      </thead>
      <tbody>
        <?php
    $recQuery = "
  SELECT 
      r.id AS recID, r.question, r.answerA, r.answerB, r.answerC, r.answerD, 
      r.correctAnswer, r.questionFigureFileName, r.comments AS learnerComment, 
      u.firstName AS learnerName, u.photoFileName AS learnerPhoto,
      t.topicName
  FROM recommendedquestion r
  JOIN user u ON r.learnerID = u.id
  JOIN quiz q ON r.quizID = q.id
  JOIN topic t ON q.topicID = t.id
  WHERE q.educatorID = ? AND r.status = 'pending'";

        $stmt2 = $conn->prepare($recQuery);
        $stmt2->bind_param("i", $userID);
        $stmt2->execute();
        $recResult = $stmt2->get_result();

        if ($recResult->num_rows > 0) {
          while ($rec = $recResult->fetch_assoc()) {
            $img = $rec['questionFigureFileName'] ? "<img src='uploads/{$rec['questionFigureFileName']}' class='question-image'>" : "";
            echo "
            <tr>
              <td>{$rec['topicName']}</td>
              <td>
  <div class='learner-info'>
    <img src='uploads/" . htmlspecialchars($rec['learnerPhoto'] ?: 'person.png') . "' alt='Learner photo'>
    <span>" . htmlspecialchars($rec['learnerName']) . "</span>
  </div>
</td>

              <td>
                $img
                <p>{$rec['question']}</p>
                <ul>
                  <li>A) {$rec['answerA']}</li>
                  <li>B) {$rec['answerB']}</li>
                  <li>C) {$rec['answerC']}</li>
                  <li>D) {$rec['answerD']}</li>
                  <li><strong>Correct:</strong> {$rec['correctAnswer']}</li>
                </ul>
              </td>
              <td>
    <input type='text' class='comment-input' placeholder='Enter comment...'>
    
    <button class='submit-review-btn submit-btn'
            data-id='{$rec['recID']}'
            data-status='approved'>
        Approve
    </button>

    <button class='submit-review-btn submit-btn'
            data-id='{$rec['recID']}'
            data-status='disapproved'>
        Disapprove
    </button>
</td>

            </tr>";
          }
        } else {
          echo "<tr><td colspan='4' class='no-data'>No pending recommendations</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </section>
</div>

<script>
$(document).ready(function() {

    $(".submit-review-btn").click(function() {

        let recID = $(this).data("id");
        let status = $(this).data("status");
        let row = $(this).closest("tr");
        let comment = row.find(".comment-input").val();

        $.ajax({
            url: "review_recommendation_ajax.php",
            type: "POST",
            data: {
                recID: recID,
                status: status,
                comment: comment
            },
            success: function(response) {
                if (response.trim() === "true") {
                    row.fadeOut(300, function() { $(this).remove(); });
                } else {
                    alert("Error occurred");
                }
            }
        });
    });

});
</script>
</body>
</html>
