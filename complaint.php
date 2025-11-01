<?php
    include 'partials/_dbconnect.php';

// Handle form submissions first
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['createComplaint'])) {
        $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
        $reg_no = mysqli_real_escape_string($conn, $_POST['reg_no']);
        $room_no = intval($_POST['room_no']);
        $complaint = mysqli_real_escape_string($conn, $_POST['complaint']);

        $sql = "INSERT INTO complaints (student_name, reg_no, room_no, complaint) VALUES ('$student_name', '$reg_no', '$room_no', '$complaint')";
        $result = mysqli_query($conn, $sql);
        
        if($result) {
            echo '<script>alert("Complaint added successfully!"); window.location.href = "index.php?page=complaint";</script>';
            exit;
        } else {
            $error_msg = "Error adding complaint: " . mysqli_error($conn);
        }
    }

    if(isset($_POST['updateComplaint'])) {
        $id = intval($_POST['complaint_id']);
        $student_name = mysqli_real_escape_string($conn, $_POST['edit_student_name']);
        $reg_no = mysqli_real_escape_string($conn, $_POST['edit_reg_no']);
        $room_no = intval($_POST['edit_room_no']);
        $complaint = mysqli_real_escape_string($conn, $_POST['edit_complaint']);
        $status = mysqli_real_escape_string($conn, $_POST['edit_status']);

        $sql = "UPDATE complaints SET student_name='$student_name', reg_no='$reg_no', room_no='$room_no', complaint='$complaint', status='$status' WHERE id=$id";
        $result = mysqli_query($conn, $sql);

        if($result) {
            echo '<script>alert("Complaint updated successfully!"); window.location.href = "index.php?page=complaint";</script>';
            exit;
        } else {
            $error_msg = "Error updating complaint: " . mysqli_error($conn);
        }
    }

    if(isset($_POST['removeComplaint'])) {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM complaints WHERE id=$id";
        $result = mysqli_query($conn, $sql);

        if($result) {
            echo '<script>alert("Complaint deleted successfully!"); window.location.href = "index.php?page=complaint";</script>';
            exit;
        } else {
            $error_msg = "Error deleting complaint: " . mysqli_error($conn);
        }
    }
}
?>

<style>
.readonly-field {
    background-color: #f8f9fa;
    cursor: not-allowed;
}
.badge-pending { background-color: #dc3545; }
.badge-in-progress { background-color: #ffc107; }
.badge-resolved { background-color: #28a745; }
</style>

<div class="container-fluid" style="margin-top:98px">
    <?php if(isset($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>
    <div class="col-lg-12">
        <div class="row">
            <!-- FORM Panel -->
            <div class="col-md-4">
                <form method="post" id="complaintForm">
                    <div class="card">
                        <div class="card-header" style="background-color: rgb(111 202 203);">
                            Create New Complaint
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Registration Number</label>
                                <select name="reg_no" id="reg_no" class="form-control" required>
                                    <option value="">Select Student</option>
                                    <?php
                                        // Get registered students from hostelbookings table
                                        $student_sql = "SELECT DISTINCT regno, firstName, lastName, roomno FROM hostelbookings ORDER BY firstName";
                                        $student_result = mysqli_query($conn, $student_sql);
                                        if($student_result) {
                                            while($student_row = mysqli_fetch_assoc($student_result)) {
                                                echo '<option value="'.$student_row['regno'].'" data-name="'.$student_row['firstName'].' '.$student_row['lastName'].'" data-room="'.$student_row['roomno'].'">'.$student_row['regno'].' - '.$student_row['firstName'].' '.$student_row['lastName'].'</option>';
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Student Name</label>
                                <input type="text" name="student_name" id="student_name" class="form-control readonly-field" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Room Number</label>
                                <input type="number" name="room_no" id="room_no" class="form-control readonly-field" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Complaint</label>
                                <textarea name="complaint" id="complaint" rows="3" class="form-control" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" name="createComplaint">Create</button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- FORM Panel -->

            <!-- Table Panel -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-hover mb-0" id="complaintsTable">
                            <thead style="background-color: rgb(111 202 203);">
                                <tr>
                                    <th class="text-center" style="width:7%;">Id</th>
                                    <th class="text-center">Student Details</th>
                                    <th class="text-center">Complaint</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width:18%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $sql = "SELECT * FROM complaints ORDER BY created_at DESC";
                                $result = mysqli_query($conn, $sql);
                                if($result && mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)){
                                        $id = $row['id'];
                                        $student_name = htmlspecialchars($row['student_name']);
                                        $reg_no = htmlspecialchars($row['reg_no']);
                                        $room_no = $row['room_no'];
                                        $complaint = htmlspecialchars($row['complaint']);
                                        $status = $row['status'];

                                        echo '<tr>
                                                <td class="text-center">' .$id. '</td>
                                                <td>
                                                    <p><small>Name:</small> <b>' .$student_name. '</b></p>
                                                    <p><small>Reg No:</small> <b>' .$reg_no. '</b></p>
                                                    <p><small>Room:</small> <b>' .$room_no. '</b></p>
                                                </td>
                                                <td>
                                                    <p>' .$complaint. '</p>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-' .($status=="resolved" ? "success" : ($status=="in-progress" ? "warning" : "danger")). '">' .ucfirst($status). '</span>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-primary edit_complaint" 
                                                            data-id="' .$id. '" 
                                                            data-name="' .$student_name. '" 
                                                            data-regno="' .$reg_no. '" 
                                                            data-room="' .$room_no. '" 
                                                            data-complaint="' .$complaint. '" 
                                                            data-status="' .$status. '">Edit</button>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure?\')">
                                                        <input type="hidden" name="id" value="' .$id. '">
                                                        <button class="btn btn-sm btn-danger" name="removeComplaint">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="5" class="text-center">No complaints found</td></tr>';
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Table Panel -->
        </div>
    </div>	    
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Complaint</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="complaint_id" id="complaint_id">
                    <div class="form-group">
                        <label>Registration Number</label>
                        <select name="edit_reg_no" id="edit_reg_no" class="form-control" required>
                            <option value="">Select Student</option>
                            <?php
                                // Get registered students from hostelbookings table for edit modal
                                $edit_student_sql = "SELECT DISTINCT regno, firstName, lastName, roomno FROM hostelbookings ORDER BY firstName";
                                $edit_student_result = mysqli_query($conn, $edit_student_sql);
                                if($edit_student_result) {
                                    while($edit_student_row = mysqli_fetch_assoc($edit_student_result)) {
                                        echo '<option value="'.$edit_student_row['regno'].'" data-name="'.$edit_student_row['firstName'].' '.$edit_student_row['lastName'].'" data-room="'.$edit_student_row['roomno'].'">'.$edit_student_row['regno'].' - '.$edit_student_row['firstName'].' '.$edit_student_row['lastName'].'</option>';
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Student Name</label>
                        <input type="text" name="edit_student_name" id="edit_student_name" class="form-control readonly-field" readonly required>
                    </div>
                    <div class="form-group">
                        <label>Room Number</label>
                        <input type="number" name="edit_room_no" id="edit_room_no" class="form-control readonly-field" readonly required>
                    </div>
                    <div class="form-group">
                        <label>Complaint</label>
                        <textarea name="edit_complaint" id="edit_complaint" rows="3" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="edit_status" id="edit_status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="in-progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="updateComplaint" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
$(document).ready(function() {
    // Initialize DataTable if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#complaintsTable').DataTable({
            "order": [[ 0, "desc" ]],
            "pageLength": 10,
            "responsive": true
        });
    }

    // Handle student selection in add form
    $('#reg_no').change(function() {
        var selectedOption = $(this).find('option:selected');
        var studentName = selectedOption.data('name') || '';
        var roomNo = selectedOption.data('room') || '';
        
        $('#student_name').val(studentName);
        $('#room_no').val(roomNo);
    });

    // Handle student selection in edit form
    $('#edit_reg_no').change(function() {
        var selectedOption = $(this).find('option:selected');
        var studentName = selectedOption.data('name') || '';
        var roomNo = selectedOption.data('room') || '';
        
        $('#edit_student_name').val(studentName);
        $('#edit_room_no').val(roomNo);
    });

    // Handle Edit Button Click
    $(document).on('click', '.edit_complaint', function() {
        var id = $(this).data('id');
        var student_name = $(this).data('name');
        var reg_no = $(this).data('regno');
        var room_no = $(this).data('room');
        var complaint = $(this).data('complaint');
        var status = $(this).data('status');

        $('#complaint_id').val(id);
        $('#edit_reg_no').val(reg_no);
        $('#edit_student_name').val(student_name);
        $('#edit_room_no').val(room_no);
        $('#edit_complaint').val(complaint);
        $('#edit_status').val(status);

        $('#editModal').modal('show');
    })
});
</script>