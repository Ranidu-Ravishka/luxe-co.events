<?php
session_start();
require_once('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['testimonial_error'] = "You must be logged in to submit a testimonial.";
    header("Location: index.php#testimonials");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (empty($_POST['content']) || empty($_POST['rating'])) {
        $_SESSION['testimonial_error'] = "Please fill in all required fields.";
        header("Location: index.php#testimonials");
        exit();
    }

    // Sanitize input
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $rating = intval($_POST['rating']);
    $user_id = $_SESSION['user_id'];

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $_SESSION['testimonial_error'] = "Invalid rating value.";
        header("Location: index.php#testimonials");
        exit();
    }

    // Insert testimonial
    $query = "INSERT INTO testimonials (user_id, content, rating, status, created_at) 
              VALUES (?, ?, ?, 'pending', NOW())";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isi", $user_id, $content, $rating);

    if ($stmt->execute()) {
        $_SESSION['testimonial_success'] = "Thank you for your testimonial! It will be reviewed and published soon.";
    } else {
        $_SESSION['testimonial_error'] = "Error submitting testimonial. Please try again.";
    }

    $stmt->close();
} else {
    $_SESSION['testimonial_error'] = "Invalid request method.";
}

// Redirect back to testimonials section
header("Location: index.php#testimonials");
exit();
?> 