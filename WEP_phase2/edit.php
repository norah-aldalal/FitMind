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

// Get question ID from URL
$questionID = isset($_GET['qid']) ? intval($_GET['qid']) : 0;
$successMessage = "";
$errorMessage = "";

// Get question data
$questionQuery = "SELECT * FROM QuizQuestion WHERE id = ?";
$stmt = $conn->prepare($questionQuery);
$stmt->bind_param("i", $questionID);
$stmt->execute();
$questionResult = $stmt->get_result();
$question = $questionResult->fetch_assoc();

if (!$question) {
    die("Question not found");
}

$quizID = $question['quizID'];

// Verify the question belongs to the current educator
$verifyStmt = $conn->prepare("SELECT q.id FROM Quiz q WHERE q.id = ? AND q.educatorID = ?");
$verifyStmt->bind_param("ii", $quizID, $_SESSION['userID']);
$verifyStmt->execute();
$verifyResult = $verifyStmt->get_result();

if ($verifyResult->num_rows === 0) {
    die("Access denied - you don't own this question");
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Question • FitMind</title>
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
    
    .btn-sm {
      padding: 6px 12px;
      font-size: 13px;
    }
    
    h1 {
      text-align: center;
      color: #14532d;
      margin: 25px 0;
    }
    
    .current-image {
      margin: 10px 0;
    }
    
    .image-actions {
      margin: 10px 0;
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
    <h1>Edit Question</h1>

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

      <form method="POST" enctype="multipart/form-data" action="process_edit_question.php">
        <input type="hidden" name="questionID" value="<?php echo $questionID; ?>">
        <input type="hidden" name="quizID" value="<?php echo $quizID; ?>">
        
        <div class="form-row">
          <label>Current Image:</label>
          <div class="current-image">
            <?php if (!empty($question['questionFigureFileName'])): ?>
              <img id="currentImage" alt="Current Question Image" class="preview-image" 
                   src="uploads/<?php echo htmlspecialchars($question['questionFigureFileName']); ?>">
              <div class="image-actions">
                <button type="button" class="btn btn-red btn-sm" onclick="removeImage()">
                  🗑️ Remove Current Image
                </button>
                <input type="hidden" name="remove_image" id="removeImageInput" value="0">
              </div>
            <?php else: ?>
              <div class="preview-placeholder">No image uploaded</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-row">
          <label>Change Image:</label>
          <input type="file" id="newImage" name="newImage" accept="image/*" onchange="previewNewImage(this)">
          <div class="hint">Select a new image to replace the current one</div>
        </div>

        <div class="form-row">
          <label>New Image Preview:</label>
          <div class="preview-container">
            <div class="preview-placeholder" id="newImagePreviewPlaceholder">
              No new image selected
            </div>
            <img id="newImagePreview" class="preview-image" style="display:none;" alt="New image preview">
          </div>
        </div>

        <div class="form-row">
          <label>Question:</label>
          <textarea id="question" name="question" rows="3" required><?php echo htmlspecialchars($question['question']); ?></textarea>
        </div>

        <div class="form-row">
          <label>Answer A:</label>
          <input type="text" id="ansA" name="answerA" value="<?php echo htmlspecialchars($question['answerA']); ?>" required>
        </div>

        <div class="form-row">
          <label>Answer B:</label>
          <input type="text" id="ansB" name="answerB" value="<?php echo htmlspecialchars($question['answerB']); ?>" required>
        </div>

        <div class="form-row">
          <label>Answer C:</label>
          <input type="text" id="ansC" name="answerC" value="<?php echo htmlspecialchars($question['answerC']); ?>" required>
        </div>

        <div class="form-row">
          <label>Answer D:</label>
          <input type="text" id="ansD" name="answerD" value="<?php echo htmlspecialchars($question['answerD']); ?>" required>
        </div>

        <div class="form-row">
          <label>Correct Answer:</label>
          <select id="correct" name="correctAnswer" required>
            <option value="A" <?php echo $question['correctAnswer'] == 'A' ? 'selected' : ''; ?>>A</option>
            <option value="B" <?php echo $question['correctAnswer'] == 'B' ? 'selected' : ''; ?>>B</option>
            <option value="C" <?php echo $question['correctAnswer'] == 'C' ? 'selected' : ''; ?>>C</option>
            <option value="D" <?php echo $question['correctAnswer'] == 'D' ? 'selected' : ''; ?>>D</option>
          </select>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-green">💾 Save Changes</button>
          <a href="quiz.php?quizID=<?php echo $quizID; ?>" class="btn btn-red">❌ Cancel</a>
        </div>
      </form>
    </section>
  </main>

  <script>
  function previewNewImage(input) {
    const preview = document.getElementById('newImagePreview');
    const placeholder = document.getElementById('newImagePreviewPlaceholder');
    
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

  function removeImage() {
    if (confirm('Are you sure you want to remove the current image?')) {
      document.getElementById('removeImageInput').value = '1';
      const currentImage = document.getElementById('currentImage');
      if (currentImage) {
        currentImage.style.opacity = '0.5';
        currentImage.style.borderColor = '#ef4444';
      }
      alert('Image will be removed when you save changes.');
    }
  }

  // Preview new image when selected
  document.getElementById("newImage").addEventListener("change", function(e) {
    previewNewImage(this);
  });
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