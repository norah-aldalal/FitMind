<?php
include 'db_connection.php';

session_start();



if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, password, userType FROM User WHERE emailAddress = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                //  Store user info in session
                $_SESSION['userID'] = $user['id'];
                $_SESSION['userType'] = $user['userType'];

     // Redirect based on type
if ($user['userType'] === 'learner') {
    header("Location: learner_homepage.php");
    exit();
} elseif ($user['userType'] === 'educator') {
    header("Location: educator_homepage.php");
    exit();
} else {
    // If userType is unexpected, redirect back with an error
    header("Location: Log-in.php?error=" . urlencode("Invalid user type."));
    exit();
}
}
}

// Invalid credentials → redirect back to login page with message
header("Location: Log-in.php?error=" . urlencode("Invalid email or password."));
exit();

} else {
    header("Location: Log-in.php?error=" . urlencode("Please fill in both fields."));
    exit();
}
} else {
    // If accessed directly, return to login page
    header("Location: Log-in.php");
    exit();
}
