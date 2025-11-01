-- DATABASE UPDATE FOR ROOMMATE BOOKING FEATURES
-- Run these commands one by one in phpMyAdmin

-- Step 1: Add new columns to existing hostelbookings table
ALTER TABLE `hostelbookings` 
ADD COLUMN `booking_type` enum('solo','joint','matched') DEFAULT 'solo' AFTER `pin_code`,
ADD COLUMN `roommate_pair_id` int(11) NULL AFTER `booking_type`,
ADD COLUMN `looking_for_roommate` tinyint(1) DEFAULT 1 AFTER `roommate_pair_id`,
ADD COLUMN `booking_status` enum('pending','confirmed','cancelled') DEFAULT 'pending' AFTER `looking_for_roommate`,
ADD COLUMN `joint_booking_id` varchar(50) NULL AFTER `booking_status`;

-- Step 2: Add indexes for better performance
ALTER TABLE `hostelbookings`
ADD INDEX `idx_roommate_pair` (`roommate_pair_id`),
ADD INDEX `idx_joint_booking` (`joint_booking_id`),
ADD INDEX `idx_booking_type` (`booking_type`),
ADD INDEX `idx_booking_status` (`booking_status`);

-- Step 3: Add new columns to roommate_requests table
ALTER TABLE `roommate_requests`
ADD COLUMN `request_type` enum('roommate_only','joint_booking') DEFAULT 'roommate_only' AFTER `status`,
ADD COLUMN `preferred_room_type` int(11) NULL AFTER `request_type`,
ADD COLUMN `preferred_budget` int(11) NULL AFTER `preferred_room_type`;

-- Step 4: Create joint_bookings table
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
  UNIQUE KEY `joint_booking_id` (`joint_booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Step 5: Create solo_match_queue table
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
  UNIQUE KEY `student_reg_no` (`student_reg_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Step 6: Create booking_workflow table
CREATE TABLE `booking_workflow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` varchar(50) NOT NULL UNIQUE,
  `workflow_type` enum('solo_booking','joint_booking','match_later') NOT NULL,
  `student_reg_nos` text NOT NULL,
  `current_step` varchar(50) NOT NULL,
  `workflow_data` text NULL,
  `status` enum('in_progress','completed','cancelled') DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workflow_id` (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Step 7: Create improved views

-- Drop existing views first
DROP VIEW IF EXISTS `available_rooms_view`;
DROP VIEW IF EXISTS `solo_students_view`;
DROP VIEW IF EXISTS `joint_booking_view`;

-- Create solo students view
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

-- Create available rooms view
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

-- Create joint booking view
CREATE VIEW `joint_booking_view` AS
SELECT 
    jb.*,
    CONCAT(h1.firstName, ' ', h1.lastName) as student1_name,
    CONCAT(h2.firstName, ' ', h2.lastName) as student2_name,
    h1.contactno as student1_contact,
    h2.contactno as student2_contact,
    rm.match_score as compatibility_score
FROM joint_bookings jb
JOIN hostelbookings h1 ON jb.student1_reg_no = h1.regno
JOIN hostelbookings h2 ON jb.student2_reg_no = h2.regno
LEFT JOIN roommate_matches rm ON jb.roommate_pair_id = rm.id;

-- Step 8: Update existing data (set default booking status for existing records)
UPDATE hostelbookings SET booking_status = 'confirmed' WHERE booking_status IS NULL;
UPDATE hostelbookings SET booking_type = 'solo' WHERE booking_type IS NULL;
UPDATE hostelbookings SET looking_for_roommate = 1 WHERE looking_for_roommate IS NULL;