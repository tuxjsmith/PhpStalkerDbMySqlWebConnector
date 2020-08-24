# PhpStalkerDbMySqlWebConnector
StalkerDB PHP web MySql Connectors.

show databases;
+--------------------+

| Database           |

+--------------------+

| followingCars      |

| information_schema |

| mysql              |

| performance_schema |

| sys                |

| testDatabase       |

+--------------------+


use followingCars;
show tables;
+-------------------------+
| Tables_in_followingCars |
+-------------------------+
| crimeStoppers           |
| data                    |
| globalNotes             |
| images                  |
+-------------------------+

describe crimeStoppers;
+----------+-------------+------+-----+---------+----------------+
| Field    | Type        | Null | Key | Default | Extra          |
+----------+-------------+------+-----+---------+----------------+
| reg      | varchar(25) | NO   |     | dunno   |                |
| reported | tinyint(1)  | NO   |     | 1       |                |
| id       | int         | NO   | PRI | NULL    | auto_increment |
+----------+-------------+------+-----+---------+----------------+

describe data;
+----------+--------------+------+-----+--------------+----------------+
| Field    | Type         | Null | Key | Default      | Extra          |
+----------+--------------+------+-----+--------------+----------------+
| id       | int          | NO   | PRI | NULL         | auto_increment |
| reg      | varchar(25)  | NO   |     | NULL         |                |
| date     | datetime     | NO   |     | NULL         |                |
| fileName | varchar(255) | YES  |     | none entered |                |
| make     | varchar(255) | YES  |     | none entered |                |
| notes    | text         | NO   |     | NULL         |                |
| location | varchar(255) | YES  |     | none entered |                |
| deleted  | tinyint(1)   | NO   |     | 0            |                |
+----------+--------------+------+-----+--------------+----------------+

describe globalNotes;
+-------+------+------+-----+---------+-------+
| Field | Type | Null | Key | Default | Extra |
+-------+------+------+-----+---------+-------+
| notes | text | NO   |     | NULL    |       |
+-------+------+------+-----+---------+-------+

describe images;
+----------+-------------+------+-----+---------+----------------+
| Field    | Type        | Null | Key | Default | Extra          |
+----------+-------------+------+-----+---------+----------------+
| id       | int         | NO   | PRI | NULL    | auto_increment |
| mimeText | mediumtext  | NO   |     | NULL    |                |
| dataReg  | varchar(25) | NO   |     | NULL    |                |
+----------+-------------+------+-----+---------+----------------+
