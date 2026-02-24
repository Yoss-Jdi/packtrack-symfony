<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add publication_reactions table for forum likes/dislikes.
 * Tables publications and commentaires should already exist (see PackTrackDB.sql).
 */
final class Version20260211120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add publication_reactions table for forum likes/dislikes';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on "mysql".'
        );

        $this->addSql(<<<'SQL'
CREATE TABLE publication_reactions (
    ID_Reaction INT AUTO_INCREMENT NOT NULL,
    reaction SMALLINT NOT NULL,
    dateCreation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ID_Publication INT NOT NULL,
    ID_Auteur INT NOT NULL,
    UNIQUE INDEX uniq_publication_auteur (ID_Publication, ID_Auteur),
    INDEX IDX_reaction_publication (ID_Publication),
    INDEX IDX_reaction_auteur (ID_Auteur),
    PRIMARY KEY(ID_Reaction)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB
SQL);

        $this->addSql('ALTER TABLE publication_reactions ADD CONSTRAINT FK_reaction_publication FOREIGN KEY (ID_Publication) REFERENCES publications (ID_Publication) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publication_reactions ADD CONSTRAINT FK_reaction_auteur FOREIGN KEY (ID_Auteur) REFERENCES utilisateurs (ID_Utilisateur) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on "mysql".'
        );

        $this->addSql('DROP TABLE publication_reactions');
    }
}
