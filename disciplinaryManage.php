<!-- Disciplinary Records Management -->
<style>
.stat-card { transition: transform 0.2s; }
.stat-card:hover { transform: translateY(-2px); }
.severity-minor { border-left: 4px solid #17a2b8; }
.severity-moderate { border-left: 4px solid #ffc107; }
.severity-major { border-left: 4px solid #dc3545; }
.severity-critical { border-left: 4px solid #343a40; }
</style>

<div class="container-fluid">
<?php
// Get all students for dropdown
$students_sql = "SELECT registration_no, CONCAT(first_name, ' ', last_name) as full_name, emailid, contact_no 
                 FROM userregistration ORDER BY first_name";
$students_result = mysqli_query($conn, $students_sql);

// Get violation categories
$categories_sql = "SELECT * FROM violation_categories WHERE is_active = 1 ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_sql);

// Get all disciplinary records
$records_sql = "SELECT * FROM disciplinary_records_view ORDER BY incident_date DESC, created_at DESC";
$records_result = mysqli_query($conn, $records_sql);
$disciplinary_records = array();
if($records_result) {
    while($row = mysqli_fetch_assoc($records_result)) {
        $disciplinary_records[] = $row;
    }
}

// Calculate statistics
$total_records = count($disciplinary_records);
$open_cases = count(array_filter($disciplinary_records, function($r) { return $r['status'] == 'open'; }));
$critical_cases = count(array_filter($disciplinary_records, function($r) { return $r['severity'] == 'critical'; }));
$pending_fines = array_sum(array_map(function($r) { 
    return ($r['fine_paid'] == 'no' || $r['fine_paid'] == 'partial') ? floatval($r['fine_amount']) : 0; 
}, $disciplinary_records));
?>

    <div class="row mb-3">
        <div class="col-12">
            <h2><i class="fa fa-gavel"></i> Disciplinary Records Management</h2>
            <p class="text-muted">Track and manage student disciplinary actions</p>
        </div>
    </div>

    <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white" style="background-color: #6c757d !important;">
                        <div class="card-body text-center">
                            <i class="fa fa-file-alt fa-2x mb-2"></i>
                            <h3><?php echo $total_records; ?></h3>
                            <p class="mb-0">Total Records</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white" style="background-color: #ffc107 !important;">
                        <div class="card-body text-center">
                            <i class="fa fa-folder-open fa-2x mb-2"></i>
                            <h3><?php echo $open_cases; ?></h3>
                            <p class="mb-0">Open Cases</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white" style="background-color: #dc3545 !important;">
                        <div class="card-body text-center">
                            <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
                            <h3><?php echo $critical_cases; ?></h3>
                            <p class="mb-0">Critical Cases</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white" style="background-color: #17a2b8 !important;">
                        <div class="card-body text-center">
                            <i class="fa fa-rupee-sign fa-2x mb-2"></i>
                            <h3>₹<?php echo number_format($pending_fines, 2); ?></h3>
                            <p class="mb-0">Pending Fines</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Add New Record Form -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5><i class="fa fa-plus-circle"></i> Add Disciplinary Record</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="partials/_disciplinaryManage.php" id="disciplinaryForm">
                                <input type="hidden" name="action" value="add">
                                
                                <div class="form-group">
                                    <label><i class="fa fa-user"></i> Student *</label>
                                    <select name="student_reg_no" id="student_reg_no" class="form-control" required onchange="loadStudentInfo(this.value)">
                                        <option value="">Select Student</option>
                                        <?php
                                        mysqli_data_seek($students_result, 0);
                                        while($student = mysqli_fetch_assoc($students_result)) {
                                            echo '<option value="'.$student['registration_no'].'" 
                                                  data-email="'.$student['emailid'].'" 
                                                  data-contact="'.$student['contact_no'].'">'
                                                  .$student['registration_no'].' - '.$student['full_name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                    <small id="studentInfo" class="form-text text-muted"></small>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label><i class="fa fa-calendar"></i> Incident Date *</label>
                                        <input type="date" name="incident_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label><i class="fa fa-clock"></i> Incident Time</label>
                                        <input type="time" name="incident_time" class="form-control" value="<?php echo date('H:i'); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-list"></i> Violation Category *</label>
                                    <select name="violation_category" class="form-control" required onchange="updateSeverity(this)">
                                        <option value="">Select Category</option>
                                        <?php
                                        mysqli_data_seek($categories_result, 0);
                                        while($cat = mysqli_fetch_assoc($categories_result)) {
                                            echo '<option value="'.$cat['category_code'].'" data-severity="'.$cat['default_severity'].'">'
                                                 .$cat['category_name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label><i class="fa fa-exclamation-circle"></i> Severity *</label>
                                        <select name="severity" id="severity" class="form-control" required>
                                            <option value="minor">Minor</option>
                                            <option value="moderate">Moderate</option>
                                            <option value="major">Major</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label><i class="fa fa-gavel"></i> Action Type *</label>
                                        <select name="incident_type" id="incident_type" class="form-control" required onchange="toggleFields()">
                                            <option value="warning">Warning</option>
                                            <option value="fine">Fine</option>
                                            <option value="suspension">Suspension</option>
                                            <option value="expulsion">Expulsion</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-align-left"></i> Description *</label>
                                    <textarea name="description" class="form-control" rows="3" required placeholder="Describe the incident in detail"></textarea>
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-map-marker-alt"></i> Location</label>
                                    <input type="text" name="location" class="form-control" placeholder="e.g., Room 101, Mess Hall">
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-user-shield"></i> Reported By</label>
                                    <input type="text" name="reported_by" class="form-control" placeholder="Name of person who reported">
                                </div>

                                <div class="form-group" id="fine_fields" style="display:none;">
                                    <label><i class="fa fa-rupee-sign"></i> Fine Amount</label>
                                    <input type="number" name="fine_amount" class="form-control" step="0.01" min="0" value="0">
                                </div>

                                <div class="form-group" id="suspension_fields" style="display:none;">
                                    <label><i class="fa fa-calendar-times"></i> Suspension Period</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="date" name="suspension_start_date" class="form-control" placeholder="Start Date">
                                        </div>
                                        <div class="col-6">
                                            <input type="date" name="suspension_end_date" class="form-control" placeholder="End Date">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-clipboard-check"></i> Action Taken *</label>
                                    <textarea name="action_taken" class="form-control" rows="2" required placeholder="Describe the action taken"></textarea>
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-sticky-note"></i> Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2" placeholder="Additional notes"></textarea>
                                </div>

                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fa fa-save"></i> Add Record
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Records List -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5><i class="fa fa-list"></i> Disciplinary Records</h5>
                                <div>
                                    <button class="btn btn-sm btn-outline-light" onclick="exportRecords('pdf')">
                                        <i class="fa fa-file-pdf"></i> PDF
                                    </button>
                                    <button class="btn btn-sm btn-outline-light" onclick="exportRecords('excel')">
                                        <i class="fa fa-file-excel"></i> Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filters -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <select id="filterSeverity" class="form-control form-control-sm" onchange="filterRecords()">
                                        <option value="">All Severities</option>
                                        <option value="minor">Minor</option>
                                        <option value="moderate">Moderate</option>
                                        <option value="major">Major</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filterStatus" class="form-control form-control-sm" onchange="filterRecords()">
                                        <option value="">All Status</option>
                                        <option value="open">Open</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="under_review">Under Review</option>
                                        <option value="appealed">Appealed</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filterType" class="form-control form-control-sm" onchange="filterRecords()">
                                        <option value="">All Types</option>
                                        <option value="warning">Warning</option>
                                        <option value="fine">Fine</option>
                                        <option value="suspension">Suspension</option>
                                        <option value="expulsion">Expulsion</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" id="searchStudent" class="form-control form-control-sm" placeholder="Search student..." onkeyup="filterRecords()">
                                </div>
                            </div>

                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-hover table-sm" id="recordsTable">
                                    <thead class="thead-dark sticky-top">
                                        <tr>
                                            <th>Date</th>
                                            <th>Student</th>
                                            <th>Violation</th>
                                            <th>Severity</th>
                                            <th>Action</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($disciplinary_records)): ?>
                                            <?php foreach($disciplinary_records as $record): ?>
                                                <?php
                                                $severity_badge = '';
                                                switch($record['severity']) {
                                                    case 'minor': $severity_badge = 'badge-info'; break;
                                                    case 'moderate': $severity_badge = 'badge-warning'; break;
                                                    case 'major': $severity_badge = 'badge-danger'; break;
                                                    case 'critical': $severity_badge = 'badge-dark'; break;
                                                }
                                                
                                                $status_badge = '';
                                                switch($record['status']) {
                                                    case 'open': $status_badge = 'badge-warning'; break;
                                                    case 'resolved': $status_badge = 'badge-success'; break;
                                                    case 'under_review': $status_badge = 'badge-info'; break;
                                                    case 'appealed': $status_badge = 'badge-secondary'; break;
                                                }
                                                ?>
                                                <tr data-severity="<?php echo $record['severity']; ?>" 
                                                    data-status="<?php echo $record['status']; ?>" 
                                                    data-type="<?php echo $record['incident_type']; ?>"
                                                    data-student="<?php echo strtolower($record['student_name'].' '.$record['student_reg_no']); ?>">
                                                    <td>
                                                        <small><?php echo date('d M Y', strtotime($record['incident_date'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo $record['student_name']; ?></strong><br>
                                                        <small class="text-muted"><?php echo $record['student_reg_no']; ?></small>
                                                    </td>
                                                    <td>
                                                        <small><?php echo $record['violation_category_name']; ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo $severity_badge; ?>">
                                                            <?php echo ucfirst($record['severity']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small><?php echo $record['incident_type_label']; ?></small>
                                                        <?php if($record['incident_type'] == 'fine' && $record['fine_amount'] > 0): ?>
                                                            <br><small class="text-danger">₹<?php echo number_format($record['fine_amount'], 2); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo $status_badge; ?>">
                                                            <?php echo $record['status_label']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info" onclick="viewRecord(<?php echo $record['id']; ?>)" title="View Details">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-primary" onclick="editRecord(<?php echo $record['id']; ?>)" title="Edit">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <form method="POST" action="partials/_disciplinaryManage.php" class="d-inline">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')" title="Delete">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="fa fa-info-circle fa-2x mb-2"></i>
                                                    <p>No disciplinary records found</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include export libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
    const recordsData = <?php echo json_encode($disciplinary_records); ?>;

    function loadStudentInfo(regNo) {
        const select = document.getElementById('student_reg_no');
        const option = select.options[select.selectedIndex];
        const email = option.getAttribute('data-email');
        const contact = option.getAttribute('data-contact');
        
        if(regNo) {
            document.getElementById('studentInfo').innerHTML = 
                '<i class="fa fa-envelope"></i> ' + email + ' | <i class="fa fa-phone"></i> ' + contact;
        } else {
            document.getElementById('studentInfo').innerHTML = '';
        }
    }

    function updateSeverity(select) {
        const severity = select.options[select.selectedIndex].getAttribute('data-severity');
        if(severity) {
            document.getElementById('severity').value = severity;
        }
    }

    function toggleFields() {
        const type = document.getElementById('incident_type').value;
        document.getElementById('fine_fields').style.display = (type === 'fine') ? 'block' : 'none';
        document.getElementById('suspension_fields').style.display = (type === 'suspension') ? 'block' : 'none';
    }

    function filterRecords() {
        const severity = document.getElementById('filterSeverity').value.toLowerCase();
        const status = document.getElementById('filterStatus').value.toLowerCase();
        const type = document.getElementById('filterType').value.toLowerCase();
        const search = document.getElementById('searchStudent').value.toLowerCase();
        
        const rows = document.querySelectorAll('#recordsTable tbody tr');
        
        rows.forEach(row => {
            if(row.cells.length === 1) return; // Skip "no records" row
            
            const rowSeverity = row.getAttribute('data-severity');
            const rowStatus = row.getAttribute('data-status');
            const rowType = row.getAttribute('data-type');
            const rowStudent = row.getAttribute('data-student');
            
            let show = true;
            
            if(severity && rowSeverity !== severity) show = false;
            if(status && rowStatus !== status) show = false;
            if(type && rowType !== type) show = false;
            if(search && !rowStudent.includes(search)) show = false;
            
            row.style.display = show ? '' : 'none';
        });
    }

    function viewRecord(id) {
        const record = recordsData.find(r => r.id == id);
        if(record) {
            let details = `DISCIPLINARY RECORD DETAILS\n\n`;
            details += `Student: ${record.student_name} (${record.student_reg_no})\n`;
            details += `Room: ${record.room_no || 'N/A'}\n\n`;
            details += `Incident Date: ${new Date(record.incident_date).toLocaleDateString()}\n`;
            details += `Violation: ${record.violation_category_name}\n`;
            details += `Severity: ${record.severity_label}\n`;
            details += `Action Type: ${record.incident_type_label}\n\n`;
            details += `Description: ${record.description}\n\n`;
            details += `Action Taken: ${record.action_taken}\n\n`;
            if(record.fine_amount > 0) {
                details += `Fine Amount: ₹${record.fine_amount}\n`;
                details += `Fine Paid: ${record.fine_paid}\n\n`;
            }
            details += `Status: ${record.status_label}\n`;
            details += `Recorded By: ${record.recorded_by}\n`;
            details += `Created: ${new Date(record.created_at).toLocaleString()}`;
            
            alert(details);
        }
    }

    function editRecord(id) {
        alert('Edit functionality: Redirect to edit form with record ID ' + id);
        // Implement edit functionality as needed
    }

    function exportRecords(format) {
        if(format === 'pdf') {
            exportToPDF();
        } else if(format === 'excel') {
            exportToExcel();
        }
    }

    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4');
        
        doc.setFontSize(16);
        doc.text('Disciplinary Records Report', 14, 15);
        
        doc.setFontSize(10);
        doc.text('Generated on: ' + new Date().toLocaleString(), 14, 22);
        
        const tableData = [];
        recordsData.forEach(record => {
            tableData.push([
                new Date(record.incident_date).toLocaleDateString(),
                record.student_name + '\n' + record.student_reg_no,
                record.violation_category_name,
                record.severity_label,
                record.incident_type_label,
                record.status_label
            ]);
        });
        
        doc.autoTable({
            head: [['Date', 'Student', 'Violation', 'Severity', 'Action', 'Status']],
            body: tableData,
            startY: 28,
            theme: 'grid',
            headStyles: { fillColor: [220, 53, 69] },
            styles: { fontSize: 8 }
        });
        
        doc.save('Disciplinary_Records_' + new Date().toISOString().split('T')[0] + '.pdf');
    }

    function exportToExcel() {
        const wb = XLSX.utils.book_new();
        
        const data = [['Date', 'Student Name', 'Reg No', 'Violation', 'Severity', 'Action Type', 'Description', 'Action Taken', 'Fine Amount', 'Status', 'Recorded By']];
        
        recordsData.forEach(record => {
            data.push([
                new Date(record.incident_date).toLocaleDateString(),
                record.student_name,
                record.student_reg_no,
                record.violation_category_name,
                record.severity_label,
                record.incident_type_label,
                record.description,
                record.action_taken,
                record.fine_amount > 0 ? '₹' + record.fine_amount : '-',
                record.status_label,
                record.recorded_by
            ]);
        });
        
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, 'Disciplinary Records');
        
        XLSX.writeFile(wb, 'Disciplinary_Records_' + new Date().toISOString().split('T')[0] + '.xlsx');
    }
    </script>
</div>
