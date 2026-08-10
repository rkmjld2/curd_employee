## `db.php`

```php
<?php

/*
    TiDB Cloud Database Connection

    Values are obtained from Render Environment Variables.
*/

$DB_HOST = getenv("DB_HOST");
$DB_USER = getenv("DB_USER");
$DB_PASSWORD = getenv("DB_PASSWORD");
$DB_NAME = getenv("DB_NAME");
$DB_PORT = getenv("DB_PORT");

/*
    Create MySQLi connection
*/

$conn = mysqli_init();

/*
    Enable SSL for TiDB Cloud
*/

mysqli_ssl_set(
    $conn,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL
);

/*
    Connect to TiDB Cloud
*/

mysqli_real_connect(
    $conn,
    $DB_HOST,
    $DB_USER,
    $DB_PASSWORD,
    $DB_NAME,
    $DB_PORT,
    NULL,
    MYSQLI_CLIENT_SSL
);


/*
    Check connection
*/

if (mysqli_connect_errno()) {

    die(
        "Database connection failed: "
        . mysqli_connect_error()
    );
}


/*
    Character set
*/

mysqli_set_charset($conn, "utf8mb4");

?>
```
