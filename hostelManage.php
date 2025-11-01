<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row">
            <!-- FORM Panel -->
            <div class="col-md-12">
                <form action="partials/_hostelManage.php" method="post" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header">
                            Hostel Bookings
                        </div>
                        <div class="card-body">
                        <div class="row">
                        <div class="form-group col-md-4">
                        <label for="roomno">Room Number:</label>
                        <select name="roomNo" id="roomNo" class="custom-select browser-default" required onchange="selectdata(this.value)">
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
                            <div class="row">
                            <div class="form-group col-md-4">
                        <label for="duration">Total Duration:</label>
                        <select name="duration" id="duration" class="custom-select browser-default" required>
                        <option value="">Choose Duration</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
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
                        <br>
                        <center><p><b>Student's Personal Information</b></p></center>
                        <div class="row">
                            <div class="form-group col-md-4">
                        <label for="food">Registration Number:</label>
                        <select name="reg_no" id="reg_no" class="custom-select browser-default" required>
                        <option value="">Select Registration Number</option>
                        <!-- Registration numbers will be loaded dynamically based on room selection -->
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
                                <input type="text" class="form-control" name="gender" required placeholder="Enter first name" id="gender" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Contact Number: </label>
                                <input type="text" class="form-control" name="phone" required placeholder="Enter phone number" id="phone" readonly maxlength="10">
                            </div>
                            </div> 
                            <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">Emergency Contact Number: </label>
                                <input type="text" class="form-control" name="emg_no" required placeholder="Enter emergency number" maxlength="10">
                            </div> 
                            <div class="form-group col-md-4">
                        <label for="food">Preferred Course:</label>
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
                            <br>
                        <center><p><b>Guardian's Information</b></p></center>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">Guardian Name: </label>
                                <input type="text" class="form-control" name="guardian_name" required placeholder="Enter guardian  name">
                            </div> 
                            <div class="form-group col-md-4">
                                <label class="control-label">Relation: </label>
                                <input type="text" class="form-control" name="relation" required placeholder="Enter relation">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Contact Number: </label>
                                <input type="text" class="form-control" name="contact_number" required placeholder="Enter contact number" maxlength="10">
                            </div>
                            </div> 
                            <br>
                            <center><p><b>Address Information</b></p></center>
                        <div class="row">
                             <div class="form-group col-md-4">
                        <label for="food">State:</label>
                        <select name="state" id="state" class="custom-select browser-default" required>
                        <option value="">Select State</option>
                        <?php 
                        $statesql = "SELECT State FROM `state_master`";
                        $stateResult = mysqli_query($conn, $statesql);
                        while($stateRow = mysqli_fetch_assoc($stateResult)){
                        ?>
                        <option value="<?php echo $stateRow['State']; ?>"><?php echo $stateRow['State']; ?></option>
                    <?php } ?>
                        </select>
                    </div> 
                            <div class="form-group col-md-4">
                                <label class="control-label">City: </label>
                                <input type="text" class="form-control" name="city" required placeholder="Enter city name">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Address: </label>
                                <textarea class="form-control" name="address" required placeholder="Enter Address" rows="3"></textarea>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Postal Code: </label>
                                <input type="text" class="form-control" name="postal_code" required placeholder="Enter postal code">
                            </div>
                            </div> 
                        </div>  
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" name="createHostal" id="createHostal" class="btn btn-sm btn-primary col-sm-3 offset-md-4"> Submit </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- FORM Panel -->
    
        </div>
    </div>	    
</div>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
   
function selectdata(no)
{
 $.ajax({
    url: 'fetch-data.php',
    method: 'post',
    data: 'room='+no,
    success: function(result)
    {
        $('#seater').val(result);
        
    }
 });
 $.ajax({
    url: 'fetch-data.php',
    method: 'post',
    data: 'roomid='+no,
    success: function(result)
    {
        $('#fees').val(result);
        
    }
 });

 $.ajax({
    url: 'fetch-data.php',
    method: 'post',
    data: {roomsno:no},
    dataType: "JSON",
    success: function(result)
    {
        if(result['success']==0)
        {
        $("#availability-status").html(result['msg']);
        $('#createHostal').hide();
        }
        else
        {
            $("#availability-status").html(result['msg']);
            $('#createHostal').show();
        }
                
    }
 });

}
$(document).ready(function() {
        $('#duration').change(function(){
         var foodstatus = $("#foodstatus").val();
         if(foodstatus==1){
         var duration = $("#duration").val();
         var fees = $("#fees").val();
         var total_amt = duration*fees+4000;
         
            $('#total_ammount').val(total_amt);
        }
        else
        {
            var duration = $("#duration").val();
         var fees = $("#fees").val();
         var total_amt = duration*fees;
         
            $('#total_ammount').val(total_amt);
        }
        });
    });

$(document).ready(function() {
        $('#roomNo').change(function(){
            // Clear previous values
            $('#duration').val('');
            $('#total_ammount').val(''); 
            $('#foodstatus').val('');
            
            // Load students who have agreed on this room or all available students
            var roomNo = $(this).val();
            if(roomNo) {
                // Show loading state
                $('#reg_no').html('<option value="">Loading...</option>');
                
                // Fetch students for this room
                $.ajax({
                    url: 'get_room_students.php',
                    method: 'post',
                    data: {room_no: roomNo},
                    dataType: 'json',
                    success: function(response) {
                        var options = '<option value="">Select Registration Number</option>';
                        
                        if(response.has_agreement) {
                            // Show agreement message
                            showAgreementMessage(response.message);
                            
                            // Add only the students in the agreement
                            $.each(response.students, function(i, student) {
                                options += '<option value="' + student.reg_no + '">' + student.reg_no + ' - ' + student.name + ' (Confirmed Roommate)</option>';
                            });
                        } else {
                            // Add all available students
                            $.each(response.students, function(i, student) {
                                options += '<option value="' + student.reg_no + '">' + student.reg_no + ' - ' + student.name + '</option>';
                            });
                            
                            // Hide any previous agreement message
                            hideAgreementMessage();
                        }
                        
                        $('#reg_no').html(options);
                    },
                    error: function() {
                        $('#reg_no').html('<option value="">Error loading students</option>');
                    }
                });
            } else {
                $('#reg_no').html('<option value="">Select Registration Number</option>');
                hideAgreementMessage();
            }
        });
    });
    
    // Helper functions for agreement messages
    function showAgreementMessage(message) {
        // Remove any existing message
        $('.agreement-message').remove();
        
        // Add new message
        var alertHtml = '<div class="alert alert-info agreement-message"><i class="fa fa-handshake"></i> ' + 
                       message + '</div>';
        $('#roomNo').closest('.form-group').after(alertHtml);
    }
    
    function hideAgreementMessage() {
        $('.agreement-message').remove();
    }
$(document).ready(function() {
        $('#foodstatus').change(function(){
         var foodstatus = $(this).val(); 
         if(foodstatus==1)
         {
         var duration = $("#duration").val();
         var fees = $("#fees").val();
         var total_amt = duration*fees+4000;
         
            $('#total_ammount').val(total_amt);
         }       
         else if(foodstatus==0)
         {
            var duration = $("#duration").val();
            var fees = $("#fees").val();
            var total_amt = duration*fees;
            $('#total_ammount').val(total_amt);
         }
         else
         {
            $('#total_ammount').val('');
         }
        });
    });
$(document).ready(function() {
        $('#reg_no').change(function(){
        var reg_no = $(this).val();
        $.ajax({
        url: 'fetch-data.php',
        method: 'post',
        data: 'regNo='+reg_no,
        success: function(result)
        {
          var jsondata = $.parseJSON(result);
          $('#first_name').val(jsondata.first_name);
          $('#last_name').val(jsondata.last_name);
          $('#emailid').val(jsondata.emailid);
          $('#gender').val(jsondata.gender);
          $('#phone').val(jsondata.contact_no);          
          
          // If this is a roommate agreement, add confirmation
          if ($('.agreement-message').length > 0 && reg_no) {
            var student_name = jsondata.first_name + ' ' + jsondata.last_name;
            var confirmationMessage = '<div class="alert alert-success mt-2 mb-2">Now booking room for <strong>' + 
                                     student_name + '</strong> from confirmed roommate pair</div>';
            $('#first_name').closest('.row').prepend('<div class="col-md-12">' + confirmationMessage + '</div>');
          }
        }
     });
});
});
</script>