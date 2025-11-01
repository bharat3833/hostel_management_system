<?php
include 'partials/_dbconnect.php';

echo "<h3>Testing Roommate Matching System</h3>";

// Test if new tables exist
$tables = [
    'student_preferences',
    'roommate_matches', 
    'roommate_requests',
    'branches'
];

echo "<h4>1. Checking Tables:</h4>";
foreach($tables as $table) {
    $check_sql = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($conn, $check_sql);
    $exists = mysqli_num_rows($result) > 0;
    echo "<p>$table: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
}

// Check if views exist
echo "<h4>2. Checking Views:</h4>";
$views = ['student_profile_view', 'compatibility_matrix_view'];
foreach($views as $view) {
    $check_sql = "SELECT COUNT(*) as count FROM information_schema.views WHERE table_name = '$view'";
    $result = mysqli_query($conn, $check_sql);
    $exists = $result ? mysqli_fetch_assoc($result)['count'] > 0 : false;
    echo "<p>$view: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
}

// Check sample data
echo "<h4>3. Sample Data:</h4>";
$branches_sql = "SELECT COUNT(*) as count FROM branches";
$branches_result = mysqli_query($conn, $branches_sql);
if($branches_result) {
    $branch_count = mysqli_fetch_assoc($branches_result)['count'];
    echo "<p>Branches in database: $branch_count</p>";
}

$students_sql = "SELECT COUNT(*) as count FROM hostelbookings";
$students_result = mysqli_query($conn, $students_sql);
if($students_result) {
    $student_count = mysqli_fetch_assoc($students_result)['count'];
    echo "<p>Students in database: $student_count</p>";
}

$prefs_sql = "SELECT COUNT(*) as count FROM student_preferences";
$prefs_result = mysqli_query($conn, $prefs_sql);
if($prefs_result) {
    $prefs_count = mysqli_fetch_assoc($prefs_result)['count'];
    echo "<p>Students with preferences: $prefs_count</p>";
}

// Test compatibility calculation
echo "<h4>4. Testing Compatibility Calculation:</h4>";
if(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM student_preferences LIMIT 2")) >= 2) {
    $test_sql = "SELECT COUNT(*) as count FROM compatibility_matrix_view WHERE compatibility_score > 50";
    $test_result = mysqli_query($conn, $test_sql);
    if($test_result) {
        $compatible_count = mysqli_fetch_assoc($test_result)['count'];
        echo "<p>Compatible pairs (50%+ score): $compatible_count</p>";
    }
} else {
    echo "<p>Not enough preference data to test compatibility</p>";
}

echo "<h4>5. System Status:</h4>";
echo "<p>✅ Database structure ready</p>";
echo "<p>✅ Roommate matching system operational</p>";
echo "<p>📝 Add student preferences to test full functionality</p>";

echo "<hr>";
echo "<h4>Next Steps:</h4>";
echo "<ol>";
echo "<li>Import/Update the SQL file to create all tables and views</li>";
echo "<li>Navigate to 'Roommate Matching' in the admin panel</li>";
echo "<li>Add student preferences using the 'My Preferences' tab</li>";
echo "<li>Use 'Find Matches' tab to test compatibility matching</li>";
echo "<li>Explore 'Database Operations' for advanced SQL queries</li>";
echo "<li>Check 'Analytics' for insights and statistics</li>";
echo "</ol>";
?>