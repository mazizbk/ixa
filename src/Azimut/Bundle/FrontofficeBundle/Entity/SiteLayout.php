<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-03-05 10:26:05
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_site_layout")
 */
class SiteLayout
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"list_site_layouts", "detail_site_layout", "detail_site"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128)
     * @Groups({"list_site_layouts", "detail_site_layout", "detail_site"})
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128)
     * @Groups({"detail_site_layout"})
     * @Assert\NotBlank()
     */
    private $template;

    /**
     * @var MenuDefinition[]|ArrayCollection<MenuDefinition>
     *
     * @ORM\OneToMany(targetEntity="MenuDefinition", cascade={"persist", "remove"}, mappedBy="layout", orphanRemoval=true)
     * @Groups({"detail_site_layout"})
     */
    private $menuDefinitions;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $exceptionTemplatesDir;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $searchResultTemplate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $loginTemplate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $lostPasswordTemplate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $passwordResetTemplate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $passwordChangeTemplate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $registerTemplate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $editProfileTemplate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $postLoginTemplate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $confirmEmailTemplate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $uniquePasswordTemplate;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_site_layout"})
     */
    private $hasUserLogin = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_site_layout"})
     */
    private $isNewUserActive = false;

    /**
     * @var string
     *
     * @ORM\Column(name="new_user_role", type="string", length=50, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $newUserRole;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $basketTemplate;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"detail_site_layout"})
     */
    private $hasShop = false;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $shopLoginTemplate;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $shopRegisterTemplate;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $shopOrderAddressesTemplate;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $shopDeliveryTemplate;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $shopSummaryTemplate;

    /**
     * Template for payments form in standalone page
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $shopPaymentTemplate;

    /**
     * Template for embedding payments form
     * (in summary page for instance)
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $shopPaymentEmbedTemplate;

    /**
     * Template for user orders (in user account section)
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $shopUserOrdersTemplate;

    /**
     * Template for user order detail (in user account section)
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_site_layout"})
     */
    private $shopUserOrderShowTemplate;

    public function __construct()
    {
        $this->menuDefinitions = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    public function getMenuDefinition($placeholder)
    {
        foreach ($this->menuDefinitions as $menuDefinition) {
            if ($menuDefinition->getPlaceHolder() == $placeholder) {
                return $menuDefinition;
            }
        }

        throw new \InvalidArgumentException(sprintf('No menu with placeholder "%s" in layout "%s".', $placeholder, $this->name));
    }

    public function createMenuDefinition($name, $options = null)
    {
        foreach ($this->menuDefinitions as $menuDefinition) {
            if ($menuDefinition->getName() == $name) {
                return $menuDefinition;
            }
        }

        $menuDefinition = new MenuDefinition($name, $options);
        $menuDefinition->setLayout($this);

        $this->menuDefinitions->add($menuDefinition);

        return $menuDefinition;
    }

    public function getMenuDefinitions()
    {
        return $this->menuDefinitions;
    }

    public function addMenuDefinition(MenuDefinition $menuDefinition)
    {
        if (!$this->menuDefinitions->contains($menuDefinition)) {
            $this->menuDefinitions->add($menuDefinition);
            $menuDefinition->setLayout($this);
        }

        return $this;
    }

    public function removeMenuDefinition($menuDefinition)
    {
        if ($this->menuDefinitions->contains($menuDefinition)) {
            $this->menuDefinitions->removeElement($menuDefinition);
        }

        return $this;
    }

    public function getExceptionTemplatesDir()
    {
        return $this->exceptionTemplatesDir;
    }

    public function setExceptionTemplatesDir($exceptionTemplatesDir)
    {
        $this->exceptionTemplatesDir = $exceptionTemplatesDir;
        return $this;
    }

    public function getSearchResultTemplate()
    {
        return $this->searchResultTemplate;
    }

    public function setSearchResultTemplate($searchResultTemplate)
    {
        $this->searchResultTemplate = $searchResultTemplate;
        return $this;
    }

    public function getLoginTemplate()
    {
        return $this->loginTemplate;
    }

    public function setLoginTemplate($loginTemplate)
    {
        $this->loginTemplate = $loginTemplate;
        return $this;
    }

    public function getLostPasswordTemplate()
    {
        return $this->lostPasswordTemplate;
    }

    public function setLostPasswordTemplate($lostPasswordTemplate)
    {
        $this->lostPasswordTemplate = $lostPasswordTemplate;
        return $this;
    }

    public function getPasswordResetTemplate()
    {
        return $this->passwordResetTemplate;
    }

    public function setPasswordResetTemplate($passwordResetTemplate)
    {
        $this->passwordResetTemplate = $passwordResetTemplate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordChangeTemplate()
    {
        return $this->passwordChangeTemplate;
    }

    /**
     * @param string $passwordChangeTemplate
     */
    public function setPasswordChangeTemplate($passwordChangeTemplate)
    {
        $this->passwordChangeTemplate = $passwordChangeTemplate;
        return $this;
    }

    public function getRegisterTemplate()
    {
        return $this->registerTemplate;
    }

    public function setRegisterTemplate($registerTemplate)
    {
        $this->registerTemplate = $registerTemplate;
        return $this;
    }

    public function getEditProfileTemplate()
    {
        return $this->editProfileTemplate;
    }

    public function setEditProfileTemplate($editProfileTemplate)
    {
        $this->editProfileTemplate = $editProfileTemplate;
        return $this;
    }

    /**
     * Get postLoginTemplate
     *
     * @return string|null
     */
    public function getPostLoginTemplate()
    {
        return $this->postLoginTemplate;
    }

    /**
     * Set postLoginTemplate
     *
     * @param string|null $postLoginTemplate
     *
     * @return self
     */
    public function setPostLoginTemplate($postLoginTemplate)
    {
        $this->postLoginTemplate = $postLoginTemplate;
        return $this;
    }

    public function getConfirmEmailTemplate()
    {
        return $this->confirmEmailTemplate;
    }

    public function setConfirmEmailTemplate($confirmEmailTemplate)
    {
        $this->confirmEmailTemplate = $confirmEmailTemplate;
        return $this;
    }

    public function hasUserLogin($hasUserLogin = null)
    {
        if (null !== $hasUserLogin) {
            $this->hasUserLogin = $hasUserLogin;
            return $this;
        }

        return $this->hasUserLogin;
    }

    public function isNewUserActive($isNewUserActive = null)
    {
        if (null !== $isNewUserActive) {
            $this->isNewUserActive = $isNewUserActive;
            return $this;
        }

        return $this->isNewUserActive;
    }

    /**
     * Get newUserRole
     *
     * @return string|null
     */
    public function getNewUserRole()
    {
        return $this->newUserRole;
    }

    /**
     * Set newUserRole
     *
     * @param string|null $newUserRole
     *
     * @return self
     */
    public function setNewUserRole($newUserRole)
    {
        $this->newUserRole = $newUserRole;
        return $this;
    }

    /**
     * @return string
     */
    public function getUniquePasswordTemplate()
    {
        return $this->uniquePasswordTemplate;
    }

    /**
     * @param string $uniquePasswordTemplate
     * @return self
     */
    public function setUniquePasswordTemplate($uniquePasswordTemplate)
    {
        $this->uniquePasswordTemplate = $uniquePasswordTemplate;

        return $this;
    }

    /**
     * Get basketTemplate
     *
     * @return string|null
     */
    public function getBasketTemplate()
    {
        return $this->basketTemplate;
    }

    /**
     * Set basketTemplate
     *
     * @param string|null $basketTemplate
     *
     * @return self
     */
    public function setBasketTemplate($basketTemplate)
    {
        $this->basketTemplate = $basketTemplate;
        return $this;
    }

    /**
     * Get or set hasShop
     * @param  boolean|null  $hasShop
     * @return self|boolean
     */
    public function hasShop($hasShop = null)
    {
        if (null !== $hasShop) {
            $this->hasShop = $hasShop;
            return $this;
        }

        return $this->hasShop;
    }

    /**
     * Get shopLoginTemplate
     *
     * @return string|null
     */
    public function getShopLoginTemplate()
    {
        return $this->shopLoginTemplate;
    }

    /**
     * Set shopLoginTemplate
     *
     * @param string|null $shopLoginTemplate
     *
     * @return self
     */
    public function setShopLoginTemplate($shopLoginTemplate)
    {
        $this->shopLoginTemplate = $shopLoginTemplate;
        return $this;
    }

    /**
     * Get shopRegisterTemplate
     *
     * @return string|null
     */
    public function getShopRegisterTemplate()
    {
        return $this->shopRegisterTemplate;
    }

    /**
     * Set shopRegisterTemplate
     *
     * @param string|null $shopRegisterTemplate
     *
     * @return self
     */
    public function setShopRegisterTemplate($shopRegisterTemplate)
    {
        $this->shopRegisterTemplate = $shopRegisterTemplate;
        return $this;
    }

    /**
     * Get shopOrderAddressesTemplate
     *
     * @return string|null
     */
    public function getShopOrderAddressesTemplate()
    {
        return $this->shopOrderAddressesTemplate;
    }

    /**
     * Set shopOrderAddressesTemplate
     *
     * @param string|null $shopOrderAddressesTemplate
     *
     * @return self
     */
    public function setShopOrderAddressesTemplate($shopOrderAddressesTemplate)
    {
        $this->shopOrderAddressesTemplate = $shopOrderAddressesTemplate;
        return $this;
    }

    /**
     * Get shopDeliveryTemplate
     *
     * @return string|null
     */
    public function getShopDeliveryTemplate()
    {
        return $this->shopDeliveryTemplate;
    }

    /**
     * Set shopDeliveryTemplate
     *
     * @param string|null $shopDeliveryTemplate
     *
     * @return self
     */
    public function setShopDeliveryTemplate($shopDeliveryTemplate)
    {
        $this->shopDeliveryTemplate = $shopDeliveryTemplate;
        return $this;
    }

    /**
     * Get shopSummaryTemplate
     *
     * @return string|null
     */
    public function getShopSummaryTemplate()
    {
        return $this->shopSummaryTemplate;
    }

    /**
     * Set shopSummaryTemplate
     *
     * @param string|null $shopSummaryTemplate
     *
     * @return self
     */
    public function setShopSummaryTemplate($shopSummaryTemplate)
    {
        $this->shopSummaryTemplate = $shopSummaryTemplate;
        return $this;
    }

    /**
     * Get shopPaymentTemplate
     *
     * @return string|null
     */
    public function getShopPaymentTemplate()
    {
        return $this->shopPaymentTemplate;
    }

    /**
     * Set shopPaymentTemplate
     *
     * @param string|null $shopPaymentTemplate
     *
     * @return self
     */
    public function setShopPaymentTemplate($shopPaymentTemplate)
    {
        $this->shopPaymentTemplate = $shopPaymentTemplate;
        return $this;
    }

    /**
     * Get shopPaymentEmbedTemplate
     *
     * @return string|null
     */
    public function getShopPaymentEmbedTemplate()
    {
        return $this->shopPaymentEmbedTemplate;
    }

    /**
     * Set shopPaymentEmbedTemplate
     *
     * @param string|null $shopPaymentEmbedTemplate
     *
     * @return self
     */
    public function setShopPaymentEmbedTemplate($shopPaymentEmbedTemplate)
    {
        $this->shopPaymentEmbedTemplate = $shopPaymentEmbedTemplate;
        return $this;
    }

    /**
     * Get shopUserOrdersTemplate
     *
     * @return string|null
     */
    public function getShopUserOrdersTemplate()
    {
        return $this->shopUserOrdersTemplate;
    }

    /**
     * Set shopUserOrdersTemplate
     *
     * @param string|null $shopUserOrdersTemplate
     *
     * @return self
     */
    public function setShopUserOrdersTemplate($shopUserOrdersTemplate)
    {
        $this->shopUserOrdersTemplate = $shopUserOrdersTemplate;
        return $this;
    }

    /**
     * Get shopUserOrderShowTemplate
     *
     * @return string|null
     */
    public function getShopUserOrderShowTemplate()
    {
        return $this->shopUserOrderShowTemplate;
    }

    /**
     * Set shopUserOrderShowTemplate
     *
     * @param string|null $shopUserOrderShowTemplate
     *
     * @return self
     */
    public function setShopUserOrderShowTemplate($shopUserOrderShowTemplate)
    {
        $this->shopUserOrderShowTemplate = $shopUserOrderShowTemplate;
        return $this;
    }
}
