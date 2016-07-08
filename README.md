## PHP Test helper classes

Unit testing helper classes.

## What does it help with?

- Load file fixtures (`sql`, `json`).
- Tests where you interact with database.
- Load database fixtures (`sql`).
- Optimize database tearDown and setUp by introducing fixtures and residentFixtures. Where resident fixtures are loaded once pest test class.
- Database helper methods [see database interface](src/Database/DbItf.php).

## Install

```json
{
    "require": {
        "rzajac/php-test-helper": "0.3.*"
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
CREATE DATABASE testHelper1 DEFAULT CHARACTER SET = 'utf8' DEFAULT COLLATE = 'utf8_general_ci';
CREATE DATABASE testHelper2 DEFAULT CHARACTER SET = 'utf8' DEFAULT COLLATE = 'utf8_general_ci';

CREATE USER 'testUser'@'localhost' IDENTIFIED BY 'testUserPass';
GRANT CREATE ROUTINE, CREATE VIEW, ALTER, SHOW VIEW, CREATE, ALTER ROUTINE, EVENT, INSERT, SELECT, DELETE, TRIGGER, REFERENCES, UPDATE, DROP, EXECUTE, LOCK TABLES, CREATE TEMPORARY TABLES, INDEX ON `testHelper1`.* TO 'testUser'@'localhost';
GRANT CREATE ROUTINE, CREATE VIEW, ALTER, SHOW VIEW, CREATE, ALTER ROUTINE, EVENT, INSERT, SELECT, DELETE, TRIGGER, REFERENCES, UPDATE, DROP, EXECUTE, LOCK TABLES, CREATE TEMPORARY TABLES, INDEX ON `testHelper2`.* TO 'testUser'@'localhost';
FLUSH PRIVILEGES;
```

## License

Apache License Version 2.0
