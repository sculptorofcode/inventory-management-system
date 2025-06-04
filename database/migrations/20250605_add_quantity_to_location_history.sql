-- Add quantity column to stock location history table
-- This allows tracking how much quantity was moved in each location change

ALTER TABLE tbl_stock_location_history ADD COLUMN quantity int(11) DEFAULT NULL AFTER batch_number;
