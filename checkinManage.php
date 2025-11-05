<!-- Gate Pass & Vacation Management -->
<style>
.nav-tabs .nav-link { font-weight: 500; }
.nav-tabs .nav-link.active { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white !important; }
.stat-card { transition: transform 0.2s; }
.stat-card:hover { transform: translateY(-2px); }
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
            <h3><i class="fa fa-door-open"></i> Gate Pass & Vacation Management</h3>
            <p class="text-muted">Manage student gate passes (short-term) and vacation leaves (long-term)</p>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#gatepass"><i class="fa fa-clock"></i> Gate Pass</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#vacation"><i class="fa fa-plane"></i> Vacation</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#records"><i class="fa fa-list"></i> All Records</a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- GATE PASS TAB -->
        <div class="tab-pane fade show active" id="gatepass">
            <div class="row mt-3">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5><i class="fa fa-clock"></i> Gate Pass Entry <small>(Short-term exits)</small></h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="partials/_checkinManage.php">
                                <input type="hidden" name="pass_type" value="gate-pass">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label><i class="fa fa-user"></i> Student *</label>
                                        <select name="reg_no" id="gp_reg_no" class="form-control" required onchange="loadDetails(this,'gp')">
                                            <option value="">-- Select Student --</option>
                                            <?php
                                            $sql = "SELECT u.registration_no, CONCAT(u.first_name, ' ', u.last_name) as name, COALESCE(h.roomno, 'N/A') as room
                                                    FROM userregistration u LEFT JOIN hostelbookings h ON u.registration_no = h.regno ORDER BY u.first_name";
                                            $res = mysqli_query($conn, $sql);
                                            while($s = mysqli_fetch_assoc($res)) {
                                                // Check current status for gate pass
                                                $status_sql = "SELECT action_type FROM hostel_checkin_checkout 
                                                              WHERE reg_no = '".$s['registration_no']."' AND pass_type = 'gate-pass' 
                                                              ORDER BY action_date DESC, action_time DESC LIMIT 1";
                                                $status_res = mysqli_query($conn, $status_sql);
                                                $status = '';
                                                if(mysqli_num_rows($status_res) > 0) {
                                                    $status_data = mysqli_fetch_assoc($status_res);
                                                    $status = $status_data['action_type'] == 'check-out' ? ' [OUT]' : ' [IN]';
                                                }
                                                echo '<option value="'.$s['registration_no'].'" data-name="'.$s['name'].'" data-room="'.$s['room'].'">'.$s['registration_no'].' - '.$s['name'].' (Room: '.$s['room'].')'.$status.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Name</label>
                                        <input type="text" name="student_name" id="gp_name" class="form-control" readonly>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Room</label>
                                        <input type="text" name="room_no" id="gp_room" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label><i class="fa fa-exchange-alt"></i> Action *</label>
                                        <select name="action_type" id="gp_action" class="form-control" required>
                                            <option value="">-- Select --</option>
                                            <option value="check-out">Going Out</option>
                                            <option value="check-in">Returning</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label><i class="fa fa-calendar"></i> Date *</label>
                                        <input type="date" name="action_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label><i class="fa fa-clock"></i> Time *</label>
                                        <input type="time" name="action_time" class="form-control" value="<?php echo date('H:i'); ?>" required>
                                    </div>
                                </div>
                                <div class="row" id="gp_return" style="display:none;">
                                    <div class="form-group col-md-6">
                                        <label>Expected Return Date</label>
                                        <input type="date" name="expected_return_date" class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Expected Return Time</label>
                                        <input type="time" name="expected_return_time" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa fa-map-marker-alt"></i> Destination</label>
                                    <input type="text" name="destination" class="form-control" placeholder="e.g., City Market">
                                </div>
                                <div class="form-group">
                                    <label><i class="fa fa-comment"></i> Purpose</label>
                                    <textarea name="purpose" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa fa-sticky-note"></i> Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-info btn-block"><i class="fa fa-save"></i> Submit Gate Pass</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white stat-card mb-3" style="background-color: #17a2b8 !important;">
                        <div class="card-body">
                            <h6><i class="fa fa-users"></i> Currently Out (Gate Pass)</h6>
                            <?php
                            $gp_out = mysqli_query($conn, "SELECT COUNT(DISTINCT reg_no) as c FROM hostel_checkin_checkout cc1
                                WHERE pass_type='gate-pass' AND action_type='check-out' AND NOT EXISTS (
                                    SELECT 1 FROM hostel_checkin_checkout cc2 WHERE cc2.reg_no=cc1.reg_no AND cc2.pass_type='gate-pass'
                                    AND cc2.action_type='check-in' AND (cc2.action_date>cc1.action_date OR (cc2.action_date=cc1.action_date AND cc2.action_time>cc1.action_time)))");
                            echo '<h3>'.mysqli_fetch_assoc($gp_out)['c'].'</h3>';
                            ?>
                        </div>
                    </div>
                    <div class="card bg-success text-white stat-card" style="background-color: #28a745 !important;">
                        <div class="card-body">
                            <h6><i class="fa fa-check"></i> Today's Activity</h6>
                            <?php
                            $gp_today = mysqli_query($conn, "SELECT COUNT(*) as c FROM hostel_checkin_checkout WHERE pass_type='gate-pass' AND action_date=CURDATE()");
                            echo '<h3>'.mysqli_fetch_assoc($gp_today)['c'].'</h3>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VACATION TAB -->
        <div class="tab-pane fade" id="vacation">
            <div class="row mt-3">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-warning text-white">
                            <h5><i class="fa fa-plane"></i> Vacation Leave Entry <small>(Long-term leaves)</small></h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="partials/_checkinManage.php">
                                <input type="hidden" name="pass_type" value="vacation">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label><i class="fa fa-user"></i> Student *</label>
                                        <select name="reg_no" id="vac_reg_no" class="form-control" required onchange="loadDetails(this,'vac')">
                                            <option value="">-- Select Student --</option>
                                            <?php
                                            mysqli_data_seek($res, 0);
                                            while($s = mysqli_fetch_assoc($res)) {
                                                // Check current status for vacation
                                                $status_sql = "SELECT action_type FROM hostel_checkin_checkout 
                                                              WHERE reg_no = '".$s['registration_no']."' AND pass_type = 'vacation' 
                                                              ORDER BY action_date DESC, action_time DESC LIMIT 1";
                                                $status_res = mysqli_query($conn, $status_sql);
                                                $status = '';
                                                if(mysqli_num_rows($status_res) > 0) {
                                                    $status_data = mysqli_fetch_assoc($status_res);
                                                    $status = $status_data['action_type'] == 'check-out' ? ' [ON VACATION]' : ' [IN HOSTEL]';
                                                }
                                                echo '<option value="'.$s['registration_no'].'" data-name="'.$s['name'].'" data-room="'.$s['room'].'">'.$s['registration_no'].' - '.$s['name'].' (Room: '.$s['room'].')'.$status.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Name</label>
                                        <input type="text" name="student_name" id="vac_name" class="form-control" readonly>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Room</label>
                                        <input type="text" name="room_no" id="vac_room" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label><i class="fa fa-exchange-alt"></i> Action *</label>
                                        <select name="action_type" id="vac_action" class="form-control" required>
                                            <option value="">-- Select --</option>
                                            <option value="check-out">Starting Vacation</option>
                                            <option value="check-in">Returning</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label><i class="fa fa-calendar"></i> Date *</label>
                                        <input type="date" name="action_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label><i class="fa fa-clock"></i> Time *</label>
                                        <input type="time" name="action_time" class="form-control" value="<?php echo date('H:i'); ?>" required>
                                    </div>
                                </div>
                                <div class="row" id="vac_return" style="display:none;">
                                    <div class="form-group col-md-6">
                                        <label>Expected Return Date *</label>
                                        <input type="date" name="expected_return_date" class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Expected Return Time</label>
                                        <input type="time" name="expected_return_time" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa fa-map-marker-alt"></i> Destination *</label>
                                    <input type="text" name="destination" class="form-control" placeholder="e.g., Home Town" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa fa-comment"></i> Purpose *</label>
                                    <textarea name="purpose" class="form-control" rows="2" placeholder="e.g., Semester Break" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa fa-phone"></i> Contact During Leave</label>
                                    <input type="text" name="contact_during_leave" class="form-control" placeholder="Emergency contact">
                                </div>
                                <div class="form-group">
                                    <label><i class="fa fa-sticky-note"></i> Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning btn-block"><i class="fa fa-save"></i> Submit Vacation Leave</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white stat-card mb-3" style="background-color: #ffc107 !important;">
                        <div class="card-body">
                            <h6><i class="fa fa-plane"></i> Currently on Vacation</h6>
                            <?php
                            $vac_out = mysqli_query($conn, "SELECT COUNT(DISTINCT reg_no) as c FROM hostel_checkin_checkout cc1
                                WHERE pass_type='vacation' AND action_type='check-out' AND NOT EXISTS (
                                    SELECT 1 FROM hostel_checkin_checkout cc2 WHERE cc2.reg_no=cc1.reg_no AND cc2.pass_type='vacation'
                                    AND cc2.action_type='check-in' AND (cc2.action_date>cc1.action_date OR (cc2.action_date=cc1.action_date AND cc2.action_time>cc1.action_time)))");
                            echo '<h3>'.mysqli_fetch_assoc($vac_out)['c'].'</h3>';
                            ?>
                        </div>
                    </div>
                    <div class="card bg-primary text-white stat-card" style="background-color: #007bff !important;">
                        <div class="card-body">
                            <h6><i class="fa fa-calendar"></i> This Month</h6>
                            <?php
                            $vac_month = mysqli_query($conn, "SELECT COUNT(*) as c FROM hostel_checkin_checkout WHERE pass_type='vacation' AND MONTH(action_date)=MONTH(CURDATE()) AND YEAR(action_date)=YEAR(CURDATE())");
                            echo '<h3>'.mysqli_fetch_assoc($vac_month)['c'].'</h3>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECORDS TAB -->
        <div class="tab-pane fade" id="records">
            <div class="card mt-3">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fa fa-list"></i> All Records</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <select id="filterType" class="form-control" onchange="filterRec()">
                                <option value="">All Types</option>
                                <option value="gate-pass">Gate Pass</option>
                                <option value="vacation">Vacation</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="filterAction" class="form-control" onchange="filterRec()">
                                <option value="">All Actions</option>
                                <option value="check-in">Check-in</option>
                                <option value="check-out">Check-out</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="filterDate" class="form-control" onchange="filterRec()">
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="searchText" class="form-control" onkeyup="filterRec()" placeholder="Search student...">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-secondary btn-block" onclick="clearFilters()"><i class="fa fa-redo"></i> Clear</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="recTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Reg No</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Room</th>
                                    <th>Action</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Destination</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT cc.*, u.contact_no FROM hostel_checkin_checkout cc LEFT JOIN userregistration u ON cc.reg_no=u.registration_no ORDER BY cc.action_date DESC, cc.action_time DESC LIMIT 100";
                                $result = mysqli_query($conn, $sql);
                                while($r = mysqli_fetch_assoc($result)) {
                                    $type_badge = $r['pass_type']=='gate-pass' ? 'badge-info' : 'badge-warning';
                                    $type_icon = $r['pass_type']=='gate-pass' ? '🕐' : '✈️';
                                    $action_badge = $r['action_type']=='check-in' ? 'badge-success' : 'badge-warning';
                                    $action_icon = $r['action_type']=='check-in' ? '✅' : '🚪';
                                    echo '<tr data-type="'.$r['pass_type'].'" data-action="'.$r['action_type'].'" data-date="'.$r['action_date'].'" data-search="'.$r['reg_no'].' '.$r['student_name'].'">';
                                    echo '<td>'.$r['id'].'</td>';
                                    echo '<td><span class="badge '.$type_badge.'">'.$type_icon.' '.ucfirst(str_replace('-',' ',$r['pass_type'])).'</span></td>';
                                    echo '<td>'.$r['reg_no'].'</td>';
                                    echo '<td>'.$r['student_name'].'</td>';
                                    echo '<td>'.($r['contact_no']?:'-').'</td>';
                                    echo '<td>'.($r['room_no']?:'-').'</td>';
                                    echo '<td><span class="badge '.$action_badge.'">'.$action_icon.' '.ucfirst($r['action_type']).'</span></td>';
                                    echo '<td>'.date('d-M-Y',strtotime($r['action_date'])).'</td>';
                                    echo '<td>'.date('h:i A',strtotime($r['action_time'])).'</td>';
                                    echo '<td>'.($r['destination']?:'-').'</td>';
                                    echo '<td><button class="btn btn-sm btn-danger" onclick="delRec('.$r['id'].')"><i class="fa fa-trash"></i></button></td>';
                                    echo '</tr>';
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
function loadDetails(sel,prefix) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById(prefix+'_name').value = opt.getAttribute('data-name');
    document.getElementById(prefix+'_room').value = opt.getAttribute('data-room');
}
document.getElementById('gp_action').addEventListener('change', function() {
    document.getElementById('gp_return').style.display = this.value=='check-out' ? 'flex' : 'none';
});
document.getElementById('vac_action').addEventListener('change', function() {
    document.getElementById('vac_return').style.display = this.value=='check-out' ? 'flex' : 'none';
});
function filterRec() {
    const type = document.getElementById('filterType').value.toLowerCase();
    const action = document.getElementById('filterAction').value.toLowerCase();
    const date = document.getElementById('filterDate').value;
    const search = document.getElementById('searchText').value.toLowerCase();
    const rows = document.querySelectorAll('#recTable tbody tr');
    rows.forEach(row => {
        const rType = row.getAttribute('data-type');
        const rAction = row.getAttribute('data-action');
        const rDate = row.getAttribute('data-date');
        const rSearch = row.getAttribute('data-search').toLowerCase();
        let show = true;
        if(type && !rType.includes(type)) show = false;
        if(action && !rAction.includes(action)) show = false;
        if(date && rDate != date) show = false;
        if(search && !rSearch.includes(search)) show = false;
        row.style.display = show ? '' : 'none';
    });
}
function clearFilters() {
    document.getElementById('filterType').value = '';
    document.getElementById('filterAction').value = '';
    document.getElementById('filterDate').value = '';
    document.getElementById('searchText').value = '';
    filterRec();
}
function delRec(id) {
    if(confirm('Delete this record?')) {
        window.location.href = 'partials/_checkinManage.php?delete_id='+id;
    }
}
</script>
