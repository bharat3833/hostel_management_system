<?php
// Handle request actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['respond_to_request'])) {
        $request_id = intval($_POST['request_id']);
        $action = $_POST['action']; // 'accepted' or 'rejected'
        
        // Get request details before updating
        $request_details_sql = "SELECT requester_reg_no, requested_reg_no FROM roommate_requests WHERE id = $request_id";
        $request_details_result = mysqli_query($conn, $request_details_sql);
        $request_details = mysqli_fetch_assoc($request_details_result);
        
        $update_sql = "UPDATE roommate_requests SET status = '$action' WHERE id = $request_id";
        $update_result = mysqli_query($conn, $update_sql);
        
        if($update_result) {
            if($action == 'accepted') {
                // Check if either student already has a roommate
                $student1 = $request_details['requester_reg_no'];
                $student2 = $request_details['requested_reg_no'];
                
                $existing_pair_check = "SELECT id FROM roommate_matches 
                                       WHERE (student1_reg_no = '$student1' OR student2_reg_no = '$student1' 
                                             OR student1_reg_no = '$student2' OR student2_reg_no = '$student2')
                                       AND status = 'accepted'";
                $existing_pair_result = mysqli_query($conn, $existing_pair_check);
                
                if(mysqli_num_rows($existing_pair_result) > 0) {
                    // One of the students already has a roommate
                    $error_msg = "Cannot accept request. One or both students already have a roommate assigned.";
                    
                    // Revert the request status back to pending
                    $revert_sql = "UPDATE roommate_requests SET status = 'pending' WHERE id = $request_id";
                    mysqli_query($conn, $revert_sql);
                } else {
                    // Both students are free, create the pair
                    
                    // Calculate compatibility score
                    $compatibility_sql = "SELECT compatibility_score FROM compatibility_matrix_view 
                                         WHERE (student1 = '$student1' AND student2 = '$student2') 
                                         OR (student1 = '$student2' AND student2 = '$student1')
                                         LIMIT 1";
                    $compatibility_result = mysqli_query($conn, $compatibility_sql);
                    $compatibility_score = 0;
                    if($compatibility_result && mysqli_num_rows($compatibility_result) > 0) {
                        $compatibility_score = mysqli_fetch_assoc($compatibility_result)['compatibility_score'];
                    }
                    
                    // Insert into roommate_matches table
                    $insert_match_sql = "INSERT INTO roommate_matches 
                                        (student1_reg_no, student2_reg_no, match_score, match_factors, status) 
                                        VALUES ('$student1', '$student2', '$compatibility_score', 'Mutual acceptance', 'accepted')";
                    $insert_result = mysqli_query($conn, $insert_match_sql);
                    
                    if($insert_result) {
                        // Reject all other pending requests involving these two students
                        $reject_other_requests = "UPDATE roommate_requests 
                                                 SET status = 'rejected' 
                                                 WHERE (requester_reg_no = '$student1' OR requested_reg_no = '$student1' 
                                                       OR requester_reg_no = '$student2' OR requested_reg_no = '$student2')
                                                 AND status = 'pending' 
                                                 AND id != $request_id";
                        mysqli_query($conn, $reject_other_requests);
                        
                        $success_msg = "Request accepted successfully! Roommate pair has been created. All other pending requests for these students have been automatically rejected.";
                    } else {
                        $error_msg = "Error creating roommate pair: " . mysqli_error($conn);
                    }
                }
            } else {
                $success_msg = "Request " . ucfirst($action) . " successfully!";
            }
        } else {
            $error_msg = "Error updating request: " . mysqli_error($conn);
        }
    }
}

// Get selected student for viewing requests
$selected_student = isset($_POST['student_reg_no']) ? $_POST['student_reg_no'] : '';
$incoming_requests = array();
$outgoing_requests = array();

if($selected_student) {
    // Get incoming requests (requests sent TO this student)
    $incoming_sql = "SELECT 
                        rr.*,
                        CONCAT(u.first_name, ' ', u.last_name) as sender_name,
                        u.contact_no as sender_contact,
                        u.emailid as sender_email,
                        COALESCE((SELECT roomno FROM hostelbookings WHERE regno = rr.requester_reg_no), 'Not Assigned') as sender_room,
                        sp.branch,
                        b.branch_name
                     FROM roommate_requests rr
                     JOIN userregistration u ON rr.requester_reg_no = u.registration_no
                     LEFT JOIN student_preferences sp ON rr.requester_reg_no = sp.reg_no
                     LEFT JOIN branches b ON sp.branch = b.branch_code
                     WHERE rr.requested_reg_no = '$selected_student'
                     ORDER BY rr.created_at DESC";
    $incoming_result = mysqli_query($conn, $incoming_sql);
    if($incoming_result) {
        while($row = mysqli_fetch_assoc($incoming_result)) {
            $incoming_requests[] = $row;
        }
    }
    
    // Get outgoing requests (requests sent BY this student)
    $outgoing_sql = "SELECT 
                        rr.*,
                        CONCAT(u.first_name, ' ', u.last_name) as recipient_name,
                        u.contact_no as recipient_contact,
                        u.emailid as recipient_email,
                        COALESCE((SELECT roomno FROM hostelbookings WHERE regno = rr.requested_reg_no), 'Not Assigned') as recipient_room,
                        sp.branch,
                        b.branch_name
                     FROM roommate_requests rr
                     JOIN userregistration u ON rr.requested_reg_no = u.registration_no
                     LEFT JOIN student_preferences sp ON rr.requested_reg_no = sp.reg_no
                     LEFT JOIN branches b ON sp.branch = b.branch_code
                     WHERE rr.requester_reg_no = '$selected_student'
                     ORDER BY rr.created_at DESC";
    $outgoing_result = mysqli_query($conn, $outgoing_sql);
    if($outgoing_result) {
        while($row = mysqli_fetch_assoc($outgoing_result)) {
            $outgoing_requests[] = $row;
        }
    }
}
?>

<div class="row">
    <!-- Student Selection Panel -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                <h5><i class="fa fa-user-circle"></i> Select Student</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Student</label>
                        <select name="student_reg_no" class="form-control" onchange="this.form.submit()">
                            <option value="">Choose a student</option>
                            <?php
                                $students_sql = "SELECT DISTINCT u.registration_no as regno, CONCAT(u.first_name, ' ', u.last_name) as full_name,
                               COALESCE((SELECT roomno FROM hostelbookings WHERE regno = u.registration_no), 'Not Assigned') as roomno
                               FROM userregistration u
                               ORDER BY u.first_name";
                                $students_result = mysqli_query($conn, $students_sql);
                                if($students_result) {
                                    while($student = mysqli_fetch_assoc($students_result)) {
                                        $selected = ($student['regno'] == $selected_student) ? 'selected' : '';
                                        echo '<option value="'.$student['regno'].'" '.$selected.'>'.$student['regno'].' - '.$student['full_name'].' (Room: '.$student['roomno'].')</option>';
                                    }
                                }
                            ?>
                        </select>
                    </div>
                </form>
                
                <?php if($selected_student): ?>
                <div class="mt-3">
                    <div class="alert alert-info">
                        <small>
                            <strong>Incoming:</strong> <?php echo count($incoming_requests); ?> requests<br>
                            <strong>Outgoing:</strong> <?php echo count($outgoing_requests); ?> requests
                        </small>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Requests Display Panel -->
    <div class="col-md-9">
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
        
        <?php if($selected_student): ?>
            <!-- Incoming Requests -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #28a745; color: white;">
                    <h5><i class="fa fa-inbox"></i> Incoming Requests (<?php echo count($incoming_requests); ?>)</h5>
                    <small>Roommate requests sent to this student</small>
                </div>
                <div class="card-body">
                    <?php if(!empty($incoming_requests)): ?>
                        <?php foreach($incoming_requests as $request): ?>
                            <div class="card mb-3 border-left-success">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="card-title">
                                                From: <strong><?php echo $request['sender_name']; ?></strong>
                                                <span class="badge badge-<?php echo $request['status'] == 'pending' ? 'warning' : ($request['status'] == 'accepted' ? 'success' : 'danger'); ?> ml-2">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </h6>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fa fa-id-card"></i> Reg No: <?php echo $request['requester_reg_no']; ?><br>
                                                    <i class="fa fa-home"></i> Room: <?php echo $request['sender_room']; ?><br>
                                                    <i class="fa fa-graduation-cap"></i> Branch: <?php echo $request['branch_name'] ?: 'Not specified'; ?><br>
                                                    <i class="fa fa-envelope"></i> Email: <?php echo $request['sender_email']; ?><br>
                                                    <i class="fa fa-phone"></i> Contact: <?php echo $request['sender_contact']; ?><br>
                                                    <i class="fa fa-clock"></i> Sent: <?php echo date('M d, Y h:i A', strtotime($request['created_at'])); ?>
                                                </small>
                                            </p>
                                            <?php if($request['message']): ?>
                                                <div class="alert alert-light">
                                                    <strong>Message:</strong><br>
                                                    <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <?php if($request['status'] == 'pending'): ?>
                                                <?php
                                                // Check if this student already has a roommate
                                                $has_roommate_sql = "SELECT id FROM roommate_matches 
                                                                   WHERE (student1_reg_no = '$selected_student' OR student2_reg_no = '$selected_student')
                                                                   AND status = 'accepted'";
                                                $has_roommate_result = mysqli_query($conn, $has_roommate_sql);
                                                $already_has_roommate = mysqli_num_rows($has_roommate_result) > 0;
                                                ?>
                                                
                                                <?php if($already_has_roommate): ?>
                                                    <div class="alert alert-warning text-center p-2">
                                                        <small><i class="fa fa-exclamation-triangle"></i><br>Student already<br>has a roommate</small>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="btn-group-vertical">
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                            <input type="hidden" name="action" value="accepted">
                                                            <button type="submit" name="respond_to_request" class="btn btn-success btn-sm">
                                                                <i class="fa fa-check"></i> Accept
                                                            </button>
                                                        </form>
                                                        <form method="POST" class="d-inline mt-2">
                                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                            <input type="hidden" name="action" value="rejected">
                                                            <button type="submit" name="respond_to_request" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this request?')">
                                                                <i class="fa fa-times"></i> Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <p class="text-muted">
                                                    <i class="fa fa-info-circle"></i><br>
                                                    Request already<br><?php echo $request['status']; ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="fa fa-inbox fa-3x mb-3"></i>
                            <h5>No Incoming Requests</h5>
                            <p>This student hasn't received any roommate requests yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Outgoing Requests -->
            <div class="card">
                <div class="card-header" style="background-color: #17a2b8; color: white;">
                    <h5><i class="fa fa-paper-plane"></i> Outgoing Requests (<?php echo count($outgoing_requests); ?>)</h5>
                    <small>Roommate requests sent by this student</small>
                </div>
                <div class="card-body">
                    <?php if(!empty($outgoing_requests)): ?>
                        <?php foreach($outgoing_requests as $request): ?>
                            <div class="card mb-3 border-left-info">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-10">
                                            <h6 class="card-title">
                                                To: <strong><?php echo $request['recipient_name']; ?></strong>
                                                <span class="badge badge-<?php echo $request['status'] == 'pending' ? 'warning' : ($request['status'] == 'accepted' ? 'success' : 'danger'); ?> ml-2">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </h6>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fa fa-id-card"></i> Reg No: <?php echo $request['requested_reg_no']; ?><br>
                                                    <i class="fa fa-home"></i> Room: <?php echo $request['recipient_room']; ?><br>
                                                    <i class="fa fa-graduation-cap"></i> Branch: <?php echo $request['branch_name'] ?: 'Not specified'; ?><br>
                                                    <i class="fa fa-clock"></i> Sent: <?php echo date('M d, Y h:i A', strtotime($request['created_at'])); ?>
                                                </small>
                                            </p>
                                            <?php if($request['message']): ?>
                                                <div class="alert alert-light">
                                                    <strong>Your Message:</strong><br>
                                                    <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <?php if($request['status'] == 'pending'): ?>
                                                <small class="text-muted">
                                                    <i class="fa fa-hourglass-half"></i><br>
                                                    Waiting for<br>response
                                                </small>
                                            <?php elseif($request['status'] == 'accepted'): ?>
                                                <small class="text-success">
                                                    <i class="fa fa-check-circle fa-2x"></i><br>
                                                    Accepted!
                                                </small>
                                            <?php else: ?>
                                                <small class="text-danger">
                                                    <i class="fa fa-times-circle fa-2x"></i><br>
                                                    Rejected
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="fa fa-paper-plane fa-3x mb-3"></i>
                            <h5>No Outgoing Requests</h5>
                            <p>This student hasn't sent any roommate requests yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fa fa-envelope fa-3x text-muted mb-3"></i>
                    <h5>Roommate Requests</h5>
                    <p class="text-muted">Select a student from the left panel to view their incoming and outgoing roommate requests.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="text-success"><i class="fa fa-inbox"></i> Incoming Requests</h6>
                                    <p class="card-text small">Requests received from other students wanting to be roommates</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="text-info"><i class="fa fa-paper-plane"></i> Outgoing Requests</h6>
                                    <p class="card-text small">Requests sent to other students for roommate compatibility</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>