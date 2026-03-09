# テーブル定義書

## 1. usersテーブル

| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
|---|---|---|---|---|---|
| id | unsigned bigint | ◯ | | ◯ | |
| name | varchar(255) | | | ◯ | |
| email | varchar(255) | | ◯ | ◯ | |
| email_verified_at | timestamp | | | | |
| password | varchar(255) | | | ◯ | |
| remember_token | varchar(100) | | | | |
| created_at | timestamp | | | | |
| updated_at | timestamp | | | | |

## 2. adminsテーブル

| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
|---|---|---|---|---|---|
| id | unsigned bigint | ◯ | | ◯ | |
| name | varchar(255) | | | ◯ | |
| email | varchar(255) | | ◯ | ◯ | |
| password | varchar(255) | | | ◯ | |
| created_at | timestamp | | | | |
| updated_at | timestamp | | | | |

## 3. attendancesテーブル

| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY | 備考 |
|---|---|---|---|---|---|---|
| id | unsigned bigint | ◯ | | ◯ | | |
| user_id | unsigned bigint | | | ◯ | users(id) | |
| date | date | | | ◯ | | |
| clock_in | timestamp | | | | | |
| clock_out | timestamp | | | | | |
| status | tinyint | | | ◯ | | 0:勤務外 / 1:出勤中 / 2:休憩中 / 3:退勤済 |
| created_at | timestamp | | | | | |
| updated_at | timestamp | | | | | |

## 4. restsテーブル

| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
|---|---|---|---|---|---|
| id | unsigned bigint | ◯ | | ◯ | |
| attendance_id | unsigned bigint | | | ◯ | attendances(id) |
| rest_start | timestamp | | | | |
| rest_end | timestamp | | | | |
| created_at | timestamp | | | | |
| updated_at | timestamp | | | | |

## 5. stamp_correction_requestsテーブル

| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY | 備考 |
|---|---|---|---|---|---|---|
| id | unsigned bigint | ◯ | | ◯ | | |
| user_id | unsigned bigint | | | ◯ | users(id) | |
| attendance_id | unsigned bigint | | | ◯ | attendances(id) | |
| request_clock_in | timestamp | | | | | |
| request_clock_out | timestamp | | | | | |
| remark | varchar(255) | | | ◯ | | |
| status | tinyint | | | ◯ | | 0:承認待ち / 1:承認済み |
| approved_at | timestamp | | | | | |
| created_at | timestamp | | | | | |
| updated_at | timestamp | | | | | |

## 6. stamp_correction_request_restsテーブル

| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
|---|---|---|---|---|---|
| id | unsigned bigint | ◯ | | ◯ | |
| stamp_correction_request_id | unsigned bigint | | | ◯ | stamp_correction_requests(id) |
| rest_id | unsigned bigint | | | | rests(id) |
| request_rest_start | timestamp | | | | |
| request_rest_end | timestamp | | | | |
| created_at | timestamp | | | | |
| updated_at | timestamp | | | | |