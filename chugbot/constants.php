<?php
    define("DEBUG", false);

    define("MAX_SIZE_NUM", 10000);
    define("MIN_SIZE_NUM", -1);
    define("DEFAULT_PREF_COUNT", 6);

// Important: use 127.0.0.1:8889 as the host when running PHPUnit tests.  For regular use,
// use localhost.
//    define("MYSQL_HOST", "127.0.0.1:8889");
    define("MYSQL_HOST", "localhost");
    define("MYSQL_USER", "camprama_chugbot");
    define("MYSQL_PASSWD", "camprama_chugbot");             // This should be changed in production use.
    define("MYSQL_DB", "camprama_chugbot_db");
    define("MYSQL_PATH", "/Applications/MAMP/Library/bin"); // This should be changed in production use.
    
    define("ADMIN_EMAIL_USERNAME", "chug@campramahne.org");
    define("ADMIN_EMAIL_PASSWORD", "chug@campramahne.org"); // This should be changed in production use
?>
