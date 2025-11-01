<?php
include 'partials/_dbconnect.php';

header('Content-Type: application/json');

if(isset($_POST['room_no'])) {
    $room_no = intval($_POST['room_no']);
    
    // Check if there's an agreed room booking for this room
    $agreement_sql = "SELECT * FROM agreed_room_bookings_view WHERE agreed_room_no = $room_no AND agreement_status = 'agreed'";
    $agreement_result = mysqli_query($conn, $agreement_sql);
    
    if($agreement_result && mysqli_num_rows($agreement_result) > 0) {
        $agreement = mysqli_fetch_assoc($agreement_result);
        
        // Prepare the response with both students' details
        $response = array(
            'success' => true,
            'has_agreement' => true,
            'agreement_id' => $agreement['id'],
            'roommate_pair_id' => $agreement['roommate_pair_id'],
            'student1' => array(
                'reg_no' => $agreement['student1_reg_no'],
                'name' => $agreement['student1_name'],
                'email' => $agreement['student1_email'],
                'contact' => $agreement['student1_contact'],
                'gender' => $agreement['student1_gender'],
                'branch' => $agreement['student1_branch_name']
            ),
            'student2' => array(
                'reg_no' => $agreement['student2_reg_no'],
                'name' => $agreement['student2_name'],
                'email' => $agreement['student2_email'],
                'contact' => $agreement['student2_contact'],
                'gender' => $agreement['student2_gender'],
                'branch' => $agreement['student2_branch_name']
            ),
            'room_details' => array(
                'room_no' => $agreement['agreed_room_no'],
                'seater' => $agreement['seater'],
                'fees' => $agreement['fees'],
                'max_budget' => $agreement['max_budget']
            ),
            'compatibility_score' => $agreement['compatibility_score'],
            'special_requirements' => $agreement['special_requirements']
        );
        
        echo json_encode($response);
    } else {
        // No agreement found for this room
        echo json_encode(array(
            'success' => true,
            'has_agreement' => false,
            'message' => 'No roommate agreement found for this room.'
        ));
    }
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Room number not provided.'
    ));
}
?>