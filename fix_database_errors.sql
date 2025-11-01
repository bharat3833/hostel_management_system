-- Fix for database import errors
-- Run these commands step by step in phpMyAdmin

-- Step 1: Check if tables already exist and drop them if needed
DROP TABLE IF EXISTS `student_preferences`;
DROP TABLE IF EXISTS `roommate_matches`;
DROP TABLE IF EXISTS `roommate_requests`;
DROP TABLE IF EXISTS `branches`;

-- Step 2: Drop views if they exist
DROP VIEW IF EXISTS `student_profile_view`;
DROP VIEW IF EXISTS `compatibility_matrix_view`;

-- Step 3: Create branches table with proper structure
CREATE TABLE `branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_code` varchar(10) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branch_code` (`branch_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Step 4: Insert branch data
INSERT INTO `branches` (`branch_code`, `branch_name`, `department`) VALUES
('CSE', 'Computer Science Engineering', 'Computer Science'),
('ECE', 'Electronics and Communication Engineering', 'Electronics'),
('EEE', 'Electrical and Electronics Engineering', 'Electrical'),
('ME', 'Mechanical Engineering', 'Mechanical'),
('CE', 'Civil Engineering', 'Civil'),
('IT', 'Information Technology', 'Computer Science'),
('EIE', 'Electronics and Instrumentation Engineering', 'Electronics'),
('CHE', 'Chemical Engineering', 'Chemical'),
('AE', 'Aeronautical Engineering', 'Aeronautical'),
('BME', 'Biomedical Engineering', 'Biomedical');

-- Step 5: Create student_preferences table
CREATE TABLE `student_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(255) NOT NULL,
  `lifestyle` enum('early-bird','night-owl','moderate') NOT NULL,
  `study_preference` enum('silent','music','discussion','flexible') NOT NULL,
  `noise_tolerance` enum('complete-silence','low-noise','moderate-noise','high-noise') NOT NULL,
  `cleanliness_level` enum('very-clean','clean','moderate','flexible') NOT NULL,
  `food_habit` enum('vegetarian','non-vegetarian','vegan','jain','flexible') NOT NULL,
  `sleep_schedule` enum('early-sleeper','late-sleeper','irregular','flexible') NOT NULL,
  `social_behavior` enum('introverted','extroverted','ambivert') NOT NULL,
  `smoking_drinking` enum('none','social','regular') NOT NULL DEFAULT 'none',
  `interests` text,
  `branch` varchar(100) NOT NULL,
  `year_of_study` enum('1','2','3','4','postgrad') NOT NULL,
  `preferred_branch_same` tinyint(1) DEFAULT 1,
  `preferred_year_same` tinyint(1) DEFAULT 0,
  `priority_preferences` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reg_no` (`reg_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Step 6: Create roommate_matches table
CREATE TABLE `roommate_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student1_reg_no` varchar(255) NOT NULL,
  `student2_reg_no` varchar(255) NOT NULL,
  `match_score` decimal(5,2) NOT NULL,
  `match_factors` text,
  `status` enum('suggested','accepted','rejected','pending') DEFAULT 'suggested',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_match` (`student1_reg_no`, `student2_reg_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Step 7: Create roommate_requests table
CREATE TABLE `roommate_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requester_reg_no` varchar(255) NOT NULL,
  `requested_reg_no` varchar(255) NOT NULL,
  `message` text,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Step 8: Create views
CREATE VIEW `student_profile_view` AS
SELECT 
    h.regno,
    CONCAT(h.firstName, ' ', h.lastName) as full_name,
    h.course,
    h.gender,
    h.contactno,
    h.emailid,
    h.roomno,
    sp.lifestyle,
    sp.study_preference,
    sp.noise_tolerance,
    sp.cleanliness_level,
    sp.food_habit,
    sp.sleep_schedule,
    sp.social_behavior,
    sp.interests,
    sp.branch,
    sp.year_of_study,
    b.branch_name,
    b.department
FROM hostelbookings h
LEFT JOIN student_preferences sp ON h.regno = sp.reg_no
LEFT JOIN branches b ON sp.branch = b.branch_code;

CREATE VIEW `compatibility_matrix_view` AS
SELECT 
    s1.reg_no as student1,
    s2.reg_no as student2,
    s1.branch as branch1,
    s2.branch as branch2,
    (
        CASE WHEN s1.lifestyle = s2.lifestyle THEN 15 ELSE 0 END +
        CASE WHEN s1.study_preference = s2.study_preference THEN 20 ELSE 0 END +
        CASE WHEN s1.noise_tolerance = s2.noise_tolerance THEN 15 ELSE 0 END +
        CASE WHEN s1.cleanliness_level = s2.cleanliness_level THEN 15 ELSE 0 END +
        CASE WHEN s1.food_habit = s2.food_habit THEN 10 ELSE 0 END +
        CASE WHEN s1.sleep_schedule = s2.sleep_schedule THEN 15 ELSE 0 END +
        CASE WHEN s1.social_behavior = s2.social_behavior THEN 5 ELSE 0 END +
        CASE WHEN s1.branch = s2.branch AND s1.preferred_branch_same = 1 THEN 5 ELSE 0 END
    ) as compatibility_score
FROM student_preferences s1
CROSS JOIN student_preferences s2
WHERE s1.reg_no != s2.reg_no;