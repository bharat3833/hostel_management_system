<?php
// Enhanced hostel booking with roommate agreement support
?>
<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row">
            <!-- FORM Panel -->
            <div class="col-md-12">
                <form action="partials/_hostelManage.php" method="post" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header">
                            Enhanced Hostel Bookings - With Roommate Agreement Support
                        </div>
                        <div class="card-body">
                        
                            <!-- Room Agreement Detection -->
                            <div id="roommate-agreement-info" class="alert alert-info" style="display: none;">
                                <h6><i class="fa fa-handshake"></i> Roommate Agreement Detected!</h6>
                                <p id="agreement-details"></p>
                            </div>
                        
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="roomno">Room Number:</label>
                                    <select name="roomNo" id="roomNo" class="custom-select browser-default" required onchange="selectRoomWithAgreement(this.value)">
                                        <option value="">Select Number</option>
                                        <?php 
                                        $usersql = "SELECT * FROM `roomsdetails`";
                                        $userResult = mysqli_query($conn, $usersql);
                                        while($userRow = mysqli_fetch_assoc($userResult)){
                                            $roomNo = $userRow['room_no'];
                                        ?>
                                        <option value="<?php echo $roomNo; ?>"><?php echo $roomNo; ?></option>
                                        <?php } ?>
                                    </select>
                                    <span id="availability-status" style="font-size:14px;"></span>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="control-label">Start Date: </label>
                                    <input type="date" class="form-control" name="startdate" required>
                                </div> 
                                <div class="form-group col-md-4">
                                    <label class="control-label">Seater: </label>
                                    <input type="text" class="form-control" name="seater" id="seater" placeholder="Enter Seater No." required readonly>
                                </div>
                            </div> 
                            
                            <!-- Booking Type Selection -->
                            <div class="row" id="booking-type-section">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><strong>Booking Type:</strong></label><br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="booking_type" id="single_booking" value="single" checked onchange="toggleBookingMode()">
                                            <label class="form-check-label" for="single_booking">Single Student Booking</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="booking_type" id="roommate_booking" value="roommate" onchange="toggleBookingMode()">
                                            <label class="form-check-label" for="roommate_booking">Roommate Pair Booking</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="duration">Total Duration:</label>
                                    <select name="duration" id="duration" class="custom-select browser-default" required>
                                        <option value="">Choose Duration</option>
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="food">Food Status:</label>
                                    <select name="foodstatus" id="foodstatus" class="custom-select browser-default" required>
                                        <option value="">Select Status</option>
                                        <option value="1">Required (Extra 4000 Rs. per month)</option>
                                        <option value="0">Not Required</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="control-label">Total Fees Per Month: </label>
                                    <input type="text" class="form-control" name="fees" id="fees" placeholder="Fees per Month" required readonly>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="control-label">Total Amount: </label>
                                    <input type="text" class="form-control" name="total_ammount" id="total_ammount" required placeholder="Total amount" readonly>
                                </div>
                            </div>
                            
                            <!-- Student 1 Information -->
                            <br>
                            <center><p><b>Student Information</b></p></center>
                            <div id="student1-section">
                                <h6>Student 1:</h6>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="reg_no">Registration Number:</label>
                                        <select name="reg_no" id="reg_no" class="custom-select browser-default" required onchange="loadStudentDetails(this.value, 1)">
                                            <option value="">Select Registration Number</option>
                                            <?php 
                                            $usersql = "SELECT registration_no FROM `userregistration` WHERE registration_no NOT IN (SELECT regno FROM hostelbookings)";
                                            $userResult = mysqli_query($conn, $usersql);
                                            while($userRow = mysqli_fetch_assoc($userResult)){
                                            ?>
                                            <option value="<?php echo $userRow['registration_no']; ?>"><?php echo $userRow['registration_no']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div> 
                                    <div class="form-group col-md-4">
                                        <label class="control-label">First Name: </label>
                                        <input type="text" class="form-control" name="first_name" id="first_name" required placeholder="Enter first name" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="control-label">Last Name: </label>
                                        <input type="text" class="form-control" name="last_name" required placeholder="Enter last name" id="last_name" readonly>
                                    </div>
                                </div> 
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label class="control-label">Emailid: </label>
                                        <input type="text" class="form-control" name="emailid" required placeholder="Enter emailid" id="emailid" readonly>
                                    </div> 
                                    <div class="form-group col-md-4">
                                        <label class="control-label">Gender: </label>
                                        <input type="text" class="form-control" name="gender" required placeholder="Enter gender" id="gender" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="control-label">Contact Number: </label>
                                        <input type="text" class="form-control" name="phone" required placeholder="Enter phone number" id="phone" readonly maxlength="10">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Student 2 Information (Hidden by default) -->
                            <div id="student2-section" style="display: none;">
                                <h6>Student 2 (Roommate):</h6>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="reg_no_2">Registration Number:</label>
                                        <select name="reg_no_2" id="reg_no_2" class="custom-select browser-default" onchange="loadStudentDetails(this.value, 2)">
                                            <option value="">Select Registration Number</option>
                                            <?php 
                                            mysqli_data_seek($userResult, 0);
                                            while($userRow = mysqli_fetch_assoc($userResult)){
                                            ?>
                                            <option value="<?php echo $userRow['registration_no']; ?>"><?php echo $userRow['registration_no']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div> 
                                    <div class="form-group col-md-4">
                                        <label class="control-label">First Name: </label>
                                        <input type="text" class="form-control" name="first_name_2" id="first_name_2" placeholder="Enter first name" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="control-label">Last Name: </label>
                                        <input type="text" class="form-control" name="last_name_2" placeholder="Enter last name" id="last_name_2" readonly>
                                    </div>
                                </div> 
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label class="control-label">Emailid: </label>
                                        <input type="text" class="form-control" name="emailid_2" placeholder="Enter emailid" id="emailid_2" readonly>
                                    </div> 
                                    <div class="form-group col-md-4">
                                        <label class="control-label">Gender: </label>
                                        <input type="text" class="form-control" name="gender_2" placeholder="Enter gender" id="gender_2" readonly>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="control-label">Contact Number: </label>
                                        <input type="text" class="form-control" name="phone_2" placeholder="Enter phone number" id="phone_2" readonly maxlength="10">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden fields for roommate booking -->
                            <input type="hidden" name="agreement_id" id="agreement_id" value="">
                            <input type="hidden" name="roommate_pair_id" id="roommate_pair_id" value="">
                            
                            <!-- Rest of the form continues with guardian info, address, etc. -->
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="control-label">Emergency Contact Number: </label>
                                    <input type="text" class="form-control" name="emg_no" required placeholder="Enter emergency number" maxlength="10">
                                </div> 
                                <div class="form-group col-md-4">
                                    <label for="course">Preferred Course:</label>
                                    <select name="course" id="course" class="custom-select browser-default" required>
                                        <option value="">Select Course</option>
                                        <?php 
                                        $coursesql = "SELECT course_fn FROM `courses`";
                                        $courseResult = mysqli_query($conn, $coursesql);
                                        while($courseRow = mysqli_fetch_assoc($courseResult)){
                                        ?>
                                        <option value="<?php echo $courseRow['course_fn']; ?>"><?php echo $courseRow['course_fn']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div> 
                            </div>
                            
                            <!-- Guardian and Address sections would continue here -->
                            <!-- (Keeping them same as original for brevity) -->
                            
                        </div>  
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" name="createHostal" id="createHostal" class="btn btn-sm btn-primary col-sm-3 offset-md-4"> Submit Booking </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>	    
</div>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
let currentRoommateAgreement = null;

// Enhanced room selection with agreement detection
function selectRoomWithAgreement(roomNo) {
    if(!roomNo) {
        $('#roommate-agreement-info').hide();
        currentRoommateAgreement = null;
        return;
    }
    
    // First check for roommate agreement
    $.ajax({
        url: 'get_roommate_booking_details.php',
        method: 'post',
        data: {room_no: roomNo},
        dataType: 'json',
        success: function(result) {
            if(result.success && result.has_agreement) {
                // Found roommate agreement
                currentRoommateAgreement = result;
                showRoommateAgreement(result);
                autoFillRoommateDetails(result);
            } else {
                // No agreement, proceed with normal room selection
                $('#roommate-agreement-info').hide();
                currentRoommateAgreement = null;
                selectdata(roomNo); // Original function
            }
        },
        error: function() {
            // Fallback to original function
            selectdata(roomNo);
        }
    });
}

function showRoommateAgreement(agreement) {
    const details = `
        <strong>Agreed Roommate Pair Found!</strong><br>
        <strong>${agreement.student1.name}</strong> (${agreement.student1.reg_no}) & 
        <strong>${agreement.student2.name}</strong> (${agreement.student2.reg_no})<br>
        Compatibility Score: ${agreement.compatibility_score}%<br>
        ${agreement.special_requirements ? 'Special Requirements: ' + agreement.special_requirements : ''}
    `;
    
    $('#agreement-details').html(details);
    $('#roommate-agreement-info').show();
    
    // Automatically switch to roommate booking mode
    $('#roommate_booking').prop('checked', true);
    toggleBookingMode();
}

function autoFillRoommateDetails(agreement) {
    // Fill room details
    $('#seater').val(agreement.room_details.seater);
    $('#fees').val(agreement.room_details.fees);
    
    // Fill hidden fields
    $('#agreement_id').val(agreement.agreement_id);
    $('#roommate_pair_id').val(agreement.roommate_pair_id);
    
    // Auto-fill student 1 details
    $('#reg_no').val(agreement.student1.reg_no);
    $('#first_name').val(agreement.student1.name.split(' ')[0]);
    $('#last_name').val(agreement.student1.name.split(' ').slice(1).join(' '));
    $('#emailid').val(agreement.student1.email);
    $('#gender').val(agreement.student1.gender);
    $('#phone').val(agreement.student1.contact);
    
    // Auto-fill student 2 details
    $('#reg_no_2').val(agreement.student2.reg_no);
    $('#first_name_2').val(agreement.student2.name.split(' ')[0]);
    $('#last_name_2').val(agreement.student2.name.split(' ').slice(1).join(' '));
    $('#emailid_2').val(agreement.student2.email);
    $('#gender_2').val(agreement.student2.gender);
    $('#phone_2').val(agreement.student2.contact);
    
    // Show availability status
    $("#availability-status").html('<span class="text-success"><i class="fa fa-check"></i> Room available for agreed roommate pair</span>');
    $('#createHostal').show();
}

function toggleBookingMode() {
    const isRoommateBooking = $('#roommate_booking').is(':checked');
    
    if(isRoommateBooking) {
        $('#student2-section').show();
        $('#reg_no_2').prop('required', true);
    } else {
        $('#student2-section').hide();
        $('#reg_no_2').prop('required', false);
        // Clear student 2 fields
        $('#student2-section input, #student2-section select').val('');
    }
}

function loadStudentDetails(regNo, studentNumber) {
    if(!regNo) return;
    
    $.ajax({
        url: 'fetch-data.php',
        method: 'post',
        data: 'regNo=' + regNo,
        success: function(result) {
            var jsondata = $.parseJSON(result);
            const suffix = studentNumber === 2 ? '_2' : '';
            
            $('#first_name' + suffix).val(jsondata.first_name);
            $('#last_name' + suffix).val(jsondata.last_name);
            $('#emailid' + suffix).val(jsondata.emailid);
            $('#gender' + suffix).val(jsondata.gender);
            $('#phone' + suffix).val(jsondata.contact_no);
        }
    });
}

// Original functions (keeping them for compatibility)
function selectdata(no) {
    // Original room selection logic
    $.ajax({
        url: 'fetch-data.php',
        method: 'post',
        data: 'room='+no,
        success: function(result) {
            $('#seater').val(result);
        }
    });
    
    $.ajax({
        url: 'fetch-data.php',
        method: 'post',
        data: 'roomid='+no,
        success: function(result) {
            $('#fees').val(result);
        }
    });

    $.ajax({
        url: 'fetch-data.php',
        method: 'post',
        data: {roomsno:no},
        dataType: "JSON",
        success: function(result) {
            if(result['success']==0) {
                $("#availability-status").html(result['msg']);
                $('#createHostal').hide();
            } else {
                $("#availability-status").html(result['msg']);
                $('#createHostal').show();
            }
        }
    });
}

// Duration and fees calculation
$(document).ready(function() {
    $('#duration, #foodstatus').change(function(){
        calculateTotalAmount();
    });
    
    $('#roomNo').change(function(){
        $('#duration').val('');
        $('#total_ammount').val(''); 
        $('#foodstatus').val('');        
    });
});

function calculateTotalAmount() {
    var foodstatus = $("#foodstatus").val();
    var duration = $("#duration").val();
    var fees = $("#fees").val();
    
    if(duration && fees) {
        var total_amt = duration * fees;
        if(foodstatus == 1) {
            total_amt += 4000 * duration;
        }
        $('#total_ammount').val(total_amt);
    }
}
</script>