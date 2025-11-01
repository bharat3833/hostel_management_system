<?php
include 'partials/_dbconnect.php';

header('Content-Type: application/json');

if(isset($_POST['reg_no'])) {
    $reg_no = mysqli_real_escape_string($conn, $_POST['reg_no']);
    
    $pref_sql = "SELECT * FROM student_preferences WHERE reg_no = '$reg_no'";
    $pref_result = mysqli_query($conn, $pref_sql);
    
    if($pref_result && mysqli_num_rows($pref_result) > 0) {
        $preferences = mysqli_fetch_assoc($pref_result);
        echo json_encode([
            'success' => true,
            'preferences' => $preferences
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'preferences' => null
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
?>