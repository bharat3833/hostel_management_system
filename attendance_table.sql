-- =============================================
-- ATTENDANCE TRACKING SYSTEM
-- =============================================

-- Create attendance summary table
CREATE TABLE IF NOT EXISTS `student_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(255) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `month` int(2) NOT NULL,
  `year` int(4) NOT NULL,
  `total_days` int(3) NOT NULL,
  `vacation_days` int(3) DEFAULT 0,
  `present_days` int(3) DEFAULT 0,
  `attendance_percentage` decimal(5,2) DEFAULT 0.00,
  `calculated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `calculated_by` varchar(255) DEFAULT 'admin',
  PRIMARY KEY (`id`),
  KEY `reg_no` (`reg_no`),
  KEY `month_year` (`month`, `year`),
  UNIQUE KEY `unique_attendance` (`reg_no`, `month`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create view for attendance with student details
CREATE OR REPLACE VIEW `attendance_view` AS
SELECT 
    a.id,
    a.reg_no,
    a.student_name,
    a.month,
    a.year,
    a.total_days,
    a.vacation_days,
    a.present_days,
    a.attendance_percentage,
    a.calculated_on,
    a.calculated_by,
    u.emailid,
    u.contact_no,
    COALESCE(h.roomno, 'Not Assigned') as room_no,
    DATE_FORMAT(CONCAT(a.year, '-', LPAD(a.month, 2, '0'), '-01'), '%M %Y') as month_year_label
FROM student_attendance a
LEFT JOIN userregistration u ON a.reg_no = u.registration_no
LEFT JOIN hostelbookings h ON a.reg_no = h.regno
ORDER BY a.year DESC, a.month DESC, a.student_name;
