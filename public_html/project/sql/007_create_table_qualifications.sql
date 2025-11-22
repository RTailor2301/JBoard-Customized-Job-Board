CREATE TABLE IF Not EXISTS `IT202_F25_Jsearch_Qualifications` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `job_id`       VARCHAR(128) NOT NULL,
    `qualification_text` TEXT NOT NULL,

    FOREIGN KEY (`job_id`) REFERENCES `IT202_F25_Jsearch`(`job_id`)
        ON DELETE CASCADE
)