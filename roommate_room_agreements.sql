-- Table for storing roommate room agreements
CREATE TABLE `roommate_room_agreements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roommate_pair_id` int(11) NOT NULL,
  `student1_reg_no` varchar(255) NOT NULL,
  `student2_reg_no` varchar(255) NOT NULL,
  `agreed_room_no` int(11) NOT NULL,
  `student1_agreed` tinyint(1) DEFAULT 0,
  `student2_agreed` tinyint(1) DEFAULT 0,
  `agreement_status` enum('pending','agreed','cancelled','booked') DEFAULT 'pending',
  `room_type_preference` varchar(100) NULL,
  `max_budget` int(11) NULL,
  `preferred_floor` int(11) NULL,
  `special_requirements` text NULL,
  `agreed_at` timestamp NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_room_agreement` (`roommate_pair_id`, `agreed_room_no`),
  INDEX `idx_roommate_pair` (`roommate_pair_id`),
  INDEX `idx_room_no` (`agreed_room_no`),
  INDEX `idx_agreement_status` (`agreement_status`),
  INDEX `idx_students` (`student1_reg_no`, `student2_reg_no`),
  FOREIGN KEY (`roommate_pair_id`) REFERENCES `roommate_matches`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`agreed_room_no`) REFERENCES `roomsdetails`(`room_no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- View for agreed room bookings ready for processing
CREATE VIEW `agreed_room_bookings_view` AS
SELECT 
    rra.*,
    CONCAT(u1.first_name, ' ', u1.last_name) as student1_name,
    CONCAT(u2.first_name, ' ', u2.last_name) as student2_name,
    u1.contact_no as student1_contact,
    u1.emailid as student1_email,
    u1.gender as student1_gender,
    u2.contact_no as student2_contact,
    u2.emailid as student2_email,
    u2.gender as student2_gender,
    rd.seater,
    rd.fees,
    rm.match_score as compatibility_score,
    sp1.branch as student1_branch,
    sp2.branch as student2_branch,
    b1.branch_name as student1_branch_name,
    b2.branch_name as student2_branch_name
FROM roommate_room_agreements rra
JOIN userregistration u1 ON rra.student1_reg_no = u1.registration_no
JOIN userregistration u2 ON rra.student2_reg_no = u2.registration_no
JOIN roomsdetails rd ON rra.agreed_room_no = rd.room_no
JOIN roommate_matches rm ON rra.roommate_pair_id = rm.id
LEFT JOIN student_preferences sp1 ON rra.student1_reg_no = sp1.reg_no
LEFT JOIN student_preferences sp2 ON rra.student2_reg_no = sp2.reg_no
LEFT JOIN branches b1 ON sp1.branch = b1.branch_code
LEFT JOIN branches b2 ON sp2.branch = b2.branch_code
WHERE rra.agreement_status = 'agreed'
ORDER BY rra.agreed_at ASC;

-- Trigger to update agreement status when both students agree
DELIMITER //
CREATE TRIGGER `update_agreement_status` 
AFTER UPDATE ON `roommate_room_agreements`
FOR EACH ROW
BEGIN
    IF NEW.student1_agreed = 1 AND NEW.student2_agreed = 1 AND OLD.agreement_status = 'pending' THEN
        UPDATE roommate_room_agreements 
        SET agreement_status = 'agreed', agreed_at = NOW() 
        WHERE id = NEW.id;
    END IF;
END//
DELIMITER ;

-- Sample data for testing
-- INSERT INTO `roommate_room_agreements` 
-- (`roommate_pair_id`, `student1_reg_no`, `student2_reg_no`, `agreed_room_no`, `student1_agreed`, `student2_agreed`, `max_budget`) 
-- VALUES 
-- (1, 'CS2021001', 'CS2021002', 101, 1, 1, 5000),
-- (2, 'CS2021003', 'CS2021004', 102, 1, 0, 6000);