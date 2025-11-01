-- Fix for missing available_rooms_view
USE hostel_db;

-- Create the missing available_rooms_view
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
LEFT JOIN hostelbookings h ON rd.room_no = h.roomno
GROUP BY rd.room_no, rd.seater, rd.fees
ORDER BY available_spaces DESC, rd.fees ASC;

SELECT 'available_rooms_view created successfully!' as Status;