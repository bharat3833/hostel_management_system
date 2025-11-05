-- =============================================
-- UPDATE EXISTING TABLE FOR GATE PASS & VACATION
-- =============================================
-- Run this if you already have the hostel_checkin_checkout table

-- Add new columns to existing table
ALTER TABLE `hostel_checkin_checkout` 
ADD COLUMN `pass_type` enum('gate-pass','vacation') NOT NULL DEFAULT 'gate-pass' AFTER `room_no`,
ADD COLUMN `expected_return_date` date DEFAULT NULL AFTER `action_time`,
ADD COLUMN `expected_return_time` time DEFAULT NULL AFTER `expected_return_date`,
ADD COLUMN `destination` varchar(255) DEFAULT NULL AFTER `expected_return_time`,
ADD COLUMN `purpose` text DEFAULT NULL AFTER `destination`,
ADD COLUMN `contact_during_leave` varchar(20) DEFAULT NULL AFTER `purpose`,
ADD KEY `pass_type` (`pass_type`);

-- Drop and recreate the view with new fields
DROP VIEW IF EXISTS `checkin_checkout_view`;

CREATE VIEW `checkin_checkout_view` AS
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
