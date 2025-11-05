<?php
// Handle pair management actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['break_pair'])) {
        $pair_id = intval($_POST['pair_id']);
        
        // Update status to rejected (broken pair)
        $break_sql = "UPDATE roommate_matches SET status = 'rejected' WHERE id = $pair_id";
        $break_result = mysqli_query($conn, $break_sql);
        
        if($break_result) {
            $success_msg = "Roommate pair has been broken successfully.";
        } else {
            $error_msg = "Error breaking pair: " . mysqli_error($conn);
        }
    }
    
    if(isset($_POST['create_manual_pair'])) {
        $student1 = mysqli_real_escape_string($conn, $_POST['student1_reg_no']);
        $student2 = mysqli_real_escape_string($conn, $_POST['student2_reg_no']);
        
        if($student1 && $student2 && $student1 != $student2) {
            // Check if either student already has a roommate OR if this exact pair already exists
            $existing_pair_check = "SELECT id FROM roommate_matches 
                                   WHERE (student1_reg_no = '$student1' OR student2_reg_no = '$student1' 
                                         OR student1_reg_no = '$student2' OR student2_reg_no = '$student2')
                                   AND status = 'accepted'";
            $existing_pair_result = mysqli_query($conn, $existing_pair_check);
            
            // Also check for exact duplicate (both directions)
            $duplicate_check = "SELECT id FROM roommate_matches 
                               WHERE ((student1_reg_no = '$student1' AND student2_reg_no = '$student2') 
                                     OR (student1_reg_no = '$student2' AND student2_reg_no = '$student1'))
                               AND status IN ('accepted', 'pending')";
            $duplicate_result = mysqli_query($conn, $duplicate_check);
            
            if(mysqli_num_rows($existing_pair_result) > 0) {
                $error_msg = "Cannot create pair. One or both students already have a roommate assigned.";
            } elseif(mysqli_num_rows($duplicate_result) > 0) {
                $error_msg = "This roommate pair already exists in the system.";
            } else {
                // Calculate compatibility score (simple fallback if view doesn't exist)
                $compatibility_score = 50; // Default score
                
                // Try to get actual compatibility score
                $compatibility_sql = "SELECT 
                    CASE 
                        WHEN sp1.lifestyle = sp2.lifestyle THEN 15 ELSE 0 
                    END +
                    CASE 
                        WHEN sp1.study_preference = sp2.study_preference THEN 20 ELSE 0 
                    END +
                    CASE 
                        WHEN sp1.food_habit = sp2.food_habit THEN 10 ELSE 0 
                    END as compatibility_score
                    FROM student_preferences sp1, student_preferences sp2 
                    WHERE sp1.reg_no = '$student1' AND sp2.reg_no = '$student2'";
                $compatibility_result = mysqli_query($conn, $compatibility_sql);
                if($compatibility_result && mysqli_num_rows($compatibility_result) > 0) {
                    $compatibility_score = mysqli_fetch_assoc($compatibility_result)['compatibility_score'];
                }
                
                // Insert manual pair with error handling
                $insert_pair_sql = "INSERT INTO roommate_matches 
                                   (student1_reg_no, student2_reg_no, match_score, match_factors, status) 
                                   VALUES ('$student1', '$student2', '$compatibility_score', 'Manual assignment by admin', 'accepted')";
                
                try {
                    $insert_result = mysqli_query($conn, $insert_pair_sql);
                } catch (Exception $e) {
                    $insert_result = false;
                    $error_msg = "Error: This pair might already exist or there's a database constraint issue.";
                }
                
                if($insert_result) {
                    // Reject all pending requests involving these two students
                    $reject_requests = "UPDATE roommate_requests 
                                       SET status = 'rejected' 
                                       WHERE (requester_reg_no = '$student1' OR requested_reg_no = '$student1' 
                                             OR requester_reg_no = '$student2' OR requested_reg_no = '$student2')
                                       AND status = 'pending'";
                    mysqli_query($conn, $reject_requests);
                    
                    $success_msg = "Manual roommate pair created successfully! All pending requests for these students have been rejected.";
                } else {
                    $error_msg = "Error creating pair: " . mysqli_error($conn);
                }
            }
        } else {
            $error_msg = "Please select two different students.";
        }
    }
}

// Get all confirmed roommate pairs
$pairs_sql = "SELECT 
                rm.*,
                CONCAT(u1.first_name, ' ', u1.last_name) as student1_name,
                u1.contact_no as student1_contact,
                u1.emailid as student1_email,
                sp1.branch as student1_branch,
                b1.branch_name as student1_branch_name,
                sp1.year_of_study as student1_year,
                CONCAT(u2.first_name, ' ', u2.last_name) as student2_name,
                u2.contact_no as student2_contact,
                u2.emailid as student2_email,
                sp2.branch as student2_branch,
                b2.branch_name as student2_branch_name,
                sp2.year_of_study as student2_year
              FROM roommate_matches rm
              JOIN userregistration u1 ON rm.student1_reg_no = u1.registration_no
              JOIN userregistration u2 ON rm.student2_reg_no = u2.registration_no
              LEFT JOIN student_preferences sp1 ON rm.student1_reg_no = sp1.reg_no
              LEFT JOIN student_preferences sp2 ON rm.student2_reg_no = sp2.reg_no
              LEFT JOIN branches b1 ON sp1.branch = b1.branch_code
              LEFT JOIN branches b2 ON sp2.branch = b2.branch_code
              WHERE rm.status = 'accepted'
              ORDER BY rm.created_at DESC";

$pairs_result = mysqli_query($conn, $pairs_sql);
$roommate_pairs = array();
if($pairs_result) {
    while($row = mysqli_fetch_assoc($pairs_result)) {
        $roommate_pairs[] = $row;
    }
}

// Get statistics
$total_pairs = count($roommate_pairs);
$high_compatibility_pairs = 0;
$same_branch_pairs = 0;

foreach($roommate_pairs as $pair) {
    if($pair['match_score'] >= 70) $high_compatibility_pairs++;
    if($pair['student1_branch'] == $pair['student2_branch']) $same_branch_pairs++;
}
?>

<div class="row mb-4">
    <!-- Statistics Cards -->
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body text-center">
                <i class="fa fa-handshake fa-2x mb-2"></i>
                <h3><?php echo $total_pairs; ?></h3>
                <p class="card-text">Total Pairs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body text-center">
                <i class="fa fa-star fa-2x mb-2"></i>
                <h3><?php echo $high_compatibility_pairs; ?></h3>
                <p class="card-text">High Compatibility</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body text-center">
                <i class="fa fa-graduation-cap fa-2x mb-2"></i>
                <h3><?php echo $same_branch_pairs; ?></h3>
                <p class="card-text">Same Branch</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body text-center">
                <i class="fa fa-percentage fa-2x mb-2"></i>
                <h3><?php echo $total_pairs > 0 ? round(($high_compatibility_pairs/$total_pairs)*100) : 0; ?>%</h3>
                <p class="card-text">Success Rate</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Roommate Pairs List -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                <h5><i class="fa fa-handshake"></i> Confirmed Roommate Pairs</h5>
                <small>Students who have mutually agreed to be roommates</small>
            </div>
            <div class="card-body">
                <?php if(isset($success_msg)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $success_msg; ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error_msg; ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($roommate_pairs)): ?>
                    <div class="row">
                        <?php foreach($roommate_pairs as $pair): ?>
                            <?php 
                                $score = $pair['match_score'];
                                $score_class = $score >= 70 ? 'success' : ($score >= 40 ? 'warning' : 'danger');
                                $score_text = $score >= 70 ? 'Excellent' : ($score >= 40 ? 'Good' : 'Fair');
                            ?>
                            <div class="col-md-6 mb-4">
                                <div class="card border-<?php echo $score_class; ?>">
                                    <div class="card-header bg-<?php echo $score_class; ?> text-white">
                                        <div class="d-flex justify-content-between">
                                            <span><i class="fa fa-users"></i> Roommate Pair</span>
                                            <span class="badge badge-light text-<?php echo $score_class; ?>">
                                                <?php echo $score; ?>% <?php echo $score_text; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Student 1 -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <h6><i class="fa fa-user"></i> <?php echo $pair['student1_name']; ?></h6>
                                            <small class="text-muted">
                                                <i class="fa fa-id-card"></i> <?php echo $pair['student1_reg_no']; ?><br>
                                                <i class="fa fa-graduation-cap"></i> <?php echo $pair['student1_branch_name'] ?: 'Branch not specified'; ?>
                                                <?php if($pair['student1_year']): ?> - Year <?php echo $pair['student1_year']; ?><?php endif; ?><br>
                                                <i class="fa fa-envelope"></i> <?php echo $pair['student1_email']; ?><br>
                                                <i class="fa fa-phone"></i> <?php echo $pair['student1_contact']; ?>
                                            </small>
                                        </div>
                                        
                                        <!-- Student 2 -->
                                        <div class="pb-2 mb-2">
                                            <h6><i class="fa fa-user"></i> <?php echo $pair['student2_name']; ?></h6>
                                            <small class="text-muted">
                                                <i class="fa fa-id-card"></i> <?php echo $pair['student2_reg_no']; ?><br>
                                                <i class="fa fa-graduation-cap"></i> <?php echo $pair['student2_branch_name'] ?: 'Branch not specified'; ?>
                                                <?php if($pair['student2_year']): ?> - Year <?php echo $pair['student2_year']; ?><?php endif; ?><br>
                                                <i class="fa fa-envelope"></i> <?php echo $pair['student2_email']; ?><br>
                                                <i class="fa fa-phone"></i> <?php echo $pair['student2_contact']; ?>
                                            </small>
                                        </div>
                                        
                                        <!-- Pair Details -->
                                        <div class="border-top pt-2">
                                            <small>
                                                <strong>Paired:</strong> <?php echo date('M d, Y', strtotime($pair['created_at'])); ?><br>
                                                <strong>Method:</strong> <?php echo $pair['match_factors']; ?><br>
                                                <?php if($pair['student1_branch'] == $pair['student2_branch']): ?>
                                                    <span class="text-success"><i class="fa fa-check"></i> Same Branch</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center">
                                        <button class="btn btn-sm btn-info" onclick="viewPairDetails(<?php echo $pair['id']; ?>)">
                                            <i class="fa fa-eye"></i> View Details
                                        </button>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="pair_id" value="<?php echo $pair['id']; ?>">
                                            <button type="submit" name="break_pair" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to break this roommate pair?')">
                                                <i class="fa fa-unlink"></i> Break Pair
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="fa fa-handshake fa-3x mb-3"></i>
                        <h5>No Roommate Pairs Yet</h5>
                        <p>No confirmed roommate pairs found. Pairs are created when students accept roommate requests.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Manual Pair Creation -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header" style="background-color: #6f42c1; color: white;">
                <h5><i class="fa fa-plus-circle"></i> Create Manual Pair</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Student 1</label>
                        <select name="student1_reg_no" class="form-control" required>
                            <option value="">Select First Student</option>
                            <?php
                                $students_sql = "SELECT DISTINCT u.registration_no as regno, CONCAT(u.first_name, ' ', u.last_name) as full_name 
                               FROM userregistration u ORDER BY u.first_name";
                                $students_result = mysqli_query($conn, $students_sql);
                                if($students_result) {
                                    while($student = mysqli_fetch_assoc($students_result)) {
                                        echo '<option value="'.$student['regno'].'">'.$student['regno'].' - '.$student['full_name'].'</option>';
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Student 2</label>
                        <select name="student2_reg_no" class="form-control" required>
                            <option value="">Select Second Student</option>
                            <?php
                                mysqli_data_seek($students_result, 0);
                                while($student = mysqli_fetch_assoc($students_result)) {
                                    echo '<option value="'.$student['regno'].'">'.$student['regno'].' - '.$student['full_name'].'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="create_manual_pair" class="btn btn-primary btn-block">
                        <i class="fa fa-handshake"></i> Create Pair
                    </button>
                </form>
                
                <div class="mt-4">
                    <h6>Quick Actions</h6>
                    <div class="btn-group btn-block mb-2">
                        <button class="btn btn-outline-info btn-sm" onclick="exportPairs('pdf')">
                            <i class="fa fa-file-pdf"></i> Pairs PDF
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="exportPairs('excel')">
                            <i class="fa fa-file-excel"></i> Pairs Excel
                        </button>
                    </div>
                    <div class="btn-group btn-block">
                        <button class="btn btn-outline-success btn-sm" onclick="exportCompatibilityReport('pdf')">
                            <i class="fa fa-file-pdf"></i> Report PDF
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="exportCompatibilityReport('excel')">
                            <i class="fa fa-file-excel"></i> Report Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h6><i class="fa fa-clock"></i> Recent Pairings</h6>
            </div>
            <div class="card-body p-2">
                <?php
                $recent_pairs = array_slice($roommate_pairs, 0, 3);
                if(!empty($recent_pairs)):
                    foreach($recent_pairs as $recent):
                ?>
                    <div class="d-flex justify-content-between border-bottom py-1">
                        <small>
                            <strong><?php echo $recent['student1_name']; ?></strong><br>
                            <strong><?php echo $recent['student2_name']; ?></strong>
                        </small>
                        <small class="text-muted">
                            <?php echo date('M d', strtotime($recent['created_at'])); ?>
                        </small>
                    </div>
                <?php
                    endforeach;
                else:
                ?>
                    <small class="text-muted">No recent pairings</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Include export libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
// Store pairs data for export
const pairsData = <?php echo json_encode($roommate_pairs); ?>;
const stats = {
    totalPairs: <?php echo $total_pairs; ?>,
    highCompatibility: <?php echo $high_compatibility_pairs; ?>,
    sameBranch: <?php echo $same_branch_pairs; ?>,
    successRate: <?php echo $total_pairs > 0 ? round(($high_compatibility_pairs/$total_pairs)*100) : 0; ?>
};

function viewPairDetails(pairId) {
    const pair = pairsData.find(p => p.id == pairId);
    if(pair) {
        let details = `Roommate Pair Details\n\n`;
        details += `Student 1: ${pair.student1_name} (${pair.student1_reg_no})\n`;
        details += `Branch: ${pair.student1_branch_name || 'N/A'}\n`;
        details += `Contact: ${pair.student1_contact}\n\n`;
        details += `Student 2: ${pair.student2_name} (${pair.student2_reg_no})\n`;
        details += `Branch: ${pair.student2_branch_name || 'N/A'}\n`;
        details += `Contact: ${pair.student2_contact}\n\n`;
        details += `Compatibility Score: ${pair.match_score}%\n`;
        details += `Match Method: ${pair.match_factors}\n`;
        details += `Paired On: ${new Date(pair.created_at).toLocaleDateString()}`;
        alert(details);
    }
}

function exportPairs(format) {
    if(format === 'pdf') {
        exportPairsPDF();
    } else if(format === 'excel') {
        exportPairsExcel();
    }
}

function exportPairsPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Title
    doc.setFontSize(18);
    doc.text('Roommate Pairs List', 14, 20);
    
    // Date
    doc.setFontSize(10);
    doc.text('Generated on: ' + new Date().toLocaleString(), 14, 28);
    
    // Prepare table data
    const tableData = [];
    pairsData.forEach(pair => {
        tableData.push([
            pair.student1_name + '\n' + pair.student1_reg_no,
            pair.student2_name + '\n' + pair.student2_reg_no,
            pair.match_score + '%',
            pair.student1_branch_name || 'N/A',
            new Date(pair.created_at).toLocaleDateString()
        ]);
    });
    
    // Add table
    doc.autoTable({
        head: [['Student 1', 'Student 2', 'Score', 'Branch', 'Paired On']],
        body: tableData,
        startY: 35,
        theme: 'grid',
        headStyles: { fillColor: [111, 202, 203] },
        styles: { fontSize: 9 }
    });
    
    doc.save('Roommate_Pairs_' + new Date().toISOString().split('T')[0] + '.pdf');
}

function exportPairsExcel() {
    const wb = XLSX.utils.book_new();
    
    // Prepare data
    const data = [['Student 1 Name', 'Student 1 Reg No', 'Student 2 Name', 'Student 2 Reg No', 'Compatibility Score', 'Branch', 'Contact 1', 'Contact 2', 'Paired On']];
    
    pairsData.forEach(pair => {
        data.push([
            pair.student1_name,
            pair.student1_reg_no,
            pair.student2_name,
            pair.student2_reg_no,
            pair.match_score + '%',
            pair.student1_branch_name || 'N/A',
            pair.student1_contact,
            pair.student2_contact,
            new Date(pair.created_at).toLocaleDateString()
        ]);
    });
    
    const ws = XLSX.utils.aoa_to_sheet(data);
    XLSX.utils.book_append_sheet(wb, ws, 'Roommate Pairs');
    
    XLSX.writeFile(wb, 'Roommate_Pairs_' + new Date().toISOString().split('T')[0] + '.xlsx');
}

function exportCompatibilityReport(format) {
    if(format === 'pdf') {
        exportCompatibilityPDF();
    } else if(format === 'excel') {
        exportCompatibilityExcel();
    }
}

function exportCompatibilityPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Title
    doc.setFontSize(18);
    doc.text('Roommate Compatibility Report', 14, 20);
    
    // Date
    doc.setFontSize(10);
    doc.text('Generated on: ' + new Date().toLocaleString(), 14, 28);
    
    let yPos = 40;
    
    // Statistics
    doc.setFontSize(14);
    doc.text('Overall Statistics', 14, yPos);
    yPos += 10;
    
    doc.setFontSize(10);
    doc.text('Total Pairs: ' + stats.totalPairs, 14, yPos);
    yPos += 7;
    doc.text('High Compatibility Pairs (≥70%): ' + stats.highCompatibility, 14, yPos);
    yPos += 7;
    doc.text('Same Branch Pairs: ' + stats.sameBranch, 14, yPos);
    yPos += 7;
    doc.text('Success Rate: ' + stats.successRate + '%', 14, yPos);
    yPos += 15;
    
    // Compatibility breakdown
    doc.setFontSize(14);
    doc.text('Compatibility Breakdown', 14, yPos);
    yPos += 10;
    
    const excellent = pairsData.filter(p => p.match_score >= 70).length;
    const good = pairsData.filter(p => p.match_score >= 40 && p.match_score < 70).length;
    const fair = pairsData.filter(p => p.match_score < 40).length;
    
    const breakdownData = [
        ['Excellent (≥70%)', excellent, Math.round((excellent/stats.totalPairs)*100) + '%'],
        ['Good (40-69%)', good, Math.round((good/stats.totalPairs)*100) + '%'],
        ['Fair (<40%)', fair, Math.round((fair/stats.totalPairs)*100) + '%']
    ];
    
    doc.autoTable({
        head: [['Category', 'Count', 'Percentage']],
        body: breakdownData,
        startY: yPos,
        theme: 'grid',
        headStyles: { fillColor: [40, 167, 69] }
    });
    
    doc.save('Compatibility_Report_' + new Date().toISOString().split('T')[0] + '.pdf');
}

function exportCompatibilityExcel() {
    const wb = XLSX.utils.book_new();
    
    // Sheet 1: Statistics
    const statsData = [
        ['Metric', 'Value'],
        ['Total Pairs', stats.totalPairs],
        ['High Compatibility Pairs (≥70%)', stats.highCompatibility],
        ['Same Branch Pairs', stats.sameBranch],
        ['Success Rate', stats.successRate + '%']
    ];
    
    const ws1 = XLSX.utils.aoa_to_sheet(statsData);
    XLSX.utils.book_append_sheet(wb, ws1, 'Statistics');
    
    // Sheet 2: Compatibility Breakdown
    const excellent = pairsData.filter(p => p.match_score >= 70).length;
    const good = pairsData.filter(p => p.match_score >= 40 && p.match_score < 70).length;
    const fair = pairsData.filter(p => p.match_score < 40).length;
    
    const breakdownData = [
        ['Category', 'Count', 'Percentage'],
        ['Excellent (≥70%)', excellent, Math.round((excellent/stats.totalPairs)*100) + '%'],
        ['Good (40-69%)', good, Math.round((good/stats.totalPairs)*100) + '%'],
        ['Fair (<40%)', fair, Math.round((fair/stats.totalPairs)*100) + '%']
    ];
    
    const ws2 = XLSX.utils.aoa_to_sheet(breakdownData);
    XLSX.utils.book_append_sheet(wb, ws2, 'Compatibility Breakdown');
    
    // Sheet 3: Detailed Pairs
    const detailsData = [['Student 1', 'Reg No 1', 'Student 2', 'Reg No 2', 'Score', 'Category', 'Branch', 'Paired On']];
    
    pairsData.forEach(pair => {
        const category = pair.match_score >= 70 ? 'Excellent' : (pair.match_score >= 40 ? 'Good' : 'Fair');
        detailsData.push([
            pair.student1_name,
            pair.student1_reg_no,
            pair.student2_name,
            pair.student2_reg_no,
            pair.match_score + '%',
            category,
            pair.student1_branch_name || 'N/A',
            new Date(pair.created_at).toLocaleDateString()
        ]);
    });
    
    const ws3 = XLSX.utils.aoa_to_sheet(detailsData);
    XLSX.utils.book_append_sheet(wb, ws3, 'Detailed Pairs');
    
    XLSX.writeFile(wb, 'Compatibility_Report_' + new Date().toISOString().split('T')[0] + '.xlsx');
}
</script>