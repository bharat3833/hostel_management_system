<?php
// Get existing preferences if any
$existing_preferences = null;
$selected_reg_no = '';

// Check both GET and POST for reg_no
if(isset($_GET['reg_no']) && !empty($_GET['reg_no'])) {
    $selected_reg_no = mysqli_real_escape_string($conn, $_GET['reg_no']);
} elseif(isset($_POST['reg_no']) && !empty($_POST['reg_no'])) {
    $selected_reg_no = mysqli_real_escape_string($conn, $_POST['reg_no']);
}

if($selected_reg_no) {
    $pref_sql = "SELECT * FROM student_preferences WHERE reg_no = '$selected_reg_no'";
    $pref_result = mysqli_query($conn, $pref_sql);
    if($pref_result && mysqli_num_rows($pref_result) > 0) {
        $existing_preferences = mysqli_fetch_assoc($pref_result);
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card preference-card">
            <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                <h5><i class="fa fa-user-cog"></i> Roommate Preferences</h5>
                <small>Fill out your preferences to find compatible roommates</small>
            </div>
            <div class="card-body">
                <form method="POST" id="preferencesForm">
                    <div class="row">
                        <!-- Student Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-user"></i> Select Student</label>
                                <select name="reg_no" id="reg_no" class="form-control" required>
                                    <option value="">Select Student</option>
                                    <?php
                                        $student_sql = "SELECT DISTINCT registration_no as regno, first_name as firstName, last_name as lastName FROM userregistration ORDER BY first_name";
                                        $student_result = mysqli_query($conn, $student_sql);
                                        if($student_result) {
                                            while($student_row = mysqli_fetch_assoc($student_result)) {
                                                $selected = ($selected_reg_no == $student_row['regno']) ? 'selected' : '';
                                                echo '<option value="'.$student_row['regno'].'" '.$selected.'>'.$student_row['regno'].' - '.$student_row['firstName'].' '.$student_row['lastName'].'</option>';
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Branch -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-graduation-cap"></i> Branch</label>
                                <select name="branch" id="branch" class="form-control" required>
                                    <option value="">Select Branch</option>
                                    <?php
                                        $branch_sql = "SELECT * FROM branches ORDER BY branch_name";
                                        $branch_result = mysqli_query($conn, $branch_sql);
                                        if($branch_result) {
                                            while($branch_row = mysqli_fetch_assoc($branch_result)) {
                                                $selected = ($existing_preferences && $existing_preferences['branch'] == $branch_row['branch_code']) ? 'selected' : '';
                                                echo '<option value="'.$branch_row['branch_code'].'" '.$selected.'>'.$branch_row['branch_code'].' - '.$branch_row['branch_name'].'</option>';
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Lifestyle -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-clock"></i> Lifestyle</label>
                                <select name="lifestyle" id="lifestyle" class="form-control" required>
                                    <option value="">Select Lifestyle</option>
                                    <option value="early-bird" <?php echo ($existing_preferences && $existing_preferences['lifestyle'] == 'early-bird') ? 'selected' : ''; ?>>Early Bird (6AM-10PM)</option>
                                    <option value="night-owl" <?php echo ($existing_preferences && $existing_preferences['lifestyle'] == 'night-owl') ? 'selected' : ''; ?>>Night Owl (10AM-2AM)</option>
                                    <option value="moderate" <?php echo ($existing_preferences && $existing_preferences['lifestyle'] == 'moderate') ? 'selected' : ''; ?>>Moderate (8AM-12AM)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Study Preference -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-book"></i> Study Preference</label>
                                <select name="study_preference" id="study_preference" class="form-control" required>
                                    <option value="">Select Study Style</option>
                                    <option value="silent" <?php echo ($existing_preferences && $existing_preferences['study_preference'] == 'silent') ? 'selected' : ''; ?>>Complete Silence</option>
                                    <option value="music" <?php echo ($existing_preferences && $existing_preferences['study_preference'] == 'music') ? 'selected' : ''; ?>>With Background Music</option>
                                    <option value="discussion" <?php echo ($existing_preferences && $existing_preferences['study_preference'] == 'discussion') ? 'selected' : ''; ?>>Group Discussion</option>
                                    <option value="flexible" <?php echo ($existing_preferences && $existing_preferences['study_preference'] == 'flexible') ? 'selected' : ''; ?>>Flexible</option>
                                </select>
                            </div>
                        </div>

                        <!-- Noise Tolerance -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-volume-up"></i> Noise Tolerance</label>
                                <select name="noise_tolerance" id="noise_tolerance" class="form-control" required>
                                    <option value="">Select Tolerance</option>
                                    <option value="complete-silence" <?php echo ($existing_preferences && $existing_preferences['noise_tolerance'] == 'complete-silence') ? 'selected' : ''; ?>>Complete Silence</option>
                                    <option value="low-noise" <?php echo ($existing_preferences && $existing_preferences['noise_tolerance'] == 'low-noise') ? 'selected' : ''; ?>>Low Noise</option>
                                    <option value="moderate-noise" <?php echo ($existing_preferences && $existing_preferences['noise_tolerance'] == 'moderate-noise') ? 'selected' : ''; ?>>Moderate Noise</option>
                                    <option value="high-noise" <?php echo ($existing_preferences && $existing_preferences['noise_tolerance'] == 'high-noise') ? 'selected' : ''; ?>>High Noise</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Cleanliness Level -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-broom"></i> Cleanliness Level</label>
                                <select name="cleanliness_level" id="cleanliness_level" class="form-control" required>
                                    <option value="">Select Level</option>
                                    <option value="very-clean" <?php echo ($existing_preferences && $existing_preferences['cleanliness_level'] == 'very-clean') ? 'selected' : ''; ?>>Very Clean</option>
                                    <option value="clean" <?php echo ($existing_preferences && $existing_preferences['cleanliness_level'] == 'clean') ? 'selected' : ''; ?>>Clean</option>
                                    <option value="moderate" <?php echo ($existing_preferences && $existing_preferences['cleanliness_level'] == 'moderate') ? 'selected' : ''; ?>>Moderate</option>
                                    <option value="flexible" <?php echo ($existing_preferences && $existing_preferences['cleanliness_level'] == 'flexible') ? 'selected' : ''; ?>>Flexible</option>
                                </select>
                            </div>
                        </div>

                        <!-- Food Habit -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-utensils"></i> Food Habit</label>
                                <select name="food_habit" id="food_habit" class="form-control" required>
                                    <option value="">Select Food Habit</option>
                                    <option value="vegetarian" <?php echo ($existing_preferences && $existing_preferences['food_habit'] == 'vegetarian') ? 'selected' : ''; ?>>Vegetarian</option>
                                    <option value="non-vegetarian" <?php echo ($existing_preferences && $existing_preferences['food_habit'] == 'non-vegetarian') ? 'selected' : ''; ?>>Non-Vegetarian</option>
                                    <option value="vegan" <?php echo ($existing_preferences && $existing_preferences['food_habit'] == 'vegan') ? 'selected' : ''; ?>>Vegan</option>
                                    <option value="jain" <?php echo ($existing_preferences && $existing_preferences['food_habit'] == 'jain') ? 'selected' : ''; ?>>Jain</option>
                                    <option value="flexible" <?php echo ($existing_preferences && $existing_preferences['food_habit'] == 'flexible') ? 'selected' : ''; ?>>Flexible</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sleep Schedule -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-bed"></i> Sleep Schedule</label>
                                <select name="sleep_schedule" id="sleep_schedule" class="form-control" required>
                                    <option value="">Select Schedule</option>
                                    <option value="early-sleeper" <?php echo ($existing_preferences && $existing_preferences['sleep_schedule'] == 'early-sleeper') ? 'selected' : ''; ?>>Early Sleeper (9-10PM)</option>
                                    <option value="late-sleeper" <?php echo ($existing_preferences && $existing_preferences['sleep_schedule'] == 'late-sleeper') ? 'selected' : ''; ?>>Late Sleeper (12-2AM)</option>
                                    <option value="irregular" <?php echo ($existing_preferences && $existing_preferences['sleep_schedule'] == 'irregular') ? 'selected' : ''; ?>>Irregular</option>
                                    <option value="flexible" <?php echo ($existing_preferences && $existing_preferences['sleep_schedule'] == 'flexible') ? 'selected' : ''; ?>>Flexible</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Social Behavior -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-users"></i> Social Behavior</label>
                                <select name="social_behavior" id="social_behavior" class="form-control" required>
                                    <option value="">Select Behavior</option>
                                    <option value="introverted" <?php echo ($existing_preferences && $existing_preferences['social_behavior'] == 'introverted') ? 'selected' : ''; ?>>Introverted</option>
                                    <option value="extroverted" <?php echo ($existing_preferences && $existing_preferences['social_behavior'] == 'extroverted') ? 'selected' : ''; ?>>Extroverted</option>
                                    <option value="ambivert" <?php echo ($existing_preferences && $existing_preferences['social_behavior'] == 'ambivert') ? 'selected' : ''; ?>>Ambivert</option>
                                </select>
                            </div>
                        </div>

                        <!-- Smoking/Drinking -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-smoking-ban"></i> Smoking/Drinking</label>
                                <select name="smoking_drinking" id="smoking_drinking" class="form-control" required>
                                    <option value="none" <?php echo ($existing_preferences && $existing_preferences['smoking_drinking'] == 'none') ? 'selected' : ''; ?>>None</option>
                                    <option value="social" <?php echo ($existing_preferences && $existing_preferences['smoking_drinking'] == 'social') ? 'selected' : ''; ?>>Social</option>
                                    <option value="regular" <?php echo ($existing_preferences && $existing_preferences['smoking_drinking'] == 'regular') ? 'selected' : ''; ?>>Regular</option>
                                </select>
                            </div>
                        </div>

                        <!-- Year of Study -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-calendar"></i> Year of Study</label>
                                <select name="year_of_study" id="year_of_study" class="form-control" required>
                                    <option value="">Select Year</option>
                                    <option value="1" <?php echo ($existing_preferences && $existing_preferences['year_of_study'] == '1') ? 'selected' : ''; ?>>1st Year</option>
                                    <option value="2" <?php echo ($existing_preferences && $existing_preferences['year_of_study'] == '2') ? 'selected' : ''; ?>>2nd Year</option>
                                    <option value="3" <?php echo ($existing_preferences && $existing_preferences['year_of_study'] == '3') ? 'selected' : ''; ?>>3rd Year</option>
                                    <option value="4" <?php echo ($existing_preferences && $existing_preferences['year_of_study'] == '4') ? 'selected' : ''; ?>>4th Year</option>
                                    <option value="postgrad" <?php echo ($existing_preferences && $existing_preferences['year_of_study'] == 'postgrad') ? 'selected' : ''; ?>>Post Graduate</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Interests -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="fa fa-heart"></i> Interests & Hobbies</label>
                                <textarea name="interests" id="interests" class="form-control" rows="3" 
                                    placeholder="e.g., Reading, Sports, Music, Gaming, Coding, Photography..."><?php echo $existing_preferences ? $existing_preferences['interests'] : ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Preferences -->
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="preferred_branch_same" id="preferred_branch_same" 
                                    <?php echo ($existing_preferences && $existing_preferences['preferred_branch_same']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="preferred_branch_same">
                                    Prefer same branch roommate
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="preferred_year_same" id="preferred_year_same"
                                    <?php echo ($existing_preferences && $existing_preferences['preferred_year_same']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="preferred_year_same">
                                    Prefer same year roommate
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- Priority Preferences -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="fa fa-star"></i> Priority Preferences</label>
                                <textarea name="priority_preferences" id="priority_preferences" class="form-control" rows="2" 
                                    placeholder="What are your most important criteria for a roommate?"><?php echo $existing_preferences ? $existing_preferences['priority_preferences'] : ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="savePreferences" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle student selection change
    $('#reg_no').change(function() {
        var regNo = $(this).val();
        if(regNo) {
            // Use AJAX to load preferences without page refresh
            $.post('get_student_preferences.php', {reg_no: regNo}, function(data) {
                if(data.success) {
                    if(data.preferences) {
                        // Fill the form with existing preferences
                        $('#lifestyle').val(data.preferences.lifestyle);
                        $('#study_preference').val(data.preferences.study_preference);
                        $('#noise_tolerance').val(data.preferences.noise_tolerance);
                        $('#cleanliness_level').val(data.preferences.cleanliness_level);
                        $('#food_habit').val(data.preferences.food_habit);
                        $('#sleep_schedule').val(data.preferences.sleep_schedule);
                        $('#social_behavior').val(data.preferences.social_behavior);
                        $('#smoking_drinking').val(data.preferences.smoking_drinking);
                        $('#year_of_study').val(data.preferences.year_of_study);
                        $('#interests').val(data.preferences.interests || '');
                        $('#priority_preferences').val(data.preferences.priority_preferences || '');
                        $('#preferred_branch_same').prop('checked', data.preferences.preferred_branch_same == 1);
                        $('#preferred_year_same').prop('checked', data.preferences.preferred_year_same == 1);
                        $('#branch').val(data.preferences.branch);
                        
                        // Show success message
                        $('<div class="alert alert-info alert-dismissible fade show" role="alert">' +
                          '<strong>Preferences loaded!</strong> Existing preferences found for this student.' +
                          '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
                          '</div>').insertBefore('#preferencesForm');
                    } else {
                        // Clear form for new student (except reg_no and required defaults)
                        $('#preferencesForm')[0].reset();
                        $('#reg_no').val(regNo); // Keep the selected student
                        
                        // Show info message
                        $('<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                          '<strong>New student!</strong> No existing preferences found. Please fill out the form.' +
                          '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
                          '</div>').insertBefore('#preferencesForm');
                    }
                    
                    // Auto-remove alerts after 3 seconds
                    setTimeout(function() {
                        $('.alert').fadeOut();
                    }, 3000);
                }
            }, 'json').fail(function() {
                alert('Error loading preferences. Please try again.');
            });
        }
    });
});
</script>