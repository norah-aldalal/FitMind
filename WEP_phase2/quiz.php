<?php
include 'db_connection.php';

session_start();
if (!isset($_SESSION['userID']) || $_SESSION['userType'] !== 'educator') {
      $errorMsg = "not logged-in. Redirecting to Homepage...";
    
    // Show the error message and stop execution
    echo "<div style='color: red; text-align: center; margin-top: 20px;'>$errorMsg</div>";
    echo "<script>
            setTimeout(function() {
                window.location.href = 'System_Homepage.html?error= not logged-in';
            }, 3000); // wait 3 seconds before redirect
          </script>";
    exit();
}// end me 



$quizID = isset($_GET['quizID']) ? intval($_GET['quizID']) : 0;

if ($quizID === 0) {
    die("Invalid quiz ID");
}

// Get quiz details
$quizQuery = "SELECT q.*, t.topicName 
              FROM Quiz q 
              JOIN Topic t ON q.topicID = t.id 
              WHERE q.id = ? AND q.educatorID = ?";
$stmt = $conn->prepare($quizQuery);
$stmt->bind_param("ii", $quizID, $_SESSION['userID']);
$stmt->execute();
$quizResult = $stmt->get_result();
$quiz = $quizResult->fetch_assoc();

if (!$quiz) {
    die("Quiz not found or access denied");
}

// Get questions for this quiz
$questionsQuery = "SELECT * FROM QuizQuestion WHERE quizID = ? ORDER BY id";
$stmt = $conn->prepare($questionsQuery);
$stmt->bind_param("i", $quizID);
$stmt->execute();
$questionsResult = $stmt->get_result();
$questions = [];

while ($row = $questionsResult->fetch_assoc()) {
    $questions[] = $row;
}

$successMessage = isset($_GET['success']) ? $_GET['success'] : '';
$errorMessage = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quiz Questions • FitMind</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .page {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .quiz-header {
      background: #fff;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .quiz-title {
      color: #14532d;
      margin: 0 0 10px 0;
      font-size: 2em;
    }

    .quiz-meta {
      color: #6b7280;
      font-size: 1.1em;
      margin-bottom: 20px;
    }

    .questions-section {
      background: #fff;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .section-title {
      color: #14532d;
      margin: 0 0 20px 0;
      font-size: 1.5em;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .question-card {
      background: #f9fafb;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      position: relative;
    }

    .question-header {
      display: flex;
      justify-content: between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }

    .question-text {
      flex: 1;
      font-weight: 600;
      color: #374151;
      font-size: 1.1em;
      line-height: 1.5;
    }

    .question-actions {
      display: flex;
      gap: 8px;
    }

    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 13px;
      transition: all 0.2s;
    }

    .btn-sm {
      padding: 4px 8px;
      font-size: 12px;
    }

    .btn-green {
      background: #22c55e;
      color: white;
    }

    .btn-green:hover {
      background: #16a34a;
    }

    .btn-blue {
      background: #3b82f6;
      color: white;
    }

    .btn-blue:hover {
      background: #2563eb;
    }

    .btn-red {
      background: #ef4444;
      color: white;
    }

    .btn-red:hover {
      background: #dc2626;
    }

    .answers-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-top: 1rem;
    }

    .answer-option {
      padding: 10px;
      border: 2px solid #e5e7eb;
      border-radius: 6px;
      background: white;
    }

    .answer-option.correct {
      border-color: #22c55e;
      background: #f0fdf4;
    }

    .answer-label {
      font-weight: 600;
      color: #374151;
      margin-bottom: 4px;
    }

    .answer-text {
      color: #6b7280;
    }

    .question-image {
      max-width: 200px;
      max-height: 150px;
      border: 2px solid #22c55e;
      border-radius: 6px;
      margin: 10px 0;
    }

    .no-questions {
      text-align: center;
      padding: 3rem;
      color: #6b7280;
      font-size: 1.1em;
    }

    .alert-success {
      background-color: #d1fae5;
      color: #065f46;
      padding: 14px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid #10b981;
      font-weight: 500;
    }

    .alert-error {
      background-color: #fee2e2;
      color: #991b1b;
      padding: 14px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid #ef4444;
      font-weight: 500;
    }

    .action-buttons {
      display: flex;
      gap: 12px;
      margin-top: 20px;
    }

    .btn-large {
      padding: 12px 24px;
      font-size: 15px;
    }

    .question-number {
      background: #22c55e;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: 600;
      font-size: 12px;
      margin-right: 8px;
    }

    .ajax-message {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
      max-width: 400px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .delete-question-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  </style>
</head>
<body>

  <header class="header">
    <img class="logo" src="Image/Logo3.png" alt="FitMind Logo">
    <h1 class="header-title">FitMind</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </header>

  <main class="page">
    <?php if ($successMessage): ?>
      <div class="alert-success">✅ <?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
      <div class="alert-error">❌ <?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <section class="quiz-header">
      <h1 class="quiz-title"><?php echo htmlspecialchars($quiz['topicName']); ?></h1>
      <div class="quiz-meta">
        <strong>Topic:</strong> <?php echo htmlspecialchars($quiz['topicName']); ?> • 
        <strong>Questions:</strong> <?php echo count($questions); ?>
      </div>
      
      <div class="action-buttons">
        <a href="add.php?quizID=<?php echo $quizID; ?>" class="btn btn-green btn-large">
          ➕ Add New Question
        </a>
        <a href="educator_homepage.php" class="btn btn-blue btn-large">
          ← Back to Dashboard
        </a>
      </div>
    </section>

    <section class="questions-section">
      <h2 class="section-title">
        Quiz Questions
        <span style="font-size: 0.9em; color: #6b7280;">
          Total: <?php echo count($questions); ?> questions
        </span>
      </h2>

      <?php if (empty($questions)): ?>
        <div class="no-questions">
          <p>No questions added yet.</p>
          <p>Click "Add New Question" to create the first question for this quiz.</p>
        </div>
      <?php else: ?>
        <?php foreach ($questions as $index => $question): ?>
          <div class="question-card" id="question-<?php echo $question['id']; ?>">
            <div class="question-header">
              <div class="question-text">
                <span class="question-number">Q<?php echo $index + 1; ?></span>
                <?php echo htmlspecialchars($question['question']); ?>
              </div>
              <div class="question-actions">
                <a href="edit.php?qid=<?php echo $question['id']; ?>" class="btn btn-blue btn-sm">
                  ✏️ Edit
                </a>
                <button class="btn btn-red btn-sm delete-question-btn" 
                        data-question-id="<?php echo $question['id']; ?>"
                        data-quiz-id="<?php echo $quizID; ?>">
                  🗑️ Delete
                </button>
              </div>
            </div>

            <?php if (!empty($question['questionFigureFileName'])): ?>
              <img src="uploads/<?php echo htmlspecialchars($question['questionFigureFileName']); ?>" 
                   alt="Question Image" 
                   class="question-image">
            <?php endif; ?>

            <div class="answers-grid">
              <div class="answer-option <?php echo $question['correctAnswer'] === 'A' ? 'correct' : ''; ?>">
                <div class="answer-label">A</div>
                <div class="answer-text"><?php echo htmlspecialchars($question['answerA']); ?></div>
              </div>
              
              <div class="answer-option <?php echo $question['correctAnswer'] === 'B' ? 'correct' : ''; ?>">
                <div class="answer-label">B</div>
                <div class="answer-text"><?php echo htmlspecialchars($question['answerB']); ?></div>
              </div>
              
              <div class="answer-option <?php echo $question['correctAnswer'] === 'C' ? 'correct' : ''; ?>">
                <div class="answer-label">C</div>
                <div class="answer-text"><?php echo htmlspecialchars($question['answerC']); ?></div>
              </div>
              
              <div class="answer-option <?php echo $question['correctAnswer'] === 'D' ? 'correct' : ''; ?>">
                <div class="answer-label">D</div>
                <div class="answer-text"><?php echo htmlspecialchars($question['answerD']); ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </main>

  <footer class="footer">
    <img src="Image/Logo3.png" alt="FitMind Logo" class="footer-logo">
    <p class="footer-text">Contact: support@fitmind.com | (555) 123-4567</p>
    <p class="footer-text">&copy; 2025 FitMind. All rights reserved.</p>
    <a href="#" class="footer-link">Privacy Policy</a> |
    <a href="#" class="footer-link">Terms of Service</a>
  </footer>

  <script>
  $(document).ready(function() {
      // AJAX delete functionality
      $('.delete-question-btn').on('click', function(e) {
          e.preventDefault();
          
          const questionID = $(this).data('question-id');
          const quizID = $(this).data('quiz-id');
          const questionCard = $(this).closest('.question-card');
          
          if (confirm('Are you sure you want to delete this question?')) {
              // Show loading state
              const deleteBtn = $(this);
              deleteBtn.html('⏳ Deleting...').prop('disabled', true);
              
              // AJAX request
              $.ajax({
                  url: 'ajax_delete_question.php',
                  type: 'POST',
                  data: {
                      questionID: questionID,
                      quizID: quizID
                  },
                  dataType: 'json',
                  success: function(response) {
                      if (response.success === true) {
                          // Remove the question row from HTML table
                          questionCard.fadeOut(300, function() {
                              $(this).remove();
                              showSuccessMessage('Question deleted successfully');
                              
                              // Update question count
                              updateQuestionCount();
                          });
                      } else {
                          showErrorMessage('Failed to delete question: ' + response.message);
                          deleteBtn.html('🗑️ Delete').prop('disabled', false);
                      }
                  },
                  error: function(xhr, status, error) {
                      showErrorMessage('Error deleting question: ' + error);
                      deleteBtn.html('🗑️ Delete').prop('disabled', false);
                  }
              });
          }
      });
      
      function showSuccessMessage(message) {
          // Remove any existing messages
          $('.ajax-message').remove();
          
          // Create success message
          const successMsg = $('<div class="alert-success ajax-message">✅ ' + message + '</div>');
          $('.page').prepend(successMsg);
          
          // Auto-hide after 5 seconds
          setTimeout(function() {
              successMsg.fadeOut(300, function() {
                  $(this).remove();
              });
          }, 5000);
      }
      
      function showErrorMessage(message) {
          // Remove any existing messages
          $('.ajax-message').remove();
          
          // Create error message
          const errorMsg = $('<div class="alert-error ajax-message">❌ ' + message + '</div>');
          $('.page').prepend(errorMsg);
          
          // Auto-hide after 5 seconds
          setTimeout(function() {
              errorMsg.fadeOut(300, function() {
                  $(this).remove();
              });
          }, 5000);
      }
      
      function updateQuestionCount() {
          const questionCount = $('.question-card').length;
          $('.section-title span').text('Total: ' + questionCount + ' questions');
          
          // Update quiz meta if needed
          $('.quiz-meta strong:contains("Questions")').parent().html(
              '<strong>Questions:</strong> ' + questionCount
          );
      }
  });
  </script>

</body>
</html>
<?php $conn->close(); ?>