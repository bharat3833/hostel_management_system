  <header class="header" id="header">
    <div class="header__toggle">
      <i class='bx bx-menu' id="header-toggle"></i>
    </div>
    <div style="display: flex; align-items: center;">
      <div class="theme-toggle" id="theme-toggle" title="Toggle Dark Mode (Ctrl+Shift+D)">
        <i class="fas fa-moon"></i>
      </div>
      <div class="header__img">
        <img src="img/profilePic.jpg" alt="Profile"/>
      </div>
    </div>
  </header>

    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div>
                <a href="index.php" class="nav__logo">
                    <i class='bx bx-layer nav__logo-icon'></i>
                    <span class="nav__logo-name">Hostel Management</span>
                </a>

                <div class="nav__list">
                    <?php if(!isset($studentView) || !$studentView): // Admin view menu items ?>
                    <a href="index.php" class="nav__link nav-home">
                      <i class='bx bx-grid-alt nav__icon' ></i>
                      <span class="nav__name">Home</span>
                    </a>
                    <a href="index.php?page=userManage" class="nav-orderManage nav__link ">
                      <i class='bx bx-user nav__icon' ></i>
                      <span class="nav__name">Student Registration</span>
                    </a>
                    <a href="index.php?page=hostelManage" class="nav__link nav-categoryManage">
                      <i class='fa fa-hotel' ></i>
                      <span class="nav__name">Book Hostel</span>
                    </a>
                    <a href="index.php?page=hostelstuManage" class="nav__link nav-categoryManage">
                      <i class='fa fa-users' ></i>
                      <span class="nav__name">Hostel Students</span>
                    </a>
                    <a href="index.php?page=roomManage" class="nav__link nav-menuManage">
                      <i class='fa fa-bed' ></i>
                      <span class="nav__name">Manage Rooms</span>
                    </a>
                    <a href="index.php?page=courseManage" class="nav__link nav-contactManage">
                      <i class="fa fa-tasks"></i>
                      <span class="nav__name">Manage Courses</span>
                    </a>
                    <a href="index.php?page=complaint" class="nav__link nav-complaint">
                      <i class="fa fa-exclamation-circle"></i>
                      <span class="nav__name">Manage Complaints</span>
                    </a>
                    <a href="index.php?page=checkinManage" class="nav__link nav-checkinManage">
                      <i class="fa fa-door-open"></i>
                      <span class="nav__name">Check-in/Check-out</span>
                    </a>
                    <a href="index.php?page=attendanceManage" class="nav__link nav-attendanceManage">
                      <i class="fa fa-calendar-check"></i>
                      <span class="nav__name">Attendance</span>
                    </a>
                    <a href="index.php?page=disciplinaryManage" class="nav__link nav-disciplinaryManage">
                      <i class="fa fa-gavel"></i>
                      <span class="nav__name">Disciplinary Records</span>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Always show roommate matching - for both admin and student views -->
                    <a href="index.php?page=roommate<?php echo isset($studentView) && $studentView ? '&tab=preferences&reg_no='.$studentRegNo.'&student_view=1' : ''; ?>" class="nav__link nav-roommate">
                      <i class="fa fa-user-friends"></i>
                      <span class="nav__name">Roommate Matching</span>
                    </a>
                </div>
            </div>
            <div>
                <div class="nav__link" style="margin-bottom:.35rem; cursor:default;">
                    <div class="header__img" style="margin-right:.65rem;">
                        <img src="img/profilePic.jpg" alt="Profile"/>
                    </div>
                    <div>
                        <span class="nav__name"><?php echo isset($studentView) && $studentView ? 'Student' : 'Admin'; ?></span>
                        <div style="font-size:.75rem; color: var(--first-color-light);">
                            <?php echo isset($studentView) && $studentView ? 'My Preferences' : 'Dashboard'; ?>
                        </div>
                    </div>
                </div>
                <a href="<?php echo isset($studentView) && $studentView ? 'student-logout.php' : 'partials/_logout.php'; ?>" class="nav__link">
                  <i class='bx bx-log-out nav__icon' ></i>
                  <span class="nav__name">Log Out</span>
                </a>
            </div>
        </nav>
    </div>  
    
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>
    <?php $page = isset($_GET['page']) ? $_GET['page'] :'home'; ?>
	  $('.nav-<?php echo $page; ?>').addClass('active')
</script>
