<?php
include 'partials/_dbconnect.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $requester_reg_no = mysqli_real_escape_string($conn, $_POST['requester_reg_no']);
    $requested_reg_no = mysqli_real_escape_string($conn, $_POST['requested_reg_no']);
    $message = isset($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : '';
    
    // Check if request already exists
    $check_sql = "SELECT id FROM roommate_requests 
                  WHERE requester_reg_no = '$requester_reg_no' 
                  AND requested_reg_no = '$requested_reg_no'
                  AND status != 'rejected'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'You have already sent a request to this student.'
        ]);
        exit;
    }
    
    // Insert new request
    $insert_sql = "INSERT INTO roommate_requests (requester_reg_no, requested_reg_no, message) 
                   VALUES ('$requester_reg_no', '$requested_reg_no', '$message')";
    $insert_result = mysqli_query($conn, $insert_sql);
    
    if($insert_result) {
        // Get names for notification
        $names_sql = "SELECT 
                        (SELECT CONCAT(first_name, ' ', last_name) FROM userregistration WHERE registration_no = '$requester_reg_no') as requester_name,
                        (SELECT CONCAT(first_name, ' ', last_name) FROM userregistration WHERE registration_no = '$requested_reg_no') as requested_name";
        $names_result = mysqli_query($conn, $names_sql);
        $names = mysqli_fetch_assoc($names_result);
        
        echo json_encode([
            'success' => true,
            'message' => 'Roommate request sent successfully to ' . $names['requested_name'] . '!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error sending request: ' . mysqli_error($conn)
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>