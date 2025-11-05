<!-- Attendance Management Page -->
<style>
.attendance-card { border-left: 4px solid #3b82f6; }
.vacation-card { border-left: 4px solid #f59e0b; }
.present-card { border-left: 4px solid #10b981; }
.calendar-day { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; margin: 2px; border-radius: 6px; font-size: 12px; font-weight: 500; }
.day-present { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
.day-vacation { background: #fed7aa; color: #92400e; border: 1px solid #f59e0b; }
.day-future { background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; }
</style>

<div class="container-fluid">
    <?php
    if(isset($_SESSION['success_msg'])) {
        echo '<div class="alert alert-success alert-dismissible fade show"><strong>Success!</strong> '.$_SESSION['success_msg'].'<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>';
        unset($_SESSION['success_msg']);
    }
    if(isset($_SESSION['error_msg'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show"><strong>Error!</strong> '.$_SESSION['error_msg'].'<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>';
        unset($_SESSION['error_msg']);
    }
    ?>

    <div class="row mb-3">
        <div class="col-md-12">
            <h3><i class="fa fa-calendar-check"></i> Student Attendance Management</h3>
            <p class="text-muted">Track student attendance based on vacation records</p>
        </div>
    </div>

    <!-- Selection Form -->
    <div class="row">
        <div class="col-md-12">
            <div class="card attendance-card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fa fa-search"></i> Calculate Attendance</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="attendanceForm">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label><i class="fa fa-user"></i> Select Student *</label>
                                <select name="reg_no" id="reg_no" class="form-control" required onchange="loadStudentInfo(this)">
                                    <option value="">-- Select Student --</option>
                                    <?php
                                    $student_sql = "SELECT u.registration_no, CONCAT(u.first_name, ' ', u.last_name) as name, 
                                                   COALESCE(h.roomno, 'Not Assigned') as room
                                                   FROM userregistration u 
                                                   LEFT JOIN hostelbookings h ON u.registration_no = h.regno 
                                                   ORDER BY u.first_name";
                                    $student_result = mysqli_query($conn, $student_sql);
                                    while($student = mysqli_fetch_assoc($student_result)) {
                                        $selected = (isset($_POST['reg_no']) && $_POST['reg_no'] == $student['registration_no']) ? 'selected' : '';
                                        echo '<option value="'.$student['registration_no'].'" data-name="'.$student['name'].'" data-room="'.$student['room'].'" '.$selected.'>';
                                        echo $student['registration_no'].' - '.$student['name'].' (Room: '.$student['room'].')';
                                        echo '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label><i class="fa fa-calendar"></i> Month *</label>
                                <select name="month" id="month" class="form-control" required>
                                    <?php
                                    $current_month = isset($_POST['month']) ? $_POST['month'] : date('n');
                                    for($m = 1; $m <= 12; $m++) {
                                        $selected = ($m == $current_month) ? 'selected' : '';
                                        echo '<option value="'.$m.'" '.$selected.'>'.date('F', mktime(0, 0, 0, $m, 1)).'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label><i class="fa fa-calendar-alt"></i> Year *</label>
                                <select name="year" id="year" class="form-control" required>
                                    <?php
                                    $current_year = isset($_POST['year']) ? $_POST['year'] : date('Y');
                                    for($y = date('Y') - 2; $y <= date('Y') + 1; $y++) {
                                        $selected = ($y == $current_year) ? 'selected' : '';
                                        echo '<option value="'.$y.'" '.$selected.'>'.$y.'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" name="calculate_attendance" class="btn btn-primary btn-block">
                                    <i class="fa fa-calculator"></i> Calculate
                                </button>
                            </div>
                            <div class="form-group col-md-2">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="full_month" name="full_month" value="1">
                                    <label class="custom-control-label" for="full_month">Full Month</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Process attendance calculation
    if(isset($_POST['calculate_attendance'])) {
        $reg_no = mysqli_real_escape_string($conn, $_POST['reg_no']);
        $month = intval($_POST['month']);
        $year = intval($_POST['year']);
        
        // Get student details
        $student_sql = "SELECT CONCAT(first_name, ' ', last_name) as name FROM userregistration WHERE registration_no = '$reg_no'";
        $student_res = mysqli_query($conn, $student_sql);
        $student_data = mysqli_fetch_assoc($student_res);
        $student_name = $student_data['name'];
        
        // Calculate total days in month
        $total_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        // Get current date for comparison
        $current_date = date('Y-m-d');
        $selected_month_end = sprintf("%04d-%02d-%02d", $year, $month, $total_days);
        
        // If selected month is current month, only count up to today (unless full_month is checked)
        $current_year_month = date('Y-m');
        $selected_year_month = sprintf("%04d-%02d", $year, $month);
        $full_month = isset($_POST['full_month']) && $_POST['full_month'] == '1';
        
        if($current_year_month == $selected_year_month && !$full_month) {
            $total_days = intval(date('j')); // Current day of month
            $selected_month_end = $current_date;
        }
        
        // Calculate vacation days for this month
        // Logic: If check-out time is >= 12:00 AM (00:00), vacation starts next day
        $vacation_days = 0;
        $vacation_details = array();
        
        // Get all vacation check-outs for this student
        $vacation_sql = "SELECT cc1.*, 
                        (SELECT action_date FROM hostel_checkin_checkout cc2 
                         WHERE cc2.reg_no = cc1.reg_no 
                         AND cc2.pass_type = 'vacation' 
                         AND cc2.action_type = 'check-in'
                         AND (cc2.action_date > cc1.action_date OR (cc2.action_date = cc1.action_date AND cc2.action_time > cc1.action_time))
                         ORDER BY cc2.action_date ASC, cc2.action_time ASC LIMIT 1) as actual_return_date
                        FROM hostel_checkin_checkout cc1
                        WHERE cc1.reg_no = '$reg_no' 
                        AND cc1.pass_type = 'vacation' 
                        AND cc1.action_type = 'check-out'
                        ORDER BY cc1.action_date";
        
        $vacation_result = mysqli_query($conn, $vacation_sql);
        
        while($vacation = mysqli_fetch_assoc($vacation_result)) {
            $checkout_date = $vacation['action_date'];
            $checkout_time = $vacation['action_time'];
            $actual_return = $vacation['actual_return_date'];
            $expected_return = $vacation['expected_return_date'];
            
            // Determine vacation start date based on 12 AM rule
            // Rule: Vacation always starts the NEXT day after checkout
            // Because if they checkout on Nov 5, they were present on Nov 5
            // Vacation absence starts from Nov 6
            $start_date = date('Y-m-d', strtotime($checkout_date . ' +1 day'));
            
            // Determine end date: use actual return if exists, otherwise expected return, otherwise current date
            if($actual_return) {
                // Student returned on this date, so vacation ended day before
                $end_date = date('Y-m-d', strtotime($actual_return . ' -1 day'));
            } elseif($expected_return) {
                // Expected return date means student returns on this date, vacation ends day before
                $end_date = date('Y-m-d', strtotime($expected_return . ' -1 day'));
            } else {
                // Still on vacation, use current date or end of month (whichever is earlier)
                $end_date = ($current_date < $selected_month_end) ? $current_date : $selected_month_end;
            }
            
            // Calculate days only within the selected month
            $month_start = sprintf("%04d-%02d-01", $year, $month);
            $month_end = sprintf("%04d-%02d-%02d", $year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));
            
            // Get overlap with selected month
            $actual_start = max($start_date, $month_start);
            $actual_end = min($end_date, $month_end);
            
            // Only count if there's overlap with this month
            if($actual_start <= $actual_end) {
                $start_dt = new DateTime($actual_start);
                $end_dt = new DateTime($actual_end);
                $end_dt->modify('+1 day'); // Include end date
                $interval = $start_dt->diff($end_dt);
                $days = $interval->days;
                
                $vacation_days += $days;
                $vacation_details[] = array(
                    'start' => $actual_start,
                    'end' => $actual_end,
                    'days' => $days,
                    'destination' => $vacation['destination'],
                    'checkout_time' => $checkout_time,
                    'checkout_date' => $checkout_date,
                    'return_date' => $actual_return ? $actual_return : ($expected_return ? $expected_return : 'Still on vacation')
                );
            }
        }
        
        // Calculate present days
        $present_days = $total_days - $vacation_days;
        $attendance_percentage = ($total_days > 0) ? round(($present_days / $total_days) * 100, 2) : 0;
        
        // Save to database
        $recorded_by = $_SESSION['adminuserId'];
        $save_sql = "INSERT INTO student_attendance 
                    (reg_no, student_name, month, year, total_days, vacation_days, present_days, attendance_percentage, calculated_by)
                    VALUES ('$reg_no', '$student_name', $month, $year, $total_days, $vacation_days, $present_days, $attendance_percentage, '$recorded_by')
                    ON DUPLICATE KEY UPDATE 
                    total_days = $total_days,
                    vacation_days = $vacation_days,
                    present_days = $present_days,
                    attendance_percentage = $attendance_percentage,
                    calculated_on = CURRENT_TIMESTAMP,
                    calculated_by = '$recorded_by'";
        
        mysqli_query($conn, $save_sql);
        
        // Display results
        ?>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fa fa-chart-bar"></i> Attendance Report - <?php echo $student_name; ?> (<?php echo date('F Y', mktime(0,0,0,$month,1,$year)); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card attendance-card">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Total Days</h6>
                                        <h2 class="text-primary"><?php echo $total_days; ?></h2>
                                        <small>Days in <?php echo date('F', mktime(0,0,0,$month,1)); ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card vacation-card">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Vacation Days</h6>
                                        <h2 class="text-warning"><?php echo $vacation_days; ?></h2>
                                        <small>Days on vacation</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card present-card">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Present Days</h6>
                                        <h2 class="text-success"><?php echo $present_days; ?></h2>
                                        <small>Days in hostel</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card" style="border-left: 4px solid #8b5cf6;">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Attendance %</h6>
                                        <h2 style="color: #8b5cf6;"><?php echo $attendance_percentage; ?>%</h2>
                                        <small>Overall attendance</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calendar View -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6><i class="fa fa-calendar"></i> Day-wise Breakdown</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap">
                                    <?php
                                    // Create array of vacation dates
                                    $vacation_dates = array();
                                    foreach($vacation_details as $vac) {
                                        $start = new DateTime($vac['start']);
                                        $end = new DateTime($vac['end']);
                                        $end->modify('+1 day');
                                        $period = new DatePeriod($start, new DateInterval('P1D'), $end);
                                        foreach($period as $date) {
                                            $vacation_dates[] = $date->format('Y-m-d');
                                        }
                                    }
                                    
                                    // Display calendar
                                    for($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $day++) {
                                        $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
                                        $is_vacation = in_array($date, $vacation_dates);
                                        $is_future = $date > $current_date;
                                        
                                        if($is_future) {
                                            $class = 'day-future';
                                            $title = 'Future date';
                                        } elseif($is_vacation) {
                                            $class = 'day-vacation';
                                            $title = 'On vacation';
                                        } else {
                                            $class = 'day-present';
                                            $title = 'Present';
                                        }
                                        
                                        echo '<div class="calendar-day '.$class.'" title="'.$title.'">'.$day.'</div>';
                                    }
                                    ?>
                                </div>
                                <div class="mt-3">
                                    <span class="badge badge-success mr-2">■ Present</span>
                                    <span class="badge badge-warning mr-2">■ Vacation</span>
                                    <span class="badge badge-secondary">■ Future</span>
                                </div>
                            </div>
                        </div>

                        <!-- Vacation Details -->
                        <?php if(count($vacation_details) > 0): ?>
                        <div class="card">
                            <div class="card-header bg-warning text-white">
                                <h6><i class="fa fa-plane"></i> Vacation Details</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Checkout Date</th>
                                            <th>Return Date</th>
                                            <th>Vacation Start</th>
                                            <th>Vacation End</th>
                                            <th>Days Absent</th>
                                            <th>Destination</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($vacation_details as $vac): ?>
                                        <tr>
                                            <td><?php echo date('d M Y', strtotime($vac['checkout_date'])); ?> <small class="text-muted">(<?php echo date('h:i A', strtotime($vac['checkout_time'])); ?>)</small></td>
                                            <td><?php echo is_string($vac['return_date']) && $vac['return_date'] != 'Still on vacation' ? date('d M Y', strtotime($vac['return_date'])) : $vac['return_date']; ?></td>
                                            <td><?php echo date('d M Y', strtotime($vac['start'])); ?></td>
                                            <td><?php echo date('d M Y', strtotime($vac['end'])); ?></td>
                                            <td><span class="badge badge-warning"><?php echo $vac['days']; ?> days</span></td>
                                            <td><?php echo $vac['destination'] ?: '-'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <!-- Attendance History -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-history"></i> Attendance History</h5>
                    <button class="btn btn-danger btn-sm" onclick="clearAllHistory()">
                        <i class="fa fa-trash"></i> Clear All History
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> <strong>Note:</strong> These are saved calculations. If you deleted vacation records, click the delete button for that record or recalculate to update.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Reg No</th>
                                    <th>Student Name</th>
                                    <th>Month</th>
                                    <th>Year</th>
                                    <th>Total Days</th>
                                    <th>Vacation Days</th>
                                    <th>Present Days</th>
                                    <th>Attendance %</th>
                                    <th>Calculated On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $history_sql = "SELECT * FROM student_attendance ORDER BY year DESC, month DESC, student_name LIMIT 50";
                                $history_result = mysqli_query($conn, $history_sql);
                                
                                if(mysqli_num_rows($history_result) > 0) {
                                    while($record = mysqli_fetch_assoc($history_result)) {
                                        $percentage_class = $record['attendance_percentage'] >= 75 ? 'text-success' : 'text-danger';
                                        echo '<tr>';
                                        echo '<td>'.$record['reg_no'].'</td>';
                                        echo '<td>'.$record['student_name'].'</td>';
                                        echo '<td>'.date('F', mktime(0,0,0,$record['month'],1)).'</td>';
                                        echo '<td>'.$record['year'].'</td>';
                                        echo '<td>'.$record['total_days'].'</td>';
                                        echo '<td><span class="badge badge-warning">'.$record['vacation_days'].'</span></td>';
                                        echo '<td><span class="badge badge-success">'.$record['present_days'].'</span></td>';
                                        echo '<td class="'.$percentage_class.' font-weight-bold">'.$record['attendance_percentage'].'%</td>';
                                        echo '<td>'.date('d-M-Y h:i A', strtotime($record['calculated_on'])).'</td>';
                                        echo '<td><button class="btn btn-sm btn-danger" onclick="deleteRecord('.$record['id'].')"><i class="fa fa-trash"></i></button></td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="10" class="text-center">No attendance records yet. Calculate attendance above.</td></tr>';
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
function loadStudentInfo(select) {
    const option = select.options[select.selectedIndex];
    // Can add additional logic here if needed
}

function deleteRecord(id) {
    if(confirm('Delete this attendance record?')) {
        window.location.href = 'partials/_attendanceManage.php?delete_id=' + id;
    }
}

function clearAllHistory() {
    if(confirm('Are you sure you want to delete ALL attendance history records?\n\nThis action cannot be undone!')) {
        if(confirm('This will permanently delete all saved attendance calculations. Continue?')) {
            window.location.href = 'partials/_attendanceManage.php?clear_all=1';
        }
    }
}
</script>
