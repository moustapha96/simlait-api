<?php

namespace App\Doctrine;

use Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs;

class DoctrineListener
{
    public function postGenerateSchema(GenerateSchemaTableEventArgs $eventArgs)
    {
        $schema = $eventArgs->getSchema();

        foreach ($schema->getTables() as $table) {
            // Si la table n'a pas encore de colonne `createdAt`
            if (!$table->hasColumn('createdAt')) {
                $table->addColumn('createdAt', 'datetime', ['notnull' => false]);
            }
        }
    }
}
