<?php

namespace UJM\ExoBundle\Installation\Migrations\pdo_mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated migration based on mapping information: modify it with caution.
 *
 * Generation date: 2021/03/03 08:56:15
 */
class Version20210303085613 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE ujm_code_folder (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                readOnly TINYINT(1) NOT NULL, 
                INDEX IDX_4371ADAC1E27F6BF (question_id), 
                INDEX IDX_4371ADAC727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE ujm_code_file (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                parent_id INT DEFAULT NULL, 
                name VARCHAR(255) NOT NULL, 
                readOnly TINYINT(1) NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                content MEDIUMTEXT NOT NULL, 
                score DOUBLE PRECISION DEFAULT NULL, 
                feedback LONGTEXT DEFAULT NULL, 
                INDEX IDX_E5E843F91E27F6BF (question_id), 
                INDEX IDX_E5E843F9727ACA70 (parent_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE ujm_code_question (
                id INT AUTO_INCREMENT NOT NULL, 
                question_id INT DEFAULT NULL, 
                treeIsEditable TINYINT(1) NOT NULL, 
                placeholderTree_id INT DEFAULT NULL, 
                solutionTree_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_1BAADB8AA5B53C6B (placeholderTree_id), 
                UNIQUE INDEX UNIQ_1BAADB8A42F36DDB (solutionTree_id), 
                UNIQUE INDEX UNIQ_1BAADB8A1E27F6BF (question_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE ujm_code_folder 
            ADD CONSTRAINT FK_4371ADAC1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_code_question (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_code_folder 
            ADD CONSTRAINT FK_4371ADAC727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES ujm_code_folder (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_code_file 
            ADD CONSTRAINT FK_E5E843F91E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_code_question (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_code_file 
            ADD CONSTRAINT FK_E5E843F9727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES ujm_code_folder (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_code_question 
            ADD CONSTRAINT FK_1BAADB8AA5B53C6B FOREIGN KEY (placeholderTree_id) 
            REFERENCES ujm_code_folder (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_code_question 
            ADD CONSTRAINT FK_1BAADB8A42F36DDB FOREIGN KEY (solutionTree_id) 
            REFERENCES ujm_code_folder (id)
        ");
        $this->addSql("
            ALTER TABLE ujm_code_question 
            ADD CONSTRAINT FK_1BAADB8A1E27F6BF FOREIGN KEY (question_id) 
            REFERENCES ujm_question (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE ujm_code_folder 
            DROP FOREIGN KEY FK_4371ADAC727ACA70
        ");
        $this->addSql("
            ALTER TABLE ujm_code_file 
            DROP FOREIGN KEY FK_E5E843F9727ACA70
        ");
        $this->addSql("
            ALTER TABLE ujm_code_question 
            DROP FOREIGN KEY FK_1BAADB8AA5B53C6B
        ");
        $this->addSql("
            ALTER TABLE ujm_code_question 
            DROP FOREIGN KEY FK_1BAADB8A42F36DDB
        ");
        $this->addSql("
            ALTER TABLE ujm_code_folder 
            DROP FOREIGN KEY FK_4371ADAC1E27F6BF
        ");
        $this->addSql("
            ALTER TABLE ujm_code_file 
            DROP FOREIGN KEY FK_E5E843F91E27F6BF
        ");
        $this->addSql("
            DROP TABLE ujm_code_folder
        ");
        $this->addSql("
            DROP TABLE ujm_code_file
        ");
        $this->addSql("
            DROP TABLE ujm_code_question
        ");
    }
}
