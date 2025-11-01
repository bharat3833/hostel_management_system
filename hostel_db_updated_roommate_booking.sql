SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Updated hostelbookings table with roommate booking features
DROP TABLE IF EXISTS `hostelbookings`;
CREATE TABLE `hostelbookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomno` int(11) NOT NULL,
  `seater` int(11) NOT NULL,
  `feespm` int(11) NOT NULL,
  `total_amount` int(11) NOT NULL,
  `foodstatus` int(11) NOT NULL,
  `stayfrom` date NOT NULL,
  `duration` int(11) NOT NULL,
  `course` varchar(500) NOT NULL,
  `regno` varchar(255) NOT NULL,
  `firstName` varchar(500) NOT NULL,
  `lastName` varchar(500) NOT NULL,
  `gender` varchar(250) NOT NULL,
  `contactno` bigint(11) NOT NULL,
  `emailid` varchar(500) NOT NULL,
  `egycontactno` bigint(11) NOT NULL,
  `guardian_name` varchar(500) NOT NULL,
  `guardian_relation` varchar(500) NOT NULL,
  `guardian_contact` bigint(11) NOT NULL,
  `state` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(500) NOT NULL,
  `pin_code` int(11) NOT NULL,
  `booking_type` enum('solo','joint','matched') DEFAULT 'solo',
  `roommate_pair_id` int(11) NULL,
  `looking_for_roommate` tinyint(1) DEFAULT 1,
  `booking_status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `joint_booking_id` varchar(50) NULL,
  `entry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_roommate_pair` (`roommate_pair_id`),
  INDEX `idx_joint_booking` (`joint_booking_id`),
  INDEX `idx_booking_type` (`booking_type`),
  INDEX `idx_booking_status` (`booking_status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- Updated roommate_requests table with booking features
DROP TABLE IF EXISTS `roommate_requests`;
CREATE TABLE `roommate_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requester_reg_no` varchar(255) NOT NULL,
  `requested_reg_no` varchar(255) NOT NULL,
  `message` text,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `request_type` enum('roommate_only','joint_booking') DEFAULT 'roommate_only',
  `preferred_room_type` int(11) NULL,
  `preferred_budget` int(11) NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_requester` (`requester_reg_no`),
  INDEX `idx_requested` (`requested_reg_no`),
  INDEX `idx_request_type` (`request_type`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Joint booking management table
CREATE TABLE `joint_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `joint_booking_id` varchar(50) NOT NULL UNIQUE,
  `student1_reg_no` varchar(255) NOT NULL,
  `student2_reg_no` varchar(255) NOT NULL,
  `roommate_pair_id` int(11) NOT NULL,
  `preferred_room_type` int(11) NULL,
  `preferred_budget_range` varchar(50) NULL,
  `booking_status` enum('initiated','room_selected','payment_pending','confirmed','cancelled') DEFAULT 'initiated',
  `selected_room` int(11) NULL,
  `total_amount` int(11) NULL,
  `booking_preferences` text NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `joint_booking_id` (`joint_booking_id`),
  INDEX `idx_roommate_pair` (`roommate_pair_id`),
  INDEX `idx_students` (`student1_reg_no`, `student2_reg_no`),
  INDEX `idx_selected_room` (`selected_room`),
  INDEX `idx_booking_status` (`booking_status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Solo students looking for matches table
CREATE TABLE `solo_match_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_reg_no` varchar(255) NOT NULL,
  `hostel_booking_id` int(11) NOT NULL,
  `room_no` int(11) NOT NULL,
  `room_capacity` int(11) NOT NULL,
  `looking_for_match` tinyint(1) DEFAULT 1,
  `match_preferences` text NULL,
  `priority_score` decimal(5,2) DEFAULT 0,
  `urgent_booking` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_reg_no` (`student_reg_no`),
  INDEX `idx_room` (`room_no`),
  INDEX `idx_priority` (`priority_score`),
  INDEX `idx_looking` (`looking_for_match`),
  INDEX `idx_urgent` (`urgent_booking`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Booking workflow tracking
CREATE TABLE `booking_workflow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` varchar(50) NOT NULL UNIQUE,
  `workflow_type` enum('solo_booking','joint_booking','match_later') NOT NULL,
  `student_reg_nos` text NOT NULL, -- JSON array of student registration numbers
  `current_step` varchar(50) NOT NULL,
  `workflow_data` text NULL, -- JSON data for workflow state
  `status` enum('in_progress','completed','cancelled') DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workflow_id` (`workflow_id`),
  INDEX `idx_workflow_type` (`workflow_type`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Updated Views

-- View for solo students available for matching
CREATE VIEW `solo_students_view` AS
SELECT 
    smq.*,
    CONCAT(h.firstName, ' ', h.lastName) as student_name,
    h.course,
    h.gender,
    h.contactno,
    h.emailid,
    sp.lifestyle,
    sp.study_preference,
    sp.noise_tolerance,
    sp.cleanliness_level,
    sp.food_habit,
    sp.sleep_schedule,
    sp.social_behavior,
    sp.branch,
    sp.year_of_study,
    b.branch_name,
    rd.seater,
    rd.fees
FROM solo_match_queue smq
JOIN hostelbookings h ON smq.student_reg_no = h.regno
LEFT JOIN student_preferences sp ON smq.student_reg_no = sp.reg_no
LEFT JOIN branches b ON sp.branch = b.branch_code
LEFT JOIN roomsdetails rd ON smq.room_no = rd.room_no
WHERE smq.looking_for_match = 1;

-- View for available rooms with capacity
CREATE VIEW `available_rooms_view` AS
SELECT 
    rd.room_no,
    rd.seater,
    rd.fees,
    COUNT(h.id) as current_occupants,
    (rd.seater - COUNT(h.id)) as available_spaces,
    CASE 
        WHEN COUNT(h.id) = 0 THEN 'empty'
        WHEN COUNT(h.id) < rd.seater THEN 'partially_occupied'
        ELSE 'full'
    END as occupancy_status,
    GROUP_CONCAT(CONCAT(h.firstName, ' ', h.lastName) SEPARATOR ', ') as current_residents,
    GROUP_CONCAT(h.regno SEPARATOR ', ') as current_reg_nos
FROM roomsdetails rd
LEFT JOIN hostelbookings h ON rd.room_no = h.roomno AND h.booking_status = 'confirmed'
GROUP BY rd.room_no, rd.seater, rd.fees
ORDER BY available_spaces DESC, rd.fees ASC;

-- View for joint booking status
CREATE VIEW `joint_booking_view` AS
SELECT 
    jb.*,
    CONCAT(h1.firstName, ' ', h1.lastName) as student1_name,
    CONCAT(h2.firstName, ' ', h2.lastName) as student2_name,
    h1.contactno as student1_contact,
    h2.contactno as student2_contact,
    rm.match_score as compatibility_score,
    rd.seater as room_capacity,
    rd.fees as room_fees
FROM joint_bookings jb
JOIN hostelbookings h1 ON jb.student1_reg_no = h1.regno
JOIN hostelbookings h2 ON jb.student2_reg_no = h2.regno
LEFT JOIN roommate_matches rm ON jb.roommate_pair_id = rm.id
LEFT JOIN roomsdetails rd ON jb.selected_room = rd.room_no;

-- Sample data for testing (optional)
INSERT INTO `booking_workflow` (`workflow_id`, `workflow_type`, `student_reg_nos`, `current_step`, `workflow_data`, `status`) VALUES
('WF001', 'solo_booking', '["CS2021001"]', 'room_selection', '{"urgent": true, "budget_max": 5000}', 'in_progress'),
('WF002', 'joint_booking', '["CS2021002", "CS2021003"]', 'payment_pending', '{"selected_room": 101, "total_amount": 8000}', 'in_progress');

-- Triggers for automatic workflow management

DELIMITER //

-- Trigger to add solo students to match queue when they book alone
CREATE TRIGGER `after_solo_booking_insert` 
AFTER INSERT ON `hostelbookings`
FOR EACH ROW
BEGIN
    IF NEW.booking_type = 'solo' AND NEW.looking_for_roommate = 1 THEN
        INSERT IGNORE INTO solo_match_queue 
        (student_reg_no, hostel_booking_id, room_no, room_capacity, urgent_booking)
        SELECT NEW.regno, NEW.id, NEW.roomno, NEW.seater, 
               CASE WHEN NEW.booking_status = 'confirmed' THEN 1 ELSE 0 END;
    END IF;
END//

-- Trigger to update match queue when booking is confirmed
CREATE TRIGGER `after_booking_status_update`
AFTER UPDATE ON `hostelbookings`
FOR EACH ROW
BEGIN
    IF NEW.booking_status = 'confirmed' AND OLD.booking_status != 'confirmed' THEN
        UPDATE solo_match_queue 
        SET urgent_booking = 1, priority_score = priority_score + 10
        WHERE student_reg_no = NEW.regno;
    END IF;
END//

-- Trigger to create joint booking record when roommate pair is accepted
CREATE TRIGGER `after_roommate_pair_created`
AFTER INSERT ON `roommate_matches`
FOR EACH ROW
BEGIN
    DECLARE joint_id VARCHAR(50);
    SET joint_id = CONCAT('JB', YEAR(NOW()), MONTH(NOW()), DAY(NOW()), '_', NEW.id);
    
    IF NEW.status = 'accepted' AND NEW.match_factors LIKE '%joint_booking%' THEN
        INSERT INTO joint_bookings 
        (joint_booking_id, student1_reg_no, student2_reg_no, roommate_pair_id, booking_status)
        VALUES (joint_id, NEW.student1_reg_no, NEW.student2_reg_no, NEW.id, 'initiated');
    END IF;
END//

DELIMITER ;

-- Stored procedure for finding compatible roommates for solo students
DELIMITER //
CREATE PROCEDURE `FindCompatibleRoommate`(IN student_reg_no VARCHAR(255))
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE potential_roommate VARCHAR(255);
    DECLARE compatibility_score DECIMAL(5,2);
    DECLARE cur CURSOR FOR 
        SELECT 
            ssv.student_reg_no,
            COALESCE(cv.compatibility_score, 0) as score
        FROM solo_students_view ssv
        LEFT JOIN compatibility_matrix_view cv ON 
            (cv.student1 = student_reg_no AND cv.student2 = ssv.student_reg_no)
            OR (cv.student2 = student_reg_no AND cv.student1 = ssv.student_reg_no)
        WHERE ssv.student_reg_no != student_reg_no
        AND ssv.room_capacity > 1
        AND ssv.student_reg_no NOT IN (
            SELECT student1_reg_no FROM roommate_matches WHERE status = 'accepted'
            UNION
            SELECT student2_reg_no FROM roommate_matches WHERE status = 'accepted'
        )
        ORDER BY score DESC, ssv.created_at ASC
        LIMIT 5;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO potential_roommate, compatibility_score;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Insert suggestion into roommate_requests if score is decent
        IF compatibility_score >= 30 THEN
            INSERT IGNORE INTO roommate_requests 
            (requester_reg_no, requested_reg_no, message, status, request_type)
            VALUES 
            (potential_roommate, student_reg_no, 
             CONCAT('System suggested match based on ', compatibility_score, '% compatibility. Both students are looking for roommates.'), 
             'pending', 'roommate_only');
        END IF;
    END LOOP;
    
    CLOSE cur;
END//

-- Stored procedure for initiating joint booking
CREATE PROCEDURE `InitiateJointBooking`(
    IN student1_reg VARCHAR(255), 
    IN student2_reg VARCHAR(255), 
    IN pair_id INT,
    OUT joint_booking_id VARCHAR(50)
)
BEGIN
    DECLARE new_joint_id VARCHAR(50);
    SET new_joint_id = CONCAT('JB', YEAR(NOW()), LPAD(MONTH(NOW()),2,'0'), LPAD(DAY(NOW()),2,'0'), '_', pair_id);
    
    INSERT INTO joint_bookings 
    (joint_booking_id, student1_reg_no, student2_reg_no, roommate_pair_id, booking_status)
    VALUES (new_joint_id, student1_reg, student2_reg, pair_id, 'initiated');
    
    SET joint_booking_id = new_joint_id;
END//

DELIMITER ;