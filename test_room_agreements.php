<?php
include 'partials/_dbconnect.php';

echo "<h3>Room Agreements Test & Debug</h3>";

// Check if we have required data
echo "<h4>1. Checking Required Tables:</h4>";

// Check userregistration
$users_sql = "SELECT COUNT(*) as count FROM userregistration";
$users_result = mysqli_query($conn, $users_sql);
$users_count = mysqli_fetch_assoc($users_result)['count'];
echo "Users in userregistration: $users_count<br>";

// Check roommate_matches
$matches_sql = "SELECT COUNT(*) as count FROM roommate_matches WHERE status = 'accepted'";
$matches_result = mysqli_query($conn, $matches_sql);
$matches_count = mysqli_fetch_assoc($matches_result)['count'];
echo "Confirmed roommate pairs: $matches_count<br>";

// Check roomsdetails
$rooms_sql = "SELECT COUNT(*) as count FROM roomsdetails WHERE seater >= 2";
$rooms_result = mysqli_query($conn, $rooms_sql);
$rooms_count = mysqli_fetch_assoc($rooms_result)['count'];
echo "Available 2+ seater rooms: $rooms_count<br>";

// Check room agreements
$agreements_sql = "SELECT COUNT(*) as count FROM roommate_room_agreements";
$agreements_result = mysqli_query($conn, $agreements_sql);
$agreements_count = mysqli_fetch_assoc($agreements_result)['count'];
echo "Room agreements: $agreements_count<br><br>";

echo "<h4>2. Sample Data:</h4>";

// Show sample users
echo "<strong>Sample Users:</strong><br>";
$sample_users = mysqli_query($conn, "SELECT registration_no, first_name, last_name FROM userregistration LIMIT 5");
while($user = mysqli_fetch_assoc($sample_users)) {
    echo "- {$user['registration_no']}: {$user['first_name']} {$user['last_name']}<br>";
}

// Show sample pairs
echo "<br><strong>Sample Roommate Pairs:</strong><br>";
$sample_pairs = mysqli_query($conn, "SELECT * FROM roommate_matches WHERE status = 'accepted' LIMIT 3");
while($pair = mysqli_fetch_assoc($sample_pairs)) {
    echo "- Pair ID {$pair['id']}: {$pair['student1_reg_no']} & {$pair['student2_reg_no']} (Score: {$pair['match_score']}%)<br>";
}

// Show sample rooms
echo "<br><strong>Sample Available Rooms:</strong><br>";
$sample_rooms = mysqli_query($conn, "SELECT * FROM roomsdetails WHERE seater >= 2 LIMIT 5");
while($room = mysqli_fetch_assoc($sample_rooms)) {
    echo "- Room {$room['room_no']}: {$room['seater']} seater, ₹{$room['fees']}/month<br>";
}

echo "<br><h4>3. Creating Test Data if Missing:</h4>";

// If no pairs exist, show instructions
if($matches_count == 0) {
    echo "<div style='color: red;'>";
    echo "<strong>⚠️ No confirmed roommate pairs found!</strong><br>";
    echo "You need to first:<br>";
    echo "1. Go to 'My Preferences' tab and set preferences for at least 2 students<br>";
    echo "2. Go to 'Find Matches' tab and make students send requests<br>";
    echo "3. Go to 'Requests' tab and accept the requests<br>";
    echo "4. Then you can propose rooms in 'Room Agreements' tab<br>";
    echo "</div>";
}

echo "<br><a href='index.php?page=roommate&tab=room_agreements'>← Back to Room Agreements</a>";
?>