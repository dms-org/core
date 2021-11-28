<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Doctrine\Migration;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Event\SchemaColumnDefinitionEventArgs;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Types\Type;

/**
 * The custom column definition event subscriber
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomColumnDefinitionEventSubscriber implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::onSchemaColumnDefinition];
    }

    public function onSchemaColumnDefinition(SchemaColumnDefinitionEventArgs $event)
    {
        $fieldType = $event->getTableColumn()['Type'] ?? $event->getTableColumn()['type'];

        if (stripos($fieldType, 'enum(') !== false) {
            $fieldValues = $this->parseEnumTypeToValues($fieldType);
            CustomEnumTypeGenerator::generate($fieldValues);
        }
    }

    private function parseEnumTypeToValues(string $fieldType) : array
    {
        $values = [];

        $partsInBrackets = substr($fieldType, strpos($fieldType, '(') + 1);
        $partsInBrackets = substr($partsInBrackets, 0,  strrpos($partsInBrackets, ')') - 1);

        foreach (explode(',', $partsInBrackets) as $part) {
            $values[] = trim($part, ' \'\"');
        }

        return $values;
    }
}