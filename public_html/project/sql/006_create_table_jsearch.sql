CREATE TABLE IF NOT EXISTS `IT202_F25_Jsearch` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,

    `job_id` VARCHAR(128) NOT NULL UNIQUE,
    `country` VARCHAR(100),

    `job_title` VARCHAR(255),
    `employer_name` VARCHAR(255),
    `job_publisher` VARCHAR(255),
    `job_employment_type` VARCHAR(100),
    `job_apply_link` TEXT,
    `job_location` VARCHAR(255),
    `job_city` VARCHAR(128),
    `job_state` VARCHAR(128),

    `job_description` TEXT,
    `job_is_remote` BOOLEAN,
    `job_posted_at_datetime_utc` DATETIME,
    
    `is_api` tinyint(1) DEFAULT '1',

    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)