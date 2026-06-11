CREATE DATABASE IF NOT EXISTS mineadmin_education
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS mineadmin_education_test
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'education'@'%' IDENTIFIED BY 'education_secret';

GRANT ALL PRIVILEGES ON mineadmin_education.* TO 'education'@'%';
GRANT ALL PRIVILEGES ON mineadmin_education_test.* TO 'education'@'%';

FLUSH PRIVILEGES;
