## PHP Test helper classes

Unit testing helper classes:

## What does it help with?

- Load file fixtures (sql, json).
- Tests where you interact with database.
- Load database fixtures (sql).
- Optimize database tearDown and setUp by introducing fixtures and residentFixtures. Where resident fixtures are loaded once pest test class.
- Database helper methods (see database interface)[src/Database/TestDb.php].

## Install

```json
{
    "require": {
        "rzajac/php-test-helper": "0.1.*"
    }
}
```

## Run unit tests

Yes! The package has it's own unit tests.

```
$ vendor/bin/phpunit --coverage-html=./coverage 
```

## Setup test database

```sql
CREATE USER 'unitTest'@'localhost' IDENTIFIED BY 'unitTestPass';
GRANT CREATE ROUTINE, CREATE VIEW, ALTER, SHOW VIEW, CREATE, ALTER ROUTINE, EVENT, INSERT, SELECT, DELETE, TRIGGER, REFERENCES, UPDATE, DROP, EXECUTE, LOCK TABLES, CREATE TEMPORARY TABLES, INDEX ON `test`.* TO 'unitTest'@'localhost';
FLUSH PRIVILEGES;
```

## License

Apache License Version 2.0
