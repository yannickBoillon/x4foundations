<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Catalogue : races.
 */
final class Version20261101160244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Catalogue : table race';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE race (id UUID NOT NULL, code VARCHAR(64) NOT NULL, name VARCHAR(120) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DA6FBBAF77153098 ON race (code)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE race');
    }
}
