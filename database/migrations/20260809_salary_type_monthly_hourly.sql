-- Adds the salary type used by Employees/make-salary-slips.php monthly-hourly calculation.
-- Safe to run multiple times.

INSERT INTO salary_type (st_id, st_type_name)
SELECT 4, 'Monthly Hourly'
WHERE NOT EXISTS (SELECT 1 FROM salary_type WHERE st_id = 4);

UPDATE salary_type
SET st_type_name = 'Monthly Hourly'
WHERE st_id = 4 AND st_type_name = '';
