<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-18 09:08:15
 */

namespace Azimut\Bundle\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\VirtualProperty;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;

use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseProductItem implements TranslatableEntityInterface
{
    use TimestampableEntity, BlameableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"list_product_items", "detail_product_item"})
     */
    private $id;

    /**
     * @var ArrayCollection
     */
    protected $translations;

    /**
     * @var MoneyToLocalizedStringTransformer
     */
    private $priceTransformer;

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="integer", nullable=true)
     * @Accessor(getter="getPrice")
     * @Groups({"list_product_items", "detail_product_item"})
     */
    protected $price;

    /**
     * @var int
     * Vate rate in per mille (‰)
     *
     * @ORM\Column(name="vat_rate", type="integer", nullable=true)
     * @Groups({"detail_product_item"})
     */
    protected $vatRate;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

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
     * Get translations
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get name
     *
     * @return string
     * @VirtualProperty()
     * @Groups({"list_product_items", "detail_product_item"})
     */
    public function getName($locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);
        return $proxy->getName();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name, $locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setName($name);
        return $this;
    }

    /**
     * Get price
     *
     * @return int|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Get decimal price
     *
     * @return int|null
     * * @VirtualProperty()
     * @Groups({"detail_product_item"})
     */
    public function getDecimalPrice()
    {
        if (null === $this->priceTransformer) {
            $this->priceTransformer = new MoneyToLocalizedStringTransformer(2, true, MoneyToLocalizedStringTransformer::ROUND_HALF_UP, 100);
        }
        return $this->priceTransformer->transform($this->getPrice());
    }

    /**
     * Set price
     *
     * @param int|null $price
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get class
     *
     * @return string Class of the current object
     */
    public function getClass()
    {
        return get_class($this);
    }

    /**
     * Get vat rate
     *
     * @return int|null
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }

    /**
     * Set vat rate
     *
     * @param int|null $vatRate
     *
     * @return self
     */
    public function setVatRate($vatRate)
    {
        $this->vat = $vatRate;
        return $this;
    }

    /**
     * Return the pre tax price (en français prix hors taxes)
     * @param  int $defaultVatRate Default VAT rate
     * @return int
     */
    public function getPreTaxPrice($defaultVatRate)
    {
        $vatRate = $defaultVatRate * 10;
        if (null != $this->vatRate) {
            $vatRate = $this->vatRate;
        }
        return (int) round($this->price / (1 + $vatRate/1000));
    }
}
