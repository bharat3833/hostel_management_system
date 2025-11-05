<?php
// Attendance Management Backend Handler
include '_dbconnect.php';
session_start();

// Check if admin is logged in
if(!isset($_SESSION['adminloggedin']) || $_SESSION['adminloggedin'] != true) {
    header("location: /hostel-management-system/login.php");
    exit;
}

// Handle delete single record request
if(isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    $delete_sql = "DELETE FROM student_attendance WHERE id = '$delete_id'";
    
    if(mysqli_query($conn, $delete_sql)) {
        $_SESSION['success_msg'] = "Attendance record deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting record: " . mysqli_error($conn);
    }
    
    header("location: /hostel-management-system/index.php?page=attendanceManage");
    exit;
}

// Handle clear all history request
if(isset($_GET['clear_all']) && $_GET['clear_all'] == '1') {
    $clear_sql = "TRUNCATE TABLE student_attendance";
    
    if(mysqli_query($conn, $clear_sql)) {
        $_SESSION['success_msg'] = "All attendance history cleared successfully!";
    } else {
        // If TRUNCATE fails (due to foreign keys), try DELETE
        $clear_sql = "DELETE FROM student_attendance";
        if(mysqli_query($conn, $clear_sql)) {
            $_SESSION['success_msg'] = "All attendance history cleared successfully!";
        } else {
            $_SESSION['error_msg'] = "Error clearing history: " . mysqli_error($conn);
        }
    }
    
    header("location: /hostel-management-system/index.php?page=attendanceManage");
    exit;
}

// If no valid action, redirect back
header("location: /hostel-management-system/index.php?page=attendanceManage");
exit;
?>
