Library In Backyard
===================
Collection of useful functions
-------------------

**backyard 1 usage:**


This array MUST be created by the application before invoking backyard     
```sh
$backyardDatabase = array(
    'dbhost' => 'localhost',
    'dbuser' => 'user',
    'dbpass' => '',
    'dbname' => 'default',
);
```


**backyard 2 usage:**

All backyard functions are named as backyard_camelCase 

The array $backyardDatabase (see above) SHOULD be created ONLY IF there is a table \`system\` (or different name stated in $backyardDatabase['system_table_name']) with fields containing backyard system info.