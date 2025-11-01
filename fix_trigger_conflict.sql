-- Fix trigger conflict by dropping the problematic trigger
USE hostel_db;

-- Drop the trigger that's causing the conflict
DROP TRIGGER IF EXISTS update_agreement_status;

-- We'll handle the status update manually in PHP code instead
SELECT 'Trigger conflict fixed - manual status update enabled!' as Status;