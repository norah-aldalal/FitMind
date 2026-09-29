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
$successMessage = "";
$errorMessage = "";

// Verify quiz belongs to educator
$verifyStmt = $conn->prepare("SELECT id FROM Quiz WHERE id = ? AND educatorID = ?");
$verifyStmt->bind_param("ii", $quizID, $_SESSION['userID']);
$verifyStmt->execute();
$verifyResult = $verifyStmt->get_result();

if ($verifyResult->num_rows === 0) {
    die("Quiz not found or access denied");
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Question</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .form-card { 
      max-width: 800px; 
      margin: 20px auto; 
      background: #fff;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .form-row { 
      margin: 16px 0; 
    }
    
    label { 
      display: block; 
      margin-bottom: 8px; 
      font-weight: 600; 
      color: #14532d; 
      font-size: 15px;
    }
    
    input, textarea, select { 
      width: 100%; 
      padding: 10px 12px;
      border: 2px solid #000;
      border-radius: 6px;
      font-size: 15px;
      font-family: inherit;
    }
    
    textarea {
      min-height: 100px;
      resize: vertical;
    }
    
    input[type="file"] {
      padding: 8px;
      border: 2px dashed #d1d5db;
    }
    
    .hint { 
      color: #6b7280; 
      font-size: 13px; 
      margin-top: 5px;
    }
    
    .preview-container {
      margin: 10px 0;
    }
    
    .preview-image {
      max-width: 300px;
      max-height: 200px;
      border: 2px solid #22c55e;
      border-radius: 8px;
      padding: 5px;
      background: #f0fdf4;
    }
    
    .preview-placeholder {
      width: 300px;
      height: 150px;
      border: 2px dashed #d1d5db;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6b7280;
      background: #f9fafb;
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
    
    .form-actions {
      display: flex;
      gap: 12px;
      margin-top: 25px;
      padding-top: 20px;
      border-top: 1px solid #e5e7eb;
    }
    
    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 14px;
    }
    
    .btn-green {
      background: #22c55e;
      color: white;
    }
    
    .btn-green:hover {
      background: #16a34a;
    }
    
    .btn-red {
      background: #ef4444;
      color: white;
    }
    
    .btn-red:hover {
      background: #dc2626;
    }
    
    h1 {
      text-align: center;
      color: #14532d;
      margin: 25px 0;
    }
  </style>
</head>
<body>

  <header class="header">
    <img class="logo" src="Image/Logo3.png" alt="FitMind Logo">
    <h1 class="header-title">FitMind</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </header>

  <main>
    <h1>Add New Question</h1>

    <section class="form-card">
      <?php if ($successMessage): ?>
        <div class="alert-success">
          ✅ <?php echo $successMessage; ?>
          <a href="quiz.php?quizID=<?php echo $quizID; ?>" style="float:right; color:#065f46; font-weight:600;">← Back to Quiz</a>
        </div>
      <?php endif; ?>

      <?php if ($errorMessage): ?>
        <div class="alert-error">❌ <?php echo $errorMessage; ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" action="process_add_question.php">
        <input type="hidden" name="quizID" value="<?php echo $quizID; ?>">
        
        <div class="form-row">
          <label for="question">Question Text:</label>
          <textarea id="question" name="question" required placeholder="Enter your question here..."><?php echo isset($_POST['question']) ? htmlspecialchars($_POST['question']) : ''; ?></textarea>
        </div>

        <div class="form-row">
          <label for="questionImage">Question Image (Optional):</label>
          <input type="file" id="questionImage" name="questionImage" accept="image/*" onchange="previewImage(this)">
          <div class="hint">Supported formats: JPEG, PNG, GIF, WEBP</div>
        </div>

        <div class="form-row">
          <label>Image Preview:</label>
          <div class="preview-container">
            <div class="preview-placeholder" id="imagePreviewPlaceholder">
              No image selected
            </div>
            <img id="imagePreview" class="preview-image" style="display:none;" alt="Image preview">
          </div>
        </div>

        <div class="form-row">
          <label for="answerA">Answer A:</label>
          <input type="text" id="answerA" name="answerA" required 
                 placeholder="Enter answer A" 
                 value="<?php echo isset($_POST['answerA']) ? htmlspecialchars($_POST['answerA']) : ''; ?>">
        </div>

        <div class="form-row">
          <label for="answerB">Answer B:</label>
          <input type="text" id="answerB" name="answerB" required 
                 placeholder="Enter answer B" 
                 value="<?php echo isset($_POST['answerB']) ? htmlspecialchars($_POST['answerB']) : ''; ?>">
        </div>

        <div class="form-row">
          <label for="answerC">Answer C:</label>
          <input type="text" id="answerC" name="answerC" required 
                 placeholder="Enter answer C" 
                 value="<?php echo isset($_POST['answerC']) ? htmlspecialchars($_POST['answerC']) : ''; ?>">
        </div>

        <div class="form-row">
          <label for="answerD">Answer D:</label>
          <input type="text" id="answerD" name="answerD" required 
                 placeholder="Enter answer D" 
                 value="<?php echo isset($_POST['answerD']) ? htmlspecialchars($_POST['answerD']) : ''; ?>">
        </div>

        <div class="form-row">
          <label for="correctAnswer">Correct Answer:</label>
          <select id="correctAnswer" name="correctAnswer" required>
            <option value="">Select the correct answer</option>
            <option value="A" <?php echo (isset($_POST['correctAnswer']) && $_POST['correctAnswer'] == 'A') ? 'selected' : ''; ?>>Answer A</option>
            <option value="B" <?php echo (isset($_POST['correctAnswer']) && $_POST['correctAnswer'] == 'B') ? 'selected' : ''; ?>>Answer B</option>
            <option value="C" <?php echo (isset($_POST['correctAnswer']) && $_POST['correctAnswer'] == 'C') ? 'selected' : ''; ?>>Answer C</option>
            <option value="D" <?php echo (isset($_POST['correctAnswer']) && $_POST['correctAnswer'] == 'D') ? 'selected' : ''; ?>>Answer D</option>
          </select>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-green">➕ Add Question</button>
          <a href="quiz.php?quizID=<?php echo $quizID; ?>" class="btn btn-red">❌ Cancel</a>
        </div>
      </form>
    </section>
  </main>

  <script>
  function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePreviewPlaceholder');
    
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
      }
      
      reader.readAsDataURL(input.files[0]);
    } else {
      preview.style.display = 'none';
      placeholder.style.display = 'block';
    }
  }
  </script>

  <footer class="footer">
    <img src="Image/Logo3.png" alt="FitMind Logo" class="footer-logo">
    <p class="footer-text">Contact: support@fitmind.com | (555) 123-4567</p>
    <p class="footer-text">&copy; 2025 FitMind. All rights reserved.</p>
    <a href="#" class="footer-link">Privacy Policy</a> |
    <a href="#" class="footer-link">Terms of Service</a>
  </footer>

</body>
</html>