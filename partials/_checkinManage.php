<?php
// Check-in/Check-out Management Backend Handler
include '_dbconnect.php';
session_start();

// Check if admin is logged in
if(!isset($_SESSION['adminloggedin']) || $_SESSION['adminloggedin'] != true) {
    header("location: /hostel-management-system/login.php");
    exit;
}

// Handle form submission for new entry
if($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['delete_id'])) {
    $reg_no = mysqli_real_escape_string($conn, $_POST['reg_no']);
    $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $room_no = mysqli_real_escape_string($conn, $_POST['room_no']);
    $pass_type = mysqli_real_escape_string($conn, $_POST['pass_type']);
    $action_type = mysqli_real_escape_string($conn, $_POST['action_type']);
    $action_date = mysqli_real_escape_string($conn, $_POST['action_date']);
    $action_time = mysqli_real_escape_string($conn, $_POST['action_time']);
    $expected_return_date = !empty($_POST['expected_return_date']) ? mysqli_real_escape_string($conn, $_POST['expected_return_date']) : NULL;
    $expected_return_time = !empty($_POST['expected_return_time']) ? mysqli_real_escape_string($conn, $_POST['expected_return_time']) : NULL;
    $destination = !empty($_POST['destination']) ? mysqli_real_escape_string($conn, $_POST['destination']) : NULL;
    $purpose = !empty($_POST['purpose']) ? mysqli_real_escape_string($conn, $_POST['purpose']) : NULL;
    $contact_during_leave = !empty($_POST['contact_during_leave']) ? mysqli_real_escape_string($conn, $_POST['contact_during_leave']) : NULL;
    $remarks = !empty($_POST['remarks']) ? mysqli_real_escape_string($conn, $_POST['remarks']) : NULL;
    $recorded_by = $_SESSION['adminuserId'];
    
    // Validate required fields
    if(empty($reg_no) || empty($student_name) || empty($pass_type) || empty($action_type) || empty($action_date) || empty($action_time)) {
        $_SESSION['error_msg'] = "All required fields must be filled!";
        header("location: /hostel-management-system/index.php?page=checkinManage");
        exit;
    }
    
    // Check last action for this student and pass type
    $last_action_sql = "SELECT action_type, action_date, action_time FROM hostel_checkin_checkout 
                        WHERE reg_no = '$reg_no' AND pass_type = '$pass_type' 
                        ORDER BY action_date DESC, action_time DESC LIMIT 1";
    $last_action_result = mysqli_query($conn, $last_action_sql);
    
    if(mysqli_num_rows($last_action_result) > 0) {
        $last_action = mysqli_fetch_assoc($last_action_result);
        $last_action_type = $last_action['action_type'];
        
        // Validation: Cannot check-out if already checked out
        if($action_type == 'check-out' && $last_action_type == 'check-out') {
            $pass_label = $pass_type == 'gate-pass' ? 'Gate Pass' : 'Vacation';
            $_SESSION['error_msg'] = "Cannot check-out! Student is already checked out for $pass_label. Please check-in first.";
            header("location: /hostel-management-system/index.php?page=checkinManage");
            exit;
        }
        
        // Validation: Cannot check-in if already checked in
        if($action_type == 'check-in' && $last_action_type == 'check-in') {
            $pass_label = $pass_type == 'gate-pass' ? 'Gate Pass' : 'Vacation';
            $_SESSION['error_msg'] = "Cannot check-in! Student is already in the hostel for $pass_label. Please check-out first.";
            header("location: /hostel-management-system/index.php?page=checkinManage");
            exit;
        }
    }
    
    // Insert into database
    $insert_sql = "INSERT INTO hostel_checkin_checkout 
                   (reg_no, student_name, room_no, pass_type, action_type, action_date, action_time, 
                    expected_return_date, expected_return_time, destination, purpose, contact_during_leave, remarks, recorded_by) 
                   VALUES ('$reg_no', '$student_name', ".($room_no ? "'$room_no'" : "NULL").", '$pass_type', '$action_type', 
                           '$action_date', '$action_time', ".($expected_return_date ? "'$expected_return_date'" : "NULL").", 
                           ".($expected_return_time ? "'$expected_return_time'" : "NULL").", ".($destination ? "'$destination'" : "NULL").", 
                           ".($purpose ? "'$purpose'" : "NULL").", ".($contact_during_leave ? "'$contact_during_leave'" : "NULL").", 
                           ".($remarks ? "'$remarks'" : "NULL").", '$recorded_by')";
    
    if(mysqli_query($conn, $insert_sql)) {
        $pass_label = $pass_type == 'gate-pass' ? 'Gate Pass' : 'Vacation';
        $action_label = $action_type == 'check-in' ? 'Return' : 'Exit';
        $_SESSION['success_msg'] = "$pass_label $action_label recorded successfully for $student_name ($reg_no)!";
    } else {
        $_SESSION['error_msg'] = "Error recording entry: " . mysqli_error($conn);
    }
    
    header("location: /hostel-management-system/index.php?page=checkinManage");
    exit;
}

// Handle delete request
if(isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    $delete_sql = "DELETE FROM hostel_checkin_checkout WHERE id = '$delete_id'";
    
    if(mysqli_query($conn, $delete_sql)) {
        $_SESSION['success_msg'] = "Record deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting record: " . mysqli_error($conn);
    }
    
    header("location: /hostel-management-system/index.php?page=checkinManage");
    exit;
}

// If no valid action, redirect back
header("location: /hostel-management-system/index.php?page=checkinManage");
exit;
?>
