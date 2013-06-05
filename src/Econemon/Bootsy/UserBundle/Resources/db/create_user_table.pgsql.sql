BEGIN;
  -- The following creates an exact copy of the schema provided by the FOSUserBundle which is normally managed by Doctrine.
  -- However, because we chose to diverge from the Doctrine way of managing the schema, we have to do it ourselves.

  -- Note that this would ordinarily be an anonymous PL/pgsql block, but since we have to support Postgres 8.4, we
  -- temporarily create a stored procedure, silly though that may look. We do use PL/pgsql over an ordinary CREATE TABLE
  -- in order to have the script be re-runnable without harm / error messages.

  CREATE FUNCTION temp_create_user_table() RETURNS void AS $$
    DECLARE
      sch TEXT := 'public';
      tbl TEXT := 'app_user';
    BEGIN
      PERFORM * FROM information_schema.tables WHERE table_schema = sch AND table_name = tbl;
      IF NOT FOUND THEN
        EXECUTE 'CREATE TABLE ' || quote_ident(sch) || '.' || quote_ident(tbl) || '(
        id SERIAL PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        username_canonical VARCHAR(255) UNIQUE NOT NULL,
        email VARCHAR(255) NOT NULL,
        email_canonical VARCHAR(255) UNIQUE NOT NULL,
        enabled BOOLEAN NOT NULL,
        salt TEXT NOT NULL,
        password TEXT NOT NULL,
        last_login TIMESTAMP NULL,
        locked BOOLEAN NOT NULL,
        expired BOOLEAN NOT NULL,
        expires_at TIMESTAMP NULL,
        confirmation_token TEXT NULL,
        password_requested_at TIMESTAMP NULL,
        roles TEXT NOT NULL,
        credentials_expired BOOLEAN NOT NULL,
        credentials_expire_at TIMESTAMP NULL
        )';
      END IF;
    END
  $$ LANGUAGE plpgsql;

  SELECT temp_create_user_table();

  DROP FUNCTION temp_create_user_table();

COMMIT;