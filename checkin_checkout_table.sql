-- =============================================
-- CHECK-IN/CHECK-OUT TRACKING SYSTEM
-- =============================================

-- Create table for hostel entry/exit tracking
CREATE TABLE IF NOT EXISTS `hostel_checkin_checkout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(255) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `room_no` int(11) DEFAULT NULL,
  `pass_type` enum('gate-pass','vacation') NOT NULL,
  `action_type` enum('check-in','check-out') NOT NULL,
  `action_date` date NOT NULL,
  `action_time` time NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `expected_return_time` time DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `contact_during_leave` varchar(20) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` varchar(255) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reg_no` (`reg_no`),
  KEY `action_date` (`action_date`),
  KEY `action_type` (`action_type`),
  KEY `pass_type` (`pass_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create view for easy reporting
CREATE OR REPLACE VIEW `checkin_checkout_view` AS
SELECT 
    cc.id,
    cc.reg_no,
    cc.student_name,
    cc.room_no,
    cc.pass_type,
    cc.action_type,
    cc.action_date,
    cc.action_time,
    cc.expected_return_date,
    cc.expected_return_time,
    cc.destination,
    cc.purpose,
    cc.contact_during_leave,
    cc.remarks,
    cc.recorded_by,
    cc.created_at,
    u.emailid,
    u.contact_no,
    CASE 
        WHEN cc.pass_type = 'gate-pass' THEN 'Gate Pass'
        WHEN cc.pass_type = 'vacation' THEN 'Vacation'
    END as pass_type_label,
    CASE 
        WHEN cc.action_type = 'check-in' THEN 'Entered Hostel'
        WHEN cc.action_type = 'check-out' THEN 'Left Hostel'
    END as action_description
FROM hostel_checkin_checkout cc
LEFT JOIN userregistration u ON cc.reg_no = u.registration_no
ORDER BY cc.action_date DESC, cc.action_time DESC;

-- Sample data (optional - remove if not needed)
-- INSERT INTO `hostel_checkin_checkout` (`reg_no`, `student_name`, `room_no`, `action_type`, `action_date`, `action_time`, `remarks`) 
-- VALUES ('CS2021001', 'John Doe', 101, 'check-in', CURDATE(), CURTIME(), 'Regular entry');
