<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '_dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Debugging (optional - can be commented out later)
    // echo "Handler reached<br>";
    // echo '<pre>'; print_r($_POST); echo '</pre>';

    /* =====================================================
       DELETE USER
    ===================================================== */
    if (isset($_POST['removeUser'])) {
        $Id = $_POST["Id"];
        $sql = "DELETE FROM `userregistration` WHERE `id`='$Id'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            echo "<script>
                    alert('User removed successfully');
                    window.location.href='/hostel-management-system/index.php?page=userManage';
                  </script>";
        } else {
            echo "<script>
                    alert('Failed to remove user');
                    window.location.href='/hostel-management-system/index.php?page=userManage';
                  </script>";
        }
    }

    /* =====================================================
       CREATE NEW USER
    ===================================================== */
    if (isset($_POST['createUser'])) {
        $regno = $_POST["registration"];
        $firstName = $_POST["firstName"];
        $lastName = $_POST["lastName"];
        $email = $_POST["email"];
        $phone = $_POST["phone"];
        $gender = $_POST["gender"];

        $sql = "INSERT INTO `userregistration` 
                (`registration_no`, `first_name`, `last_name`, `emailid`, `contact_no`, `gender`) 
                VALUES ('$regno', '$firstName', '$lastName', '$email', '$phone', '$gender')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            echo "<script>
                    alert('Registration successful.');
                    window.location=document.referrer;
                  </script>";
        } else {
            echo "<script>
                    alert('Failed to register new user.');
                    window.location=document.referrer;
                  </script>";
        }
    }

    /* =====================================================
       EDIT USER DETAILS
    ===================================================== */
    if (isset($_POST['editUser'])) {
        // 🔧 FIXED: Use 'userId' (matches your form hidden input)
        $id = $_POST['userId'];
        $firstname = $_POST['firstName'];
        $lastname = $_POST['lastName'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $gender = $_POST['gender'];

        $sql = "UPDATE `userregistration` 
                SET `first_name`='$firstname', 
                    `last_name`='$lastname', 
                    `emailid`='$email', 
                    `contact_no`='$phone', 
                    `gender`='$gender' 
                WHERE `id`='$id'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            echo "<script>
                    alert('User updated successfully.');
                    window.location=document.referrer;
                  </script>";
        } else {
            echo "<script>
                    alert('Update failed: " . mysqli_error($conn) . "');
                    window.location=document.referrer;
                  </script>";
        }
    }

    /* =====================================================
       UPDATE PROFILE PHOTO
    ===================================================== */
    if (isset($_POST['updateProfilePhoto'])) {
        $id = $_POST["userId"];
        $check = getimagesize($_FILES["userimage"]["tmp_name"]);
        if ($check !== false) {
            $newfilename = "person-" . $id . ".jpg";
            $uploaddir = $_SERVER['DOCUMENT_ROOT'] . '/hostel-management-system/img/';
            $uploadfile = $uploaddir . $newfilename;

            if (move_uploaded_file($_FILES['userimage']['tmp_name'], $uploadfile)) {
                echo "<script>
                        alert('Profile photo updated successfully.');
                        window.location=document.referrer;
                      </script>";
            } else {
                echo "<script>
                        alert('Failed to upload image.');
                        window.location=document.referrer;
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Please select a valid .jpg image file.');
                    window.location=document.referrer;
                  </script>";
        }
    }

    /* =====================================================
       REMOVE PROFILE PHOTO
    ===================================================== */
    if (isset($_POST['removeProfilePhoto'])) {
        $id = $_POST["userId"];
        $filename = $_SERVER['DOCUMENT_ROOT'] . "/hostel-management-system/img/person-" . $id . ".jpg";

        if (file_exists($filename)) {
            unlink($filename);
            echo "<script>
                    alert('Profile photo removed.');
                    window.location=document.referrer;
                  </script>";
        } else {
            echo "<script>
                    alert('No photo found to remove.');
                    window.location=document.referrer;
                  </script>";
        }
    }
}
?>
