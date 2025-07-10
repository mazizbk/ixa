<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 15:52:59
 */

namespace Azimut\Bundle\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Timestampable\Traits\TimestampableEntity;

use Azimut\Bundle\DoctrineExtraBundle\Entity\EntityTranslationInterface;

/**
 * @ORM\Table(name="shop_order_item_translation")
 * @ORM\Entity()
 */
class OrderItemTranslation implements EntityTranslationInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var OrderItem
     * @ORM\ManyToOne(targetEntity="OrderItem", inversedBy="translations")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $orderItem;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get translatable
     *
     * @return OrderItem
     */
    public function getTranslatable()
    {
        return $this->orderItem;
    }

    /**
     * Set translatable
     * @param OrderItem $translatable
     */
    public function setTranslatable($translatable)
    {
        if (!$translatable instanceof OrderItem) {
            throw new \RuntimeException('Expected $translatable to be an instance of OrderItem');
        }

        $this->orderItem = $translatable;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale
     * @param string $locale
     *
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
