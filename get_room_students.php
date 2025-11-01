<?php
include 'partials/_dbconnect.php';

header('Content-Type: application/json');

if(isset($_POST['room_no']) && !empty($_POST['room_no'])) {
    $room_no = intval($_POST['room_no']);
    $response = array();
    
    // First check if there's a confirmed roommate agreement for this room
    $agreement_sql = "SELECT 
                        rra.student1_reg_no, 
                        rra.student2_reg_no,
                        CONCAT(u1.first_name, ' ', u1.last_name) AS student1_name,
                        CONCAT(u2.first_name, ' ', u2.last_name) AS student2_name
                      FROM roommate_room_agreements rra
                      JOIN userregistration u1 ON rra.student1_reg_no = u1.registration_no
                      JOIN userregistration u2 ON rra.student2_reg_no = u2.registration_no
                      WHERE rra.agreed_room_no = $room_no AND rra.agreement_status = 'agreed'";
    
    $agreement_result = mysqli_query($conn, $agreement_sql);
    
    if($agreement_result && mysqli_num_rows($agreement_result) > 0) {
        // We have a roommate agreement for this room
        $agreement = mysqli_fetch_assoc($agreement_result);
        
        $response['has_agreement'] = true;
        $response['message'] = "This room has a confirmed roommate agreement between " . 
                               $agreement['student1_name'] . " and " . $agreement['student2_name'];
        
        // Get students who are eligible for this room (the roommate pair)
        $response['students'] = array();
        
        // Check if student1 already has a booking
        $check_student1_sql = "SELECT id FROM hostelbookings WHERE regno = '{$agreement['student1_reg_no']}' LIMIT 1";
        $check_student1_result = mysqli_query($conn, $check_student1_sql);
        
        if(mysqli_num_rows($check_student1_result) == 0) {
            // Student1 doesn't have a booking yet
            $response['students'][] = array(
                'reg_no' => $agreement['student1_reg_no'],
                'name' => $agreement['student1_name']
            );
        }
        
        // Check if student2 already has a booking
        $check_student2_sql = "SELECT id FROM hostelbookings WHERE regno = '{$agreement['student2_reg_no']}' LIMIT 1";
        $check_student2_result = mysqli_query($conn, $check_student2_sql);
        
        if(mysqli_num_rows($check_student2_result) == 0) {
            // Student2 doesn't have a booking yet
            $response['students'][] = array(
                'reg_no' => $agreement['student2_reg_no'],
                'name' => $agreement['student2_name']
            );
        }
        
        if(empty($response['students'])) {
            $response['message'] = "Both students in the roommate agreement already have bookings.";
        }
    } else {
        // No roommate agreement, show all available students
        $students_sql = "SELECT u.registration_no, CONCAT(u.first_name, ' ', u.last_name) as name 
                        FROM userregistration u 
                        WHERE u.registration_no NOT IN (SELECT regno FROM hostelbookings)";
        $students_result = mysqli_query($conn, $students_sql);
        
        $response['has_agreement'] = false;
        $response['students'] = array();
        
        while($student = mysqli_fetch_assoc($students_result)) {
            $response['students'][] = array(
                'reg_no' => $student['registration_no'],
                'name' => $student['name']
            );
        }
    }
    
    echo json_encode($response);
} else {
    echo json_encode(array('error' => 'Room number is required'));
}
?>