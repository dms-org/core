<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\PropertyConverters\ConvertedPropertyEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\PropertyConverters\ConvertedPropertyEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConvertedPropertyTest extends DbIntegrationTest
{
    /**
     * @return IEntityMapper
     */
    protected function loadMapper()
    {
        return new ConvertedPropertyEntityMapper('converted_properties');
    }

    public function testPersist()
    {
        $entity = new ConvertedPropertyEntity(null, 50);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
            'converted_properties' => [
                ['id' => 1, 'varchar' => 'integer:50']
            ]
        ]);
    }

    public function testLoad()
    {
        $this->db->setData([
                'converted_properties' => [
                        ['id' => 1, 'varchar' => 'integer:-100']
                ]
        ]);

        $this->assertEquals(new ConvertedPropertyEntity(1, -100), $this->repo->get(1));
    }
}