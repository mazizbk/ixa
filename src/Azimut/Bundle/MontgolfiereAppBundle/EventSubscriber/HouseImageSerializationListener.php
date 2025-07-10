<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;


use Azimut\Bundle\MontgolfiereAppBundle\Model\HouseImage;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\Asset\Packages;

class HouseImageSerializationListener implements EventSubscriberInterface
{

    /**
     * @var Packages
     */
    protected $packages;

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'class' => HouseImage::class,
                'method' => 'onPostSerialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        $image = $event->getObject();
        assert($image instanceof HouseImage);
        if(!$image->getPath()) {
            return;
        }

        $event->getVisitor()->setData('path', $this->packages->getUrl($image->getPath()));
    }
}
