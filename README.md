## PHP Test helper classes

Unit testing helper.

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
        "rzajac/php-test-helper": "^0.7"
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

Create test users and database tables:

```sql
CREATE DATABASE testHelper1 DEFAULT CHARACTER SET = 'utf8' DEFAULT COLLATE = 'utf8_general_ci';
CREATE DATABASE testHelper2 DEFAULT CHARACTER SET = 'utf8' DEFAULT COLLATE = 'utf8_general_ci';

CREATE USER 'testUser'@'localhost' IDENTIFIED BY 'testUserPass';
CREATE USER 'testUser'@'%' IDENTIFIED BY 'testUserPass';
GRANT ALL ON `testHelper1`.* TO 'testUser'@'localhost';
GRANT ALL ON `testHelper2`.* TO 'testUser'@'%';
FLUSH PRIVILEGES;
```

Run tests:

```
$ vendor/bin/phpunit
```

When you have XDebug enabled running unit tests creates coverage report in `coverage` directory.

## License

Apache License Version 2.0
