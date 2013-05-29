BEGIN;
  ALTER TABLE management.error_occurrence DROP COLUMN parameters;
  ALTER TABLE management.error_occurrence ADD CONSTRAINT single_occurrence UNIQUE(error_id, occurred_at);
COMMIT;