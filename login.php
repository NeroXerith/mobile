<?php
include('config.php');

if (isset($_POST['user_email']) && isset($_POST['user_password'])) {
    $u = trim($_POST['user_email']);
    $sql = "SELECT id, user_email, user_password FROM tbl_users WHERE user_email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $u);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashed_password = $row['user_password'];

            // Verify the entered password against the retrieved hash
            if (password_verify($_POST['user_password'], $hashed_password)) {
               echo "Success!"; // Password is correct
            } else {
                echo "Invalid credentials!"; // Password is incorrect
            }
        } else {
            echo "User does not exist!"; // No user found with the provided email
        }
    } else {
        echo $conn->error; 
    }
} else {
    echo "Invalid request";
}
?>
