SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create complaints table
CREATE TABLE `complaints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_name` varchar(255) NOT NULL,
  `reg_no` varchar(255) NOT NULL,
  `room_no` int(11) NOT NULL,
  `complaint` text NOT NULL,
  `status` enum('pending','in-progress','resolved') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create courses table
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(255) NOT NULL,
  `course_sn` varchar(255) NOT NULL,
  `course_fn` varchar(255) NOT NULL,
  `entry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create hostelbookings table
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
  `entry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- Create roomsdetails table
CREATE TABLE `roomsdetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seater` int(11) NOT NULL,
  `room_no` int(11) NOT NULL,
  `fees` int(11) NOT NULL,
  `entry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- Create state_master table
CREATE TABLE `state_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `State` varchar(38) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Insert state data
INSERT INTO `state_master` (`id`, `State`) VALUES
(1, 'Andhra Pradesh'),
(2, 'Arunachal Pradesh'),
(3, 'Assam'),
(4, 'Bihar'),
(5, 'Chhattisgarh'),
(6, 'Goa'),
(7, 'Gujarat'),
(8, 'Haryana'),
(9, 'Himachal Pradesh'),
(10, 'Jharkhand'),
(11, 'Karnataka'),
(12, 'Kerala'),
(13, 'Madhya Pradesh'),
(14, 'Maharashtra'),
(15, 'Manipur'),
(16, 'Meghalaya'),
(17, 'Mizoram'),
(18, 'Nagaland'),
(19, 'Odisha'),
(20, 'Punjab'),
(21, 'Rajasthan'),
(22, 'Sikkim'),
(23, 'Tamil Nadu'),
(24, 'Telangana'),
(25, 'Tripura'),
(26, 'Uttarakhand'),
(27, 'Uttar Pradesh'),
(28, 'West Bengal'),
(29, 'Andaman & Nicobar'),
(30, 'Chandigarh'),
(31, 'Dadra and Nagar Haveli and Daman & Diu'),
(32, 'Delhi'),
(33, 'Jammu & Kashmir'),
(34, 'Lakshadweep'),
(35, 'Puducherry'),
(36, 'Ladakh');

-- Create userregistration table
CREATE TABLE `userregistration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration_no` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `contact_no` bigint(20) NOT NULL,
  `emailid` varchar(255) NOT NULL,
  `entry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(300) NOT NULL,
  `entry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- Insert admin user
INSERT INTO `users` (`id`, `username`, `email`, `password`, `entry_date`) VALUES
(1, 'admin', 'admin@mail.com', '2b0c6e2034b6aa4ea3757321533b6741', '2020-09-08 20:31:45');

-- NEW ROOMMATE MATCHING TABLES --

-- Create branches table
CREATE TABLE `branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_code` varchar(10) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branch_code` (`branch_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Insert branches data
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

-- Create student_preferences table
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

-- Create roommate_matches table
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

-- Create roommate_requests table
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

-- CREATE VIEWS --

-- Student profile view
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

-- Compatibility matrix view
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

-- Set AUTO_INCREMENT starting values
ALTER TABLE `state_master` AUTO_INCREMENT=37;
ALTER TABLE `branches` AUTO_INCREMENT=11;