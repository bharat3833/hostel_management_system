<?php
include 'partials/_dbconnect.php';

if(isset($_POST['reg_no'])) {
    $reg_no = mysqli_real_escape_string($conn, $_POST['reg_no']);
    
    $profile_sql = "SELECT 
                      u.registration_no as regno,
                      CONCAT(u.first_name, ' ', u.last_name) as full_name,
                      COALESCE(h.course, 'Not specified') as course,
                      u.gender,
                      u.contact_no as contactno,
                      u.emailid,
                      COALESCE(h.roomno, 'Not Assigned') as roomno,
                      COALESCE(h.city, 'Not specified') as city,
                      COALESCE(h.state, 'Not specified') as state,
                      sp.lifestyle,
                      sp.study_preference,
                      sp.noise_tolerance,
                      sp.cleanliness_level,
                      sp.food_habit,
                      sp.sleep_schedule,
                      sp.social_behavior,
                      sp.smoking_drinking,
                      sp.interests,
                      sp.branch,
                      sp.year_of_study,
                      sp.priority_preferences,
                      b.branch_name,
                      b.department
                    FROM userregistration u
                    LEFT JOIN hostelbookings h ON u.registration_no = h.regno
                    LEFT JOIN student_preferences sp ON u.registration_no = sp.reg_no
                    LEFT JOIN branches b ON sp.branch = b.branch_code
                    WHERE u.registration_no = '$reg_no'";
    
    $profile_result = mysqli_query($conn, $profile_sql);
    
    if($profile_result && mysqli_num_rows($profile_result) > 0) {
        $profile = mysqli_fetch_assoc($profile_result);
        
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h5>'.$profile['full_name'].'</h5>';
        echo '<p><strong>Registration:</strong> '.$profile['regno'].'</p>';
        echo '<p><strong>Course:</strong> '.$profile['course'].'</p>';
        echo '<p><strong>Branch:</strong> '.($profile['branch_name'] ? $profile['branch_name'] : 'Not specified').'</p>';
        echo '<p><strong>Year:</strong> '.($profile['year_of_study'] ? $profile['year_of_study'] : 'Not specified').'</p>';
        echo '<p><strong>Room:</strong> '.$profile['roomno'].'</p>';
        echo '<p><strong>Gender:</strong> '.$profile['gender'].'</p>';
        echo '<p><strong>Contact:</strong> '.$profile['contactno'].'</p>';
        echo '<p><strong>Email:</strong> '.$profile['emailid'].'</p>';
        echo '<p><strong>Location:</strong> '.$profile['city'].', '.$profile['state'].'</p>';
        echo '</div>';
        
        echo '<div class="col-md-6">';
        if($profile['lifestyle']) {
            echo '<h6>Roommate Preferences:</h6>';
            echo '<p><strong>Lifestyle:</strong> '.ucwords(str_replace('-', ' ', $profile['lifestyle'])).'</p>';
            echo '<p><strong>Study Preference:</strong> '.ucwords(str_replace('-', ' ', $profile['study_preference'])).'</p>';
            echo '<p><strong>Noise Tolerance:</strong> '.ucwords(str_replace('-', ' ', $profile['noise_tolerance'])).'</p>';
            echo '<p><strong>Cleanliness:</strong> '.ucwords(str_replace('-', ' ', $profile['cleanliness_level'])).'</p>';
            echo '<p><strong>Food Habit:</strong> '.ucwords(str_replace('-', ' ', $profile['food_habit'])).'</p>';
            echo '<p><strong>Sleep Schedule:</strong> '.ucwords(str_replace('-', ' ', $profile['sleep_schedule'])).'</p>';
            echo '<p><strong>Social Behavior:</strong> '.ucwords(str_replace('-', ' ', $profile['social_behavior'])).'</p>';
            echo '<p><strong>Smoking/Drinking:</strong> '.ucwords($profile['smoking_drinking']).'</p>';
            
            if($profile['interests']) {
                echo '<p><strong>Interests:</strong> '.$profile['interests'].'</p>';
            }
            
            if($profile['priority_preferences']) {
                echo '<p><strong>Priority:</strong> '.$profile['priority_preferences'].'</p>';
            }
        } else {
            echo '<div class="alert alert-warning">';
            echo '<i class="fa fa-exclamation-triangle"></i> This student has not filled their roommate preferences yet.';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        
    } else {
        echo '<div class="alert alert-danger">Student profile not found.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request.</div>';
}
?>