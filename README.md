# Core ORM
Simple fork of https://github.com/uestla/YetORM.

## MySQL driver
Disable creating DateTime object from date and datetime MySQL columns (can be slow).

`local.config.neon`
```php
database:
    dsn: 'mysql:host=localhost;dbname=kyblik'
    user: root
    password:
    options:
        driverClass: Core\MySQLDriver
```
