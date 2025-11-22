CREATE TABLE IF NOT EXISTS `IT202_F25_Jsearch_Responsibilities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` VARCHAR(128) NOT NULL,
    `responsibility_text` TEXT NOT NULL,

    FOREIGN KEY (`job_id`) REFERENCES `IT202_F25_Jsearch`(`job_id`)
        ON DELETE CASCADE
);
