<?php
// Get analytics data
$total_students_with_prefs = 0;
$total_students = 0;
$branch_stats = array();
$preference_stats = array();
$compatibility_stats = array();

// Total students with preferences
$total_prefs_sql = "SELECT COUNT(*) as count FROM student_preferences";
$total_prefs_result = mysqli_query($conn, $total_prefs_sql);
if($total_prefs_result) {
    $total_students_with_prefs = mysqli_fetch_assoc($total_prefs_result)['count'];
}

// Total registered students
$total_students_sql = "SELECT COUNT(DISTINCT registration_no) as count FROM userregistration";
$total_students_result = mysqli_query($conn, $total_students_sql);
if($total_students_result) {
    $total_students = mysqli_fetch_assoc($total_students_result)['count'];
}

// Branch-wise statistics
$branch_stats_sql = "SELECT 
                        b.branch_name,
                        COUNT(sp.id) as student_count,
                        AVG(CASE WHEN sp.lifestyle = 'early-bird' THEN 1 ELSE 0 END) * 100 as early_bird_pct,
                        AVG(CASE WHEN sp.lifestyle = 'night-owl' THEN 1 ELSE 0 END) * 100 as night_owl_pct,
                        AVG(CASE WHEN sp.study_preference = 'silent' THEN 1 ELSE 0 END) * 100 as silent_study_pct,
                        AVG(CASE WHEN sp.cleanliness_level = 'very-clean' THEN 1 ELSE 0 END) * 100 as very_clean_pct
                     FROM student_preferences sp 
                     JOIN branches b ON sp.branch = b.branch_code 
                     GROUP BY sp.branch, b.branch_name 
                     ORDER BY student_count DESC";
$branch_stats_result = mysqli_query($conn, $branch_stats_sql);
if($branch_stats_result) {
    while($row = mysqli_fetch_assoc($branch_stats_result)) {
        $branch_stats[] = $row;
    }
}

// Preference distribution
$preferences = [
    'lifestyle' => 'Lifestyle Preferences',
    'study_preference' => 'Study Preferences', 
    'noise_tolerance' => 'Noise Tolerance',
    'cleanliness_level' => 'Cleanliness Level',
    'food_habit' => 'Food Habits',
    'sleep_schedule' => 'Sleep Schedule',
    'social_behavior' => 'Social Behavior'
];

foreach($preferences as $pref_col => $pref_name) {
    $pref_sql = "SELECT $pref_col as preference, COUNT(*) as count, 
                 ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM student_preferences), 1) as percentage
                 FROM student_preferences 
                 GROUP BY $pref_col 
                 ORDER BY count DESC";
    $pref_result = mysqli_query($conn, $pref_sql);
    if($pref_result) {
        $preference_stats[$pref_name] = array();
        while($row = mysqli_fetch_assoc($pref_result)) {
            $preference_stats[$pref_name][] = $row;
        }
    }
}

// High compatibility pairs
$high_compatibility_sql = "SELECT 
                             s1.reg_no as student1,
                             s2.reg_no as student2,
                             CONCAT(u1.first_name, ' ', u1.last_name) as name1,
                             CONCAT(u2.first_name, ' ', u2.last_name) as name2,
                             b1.branch_name as branch1,
                             b2.branch_name as branch2,
                             cv.compatibility_score
                           FROM compatibility_matrix_view cv
                           JOIN student_preferences s1 ON cv.student1 = s1.reg_no
                           JOIN student_preferences s2 ON cv.student2 = s2.reg_no
                           JOIN userregistration u1 ON s1.reg_no = u1.registration_no
                           JOIN userregistration u2 ON s2.reg_no = u2.registration_no
                           JOIN branches b1 ON s1.branch = b1.branch_code
                           JOIN branches b2 ON s2.branch = b2.branch_code
                           WHERE cv.compatibility_score >= 80
                           ORDER BY cv.compatibility_score DESC
                           LIMIT 10";
$high_compatibility_result = mysqli_query($conn, $high_compatibility_sql);
if($high_compatibility_result) {
    while($row = mysqli_fetch_assoc($high_compatibility_result)) {
        $compatibility_stats[] = $row;
    }
}
?>

<div class="row">
    <!-- Key Metrics -->
    <div class="col-md-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body text-center">
                        <i class="fa fa-users fa-2x mb-2"></i>
                        <h3><?php echo $total_students; ?></h3>
                        <p class="card-text">Total Students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <i class="fa fa-heart fa-2x mb-2"></i>
                        <h3><?php echo $total_students_with_prefs; ?></h3>
                        <p class="card-text">With Preferences</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body text-center">
                        <i class="fa fa-percentage fa-2x mb-2"></i>
                        <h3><?php echo $total_students > 0 ? round(($total_students_with_prefs/$total_students)*100, 1) : 0; ?>%</h3>
                        <p class="card-text">Completion Rate</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body text-center">
                        <i class="fa fa-handshake fa-2x mb-2"></i>
                        <h3><?php echo count($compatibility_stats); ?></h3>
                        <p class="card-text">High Compatibility Pairs</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Branch-wise Analysis -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                <h5><i class="fa fa-graduation-cap"></i> Branch-wise Analysis</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($branch_stats)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Branch</th>
                                    <th>Students</th>
                                    <th>Early Birds</th>
                                    <th>Silent Study</th>
                                    <th>Very Clean</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($branch_stats as $branch): ?>
                                <tr>
                                    <td><strong><?php echo $branch['branch_name']; ?></strong></td>
                                    <td><?php echo $branch['student_count']; ?></td>
                                    <td>
                                        <div class="progress" style="height: 15px;">
                                            <div class="progress-bar bg-success" style="width: <?php echo $branch['early_bird_pct']; ?>%">
                                                <?php echo round($branch['early_bird_pct'], 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 15px;">
                                            <div class="progress-bar bg-info" style="width: <?php echo $branch['silent_study_pct']; ?>%">
                                                <?php echo round($branch['silent_study_pct'], 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 15px;">
                                            <div class="progress-bar bg-warning" style="width: <?php echo $branch['very_clean_pct']; ?>%">
                                                <?php echo round($branch['very_clean_pct'], 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No branch data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- High Compatibility Pairs -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                <h5><i class="fa fa-handshake"></i> Top Compatible Pairs</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($compatibility_stats)): ?>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php foreach($compatibility_stats as $pair): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong><?php echo $pair['name1']; ?></strong> <small class="text-muted">(<?php echo $pair['branch1']; ?>)</small><br>
                                <strong><?php echo $pair['name2']; ?></strong> <small class="text-muted">(<?php echo $pair['branch2']; ?>)</small>
                            </div>
                            <div class="text-center">
                                <span class="badge badge-success badge-lg"><?php echo $pair['compatibility_score']; ?>%</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No high compatibility pairs found</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Preference Distribution -->
    <div class="col-md-12 mt-4">
        <div class="card">
            <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                <h5><i class="fa fa-chart-pie"></i> Preference Distribution Analysis</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach($preference_stats as $pref_name => $pref_data): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card border-light">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><?php echo $pref_name; ?></h6>
                            </div>
                            <div class="card-body p-2">
                                <?php foreach($pref_data as $item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small"><?php echo ucwords(str_replace('-', ' ', $item['preference'])); ?></span>
                                    <div class="d-flex align-items-center">
                                        <div class="progress mr-2" style="width: 60px; height: 15px;">
                                            <div class="progress-bar" style="width: <?php echo $item['percentage']; ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?php echo $item['count']; ?> (<?php echo $item['percentage']; ?>%)</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Insights & Recommendations -->
    <div class="col-md-12 mt-4">
        <div class="card">
            <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                <h5><i class="fa fa-lightbulb"></i> Insights & Recommendations</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fa fa-chart-line text-success"></i> Key Insights</h6>
                        <ul class="list-unstyled">
                            <?php 
                            $completion_rate = $total_students > 0 ? ($total_students_with_prefs/$total_students)*100 : 0;
                            if($completion_rate < 50) {
                                echo '<li><i class="fa fa-info-circle text-info"></i> Low preference completion rate ('.round($completion_rate, 1).'%). Consider encouraging more students to fill preferences.</li>';
                            }
                            
                            if(!empty($branch_stats)) {
                                $highest_branch = $branch_stats[0];
                                echo '<li><i class="fa fa-star text-warning"></i> '.$highest_branch['branch_name'].' has the most students with preferences ('.$highest_branch['student_count'].').</li>';
                            }
                            
                            if(count($compatibility_stats) > 0) {
                                echo '<li><i class="fa fa-handshake text-success"></i> '.count($compatibility_stats).' highly compatible pairs (80%+) found.</li>';
                            }
                            ?>
                            <li><i class="fa fa-users text-primary"></i> Roommate matching system is helping optimize student satisfaction.</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fa fa-recommendations text-info"></i> Recommendations</h6>
                        <ul class="list-unstyled">
                            <li><i class="fa fa-arrow-right text-muted"></i> Encourage students to complete preference forms for better matching</li>
                            <li><i class="fa fa-arrow-right text-muted"></i> Consider organizing branch-wise orientation sessions</li>
                            <li><i class="fa fa-arrow-right text-muted"></i> Implement automated matching suggestions based on high compatibility scores</li>
                            <li><i class="fa fa-arrow-right text-muted"></i> Regular surveys to update and refine matching algorithm</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Export Options -->
                <div class="mt-4 text-center">
                    <button class="btn btn-outline-primary btn-sm" onclick="exportAnalytics('pdf')">
                        <i class="fa fa-file-pdf"></i> Export Report (PDF)
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportAnalytics('excel')">
                        <i class="fa fa-file-excel"></i> Export Data (Excel)
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="refreshAnalytics()">
                        <i class="fa fa-refresh"></i> Refresh Analytics
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportAnalytics(format) {
    alert('Export to ' + format.toUpperCase() + ' functionality would be implemented here');
}

function refreshAnalytics() {
    window.location.reload();
}

// Initialize any charts or additional visualizations here
$(document).ready(function() {
    // Add any chart initialization code here
    console.log('Analytics loaded successfully');
});
</script>