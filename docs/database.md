# Database Documentation

## Commands
### Create
Create database from DATABASE_URL in .env
```shell
php .\bin\console doctrine:database:create
```

### Migrate
Update database from migration files
```shell
php .\bin\console doctrine:migration:migrate
```
### Create migration

#### First Time
```shell
php .\bin\console doctrine:migrations:generate
```

#### Next Time
```shell
php .\bin\console doctrine:migration:diff
```

_\*/!\\ Warning : if you using mariadb database don't forget to change in .env :_
```
serverVersion=<version>
```
into :
```
serverVersion=mariadb-<major_version>.<minor_version>.<patch_version>
```
ex: mysqli://<user_name>:@<_ip>:<_port>/<database_name>?serverVersion=mariadb-10.10.0&charset=utf8mb4


## Tables

### User

| **key** | **name**    | **type**     | **null** | **default value** |
|:-------:|-------------|--------------|----------|-------------------|
| **PK**  | id          | int          | false    | auto_increment    |
|         | name        | varchar(255) | false    |                   |
|         | email       | varchar(255) | false    |                   |
|         | password    | varchar(255) | false    |                   |
|         | coins       | int          | false    | 0                 |
|         | xp          | int          | false    | 0                 |
|         | roles       | longtext     | false    | ['ROLE_USER']     |
|         | is_verified | tinyint      | false    | 0                 |
|         | locale      | varchar(5)   | false    | en-US             |

### ItemType

| **key** | **name**  | **type**      | **null** | **default value** |
|:-------:|-----------|---------------|----------|-------------------|
| **PK**  | id        | int           | false    | auto_increment    |
|         | name      | varchar(255)  | false    |                   |
|         | desc      | varchar(1024) | true     |                   |
|         | type      | varchar(255)  | false    | other             |
|         | rarity    | varchar(255)  | false    | common            |
|         | fantastic | tinyint       | false    | 0                 |

_\*fantastic replace unique term, because unique is already use by database system._

### UserItem

|         **key**          | **name**     | **type** | **null** | **default value** |
|:------------------------:|--------------|----------|----------|-------------------|
|   [**PK**/_FK_](#user)   | user_id      | int      | false    |                   |
| [**PK**/_FK_](#itemtype) | item_type_id | int      | false    |                   |
|                          | quantity     | int      | false    | 0                 |

### Country

|    **key**    | **name**  | **type**     | **null** | **default value** |
|:-------------:|-----------|--------------|----------|-------------------|
|    **PK**     | id        | int          | false    | auto_increment    |
| [_FK_](#user) | user_id   | int          | false    |                   |
|               | name      | varchar(255) | false    |                   |
|               | flag      | varchar(255) | false    |                   |
|               | continent | varchar(255) | false    |                   |

### CountryItem

|         **key**          | **name**     | **type** | **null** | **default value** |
|:------------------------:|--------------|----------|----------|-------------------|
| [**PK**/_FK_](#country)  | country_id   | int      | false    |                   |
| [**PK**/_FK_](#itemtype) | item_type_id | int      | false    |                   |
|                          | quantity     | int      | false    | 0                 |

### Region

| **key** | **name** | **type**     | **null** | **default value** |
|:-------:|----------|--------------|----------|-------------------|
| **PK**  | id       | int          | false    | auto_increment    |
|         | name     | varchar(255) | false    |                   |
|         | flag     | varchar(255) | true     |                   |

### City

| **key** | **name** | **type**     | **null** | **default value** |
|:-------:|----------|--------------|----------|-------------------|
| **PK**  | id       | int          | false    | auto_increment    |
|         | name     | varchar(255) | false    |                   |
|         | flag     | varchar(255) | true     |                   |

### Game

| **key** | **name** | **type**      | **null** | **default value** |
|:-------:|----------|---------------|----------|-------------------|
| **PK**  | id       | int           | false    | auto_increment    |
|         | title    | varchar(255)  | false    |                   |
|         | desc     | varchar(1024) | true     |                   |
|         | tags     | varchar(1024) | true     |                   |
|         | img      | varchar(255)  | true     |                   |

### Level

|    **key**    | **name**   | **type**      | **null** | **default value** |
|:-------------:|------------|---------------|----------|-------------------|
|    **PK**     | id         | int           | false    | auto_increment    |
| [_FK_](#game) | game_id    | int           | false    |                   |
|               | difficulty | int           | false    | 0                 |
|               | label      | varchar(1024) | true     |                   |
|               | desc       | varchar(1024) | true     |                   |

### Score

|    **key**     | **name**   | **type** | **null** | **default value** |
|:--------------:|------------|----------|----------|-------------------|
|     **PK**     | id         | int      | false    | auto_increment    |
| [_FK_](#level) | level_id   | int      | false    |                   |
| [_FK_](#user)  | user_id    | int      | false    |                   |
|                | time       | int      | false    | 0                 |
|                | created_at | date     | false    |                   |
