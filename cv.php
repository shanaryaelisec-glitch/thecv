<?php
session_start();

// Configuration
$uploadDir = "uploads/";
$allowedImageTypes = ['jpg','jpeg','png','gif'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

if(!file_exists($uploadDir)){
    mkdir($uploadDir, 0755, true);
}

$imagePath = "";
$errorMessage = "";

if(isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK){
    $fileName = $_FILES['profile']['name'];
    $tempName = $_FILES['profile']['tmp_name'];
    $fileSize = $_FILES['profile']['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if(!in_array($fileExt, $allowedImageTypes)){
        $errorMessage = "Invalid file type. Only JPG, PNG, GIF allowed.";
    }
    elseif($fileSize > $maxFileSize){
        $errorMessage = "File too large. Max 5MB.";
    }
    else{
        $newFileName = uniqid() . '.' . $fileExt;
        $imagePath = $uploadDir . $newFileName;

        if(!move_uploaded_file($tempName, $imagePath)){
            $errorMessage = "Upload failed. Please try again.";
        }
    }
}

if($errorMessage){
    // Store error in session and redirect back
    $_SESSION['upload_error'] = $errorMessage;
    header('Location: index.html');
    exit;
}

function sanitize($data){
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

$name = sanitize($_POST['name'] ?? '');
$age = sanitize($_POST['age'] ?? '');
$gender = sanitize($_POST['gender'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$program = sanitize($_POST['program'] ?? '');
$education = sanitize($_POST['education'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$facebook = sanitize($_POST['facebook'] ?? '');
$skills = sanitize($_POST['skills'] ?? '');
$languages = sanitize($_POST['languages'] ?? '');

// Validate required fields
if(empty($name) || empty($age) || empty($gender) || empty($address) || empty($program) || empty($education) || empty($phone) || empty($email)){
    $_SESSION['form_error'] = "Please fill all required fields.";
    header('Location: index.html');
    exit;
}

// Store CV data in session with unique ID
$cvId = uniqid('cv_');
$_SESSION['current_cv_id'] = $cvId;
$_SESSION['cv_data'][$cvId] = [
    'name' => $name,
    'age' => $age,
    'gender' => $gender,
    'address' => $address,
    'program' => $program,
    'education' => $education,
    'phone' => $phone,
    'email' => $email,
    'facebook' => $facebook,
    'skills' => $skills,
    'languages' => $languages,
    'imagePath' => $imagePath
];

// Set session timeout (24 hours)
$_SESSION['cv_data'][$cvId]['expires'] = time() + (24 * 60 * 60);

// Redirect to shareable output page
header("Location: output.php?cv=" . $cvId);
exit;
?>