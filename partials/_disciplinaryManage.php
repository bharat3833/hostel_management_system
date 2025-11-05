<?php
session_start();
require '../partials/_dbconnect.php';

// Add new disciplinary record
if(isset($_POST['action']) && $_POST['action'] == 'add') {
    $student_reg_no = mysqli_real_escape_string($conn, $_POST['student_reg_no']);
    $incident_date = mysqli_real_escape_string($conn, $_POST['incident_date']);
    $incident_time = mysqli_real_escape_string($conn, $_POST['incident_time']);
    $incident_type = mysqli_real_escape_string($conn, $_POST['incident_type']);
    $severity = mysqli_real_escape_string($conn, $_POST['severity']);
    $violation_category = mysqli_real_escape_string($conn, $_POST['violation_category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $reported_by = mysqli_real_escape_string($conn, $_POST['reported_by']);
    $action_taken = mysqli_real_escape_string($conn, $_POST['action_taken']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $recorded_by = $_SESSION['login'];
    
    // Optional fields based on incident type
    $fine_amount = isset($_POST['fine_amount']) ? floatval($_POST['fine_amount']) : 0;
    $suspension_start_date = !empty($_POST['suspension_start_date']) ? mysqli_real_escape_string($conn, $_POST['suspension_start_date']) : NULL;
    $suspension_end_date = !empty($_POST['suspension_end_date']) ? mysqli_real_escape_string($conn, $_POST['suspension_end_date']) : NULL;
    
    $insert_sql = "INSERT INTO disciplinary_records 
                   (student_reg_no, incident_date, incident_time, incident_type, severity, 
                    violation_category, description, location, reported_by, action_taken, 
                    fine_amount, suspension_start_date, suspension_end_date, remarks, recorded_by, status) 
                   VALUES 
                   ('$student_reg_no', '$incident_date', " . ($incident_time ? "'$incident_time'" : "NULL") . ", 
                    '$incident_type', '$severity', '$violation_category', '$description', 
                    " . ($location ? "'$location'" : "NULL") . ", " . ($reported_by ? "'$reported_by'" : "NULL") . ", 
                    '$action_taken', '$fine_amount', " . ($suspension_start_date ? "'$suspension_start_date'" : "NULL") . ", 
                    " . ($suspension_end_date ? "'$suspension_end_date'" : "NULL") . ", 
                    " . ($remarks ? "'$remarks'" : "NULL") . ", '$recorded_by', 'open')";
    
    if(mysqli_query($conn, $insert_sql)) {
        $_SESSION['success_msg'] = "Disciplinary record added successfully!";
    } else {
        $_SESSION['error_msg'] = "Error adding record: " . mysqli_error($conn);
    }
    
    header("location: /hostel-management-system/index.php?page=disciplinaryManage");
    exit;
}

// Update record status
if(isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $record_id = intval($_POST['record_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $resolved_date = ($status == 'resolved') ? date('Y-m-d') : NULL;
    
    $update_sql = "UPDATE disciplinary_records 
                   SET status = '$status', 
                       resolved_date = " . ($resolved_date ? "'$resolved_date'" : "NULL") . " 
                   WHERE id = $record_id";
    
    if(mysqli_query($conn, $update_sql)) {
        $_SESSION['success_msg'] = "Record status updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Error updating status: " . mysqli_error($conn);
    }
    
    header("location: /hostel-management-system/index.php?page=disciplinaryManage");
    exit;
}

// Update fine payment status
if(isset($_POST['action']) && $_POST['action'] == 'update_fine') {
    $record_id = intval($_POST['record_id']);
    $fine_paid = mysqli_real_escape_string($conn, $_POST['fine_paid']);
    $fine_paid_date = ($fine_paid == 'yes') ? date('Y-m-d') : NULL;
    
    $update_sql = "UPDATE disciplinary_records 
                   SET fine_paid = '$fine_paid', 
                       fine_paid_date = " . ($fine_paid_date ? "'$fine_paid_date'" : "NULL") . " 
                   WHERE id = $record_id";
    
    if(mysqli_query($conn, $update_sql)) {
        $_SESSION['success_msg'] = "Fine payment status updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Error updating fine status: " . mysqli_error($conn);
    }
    
    header("location: /hostel-management-system/index.php?page=disciplinaryManage");
    exit;
}

// Delete record
if(isset($_POST['action']) && $_POST['action'] == 'delete') {
    $record_id = intval($_POST['record_id']);
    
    $delete_sql = "DELETE FROM disciplinary_records WHERE id = $record_id";
    
    if(mysqli_query($conn, $delete_sql)) {
        $_SESSION['success_msg'] = "Disciplinary record deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting record: " . mysqli_error($conn);
    }
    
    header("location: /hostel-management-system/index.php?page=disciplinaryManage");
    exit;
}

// If no valid action, redirect back
header("location: /hostel-management-system/index.php?page=disciplinaryManage");
exit;
?>
