<!-- Check-in/Check-out Management Page -->
<div class="container-fluid">
    <?php
    // Display success message
    if(isset($_SESSION['success_msg'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> '.$_SESSION['success_msg'].'
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
              </div>';
        unset($_SESSION['success_msg']);
    }
    
    // Display error message
    if(isset($_SESSION['error_msg'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> '.$_SESSION['error_msg'].'
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
              </div>';
        unset($_SESSION['error_msg']);
    }
    ?>
    
    <div class="row">
        <!-- Entry Form -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fa fa-door-open"></i> Hostel Check-in / Check-out Management</h5>
                </div>
                <div class="card-body">
                    <form id="checkinForm" method="POST" action="partials/_checkinManage.php">
                        <div class="row">
                            <!-- Student Selection -->
                            <div class="form-group col-md-4">
                                <label for="reg_no"><i class="fa fa-user"></i> Select Student *</label>
                                <select name="reg_no" id="reg_no" class="form-control" required onchange="loadStudentDetails(this.value)">
                                    <option value="">-- Select Student --</option>
                                    <?php
                                    // Fetch all registered students
                                    $student_sql = "SELECT u.registration_no, CONCAT(u.first_name, ' ', u.last_name) as full_name, 
                                                   COALESCE(h.roomno, 'Not Assigned') as room_no
                                                   FROM userregistration u
                                                   LEFT JOIN hostelbookings h ON u.registration_no = h.regno
                                                   ORDER BY u.first_name";
                                    $student_result = mysqli_query($conn, $student_sql);
                                    if($student_result) {
                                        while($student = mysqli_fetch_assoc($student_result)) {
                                            echo '<option value="'.$student['registration_no'].'" data-name="'.$student['full_name'].'" data-room="'.$student['room_no'].'">';
                                            echo $student['registration_no'].' - '.$student['full_name'].' (Room: '.$student['room_no'].')';
                                            echo '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Action Type -->
                            <div class="form-group col-md-3">
                                <label for="action_type"><i class="fa fa-exchange-alt"></i> Action Type *</label>
                                <select name="action_type" id="action_type" class="form-control" required>
                                    <option value="">-- Select Action --</option>
                                    <option value="check-in">✅ Check-in (Entry)</option>
                                    <option value="check-out">🚪 Check-out (Exit)</option>
                                </select>
                            </div>

                            <!-- Date (Auto-filled with today) -->
                            <div class="form-group col-md-2">
                                <label for="action_date"><i class="fa fa-calendar"></i> Date *</label>
                                <input type="date" name="action_date" id="action_date" class="form-control" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <!-- Time (Auto-filled with current time) -->
                            <div class="form-group col-md-3">
                                <label for="action_time"><i class="fa fa-clock"></i> Time *</label>
                                <input type="time" name="action_time" id="action_time" class="form-control" 
                                       value="<?php echo date('H:i'); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Hidden fields for student details -->
                            <input type="hidden" name="student_name" id="student_name">
                            <input type="hidden" name="room_no" id="room_no_hidden">

                            <!-- Remarks -->
                            <div class="form-group col-md-8">
                                <label for="remarks"><i class="fa fa-comment"></i> Remarks (Optional)</label>
                                <input type="text" name="remarks" id="remarks" class="form-control" 
                                       placeholder="e.g., Going home for weekend, Medical emergency, etc.">
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group col-md-4 d-flex align-items-end">
                                <button type="submit" name="submit_entry" class="btn btn-success btn-block">
                                    <i class="fa fa-save"></i> Record Entry
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6><i class="fa fa-sign-in-alt"></i> Today's Check-ins</h6>
                    <?php
                    $today_checkin = mysqli_query($conn, "SELECT COUNT(*) as count FROM hostel_checkin_checkout 
                                                          WHERE action_type='check-in' AND action_date=CURDATE()");
                    $checkin_count = mysqli_fetch_assoc($today_checkin)['count'];
                    ?>
                    <h3><?php echo $checkin_count; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6><i class="fa fa-sign-out-alt"></i> Today's Check-outs</h6>
                    <?php
                    $today_checkout = mysqli_query($conn, "SELECT COUNT(*) as count FROM hostel_checkin_checkout 
                                                           WHERE action_type='check-out' AND action_date=CURDATE()");
                    $checkout_count = mysqli_fetch_assoc($today_checkout)['count'];
                    ?>
                    <h3><?php echo $checkout_count; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6><i class="fa fa-users"></i> Currently In Hostel</h6>
                    <?php
                    // Calculate students currently in hostel (last action was check-in)
                    $in_hostel = mysqli_query($conn, "SELECT COUNT(DISTINCT reg_no) as count FROM hostel_checkin_checkout cc1
                                                      WHERE action_type='check-in' 
                                                      AND NOT EXISTS (
                                                          SELECT 1 FROM hostel_checkin_checkout cc2 
                                                          WHERE cc2.reg_no = cc1.reg_no 
                                                          AND cc2.action_type='check-out'
                                                          AND (cc2.action_date > cc1.action_date 
                                                               OR (cc2.action_date = cc1.action_date AND cc2.action_time > cc1.action_time))
                                                      )");
                    $in_count = mysqli_fetch_assoc($in_hostel)['count'];
                    ?>
                    <h3><?php echo $in_count; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6><i class="fa fa-door-closed"></i> Currently Out</h6>
                    <?php
                    // Calculate students currently out (last action was check-out)
                    $out_hostel = mysqli_query($conn, "SELECT COUNT(DISTINCT reg_no) as count FROM hostel_checkin_checkout cc1
                                                       WHERE action_type='check-out' 
                                                       AND NOT EXISTS (
                                                           SELECT 1 FROM hostel_checkin_checkout cc2 
                                                           WHERE cc2.reg_no = cc1.reg_no 
                                                           AND cc2.action_type='check-in'
                                                           AND (cc2.action_date > cc1.action_date 
                                                                OR (cc2.action_date = cc1.action_date AND cc2.action_time > cc1.action_time))
                                                       )");
                    $out_count = mysqli_fetch_assoc($out_hostel)['count'];
                    ?>
                    <h3><?php echo $out_count; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Entries Table -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5><i class="fa fa-list"></i> Recent Check-in/Check-out Records</h5>
                </div>
                <div class="card-body">
                    <!-- Filter Options -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select id="filterAction" class="form-control" onchange="filterRecords()">
                                <option value="">All Actions</option>
                                <option value="check-in">Check-in Only</option>
                                <option value="check-out">Check-out Only</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" id="filterDate" class="form-control" onchange="filterRecords()" 
                                   placeholder="Filter by date">
                        </div>
                        <div class="col-md-3">
                            <input type="text" id="searchStudent" class="form-control" onkeyup="filterRecords()" 
                                   placeholder="Search by Reg No or Name">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-secondary" onclick="clearFilters()">
                                <i class="fa fa-redo"></i> Clear Filters
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="checkinTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Reg No</th>
                                    <th>Student Name</th>
                                    <th>Contact No</th>
                                    <th>Room No</th>
                                    <th>Action</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $records_sql = "SELECT cc.*, u.contact_no 
                                               FROM hostel_checkin_checkout cc
                                               LEFT JOIN userregistration u ON cc.reg_no = u.registration_no
                                               ORDER BY cc.action_date DESC, cc.action_time DESC LIMIT 50";
                                $records_result = mysqli_query($conn, $records_sql);
                                
                                if($records_result && mysqli_num_rows($records_result) > 0) {
                                    while($record = mysqli_fetch_assoc($records_result)) {
                                        $badge_class = $record['action_type'] == 'check-in' ? 'badge-success' : 'badge-warning';
                                        $icon = $record['action_type'] == 'check-in' ? '✅' : '🚪';
                                        
                                        echo '<tr>';
                                        echo '<td>'.$record['id'].'</td>';
                                        echo '<td>'.$record['reg_no'].'</td>';
                                        echo '<td>'.$record['student_name'].'</td>';
                                        echo '<td>'.($record['contact_no'] ? $record['contact_no'] : 'N/A').'</td>';
                                        echo '<td>'.($record['room_no'] ? $record['room_no'] : 'N/A').'</td>';
                                        echo '<td><span class="badge '.$badge_class.'">'.$icon.' '.ucfirst($record['action_type']).'</span></td>';
                                        echo '<td>'.date('d-M-Y', strtotime($record['action_date'])).'</td>';
                                        echo '<td>'.date('h:i A', strtotime($record['action_time'])).'</td>';
                                        echo '<td>'.($record['remarks'] ? $record['remarks'] : '-').'</td>';
                                        echo '<td>
                                                <button class="btn btn-sm btn-danger" onclick="deleteRecord('.$record['id'].')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                              </td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="10" class="text-center">No records found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill student details when selected
function loadStudentDetails(regNo) {
    const selectElement = document.getElementById('reg_no');
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    
    if(regNo) {
        const studentName = selectedOption.getAttribute('data-name');
        const roomNo = selectedOption.getAttribute('data-room');
        
        document.getElementById('student_name').value = studentName;
        document.getElementById('room_no_hidden').value = roomNo !== 'Not Assigned' ? roomNo : '';
    }
}

// Filter records
function filterRecords() {
    const actionFilter = document.getElementById('filterAction').value.toLowerCase();
    const dateFilter = document.getElementById('filterDate').value;
    const searchText = document.getElementById('searchStudent').value.toLowerCase();
    
    const table = document.getElementById('checkinTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for(let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        if(cells.length > 0) {
            const regNo = cells[1].textContent.toLowerCase();
            const studentName = cells[2].textContent.toLowerCase();
            const action = cells[4].textContent.toLowerCase();
            const date = cells[5].textContent;
            
            let showRow = true;
            
            // Action filter
            if(actionFilter && !action.includes(actionFilter)) {
                showRow = false;
            }
            
            // Search filter
            if(searchText && !regNo.includes(searchText) && !studentName.includes(searchText)) {
                showRow = false;
            }
            
            // Date filter (if needed, convert date format)
            if(dateFilter) {
                const filterDateObj = new Date(dateFilter);
                const rowDateStr = date.split('-');
                const months = {Jan:0, Feb:1, Mar:2, Apr:3, May:4, Jun:5, Jul:6, Aug:7, Sep:8, Oct:9, Nov:10, Dec:11};
                const rowDateObj = new Date(rowDateStr[2], months[rowDateStr[1]], rowDateStr[0]);
                
                if(filterDateObj.toDateString() !== rowDateObj.toDateString()) {
                    showRow = false;
                }
            }
            
            rows[i].style.display = showRow ? '' : 'none';
        }
    }
}

// Clear all filters
function clearFilters() {
    document.getElementById('filterAction').value = '';
    document.getElementById('filterDate').value = '';
    document.getElementById('searchStudent').value = '';
    filterRecords();
}

// Delete record
function deleteRecord(id) {
    if(confirm('Are you sure you want to delete this record?')) {
        window.location.href = 'partials/_checkinManage.php?delete_id=' + id;
    }
}

// Auto-update time every minute
setInterval(function() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('action_time').value = hours + ':' + minutes;
}, 60000);
</script>

<style>
.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.badge {
    font-size: 14px;
    padding: 5px 10px;
}
.table th {
    font-size: 14px;
}
.table td {
    vertical-align: middle;
}
</style>
