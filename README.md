## PHP Test helper classes

Unit testing helper classes.

## What does it help with?

- Load database fixtures (`sql`).
- Load file fixtures (`json`, `txt`, `php`).
- Tests where you interact with database.
- Database helper methods [see database interface](src/Database/DbItf.php).

## Supported databases

- MySQL

## Install

```json
{
    "require": {
        "rzajac/php-test-helper": "0.5.*"
    }
}
```

## Run unit tests

Yes! The package has it's own unit tests.

```
$ vendor/bin/phpunit --coverage-html=./coverage 
```

## Setup database for unit tests

### MySQL

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
