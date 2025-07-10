<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-19 11:39:41
 */

namespace Azimut\Bundle\ShopBundle\Twig;

use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;
use Azimut\Bundle\ShopBundle\Service\BasketService;

class ShopTwigExtension extends \Twig_Extension
{
    /**
     * @var MoneyToLocalizedStringTransformer
     */
    protected $transformer;

    /**
     * @var BasketService;
     */
    protected $basketService;

    /**
     * @var int
     */
    protected $defaultVatRate;

    public function __construct(BasketService $basketService, $defaultVatRate)
    {
        $this->transformer = new MoneyToLocalizedStringTransformer(2, true, MoneyToLocalizedStringTransformer::ROUND_HALF_UP, 100);
        $this->basketService = $basketService;
        $this->defaultVatRate = $defaultVatRate;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'azimut_shop';
    }

    /**
     * Get filters
     *
     * @return array
     */
    public function getFilters()
    {
        // Warning : this filter behaviour is reproduced in basket.js (for post Ajax API recalculation)
        return [
            new \Twig_SimpleFilter('az_shop_price', [$this, 'price']),
        ];
    }

    /**
     * Get functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('az_shop_basket', [$this, 'getBasket']),
        ];
    }

    /**
     * Format currency
     *
     * @param int price
     * @return string
     */
    public function price($price)
    {
        if (null === $price) {
            return null;
        }
        return $this->transformer->transform($price) . ' €'; // @TODO use localized money format
    }

    /**
     * Get basket
     *
     * @return Azimut\Bundle\ShopBundle\Entity\Order
     */
    public function getBasket()
    {
        return $this->basketService->getBasket();
    }
}
