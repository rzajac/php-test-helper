## PHP Test helper classes

Collection of classes to help with unit testing

## Setup test database

```sql
CREATE USER 'unitTest'@'localhost' IDENTIFIED BY 'unitTestPass';
GRANT CREATE ROUTINE, CREATE VIEW, ALTER, SHOW VIEW, CREATE, ALTER ROUTINE, EVENT, INSERT, SELECT, DELETE, TRIGGER, REFERENCES, UPDATE, DROP, EXECUTE, LOCK TABLES, CREATE TEMPORARY TABLES, INDEX ON `test`.* TO 'unitTest'@'localhost';
FLUSH PRIVILEGES;
```

## License

Apache License Version 2.0
