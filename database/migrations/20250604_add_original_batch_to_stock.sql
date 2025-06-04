-- Add original_batch column to tbl_stock table
ALTER TABLE tbl_stock ADD COLUMN original_batch varchar(50) DEFAULT NULL AFTER batch_number;
