<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Please log in to submit your wedding plan.";
        header("Location: /WeddinPlaning/includes/login & registration.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $bride_name = $_POST['brideName'];
    $groom_name = $_POST['groomName'];
    $bride_age = $_POST['brideAge'];
    $groom_age = $_POST['groomAge'];
    $wedding_date = $_POST['weddingDate'];
    $venue_preference = $_POST['venuePreference'];
    $guest_count = $_POST['guestCount'];
    $guest_considerations = $_POST['guestConsiderations'];
    $budget_range = $_POST['budgetRange'];
    $additional_notes = $_POST['additionalNotes'];

    // Handle guest list file upload
    $guest_list_file = '';
    if (isset($_FILES['guestList']) && $_FILES['guestList']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/guest_lists/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['guestList']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['xlsx', 'xls', 'csv'];

        if (in_array($file_extension, $allowed_extensions)) {
            $unique_filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $unique_filename;

            if (move_uploaded_file($_FILES['guestList']['tmp_name'], $target_file)) {
                $guest_list_file = 'uploads/guest_lists/' . $unique_filename;
            }
        }
    }

    // Handle invitation files upload
    $invitation_files = [];
    if (isset($_FILES['invitationFiles'])) {
        $upload_dir = __DIR__ . '/../uploads/invitations/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($_FILES['invitationFiles']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['invitationFiles']['error'][$key] === UPLOAD_ERR_OK) {
                $file_extension = strtolower(pathinfo($_FILES['invitationFiles']['name'][$key], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

                if (in_array($file_extension, $allowed_extensions)) {
                    $unique_filename = uniqid() . '.' . $file_extension;
                    $target_file = $upload_dir . $unique_filename;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $invitation_files[] = 'uploads/invitations/' . $unique_filename;
                    }
                }
            }
        }
    }

    $invitation_files_json = json_encode($invitation_files);

    // Insert data into database
    $sql = "INSERT INTO wedding_plans (user_id, bride_name, groom_name, bride_age, groom_age, wedding_date, 
            venue_preference, guest_count, guest_list_file, guest_considerations, budget_range, 
            invitation_files, additional_notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issiissssssss", 
        $user_id, $bride_name, $groom_name, $bride_age, $groom_age, $wedding_date,
        $venue_preference, $guest_count, $guest_list_file, $guest_considerations, 
        $budget_range, $invitation_files_json, $additional_notes
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Your wedding plan has been submitted successfully! We'll contact you soon to discuss the details.";
        // Close the modal
        $_SESSION['close_modal'] = true;
    } else {
        $_SESSION['error'] = "Error submitting your wedding plan. Please try again.";
    }

    $stmt->close();
    $conn->close();

    // Redirect to home page
    header("Location: /WeddinPlaning/pages/index.php");
    exit();
}
?> 