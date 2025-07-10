<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-31 16:32:19
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_page_layout")
 */
class PageLayout
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"list_page_layouts", "detail_page_layout", "detail_page"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128)
     * @Groups({"list_page_layouts", "detail_page_layout", "detail_page"})
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128)
     * @Groups({"detail_page_layout", "detail_page"})
     * @Assert\NotBlank()
     */
    private $template;

    /**
     * @var ZoneDefinition[]|ArrayCollection<ZoneDefinition>
     *
     * @ORM\OneToMany(targetEntity="ZoneDefinition", cascade={"persist", "remove"}, mappedBy="layout", orphanRemoval=true)
     * @Groups({"detail_page_layout"})
     */
    private $zoneDefinitions;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"detail_page_layout"})
     */
    private $standaloneRouterController;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_page_layout"})
     */
    private $standaloneRouterHasStandaloneCmsfilesRoutes = false;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     * @Groups({"detail_page_layout"})
     */
    private $templateOptions;

    public function __construct()
    {
        $this->zoneDefinitions = new ArrayCollection();
        $this->templateOptions = [];
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

    public function getZoneDefinitions()
    {
        return $this->zoneDefinitions;
    }

    public function addZoneDefinition(ZoneDefinition $zoneDefinition)
    {
        if (!$this->zoneDefinitions->contains($zoneDefinition)) {
            $this->zoneDefinitions->add($zoneDefinition);
            $zoneDefinition->setLayout($this);
        }

        return $this;
    }

    public function removeZoneDefinition($zoneDefinition)
    {
        if ($this->zoneDefinitions->contains($zoneDefinition)) {
            $this->zoneDefinitions->removeElement($zoneDefinition);
        }

        return $this;
    }

    public function getZoneDefinition($name)
    {
        foreach ($this->zoneDefinitions as $zoneDefinition) {
            if ($zoneDefinition->getName() == $name) {
                return $zoneDefinition;
            }
        }

        throw new \InvalidArgumentException(sprintf('No zone named "%s" in layout "%s".', $name, $this->name));
    }


    public function createZoneDefinition($name, $options = null, $class = ZoneDefinitionCmsFiles::class)
    {
        foreach ($this->zoneDefinitions as $zoneDefinition) {
            if ($zoneDefinition->getName() == $name) {
                return $zoneDefinition;
            }
        }
        $zoneDefinition = new $class($name, $options);
        $this->addZoneDefinition($zoneDefinition);

        return $zoneDefinition;
    }

    /**
     * Create a single zone containing a single CmsFile (type 'page')
     * Enable direct content edition from the frontoffice app
     * @return ZoneDefinition|mixed
     * @throws \Exception When page already has zones
     */
    public function createFullPageCmsFile()
    {
        if ($this->zoneDefinitions->count() > 0) {
            throw new \Exception("Cannot create full page CmsFile, this page layout already has several zones", 1);
        }

        return $this->createZoneDefinition('content', [
            'accepted_attachment_classes' => [ CmsFileRichText::class ],
            'max_attachments_count' => 1,
            'allow_delete' => false
        ]);
    }

    /*
     * Create a single zone containing a single CmsFile (type 'page')
     * Enable direct content edition from the frontoffice app
     */
    public function createFullZoneCmsFile($name, $pageType)
    {
        return $this->createZoneDefinition($name, [
            'accepted_attachment_classes' => [ $pageType ],
            'max_attachments_count' => 1,
            'allow_delete' => false
        ]);
    }

    /*
     * Create a zone containing a custom form
     */
    public function createZoneDefinitionForm($name, $controller)
    {
        return $this->createZoneDefinition($name, [
            'controller' => $controller,
        ], ZoneDefinitionForm::class);
    }

    /*
     * Create a zone containing a cms file form
     */
    public function createZoneDefinitionCmsFileBufferForm($name, $cmsFileBufferClass)
    {
        return $this->createZoneDefinition($name, [
            'cms_file_buffer_class' => $cmsFileBufferClass,
        ], ZoneDefinitionCmsFileBufferForm::class);
    }



    public function setOptions($options)
    {
        if (isset($options['standalone_router_standalone_cmsfiles_routes'])) {
            $this->standaloneRouterHasStandaloneCmsfilesRoutes = $options['standalone_router_standalone_cmsfiles_routes'];
        }
        return $this;
    }

    public function isFullPageCmsFile()
    {
        return (1 == $this->zoneDefinitions->count() && $this->zoneDefinitions[0] instanceof ZoneDefinitionCmsFiles && $this->zoneDefinitions[0]->getMaxAttachmentsCount() == 1 && !$this->zoneDefinitions[0]->isAllowDeleteAttachments());
    }

    public function getTemplateOptions()
    {
        return $this->templateOptions;
    }

    public function setTemplateOptions($templateOptions)
    {
        $this->templateOptions = $templateOptions;
        return $this;
    }

    public function getStandaloneRouterController()
    {
        return $this->standaloneRouterController;
    }

    public function setStandaloneRouterController($standaloneRouterController)
    {
        $this->standaloneRouterController = $standaloneRouterController;
        return $this;
    }

    public function isChildPageAllowed()
    {
        return (null == $this->standaloneRouterController);
    }

    /**
     * Does standalone router expose individual cmsfiles routes
     *
     * @return bool
     */
    public function hasStandaloneRouterHasStandaloneCmsfilesRoutes()
    {
        return $this->standaloneRouterHasStandaloneCmsfilesRoutes;
    }

    /**
     * Set standaloneRouterHasStandaloneCmsfilesRoutes
     *
     * @param bool $standaloneRouterHasStandaloneCmsfilesRoutes
     *
     * @return self
     */
    public function setStandaloneRouterHasStandaloneCmsfilesRoutes($standaloneRouterHasStandaloneCmsfilesRoutes)
    {
        $this->standaloneRouterHasStandaloneCmsfilesRoutes = $standaloneRouterHasStandaloneCmsfilesRoutes;
        return $this;
    }
}
