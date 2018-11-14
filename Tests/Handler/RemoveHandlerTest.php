<?php

namespace Vich\UploaderBundle\Tests\Handler;

use Vich\TestBundle\Entity\Article;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Handler\RemoveHandler;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Kim Wuestkamp <kim@wuestkamp.com>
 */
class RemoveHandlerTest extends TestCase
{
    protected $factory;

    protected $storage;

    protected $dispatcher;

    protected $mapping;

    /**
     * @var Article
     */
    protected $object;

    /**
     * @var RemoveHandler
     */
    protected $handler;

    const FILE_FIELD_ONE = 'image';
    const FILE_FIELD_TWO = 'attachment';

    protected function setUp(): void
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->storage = $this->getStorageMock();
        $this->dispatcher = $this->getDispatcherMock();
        $this->mapping = $this->getPropertyMappingMock();
        $this->object = new Article();

        $this->handler = new RemoveHandler($this->factory, $this->storage, $this->dispatcher);
        $this->factory
            ->expects($this->any())
            ->method('fromField')
            ->with($this->object, self::FILE_FIELD_ONE)
            ->will($this->returnValue($this->mapping));
        /*$this->factory
            ->expects($this->any())
            ->method('fromField')
            ->with($this->object, self::FILE_FIELD_TWO)
            ->will($this->returnValue($this->mapping));*/
    }

    public function testAddToRemoveQueue(): void
    {
        $this->expectEvents([Events::PRE_ADD_REMOVE_QUEUE, Events::POST_ADD_REMOVE_QUEUE]);
        $this->handler->addToQueue($this->object, self::FILE_FIELD_ONE);
    }

    protected function getStorageMock()
    {
        return $this->createMock('Vich\UploaderBundle\Storage\StorageInterface');
    }

    protected function getInjectorMock()
    {
        return $this->createMock('Vich\UploaderBundle\Injector\FileInjectorInterface');
    }

    protected function getDispatcherMock()
    {
        return $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    protected function validEvent()
    {
        $object = $this->object;
        $mapping = $this->mapping;

        return $this->callback(function ($event) use ($object, $mapping) {
            return $event instanceof Event && $event->getObject() === $object && $event->getMapping() === $mapping;
        });
    }

    protected function expectEvents(array $events): void
    {
        foreach ($events as $i => $event) {
            $this->dispatcher
                ->expects($this->at($i))
                ->method('dispatch')
                ->with($event, $this->validEvent());
        }
    }
}
