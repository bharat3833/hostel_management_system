<?php
include 'partials/_dbconnect.php';

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['savePreferences'])) {
        $reg_no = mysqli_real_escape_string($conn, $_POST['reg_no']);
        $lifestyle = mysqli_real_escape_string($conn, $_POST['lifestyle']);
        $study_preference = mysqli_real_escape_string($conn, $_POST['study_preference']);
        $noise_tolerance = mysqli_real_escape_string($conn, $_POST['noise_tolerance']);
        $cleanliness_level = mysqli_real_escape_string($conn, $_POST['cleanliness_level']);
        $food_habit = mysqli_real_escape_string($conn, $_POST['food_habit']);
        $sleep_schedule = mysqli_real_escape_string($conn, $_POST['sleep_schedule']);
        $social_behavior = mysqli_real_escape_string($conn, $_POST['social_behavior']);
        $smoking_drinking = mysqli_real_escape_string($conn, $_POST['smoking_drinking']);
        $interests = mysqli_real_escape_string($conn, $_POST['interests']);
        $branch = mysqli_real_escape_string($conn, $_POST['branch']);
        $year_of_study = mysqli_real_escape_string($conn, $_POST['year_of_study']);
        $preferred_branch_same = isset($_POST['preferred_branch_same']) ? 1 : 0;
        $preferred_year_same = isset($_POST['preferred_year_same']) ? 1 : 0;
        $priority_preferences = mysqli_real_escape_string($conn, $_POST['priority_preferences']);

        // Check if preferences already exist
        $check_sql = "SELECT id FROM student_preferences WHERE reg_no = '$reg_no'";
        $check_result = mysqli_query($conn, $check_sql);

        if(mysqli_num_rows($check_result) > 0) {
            // Update existing preferences
            $sql = "UPDATE student_preferences SET 
                    lifestyle='$lifestyle', study_preference='$study_preference', 
                    noise_tolerance='$noise_tolerance', cleanliness_level='$cleanliness_level',
                    food_habit='$food_habit', sleep_schedule='$sleep_schedule',
                    social_behavior='$social_behavior', smoking_drinking='$smoking_drinking',
                    interests='$interests', branch='$branch', year_of_study='$year_of_study',
                    preferred_branch_same='$preferred_branch_same', preferred_year_same='$preferred_year_same',
                    priority_preferences='$priority_preferences'
                    WHERE reg_no='$reg_no'";
        } else {
            // Insert new preferences
            $sql = "INSERT INTO student_preferences (reg_no, lifestyle, study_preference, noise_tolerance, 
                    cleanliness_level, food_habit, sleep_schedule, social_behavior, smoking_drinking,
                    interests, branch, year_of_study, preferred_branch_same, preferred_year_same, priority_preferences) 
                    VALUES ('$reg_no', '$lifestyle', '$study_preference', '$noise_tolerance',
                    '$cleanliness_level', '$food_habit', '$sleep_schedule', '$social_behavior', '$smoking_drinking',
                    '$interests', '$branch', '$year_of_study', '$preferred_branch_same', '$preferred_year_same', '$priority_preferences')";
        }

        $result = mysqli_query($conn, $sql);
        if($result) {
            echo '<script>alert("Preferences saved successfully!"); window.location.href = "index.php?page=roommate&tab=matches";</script>';
            exit;
        } else {
            $error_msg = "Error saving preferences: " . mysqli_error($conn);
        }
    }
}

// Get current tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'preferences';
?>

<style>
.nav-tabs .nav-link.active {
    background-color: #6fcacb;
    border-color: #6fcacb;
    color: white;
}
.compatibility-score {
    font-weight: bold;
    padding: 5px 10px;
    border-radius: 15px;
    color: white;
}
.score-high { background-color: #28a745; }
.score-medium { background-color: #ffc107; color: #000; }
.score-low { background-color: #dc3545; }
.preference-card {
    border-left: 4px solid #6fcacb;
}
.match-card {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.3s ease;
}
.match-card:hover {
    border-color: #6fcacb;
    box-shadow: 0 4px 8px rgba(111, 202, 203, 0.3);
}
</style>

<div class="container-fluid" style="margin-top:98px">
    <?php if(isset($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $tab == 'preferences' ? 'active' : ''; ?>" 
               href="index.php?page=roommate&tab=preferences">
                <i class="fa fa-user-cog"></i> My Preferences
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab == 'matches' ? 'active' : ''; ?>" 
               href="index.php?page=roommate&tab=matches">
                <i class="fa fa-users"></i> Find Matches
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab == 'database' ? 'active' : ''; ?>" 
               href="index.php?page=roommate&tab=database">
                <i class="fa fa-database"></i> Database Operations
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab == 'analytics' ? 'active' : ''; ?>" 
               href="index.php?page=roommate&tab=analytics">
                <i class="fa fa-chart-bar"></i> Analytics
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab == 'requests' ? 'active' : ''; ?>" 
               href="index.php?page=roommate&tab=requests">
                <i class="fa fa-envelope"></i> Requests
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab == 'pairs' ? 'active' : ''; ?>" 
               href="index.php?page=roommate&tab=pairs">
                <i class="fa fa-handshake"></i> Roommate Pairs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab == 'room_agreements' ? 'active' : ''; ?>" 
               href="index.php?page=roommate&tab=room_agreements">
                <i class="fa fa-home"></i> Room Agreements
            </a>
        </li>
    </ul>

    <?php if($tab == 'preferences'): ?>
        <?php include 'preferences_tab.php'; ?>
    <?php elseif($tab == 'matches'): ?>
        <?php include 'matches_tab.php'; ?>
    <?php elseif($tab == 'database'): ?>
        <?php include 'database_tab.php'; ?>
    <?php elseif($tab == 'analytics'): ?>
        <?php include 'analytics_tab.php'; ?>
    <?php elseif($tab == 'requests'): ?>
        <?php include 'requests_tab.php'; ?>
    <?php elseif($tab == 'pairs'): ?>
        <?php include 'pairs_tab.php'; ?>
    <?php elseif($tab == 'room_agreements'): ?>
        <?php include 'room_agreements_tab.php'; ?>
    <?php endif; ?>
</div>