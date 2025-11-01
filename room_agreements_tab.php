<?php
// Handle room agreement actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['propose_room'])) {
        $pair_id = intval($_POST['pair_id']);
        $room_no = intval($_POST['room_no']);
        $student_reg_no = mysqli_real_escape_string($conn, $_POST['student_reg_no']);
        $max_budget = intval($_POST['max_budget']);
        $special_requirements = mysqli_real_escape_string($conn, $_POST['special_requirements']);
        
        // Get pair details
        $pair_sql = "SELECT * FROM roommate_matches WHERE id = $pair_id AND status = 'accepted'";
        $pair_result = mysqli_query($conn, $pair_sql);
        $pair = mysqli_fetch_assoc($pair_result);
        
        if($pair) {
            // Check if agreement already exists
            $existing_sql = "SELECT id FROM roommate_room_agreements WHERE roommate_pair_id = $pair_id";
            $existing_result = mysqli_query($conn, $existing_sql);
            
            if(mysqli_num_rows($existing_result) > 0) {
                $error_msg = "A room agreement already exists for this pair.";
            } else {
                // Determine which student is proposing
                $student1_agreed = ($student_reg_no == $pair['student1_reg_no']) ? 1 : 0;
                $student2_agreed = ($student_reg_no == $pair['student2_reg_no']) ? 1 : 0;
                
                // Insert room agreement
                $insert_sql = "INSERT INTO roommate_room_agreements 
                              (roommate_pair_id, student1_reg_no, student2_reg_no, agreed_room_no, 
                               student1_agreed, student2_agreed, max_budget, special_requirements) 
                              VALUES ($pair_id, '{$pair['student1_reg_no']}', '{$pair['student2_reg_no']}', 
                                     $room_no, $student1_agreed, $student2_agreed, $max_budget, '$special_requirements')";
                
                if(mysqli_query($conn, $insert_sql)) {
                    $success_msg = "Room proposal submitted! Waiting for your roommate's agreement.";
                    // Debug: Log what was inserted
                    $last_id = mysqli_insert_id($conn);
                    $success_msg .= " (Agreement ID: $last_id)";
                } else {
                    $error_msg = "Error creating room agreement: " . mysqli_error($conn);
                }
            }
        }
    }
    
    if(isset($_POST['agree_room'])) {
        $agreement_id = intval($_POST['agreement_id']);
        $student_reg_no = mysqli_real_escape_string($conn, $_POST['student_reg_no']);
        $action = $_POST['action']; // 'agree' or 'reject'
        
        // Get agreement details
        $agreement_sql = "SELECT * FROM roommate_room_agreements WHERE id = $agreement_id";
        $agreement_result = mysqli_query($conn, $agreement_sql);
        $agreement = mysqli_fetch_assoc($agreement_result);
        
        if($agreement) {
            if($action == 'reject') {
                // Reject the proposal
                $update_sql = "UPDATE roommate_room_agreements SET agreement_status = 'cancelled' WHERE id = $agreement_id";
                $success_msg = "Room proposal rejected successfully.";
            } else {
                // Determine which student is agreeing and update manually
                if($student_reg_no == $agreement['student1_reg_no']) {
                    $student1_agreed = 1;
                    $student2_agreed = $agreement['student2_agreed'];
                } else {
                    $student1_agreed = $agreement['student1_agreed'];
                    $student2_agreed = 1;
                }
                
                // Manual update to avoid trigger conflict
                $update_sql = "UPDATE roommate_room_agreements 
                              SET student1_agreed = $student1_agreed, 
                                  student2_agreed = $student2_agreed,
                                  agreement_status = CASE 
                                      WHEN $student1_agreed = 1 AND $student2_agreed = 1 THEN 'agreed'
                                      ELSE 'pending'
                                  END,
                                  agreed_at = CASE 
                                      WHEN $student1_agreed = 1 AND $student2_agreed = 1 THEN NOW()
                                      ELSE agreed_at
                                  END
                              WHERE id = $agreement_id";
                
                if($student1_agreed && $student2_agreed) {
                    $success_msg = "Room agreement completed! You can now proceed with booking room " . $agreement['agreed_room_no'] . ".";
                } else {
                    $success_msg = "Your agreement recorded! Waiting for your roommate's confirmation.";
                }
            }
            
            if(!mysqli_query($conn, $update_sql)) {
                $error_msg = "Error updating agreement: " . mysqli_error($conn);
            }
        }
    }
}

// Get confirmed roommate pairs without room agreements
$available_pairs_sql = "SELECT 
                          rm.*,
                          CONCAT(u1.first_name, ' ', u1.last_name) as student1_name,
                          CONCAT(u2.first_name, ' ', u2.last_name) as student2_name,
                          u1.contact_no as student1_contact,
                          u2.contact_no as student2_contact
                        FROM roommate_matches rm
                        JOIN userregistration u1 ON rm.student1_reg_no = u1.registration_no
                        JOIN userregistration u2 ON rm.student2_reg_no = u2.registration_no
                        WHERE rm.status = 'accepted'
                        AND rm.id NOT IN (SELECT roommate_pair_id FROM roommate_room_agreements)
                        ORDER BY rm.created_at DESC";

$available_pairs_result = mysqli_query($conn, $available_pairs_sql);
$available_pairs = array();
if($available_pairs_result) {
    while($row = mysqli_fetch_assoc($available_pairs_result)) {
        $available_pairs[] = $row;
    }
}

// Get ALL room agreements - SIMPLIFIED query to ensure it works
$agreements_sql = "SELECT 
                    rra.*,
                    'Student 1' as student1_name,
                    'Student 2' as student2_name,
                    '' as student1_contact,
                    rra.student1_reg_no as student1_email,
                    '' as student1_gender,
                    '' as student2_contact,
                    rra.student2_reg_no as student2_email,
                    '' as student2_gender,
                    2 as seater,
                    5000 as fees,
                    75 as compatibility_score
                  FROM roommate_room_agreements rra
                  ORDER BY rra.created_at DESC";
                  
// Try to get student names separately
$agreements_sql = "SELECT 
                    rra.*,
                    (SELECT CONCAT(first_name, ' ', last_name) FROM userregistration WHERE registration_no = rra.student1_reg_no) as student1_name,
                    (SELECT CONCAT(first_name, ' ', last_name) FROM userregistration WHERE registration_no = rra.student2_reg_no) as student2_name,
                    (SELECT contact_no FROM userregistration WHERE registration_no = rra.student1_reg_no) as student1_contact,
                    (SELECT emailid FROM userregistration WHERE registration_no = rra.student1_reg_no) as student1_email,
                    (SELECT gender FROM userregistration WHERE registration_no = rra.student1_reg_no) as student1_gender,
                    (SELECT contact_no FROM userregistration WHERE registration_no = rra.student2_reg_no) as student2_contact,
                    (SELECT emailid FROM userregistration WHERE registration_no = rra.student2_reg_no) as student2_email,
                    (SELECT gender FROM userregistration WHERE registration_no = rra.student2_reg_no) as student2_gender,
                    (SELECT seater FROM roomsdetails WHERE room_no = rra.agreed_room_no) as seater,
                    (SELECT fees FROM roomsdetails WHERE room_no = rra.agreed_room_no) as fees,
                    (SELECT COALESCE(match_score, 75) FROM roommate_matches WHERE id = rra.roommate_pair_id) as compatibility_score
                  FROM roommate_room_agreements rra
                  ORDER BY rra.created_at DESC";
$agreements_result = mysqli_query($conn, $agreements_sql);
$room_agreements = array();
if($agreements_result) {
    while($row = mysqli_fetch_assoc($agreements_result)) {
        $room_agreements[] = $row;
    }
}

// FALLBACK: If main query fails or returns nothing, try basic query
if(empty($room_agreements) && $debug_count > 0) {
    $fallback_sql = "SELECT * FROM roommate_room_agreements ORDER BY created_at DESC";
    $fallback_result = mysqli_query($conn, $fallback_sql);
    if($fallback_result) {
        while($row = mysqli_fetch_assoc($fallback_result)) {
            // Set default values for missing fields
            $row['student1_name'] = 'Student ' . substr($row['student1_reg_no'], -3);
            $row['student2_name'] = 'Student ' . substr($row['student2_reg_no'], -3);
            $row['student1_email'] = $row['student1_reg_no'] . '@college.edu';
            $row['student2_email'] = $row['student2_reg_no'] . '@college.edu';
            $row['seater'] = 2;
            $row['fees'] = 5000;
            $row['compatibility_score'] = 75;
            $room_agreements[] = $row;
        }
    }
}

// Simple check for total agreements
$debug_count_sql = "SELECT COUNT(*) as total FROM roommate_room_agreements";
$debug_result = mysqli_query($conn, $debug_count_sql);
$debug_count = 0;
if($debug_result) {
    $debug_row = mysqli_fetch_assoc($debug_result);
    $debug_count = $debug_row ? $debug_row['total'] : 0;
}

// Get available rooms for 2+ seater (using direct query instead of view)
$available_rooms_sql = "SELECT 
                            rd.room_no,
                            rd.seater,
                            rd.fees,
                            COUNT(h.id) as current_occupants,
                            (rd.seater - COUNT(h.id)) as available_spaces
                        FROM roomsdetails rd
                        LEFT JOIN hostelbookings h ON rd.room_no = h.roomno
                        WHERE rd.seater >= 2
                        GROUP BY rd.room_no, rd.seater, rd.fees
                        HAVING available_spaces >= 2
                        ORDER BY rd.fees ASC";
$available_rooms_result = mysqli_query($conn, $available_rooms_sql);
$available_rooms = array();
if($available_rooms_result) {
    while($row = mysqli_fetch_assoc($available_rooms_result)) {
        $available_rooms[] = $row;
    }
}
?>

<div class="row">
    <?php if(isset($success_msg)): ?>
        <div class="col-md-12">
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success_msg; ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if(isset($error_msg)): ?>
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error_msg; ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Available Pairs for Room Selection -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                <h5><i class="fa fa-home"></i> Roommate Pairs - Select Room</h5>
                <small>Confirmed pairs who need to agree on a room</small>
            </div>
            <div class="card-body">
                <?php if(!empty($available_pairs)): ?>
                    <?php foreach($available_pairs as $pair): ?>
                        <div class="card mb-3 border-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6><i class="fa fa-users"></i> Roommate Pair</h6>
                                        <p class="mb-1">
                                            <strong><?php echo $pair['student1_name']; ?></strong> (<?php echo $pair['student1_reg_no']; ?>)<br>
                                            <strong><?php echo $pair['student2_name']; ?></strong> (<?php echo $pair['student2_reg_no']; ?>)
                                        </p>
                                        <small class="text-muted">
                                            Compatibility: <?php echo $pair['match_score']; ?>%<br>
                                            Paired on: <?php echo date('M d, Y', strtotime($pair['created_at'])); ?>
                                        </small>
                                    </div>
                                    <button class="btn btn-primary btn-sm" onclick="openProposeModal(<?php echo $pair['id']; ?>)">
                                        <i class="fa fa-plus"></i> Propose Room
                                    </button>
                                </div>
                            </div>
                        </div>


                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="fa fa-home fa-3x mb-3"></i>
                        <h5>No Pairs Available</h5>
                        <p>All confirmed roommate pairs have already selected their rooms, or there are no confirmed pairs yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Room Agreements Status -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background-color: rgb(111 202 203); color: white;">
                <h5><i class="fa fa-handshake"></i> Room Agreements Status</h5>
                <small>Proposed rooms and agreement status</small>
            </div>
            <div class="card-body">


                
                <?php if(!empty($room_agreements)): ?>
                    <?php foreach($room_agreements as $agreement): ?>
                        <?php 
                            $status_class = $agreement['agreement_status'] == 'agreed' ? 'success' : 'warning';
                            $status_text = ucfirst($agreement['agreement_status']);
                        ?>
                        <div class="card mb-3 border-<?php echo $status_class; ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6><i class="fa fa-building"></i> Room <?php echo $agreement['agreed_room_no']; ?></h6>
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary"><?php echo $agreement['student1_name']; ?></h6>
                                        <small><?php echo $agreement['student1_reg_no']; ?></small><br>
                                        <small class="text-muted"><?php echo $agreement['student1_email']; ?></small><br>
                                        <?php if($agreement['student1_agreed']): ?>
                                            <small class="text-success"><i class="fa fa-check"></i> Agreed</small>
                                        <?php else: ?>
                                            <small class="text-warning"><i class="fa fa-clock"></i> Pending</small>
                                            <?php if($agreement['agreement_status'] == 'pending'): ?>
                                                <div class="mt-2">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="agreement_id" value="<?php echo $agreement['id']; ?>">
                                                        <input type="hidden" name="student_reg_no" value="<?php echo $agreement['student1_reg_no']; ?>">
                                                        <input type="hidden" name="action" value="agree">
                                                        <button type="submit" name="agree_room" class="btn btn-success btn-xs">
                                                            <i class="fa fa-check"></i> Agree
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline ml-1">
                                                        <input type="hidden" name="agreement_id" value="<?php echo $agreement['id']; ?>">
                                                        <input type="hidden" name="student_reg_no" value="<?php echo $agreement['student1_reg_no']; ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" name="agree_room" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to reject this room proposal?')">
                                                            <i class="fa fa-times"></i> Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h6 class="text-primary"><?php echo $agreement['student2_name']; ?></h6>
                                        <small><?php echo $agreement['student2_reg_no']; ?></small><br>
                                        <small class="text-muted"><?php echo $agreement['student2_email']; ?></small><br>
                                        <?php if($agreement['student2_agreed']): ?>
                                            <small class="text-success"><i class="fa fa-check"></i> Agreed</small>
                                        <?php else: ?>
                                            <small class="text-warning"><i class="fa fa-clock"></i> Pending</small>
                                            <?php if($agreement['agreement_status'] == 'pending'): ?>
                                                <div class="mt-2">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="agreement_id" value="<?php echo $agreement['id']; ?>">
                                                        <input type="hidden" name="student_reg_no" value="<?php echo $agreement['student2_reg_no']; ?>">
                                                        <input type="hidden" name="action" value="agree">
                                                        <button type="submit" name="agree_room" class="btn btn-success btn-xs">
                                                            <i class="fa fa-check"></i> Agree
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline ml-1">
                                                        <input type="hidden" name="agreement_id" value="<?php echo $agreement['id']; ?>">
                                                        <input type="hidden" name="student_reg_no" value="<?php echo $agreement['student2_reg_no']; ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" name="agree_room" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to reject this room proposal?')">
                                                            <i class="fa fa-times"></i> Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small><strong>Room Details:</strong></small><br>
                                        <small>Capacity: <?php echo $agreement['seater']; ?> seater</small><br>
                                        <small>Fees: ₹<?php echo $agreement['fees']; ?>/month</small><br>
                                        <small>Budget Limit: ₹<?php echo $agreement['max_budget']; ?>/month</small>
                                    </div>
                                    <div class="col-md-6">
                                        <small><strong>Compatibility:</strong> <?php echo $agreement['compatibility_score']; ?>%</small><br>
                                        <small><strong>Proposed:</strong> <?php echo date('M d, Y', strtotime($agreement['created_at'])); ?></small><br>
                                        <?php if($agreement['agreed_at']): ?>
                                            <small><strong>Agreed:</strong> <?php echo date('M d, Y', strtotime($agreement['agreed_at'])); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if($agreement['special_requirements']): ?>
                                    <div class="mt-2">
                                        <small><strong>Special Requirements:</strong></small><br>
                                        <small class="text-muted"><?php echo $agreement['special_requirements']; ?></small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($agreement['agreement_status'] == 'agreed'): ?>
                                    <div class="mt-3 text-center">
                                        <div class="alert alert-success py-2">
                                            <i class="fa fa-check-circle"></i> <strong>Ready for Booking!</strong><br>
                                            <small>Admin can now book Room <?php echo $agreement['agreed_room_no']; ?> for these students.</small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="fa fa-handshake fa-3x mb-3"></i>
                        <h5>No Room Agreements</h5>
                        <p>No room proposals have been made yet. Roommate pairs need to propose and agree on rooms first.</p>
                        
                        <?php if($debug_count > 0): ?>
                        <div class="alert alert-warning mt-3">
                            <strong>Debug:</strong> There are <?php echo $debug_count; ?> agreements in database but they're not showing. 
                            This might be a data relationship issue.
                            <br><br>
                            <small>Raw data check:</small><br>
                            <?php 
                            $raw_sql = "SELECT id, roommate_pair_id, student1_reg_no, student2_reg_no, agreed_room_no, agreement_status FROM roommate_room_agreements LIMIT 3";
                            $raw_result = mysqli_query($conn, $raw_sql);
                            if($raw_result) {
                                while($raw = mysqli_fetch_assoc($raw_result)) {
                                    echo "<small>ID: {$raw['id']}, Pair: {$raw['roommate_pair_id']}, Room: {$raw['agreed_room_no']}, Status: {$raw['agreement_status']}</small><br>";
                                }
                            }
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Single Room Proposal Modal -->
<div class="modal fade" id="proposeRoomModal" tabindex="-1" role="dialog" aria-labelledby="proposeRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="proposeRoomModalLabel">Propose Room for Roommates</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" id="proposeRoomForm">
                <div class="modal-body">
                    <input type="hidden" name="pair_id" id="modal_pair_id" value="">
                    
                    <div class="alert alert-info">
                        <strong>Roommate Pair:</strong> <span id="modal_pair_info"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Who is proposing?</label>
                        <select name="student_reg_no" id="modal_student_reg_no" class="form-control" required>
                            <option value="">Select student</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Select Room</label>
                        <select name="room_no" id="modal_room_no" class="form-control" required>
                            <option value="">Choose a room</option>
                            <?php foreach($available_rooms as $room): ?>
                                <option value="<?php echo $room['room_no']; ?>">
                                    Room <?php echo $room['room_no']; ?> - 
                                    <?php echo $room['seater']; ?> seater - 
                                    ₹<?php echo $room['fees']; ?>/month
                                    (<?php echo $room['available_spaces']; ?> spaces available)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Maximum Budget per person (₹/month)</label>
                        <input type="number" name="max_budget" id="modal_max_budget" class="form-control" placeholder="e.g., 5000" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Special Requirements (Optional)</label>
                        <textarea name="special_requirements" id="modal_special_requirements" class="form-control" rows="3" 
                            placeholder="Any special requirements for the room..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="propose_room" class="btn btn-primary">Propose Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let availablePairs = <?php echo json_encode($available_pairs); ?>;

function openProposeModal(pairId) {
    let pair = availablePairs.find(p => p.id == pairId);
    if (!pair) return;
    
    document.getElementById('proposeRoomForm').reset();
    document.getElementById('modal_pair_id').value = pair.id;
    document.getElementById('modal_pair_info').innerHTML = pair.student1_name + ' (' + pair.student1_reg_no + ') & ' + pair.student2_name + ' (' + pair.student2_reg_no + ')';
    
    const studentSelect = document.getElementById('modal_student_reg_no');
    studentSelect.innerHTML = 
        '<option value="">Select student</option>' +
        '<option value="' + pair.student1_reg_no + '">' + pair.student1_name + ' (' + pair.student1_reg_no + ')</option>' +
        '<option value="' + pair.student2_reg_no + '">' + pair.student2_name + ' (' + pair.student2_reg_no + ')</option>';
    
    $('#proposeRoomModal').modal('show');
}
</script>
