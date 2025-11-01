<?php
// Handle database operations
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['execute_query'])) {
    $operation = $_POST['operation'];
    $query_result = null;
    $error_msg = null;
    
    try {
        switch($operation) {
            case 'student_profile_view':
                $query = "SELECT * FROM student_profile_view ORDER BY full_name";
                $query_result = mysqli_query($conn, $query);
                break;
                
            case 'compatibility_matrix':
                $query = "SELECT * FROM compatibility_matrix_view WHERE compatibility_score >= 50 ORDER BY compatibility_score DESC LIMIT 20";
                $query_result = mysqli_query($conn, $query);
                break;
                
            case 'branch_wise_analysis':
                $query = "SELECT 
                            b.branch_name,
                            COUNT(sp.id) as total_students,
                            AVG(CASE WHEN sp.lifestyle = 'early-bird' THEN 1 ELSE 0 END) * 100 as early_bird_percentage,
                            AVG(CASE WHEN sp.study_preference = 'silent' THEN 1 ELSE 0 END) * 100 as silent_study_percentage,
                            AVG(CASE WHEN sp.cleanliness_level = 'very-clean' THEN 1 ELSE 0 END) * 100 as very_clean_percentage
                          FROM student_preferences sp 
                          JOIN branches b ON sp.branch = b.branch_code 
                          GROUP BY sp.branch, b.branch_name 
                          ORDER BY total_students DESC";
                $query_result = mysqli_query($conn, $query);
                break;
                
            case 'lifestyle_compatibility':
                $query = "SELECT 
                            sp1.lifestyle as lifestyle1,
                            sp2.lifestyle as lifestyle2,
                            COUNT(*) as potential_matches,
                            AVG(
                                CASE WHEN sp1.lifestyle = sp2.lifestyle THEN 15 ELSE 0 END +
                                CASE WHEN sp1.study_preference = sp2.study_preference THEN 20 ELSE 0 END +
                                CASE WHEN sp1.noise_tolerance = sp2.noise_tolerance THEN 15 ELSE 0 END +
                                CASE WHEN sp1.cleanliness_level = sp2.cleanliness_level THEN 15 ELSE 0 END
                            ) as avg_compatibility_score
                          FROM student_preferences sp1
                          CROSS JOIN student_preferences sp2
                          WHERE sp1.reg_no != sp2.reg_no
                          GROUP BY sp1.lifestyle, sp2.lifestyle
                          ORDER BY avg_compatibility_score DESC";
                $query_result = mysqli_query($conn, $query);
                break;
                
            case 'room_availability_join':
                $query = "SELECT 
                            rd.room_no,
                            rd.seater,
                            COUNT(h.id) as current_occupants,
                            (rd.seater - COUNT(h.id)) as available_spaces,
                            GROUP_CONCAT(CONCAT(h.firstName, ' ', h.lastName) SEPARATOR ', ') as current_residents
                          FROM roomsdetails rd
                          LEFT JOIN hostelbookings h ON rd.room_no = h.roomno
                          GROUP BY rd.room_no, rd.seater
                          HAVING available_spaces > 0
                          ORDER BY available_spaces DESC";
                $query_result = mysqli_query($conn, $query);
                break;
                
            case 'preference_merge_analysis':
                $query = "SELECT 
                            'Food Habits' as preference_type,
                            food_habit as preference_value,
                            COUNT(*) as student_count,
                            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM student_preferences), 2) as percentage
                          FROM student_preferences 
                          GROUP BY food_habit
                          UNION ALL
                          SELECT 
                            'Study Preferences' as preference_type,
                            study_preference as preference_value,
                            COUNT(*) as student_count,
                            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM student_preferences), 2) as percentage
                          FROM student_preferences 
                          GROUP BY study_preference
                          UNION ALL
                          SELECT 
                            'Lifestyle' as preference_type,
                            lifestyle as preference_value,
                            COUNT(*) as student_count,
                            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM student_preferences), 2) as percentage
                          FROM student_preferences 
                          GROUP BY lifestyle
                          ORDER BY preference_type, student_count DESC";
                $query_result = mysqli_query($conn, $query);
                break;
                
            case 'inter_branch_compatibility':
                $query = "SELECT 
                            b1.branch_name as branch1,
                            b2.branch_name as branch2,
                            COUNT(*) as potential_pairs,
                            AVG(cv.compatibility_score) as avg_compatibility,
                            MAX(cv.compatibility_score) as max_compatibility
                          FROM compatibility_matrix_view cv
                          JOIN student_preferences sp1 ON cv.student1 = sp1.reg_no
                          JOIN student_preferences sp2 ON cv.student2 = sp2.reg_no
                          JOIN branches b1 ON sp1.branch = b1.branch_code
                          JOIN branches b2 ON sp2.branch = b2.branch_code
                          WHERE cv.compatibility_score >= 40
                          GROUP BY sp1.branch, sp2.branch, b1.branch_name, b2.branch_name
                          ORDER BY avg_compatibility DESC";
                $query_result = mysqli_query($conn, $query);
                break;
                
            case 'custom_query':
                if(isset($_POST['custom_sql']) && !empty(trim($_POST['custom_sql']))) {
                    $custom_sql = trim($_POST['custom_sql']);
                    // Basic SQL injection protection
                    $allowed_keywords = ['SELECT', 'FROM', 'WHERE', 'JOIN', 'GROUP BY', 'ORDER BY', 'HAVING', 'UNION', 'AS'];
                    $dangerous_keywords = ['DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE', 'TRUNCATE'];
                    
                    $upper_sql = strtoupper($custom_sql);
                    foreach($dangerous_keywords as $keyword) {
                        if(strpos($upper_sql, $keyword) !== false) {
                            throw new Exception("Dangerous keyword '$keyword' not allowed in custom queries.");
                        }
                    }
                    
                    $query_result = mysqli_query($conn, $custom_sql);
                }
                break;
        }
        
        if(!$query_result && mysqli_error($conn)) {
            $error_msg = "Query Error: " . mysqli_error($conn);
        }
        
    } catch(Exception $e) {
        $error_msg = $e->getMessage();
    }
}
?>

<div class="row">
    <!-- Query Selection Panel -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                <h5><i class="fa fa-database"></i> Database Operations</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Select Operation</label>
                        <select name="operation" class="form-control" id="operation_select" required>
                            <option value="">Choose an operation</option>
                            <optgroup label="Views & Basic Queries">
                                <option value="student_profile_view">Student Profile View</option>
                                <option value="compatibility_matrix">Compatibility Matrix</option>
                            </optgroup>
                            <optgroup label="JOIN Operations">
                                <option value="room_availability_join">Room Availability (JOIN)</option>
                                <option value="inter_branch_compatibility">Inter-Branch Compatibility (MULTI-JOIN)</option>
                            </optgroup>
                            <optgroup label="Advanced Analytics">
                                <option value="branch_wise_analysis">Branch-wise Analysis (GROUP BY)</option>
                                <option value="lifestyle_compatibility">Lifestyle Compatibility Matrix</option>
                                <option value="preference_merge_analysis">Preference Distribution (UNION)</option>
                            </optgroup>
                            <optgroup label="Custom Operations">
                                <option value="custom_query">Custom SQL Query</option>
                            </optgroup>
                        </select>
                    </div>
                    
                    <div id="custom_query_section" style="display: none;">
                        <div class="form-group">
                            <label>Custom SQL Query</label>
                            <textarea name="custom_sql" class="form-control" rows="6" placeholder="SELECT * FROM student_profile_view WHERE...&#10;&#10;Note: Only SELECT queries are allowed for security."></textarea>
                            <small class="text-muted">Only SELECT statements are allowed. JOIN, UNION, GROUP BY, etc. are supported.</small>
                        </div>
                    </div>
                    
                    <button type="submit" name="execute_query" class="btn btn-primary btn-block">
                        <i class="fa fa-play"></i> Execute Query
                    </button>
                </form>
                
                <!-- Query Examples -->
                <div class="mt-4">
                    <h6>Sample Queries:</h6>
                    <div class="list-group list-group-flush">
                        <button type="button" class="list-group-item list-group-item-action p-2" onclick="loadSampleQuery('view')">
                            <small><strong>View:</strong> Student profiles with preferences</small>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action p-2" onclick="loadSampleQuery('join')">
                            <small><strong>JOIN:</strong> Students with room details</small>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action p-2" onclick="loadSampleQuery('groupby')">
                            <small><strong>GROUP BY:</strong> Branch-wise statistics</small>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action p-2" onclick="loadSampleQuery('union')">
                            <small><strong>UNION:</strong> Combined preference analysis</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Results Panel -->
    <div class="col-md-8">
        <?php if(isset($error_msg)): ?>
            <div class="alert alert-danger">
                <h6><i class="fa fa-exclamation-triangle"></i> Error</h6>
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($query_result) && $query_result): ?>
            <div class="card">
                <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                    <h5><i class="fa fa-table"></i> Query Results</h5>
                    <small>Showing <?php echo mysqli_num_rows($query_result); ?> rows</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px;">
                        <table class="table table-striped table-sm mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <?php 
                                    $first_row = true;
                                    mysqli_data_seek($query_result, 0);
                                    if($row = mysqli_fetch_assoc($query_result)) {
                                        foreach($row as $column => $value) {
                                            echo '<th>' . ucwords(str_replace('_', ' ', $column)) . '</th>';
                                        }
                                        echo '</tr></thead><tbody>';
                                        
                                        // Reset and show data
                                        mysqli_data_seek($query_result, 0);
                                        $row_count = 0;
                                        while($row = mysqli_fetch_assoc($query_result)) {
                                            if($row_count >= 100) { // Limit display to 100 rows
                                                echo '<tr><td colspan="'.count($row).'" class="text-center text-muted"><em>... and more rows (limited to 100 for display)</em></td></tr>';
                                                break;
                                            }
                                            echo '<tr>';
                                            foreach($row as $column => $value) {
                                                if(is_numeric($value) && strpos($value, '.') !== false) {
                                                    $value = number_format((float)$value, 2);
                                                }
                                                echo '<td>' . htmlspecialchars($value) . '</td>';
                                            }
                                            echo '</tr>';
                                            $row_count++;
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm btn-secondary" onclick="exportResults()">
                        <i class="fa fa-download"></i> Export CSV
                    </button>
                    <button class="btn btn-sm btn-info" onclick="showQueryInfo()">
                        <i class="fa fa-info-circle"></i> Query Info
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fa fa-database fa-3x text-muted mb-3"></i>
                    <h5>Database Operations</h5>
                    <p class="text-muted">Select an operation from the panel to execute database queries with advanced SQL operations like JOINs, UNIONs, Views, and analytics.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">Views & Basic Queries</h6>
                                    <p class="card-text small">Execute predefined views and basic SELECT operations on student data.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="card-title text-success">JOIN Operations</h6>
                                    <p class="card-text small">Combine data from multiple tables using various JOIN types.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h6 class="card-title text-warning">Advanced Analytics</h6>
                                    <p class="card-text small">GROUP BY, HAVING, aggregate functions for statistical analysis.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="card-title text-info">Custom Queries</h6>
                                    <p class="card-text small">Write your own SELECT queries with full SQL support.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Show/hide custom query section
$('#operation_select').change(function() {
    if($(this).val() == 'custom_query') {
        $('#custom_query_section').show();
    } else {
        $('#custom_query_section').hide();
    }
});

function loadSampleQuery(type) {
    $('#operation_select').val('custom_query');
    $('#custom_query_section').show();
    
    var queries = {
        'view': `SELECT * FROM student_profile_view 
WHERE branch = 'CSE' 
ORDER BY full_name`,
        'join': `SELECT 
    h.firstName, 
    h.lastName, 
    h.regno,
    rd.room_no,
    rd.seater,
    sp.lifestyle,
    sp.study_preference
FROM hostelbookings h
JOIN roomsdetails rd ON h.roomno = rd.room_no
LEFT JOIN student_preferences sp ON h.regno = sp.reg_no
ORDER BY rd.room_no`,
        'groupby': `SELECT 
    sp.branch,
    b.branch_name,
    COUNT(*) as total_students,
    AVG(CASE WHEN sp.lifestyle = 'early-bird' THEN 1 ELSE 0 END) * 100 as early_bird_percent
FROM student_preferences sp
JOIN branches b ON sp.branch = b.branch_code
GROUP BY sp.branch, b.branch_name
ORDER BY total_students DESC`,
        'union': `SELECT 
    'Vegetarian' as food_type,
    COUNT(*) as count
FROM student_preferences 
WHERE food_habit = 'vegetarian'
UNION ALL
SELECT 
    'Non-Vegetarian' as food_type,
    COUNT(*) as count
FROM student_preferences 
WHERE food_habit = 'non-vegetarian'`
    };
    
    $('textarea[name="custom_sql"]').val(queries[type]);
}

function exportResults() {
    // This would implement CSV export functionality
    alert('Export functionality would be implemented here');
}

function showQueryInfo() {
    alert('Query executed successfully. Check the results above.');
}
</script>