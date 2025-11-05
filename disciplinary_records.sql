-- ===============================================
-- DISCIPLINARY RECORDS SYSTEM
-- ===============================================
-- This SQL file creates tables for managing student disciplinary records

-- Create disciplinary_records table
CREATE TABLE IF NOT EXISTS `disciplinary_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_reg_no` varchar(20) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_time` time DEFAULT NULL,
  `incident_type` enum('warning','fine','suspension','expulsion','other') NOT NULL DEFAULT 'warning',
  `severity` enum('minor','moderate','major','critical') NOT NULL DEFAULT 'minor',
  `violation_category` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `reported_by` varchar(100) DEFAULT NULL,
  `witnesses` text DEFAULT NULL,
  `action_taken` text NOT NULL,
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `fine_paid` enum('yes','no','partial') DEFAULT 'no',
  `fine_paid_date` date DEFAULT NULL,
  `suspension_start_date` date DEFAULT NULL,
  `suspension_end_date` date DEFAULT NULL,
  `parent_notified` enum('yes','no') DEFAULT 'no',
  `parent_notification_date` date DEFAULT NULL,
  `follow_up_required` enum('yes','no') DEFAULT 'no',
  `follow_up_date` date DEFAULT NULL,
  `follow_up_notes` text DEFAULT NULL,
  `status` enum('open','resolved','under_review','appealed') DEFAULT 'open',
  `resolved_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_reg_no` (`student_reg_no`),
  KEY `incident_date` (`incident_date`),
  KEY `incident_type` (`incident_type`),
  KEY `severity` (`severity`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create violation categories lookup table
CREATE TABLE IF NOT EXISTS `violation_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `category_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `default_severity` enum('minor','moderate','major','critical') DEFAULT 'minor',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_code` (`category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default violation categories (skip if already exist)
INSERT IGNORE INTO `violation_categories` (`category_name`, `category_code`, `description`, `default_severity`) VALUES
('Noise Disturbance', 'NOISE', 'Creating excessive noise during quiet hours', 'minor'),
('Unauthorized Guest', 'UNAUTH_GUEST', 'Bringing unauthorized guests to hostel premises', 'moderate'),
('Damage to Property', 'PROPERTY_DMG', 'Damaging hostel property or facilities', 'major'),
('Substance Abuse', 'SUBSTANCE', 'Possession or use of prohibited substances', 'critical'),
('Fighting/Violence', 'VIOLENCE', 'Physical altercation or violent behavior', 'critical'),
('Theft', 'THEFT', 'Stealing property belonging to hostel or other students', 'major'),
('Curfew Violation', 'CURFEW', 'Returning to hostel after curfew time', 'moderate'),
('Cleanliness Issues', 'CLEANLINESS', 'Failure to maintain room cleanliness standards', 'minor'),
('Smoking', 'SMOKING', 'Smoking in prohibited areas', 'moderate'),
('Ragging', 'RAGGING', 'Involvement in ragging activities', 'critical'),
('Unauthorized Absence', 'ABSENCE', 'Leaving hostel without proper permission', 'moderate'),
('Disrespect to Staff', 'DISRESPECT', 'Disrespectful behavior towards hostel staff', 'moderate'),
('Misuse of Facilities', 'FACILITY_MISUSE', 'Improper use of hostel facilities', 'minor'),
('Late Fee Payment', 'LATE_FEE', 'Delayed payment of hostel fees', 'minor'),
('Other', 'OTHER', 'Other violations not listed above', 'minor');

-- Create view for disciplinary records with student details
CREATE OR REPLACE VIEW `disciplinary_records_view` AS
SELECT 
    dr.*,
    CONCAT(u.first_name, ' ', u.last_name) as student_name,
    u.emailid as student_email,
    u.contact_no as student_contact,
    hb.roomno as room_no,
    vc.category_name as violation_category_name,
    vc.default_severity as category_default_severity,
    CASE 
        WHEN dr.incident_type = 'warning' THEN 'Warning Issued'
        WHEN dr.incident_type = 'fine' THEN 'Fine Imposed'
        WHEN dr.incident_type = 'suspension' THEN 'Suspension'
        WHEN dr.incident_type = 'expulsion' THEN 'Expulsion'
        ELSE 'Other Action'
    END as incident_type_label,
    CASE 
        WHEN dr.severity = 'minor' THEN 'Minor'
        WHEN dr.severity = 'moderate' THEN 'Moderate'
        WHEN dr.severity = 'major' THEN 'Major'
        WHEN dr.severity = 'critical' THEN 'Critical'
    END as severity_label,
    CASE 
        WHEN dr.status = 'open' THEN 'Open'
        WHEN dr.status = 'resolved' THEN 'Resolved'
        WHEN dr.status = 'under_review' THEN 'Under Review'
        WHEN dr.status = 'appealed' THEN 'Appealed'
    END as status_label,
    DATEDIFF(CURDATE(), dr.incident_date) as days_since_incident
FROM disciplinary_records dr
JOIN userregistration u ON dr.student_reg_no = u.registration_no
LEFT JOIN hostelbookings hb ON dr.student_reg_no = hb.regno
LEFT JOIN violation_categories vc ON dr.violation_category = vc.category_code
ORDER BY dr.incident_date DESC, dr.created_at DESC;

-- ===============================================
-- USAGE INSTRUCTIONS
-- ===============================================
-- 1. Run this SQL file in your database
-- 2. The disciplinary_records table will store all disciplinary actions
-- 3. The violation_categories table provides predefined violation types
-- 4. The disciplinary_records_view provides a comprehensive view with student details
-- 5. Access the feature through the admin panel navigation
