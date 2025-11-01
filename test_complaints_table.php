<?php
include 'partials/_dbconnect.php';

// Test if complaints table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'complaints'");
if(mysqli_num_rows($result) == 0) {
    echo "Creating complaints table...<br>";
    
    $create_table_sql = "CREATE TABLE `complaints` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `student_name` varchar(255) NOT NULL,
        `reg_no` varchar(255) NOT NULL,
        `room_no` int(11) NOT NULL,
        `complaint` text NOT NULL,
        `status` enum('pending','in-progress','resolved') NOT NULL DEFAULT 'pending',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
    
    if(mysqli_query($conn, $create_table_sql)) {
        echo "Complaints table created successfully!<br>";
    } else {
        echo "Error creating complaints table: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Complaints table already exists.<br>";
}

// Test if hostelbookings table has data
$hostel_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM hostelbookings");
if($hostel_check) {
    $hostel_data = mysqli_fetch_assoc($hostel_check);
    echo "Found " . $hostel_data['count'] . " students in hostelbookings table.<br>";
} else {
    echo "Error checking hostelbookings: " . mysqli_error($conn) . "<br>";
}

// Test basic complaint functionality
echo "<br><strong>Testing complaint functionality:</strong><br>";

// List first 5 students
$student_sql = "SELECT regno, firstName, lastName, roomno FROM hostelbookings LIMIT 5";
$student_result = mysqli_query($conn, $student_sql);

if($student_result && mysqli_num_rows($student_result) > 0) {
    echo "Sample students:<br>";
    while($row = mysqli_fetch_assoc($student_result)) {
        echo "- " . $row['regno'] . " - " . $row['firstName'] . " " . $row['lastName'] . " (Room: " . $row['roomno'] . ")<br>";
    }
} else {
    echo "No students found in hostelbookings table.<br>";
}
?>