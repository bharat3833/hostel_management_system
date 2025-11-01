<?php
// Streamlined roommate matching algorithm
function calculateCompatibilityScore($pref1, $pref2) {
    $score = 0;
    $factors = array();
    
    // Study preference (20 points - most important)
    if($pref1['study_preference'] == $pref2['study_preference']) {
        $score += 20;
        $factors[] = "Same study preference";
    }
    
    // Sleep schedule (15 points)
    if($pref1['sleep_schedule'] == $pref2['sleep_schedule']) {
        $score += 15;
        $factors[] = "Same sleep schedule";
    }
    
    // Lifestyle compatibility (15 points)
    if($pref1['lifestyle'] == $pref2['lifestyle']) {
        $score += 15;
        $factors[] = "Same lifestyle";
    }
    
    // Cleanliness level (15 points)
    if($pref1['cleanliness_level'] == $pref2['cleanliness_level']) {
        $score += 15;
        $factors[] = "Same cleanliness standards";
    }
    
    // Noise tolerance (15 points)
    if($pref1['noise_tolerance'] == $pref2['noise_tolerance']) {
        $score += 15;
        $factors[] = "Same noise tolerance";
    }
    
    // Food habit (10 points)
    if($pref1['food_habit'] == $pref2['food_habit'] || 
       $pref1['food_habit'] == 'flexible' || $pref2['food_habit'] == 'flexible') {
        $score += 10;
        $factors[] = "Compatible food habits";
    }
    
    // Branch preference (5 points)
    if($pref1['branch'] == $pref2['branch'] && $pref1['preferred_branch_same'] == 1) {
        $score += 5;
        $factors[] = "Same branch";
    }
    
    // Smoking/drinking compatibility (5 points)
    if($pref1['smoking_drinking'] == $pref2['smoking_drinking']) {
        $score += 5;
        $factors[] = "Same lifestyle choices";
    }
    
    return array('score' => $score, 'factors' => $factors);
}

// Get selected student for matching
$selected_reg_no = isset($_POST['search_reg_no']) ? $_POST['search_reg_no'] : '';
$matches = array();

if($selected_reg_no) {
    // Get selected student's preferences
    $main_student_sql = "SELECT sp.*, CONCAT(u.first_name, ' ', u.last_name) as full_name, 
                        COALESCE((SELECT roomno FROM hostelbookings WHERE regno = sp.reg_no), 'Not Assigned') as roomno, 
                        b.branch_name 
                        FROM student_preferences sp 
                        JOIN userregistration u ON sp.reg_no = u.registration_no 
                        LEFT JOIN branches b ON sp.branch = b.branch_code 
                        WHERE sp.reg_no = '$selected_reg_no'";
    $main_student_result = mysqli_query($conn, $main_student_sql);
    
    if($main_student_result && mysqli_num_rows($main_student_result) > 0) {
        $main_student = mysqli_fetch_assoc($main_student_result);
        
        // Find potential matches
        $potential_matches_sql = "SELECT sp.*, CONCAT(u.first_name, ' ', u.last_name) as full_name, 
                                 COALESCE((SELECT roomno FROM hostelbookings WHERE regno = sp.reg_no), 'Not Assigned') as roomno, 
                                 b.branch_name,
                                 CASE WHEN EXISTS(
                                     SELECT 1 FROM roommate_matches rm 
                                     WHERE (rm.student1_reg_no = sp.reg_no OR rm.student2_reg_no = sp.reg_no) 
                                     AND rm.status = 'accepted'
                                 ) THEN 1 ELSE 0 END as already_paired,
                                 CASE WHEN EXISTS(
                                     SELECT 1 FROM roommate_requests rr 
                                     WHERE ((rr.requester_reg_no = '$selected_reg_no' AND rr.requested_reg_no = sp.reg_no) OR
                                           (rr.requester_reg_no = sp.reg_no AND rr.requested_reg_no = '$selected_reg_no'))
                                     AND rr.status IN ('pending', 'accepted')
                                 ) THEN 1 ELSE 0 END as request_exists
                                 FROM student_preferences sp 
                                 JOIN userregistration u ON sp.reg_no = u.registration_no 
                                 LEFT JOIN branches b ON sp.branch = b.branch_code 
                                 WHERE sp.reg_no != '$selected_reg_no'";
        $potential_matches_result = mysqli_query($conn, $potential_matches_sql);
        
        if($potential_matches_result) {
            while($potential_match = mysqli_fetch_assoc($potential_matches_result)) {
                $compatibility = calculateCompatibilityScore($main_student, $potential_match);
                $potential_match['compatibility_score'] = $compatibility['score'];
                $potential_match['compatibility_factors'] = $compatibility['factors'];
                $matches[] = $potential_match;
            }
            
            // Sort by compatibility score (highest first)
            usort($matches, function($a, $b) {
                return $b['compatibility_score'] - $a['compatibility_score'];
            });
        }
    }
}
?>

<div class="row">
    <!-- Search Panel -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                <h5><i class="fa fa-search"></i> Find Roommate Matches</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Select Student</label>
                        <select name="search_reg_no" class="form-control" required>
                            <option value="">Choose a student</option>
                            <?php
                                $search_student_sql = "SELECT sp.reg_no, CONCAT(u.first_name, ' ', u.last_name) as full_name, b.branch_name
                                      FROM student_preferences sp 
                                      JOIN userregistration u ON sp.reg_no = u.registration_no 
                                      LEFT JOIN branches b ON sp.branch = b.branch_code 
                                      ORDER BY u.first_name";
                                $search_student_result = mysqli_query($conn, $search_student_sql);
                                if($search_student_result) {
                                    while($search_student = mysqli_fetch_assoc($search_student_result)) {
                                        $selected = ($search_student['reg_no'] == $selected_reg_no) ? 'selected' : '';
                                        echo '<option value="'.$search_student['reg_no'].'" '.$selected.'>'.$search_student['reg_no'].' - '.$search_student['full_name'].' ('.$search_student['branch_name'].')</option>';
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa fa-search"></i> Find Matches
                    </button>
                </form>

                <?php if($selected_reg_no && isset($main_student)): ?>
                <div class="mt-4">
                    <h6>Selected Student Profile:</h6>
                    <div class="card">
                        <div class="card-body p-3">
                            <h6 class="card-title"><?php echo $main_student['full_name']; ?></h6>
                            <p class="card-text">
                                <small>
                                    <strong>Branch:</strong> <?php echo $main_student['branch_name']; ?><br>
                                    <strong>Year:</strong> <?php echo $main_student['year_of_study']; ?><br>
                                    <strong>Room:</strong> <?php echo $main_student['roomno']; ?>
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Matches Results -->
    <div class="col-md-8">
        <?php if($selected_reg_no && !empty($matches)): ?>
            <div class="card">
                <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                    <h5><i class="fa fa-users"></i> Compatible Roommates (<?php echo count($matches); ?> found)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach($matches as $match): ?>
                            <?php 
                                $score = $match['compatibility_score'];
                                $score_class = $score >= 70 ? 'score-high' : ($score >= 40 ? 'score-medium' : 'score-low');
                                $score_text = $score >= 70 ? 'Excellent Match' : ($score >= 40 ? 'Good Match' : 'Fair Match');
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="card match-card h-100 <?php echo $match['already_paired'] ? 'border-secondary' : ''; ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="card-title"><?php echo $match['full_name']; ?></h6>
                                                <?php if($match['already_paired']): ?>
                                                    <span class="badge badge-secondary badge-sm">Already Paired</span>
                                                <?php elseif($match['request_exists']): ?>
                                                    <span class="badge badge-warning badge-sm">Request Sent</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="compatibility-score <?php echo $score_class; ?>">
                                                <?php echo $score; ?>%
                                            </span>
                                        </div>
                                        
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <strong>Reg No:</strong> <?php echo $match['reg_no']; ?><br>
                                                <strong>Branch:</strong> <?php echo $match['branch_name']; ?><br>
                                                <strong>Year:</strong> <?php echo $match['year_of_study']; ?><br>
                                                <strong>Room:</strong> <?php echo $match['roomno']; ?>
                                            </small>
                                        </p>

                                        <div class="mb-2">
                                            <small><strong>Lifestyle:</strong> <?php echo ucwords(str_replace('-', ' ', $match['lifestyle'])); ?></small><br>
                                            <small><strong>Study:</strong> <?php echo ucwords(str_replace('-', ' ', $match['study_preference'])); ?></small><br>
                                            <small><strong>Food:</strong> <?php echo ucwords(str_replace('-', ' ', $match['food_habit'])); ?></small>
                                        </div>

                                        <?php if(!empty($match['compatibility_factors'])): ?>
                                        <div class="mb-2">
                                            <small><strong>Compatibility Factors:</strong></small><br>
                                            <?php foreach(array_slice($match['compatibility_factors'], 0, 3) as $factor): ?>
                                                <span class="badge badge-success badge-sm"><?php echo $factor; ?></span>
                                            <?php endforeach; ?>
                                            <?php if(count($match['compatibility_factors']) > 3): ?>
                                                <span class="badge badge-info badge-sm">+<?php echo count($match['compatibility_factors']) - 3; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>

                                        <div class="text-center mt-3">
                                            <button class="btn btn-sm btn-primary" onclick="viewFullProfile('<?php echo $match['reg_no']; ?>')">
                                                <i class="fa fa-eye"></i> View Profile
                                            </button>
                                            <?php if($match['already_paired']): ?>
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    <i class="fa fa-ban"></i> Already Paired
                                                </button>
                                            <?php elseif($match['request_exists']): ?>
                                                <button class="btn btn-sm btn-warning" disabled>
                                                    <i class="fa fa-hourglass"></i> Request Sent
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success" onclick="sendRoommateRequest('<?php echo $selected_reg_no; ?>', '<?php echo $match['reg_no']; ?>')">
                                                    <i class="fa fa-paper-plane"></i> Send Request
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php elseif($selected_reg_no): ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fa fa-search fa-3x text-muted mb-3"></i>
                    <h5>No matches found</h5>
                    <p class="text-muted">No compatible roommates found for the selected student. Try updating preferences or check back later.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fa fa-users fa-3x text-muted mb-3"></i>
                    <h5>Find Your Perfect Roommate</h5>
                    <p class="text-muted">Select a student from the search panel to find compatible roommates based on lifestyle, habits, and preferences.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Profile</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="profileModalBody">
                <!-- Profile content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Request Modal -->
<div class="modal fade" id="requestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Roommate Request</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="requestForm">
                <div class="modal-body">
                    <input type="hidden" id="requester_reg_no" name="requester_reg_no">
                    <input type="hidden" id="requested_reg_no" name="requested_reg_no">
                    <div class="form-group">
                        <label>Message (Optional)</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Hi! I think we'd be compatible roommates..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Send Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewFullProfile(regNo) {
    $.post('get_student_profile.php', {reg_no: regNo}, function(data) {
        $('#profileModalBody').html(data);
        $('#profileModal').modal('show');
    });
}

function sendRoommateRequest(requesterRegNo, requestedRegNo) {
    $('#requester_reg_no').val(requesterRegNo);
    $('#requested_reg_no').val(requestedRegNo);
    $('#requestModal').modal('show');
}

$('#requestForm').on('submit', function(e) {
    e.preventDefault();
    $.post('send_roommate_request.php', $(this).serialize(), function(response) {
        alert(response.message);
        $('#requestModal').modal('hide');
        if(response.success) {
            location.reload();
        }
    }, 'json');
});
</script>