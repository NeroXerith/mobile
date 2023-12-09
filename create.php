<?php
include('config.php');
$current = date('d-M-y');

if (
    isset($_POST['Fname']) && !empty($_POST['Fname']) &&
    isset($_POST['Mname']) && !empty($_POST['Mname']) &&
    isset($_POST['Lname']) && !empty($_POST['Lname']) &&
    isset($_POST['Email']) && !empty($_POST['Email']) &&
    isset($_POST['Contact']) && !empty($_POST['Contact']) &&
    isset($_POST['password']) && !empty($_POST['password']) &&
    isset($_POST['Age']) && !empty($_POST['Age'])
) {
    // Function to check for consecutive spaces
    function hasConsecutiveSpaces($str)
    {
        return strpos($str, '  ') !== false;
    }
    // Function to validate password
    function validatePassword($password)
    {
        // Password should be at least 8 characters long
        // and contain at least one number and one special character
        return strlen($password) >= 8 && preg_match('/[0-9]/', $password) && preg_match('/[^A-Za-z0-9]/', $password);
    }

    // Function to validate Age without decimal or comma
    function validateAge($age)
    {
        return ctype_digit($age); // Check if the input contains only digits (no decimal or comma)
    }

    // Function to check for leading space
    function hasLeadingSpace($str)
    {
        return substr($str, 0, 1) === ' ';
    }

    // Function to validate Email format
    function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Check password validation
    if (!validatePassword($_POST['password'])) {
        echo "Password should be at least 8 characters long and contain at least one number and one special character";
    } elseif (!validateAge($_POST['Age'])) {
        echo "Invalid Age format";
    } elseif (!validateEmail($_POST['Email'])) {
        echo "Invalid email format";
    } else {
        $Email = $_POST['Email'];

        // Check if the email already exists in the database
        $emailCheckQuery = "SELECT * FROM tbl_users WHERE user_email = '$Email'";
        $result = $conn->query($emailCheckQuery);

        if ($result->num_rows > 0) {
            echo "Email already exists, please use a different email";
        } else {
            // Check for consecutive spaces and leading spaces in each field
            $fields = ['Fname', 'Mname', 'Lname', 'Email', 'Contact', 'Age'];
            $error = false;

            foreach ($fields as $field) {
                if (hasLeadingSpace($_POST[$field]) || hasConsecutiveSpaces($_POST[$field])) {
                    switch ($field) {
                        case "Fname":
                            $field = "First Name";
                            break;
                        case "Mname":
                            $field = "Middle Name";
                            break;
                        case "Lname":
                            $field = "Last Name";
                            break;
                    }
                    echo "Leading space or consecutive spaces are not allowed in $field";
                    $error = true;
                    break;
                }
            }

            if (!$error) {
                $Fname = $_POST['Fname'];
                $Mname = $_POST['Mname'];
                $Lname = $_POST['Lname'];
                $Phone = $_POST['Contact'];
                $Password = $_POST['password']; // Plain password

                // Hash the password using BCRYPT
                $hashedPassword = password_hash($Password, PASSWORD_BCRYPT);

                $Age = $_POST['Age'];

                $sql = "INSERT INTO users (full_name, user_email, user_password, contact, age) VALUES ('$Fname $Mname $Lname', '$Email', '$hashedPassword','$Phone', '$Age')";

                if ($conn->query($sql)) {
                    echo "Success!";
                } else {
                    echo $conn->error;
                }
            }
        }
    }
} else {
    echo "Fill out all the fields!";
}
?>
