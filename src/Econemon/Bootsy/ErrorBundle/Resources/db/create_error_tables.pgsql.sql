BEGIN;

CREATE FUNCTION create_error_tables() RETURNS void AS $$
  DECLARE
    sch TEXT := 'management';
    base_table TEXT := 'error';
    log_table TEXT := 'error_occurrence';
  BEGIN
    PERFORM * FROM information_schema.schemata WHERE schema_name = sch;
    IF NOT FOUND THEN
      EXECUTE 'CREATE SCHEMA ' || quote_ident(sch);
    END IF;

      PERFORM * FROM information_schema.tables WHERE table_schema = sch AND table_name = base_table;
    IF NOT FOUND THEN
      EXECUTE 'CREATE TABLE ' || quote_ident(sch) || '.' || quote_ident(base_table) || '(
        id SERIAL PRIMARY KEY,
        file TEXT,
        line INT,
        message TEXT,
        UNIQUE (file, line, message)
      )';
    END IF;

    PERFORM * FROM information_schema.tables WHERE table_schema = sch AND table_name = log_table;
    IF NOT FOUND THEN
      EXECUTE 'CREATE TABLE ' || quote_ident(sch) || '.' || quote_ident(log_table) || '(
        id SERIAL PRIMARY KEY,
        ' || quote_ident(base_table || '_id') || ' INT NOT NULL REFERENCES ' || quote_ident(sch) || '.' || quote_ident(base_table) || ',
        occurred_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PARAMETERS TEXT NULL
      )';
    END IF;
  END
$$ LANGUAGE plpgsql;

SELECT create_error_tables();

DROP FUNCTION create_error_tables();

COMMIT;