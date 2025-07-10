<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-01-28 10:10:27
 */

namespace Azimut\Bundle\CmsBundle\Security;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\FrontofficeBundle\Entity\PageContent;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment;
use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;
use Azimut\Bundle\CmsBundle\Entity\Comment;

class AccessRoleService extends BaseAccessRoleService
{
    /**
     * @var AbstractManagerRegistry
     */
    private $localRegistry;
    public function __construct(RegistryInterface $registry, $activeBackofficeApps)
    {
        $namespace = '';
        $name = 'azimut_cms_roles'; //same as alias in service declaration used for ARType
        $roles = ['APP_CMS'];
        $entities = [
            // Comment::class,
        ];
        $rolesOnEntities = [];

        $this->localRegistry = $registry;
        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata = $this->localRegistry->getManager()->getClassMetadata(CmsFile::class);
        foreach ($classMetadata->discriminatorMap as $class) {
            // exlude all extended CmsFiles not in CmsBundle namespace
            // => NO WE NEED ALL TYPES IN CMS WIDGET
            /*if (substr($class, 0, 24) != 'Azimut\\Bundle\\CmsBundle\\') {
                continue;
            }*/

            // Exclude root CmsFile class
            if ($class == 'Azimut\Bundle\CmsBundle\Entity\CmsFile') {
                continue;
            }

            $entities[] = $class;
            $rolesOnEntities[$class] = [
                'VIEW',
                'EDIT',
                //'SUGGEST'
            ];
            switch ($class) {
                case 'Azimut\Bundle\CmsBundle\Entity\CmsFileProduct':
                    $rolesOnEntities[$class][] = 'PRICES';
                    break;
            }
        }

        // $rolesOnEntities[Comment::class] = [
        //     'VIEW',
        //     'EDIT',
        // ];

        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'cms', $roles, $rolesOnEntities, $entities);
    }

    public function isClassHidden($className)
    {
        // Classes are considered hidden if they extend CmsFile and are outside CmsBundle
        return !(0 === strpos($className, 'Azimut\Bundle\CmsBundle\Entity\CmsFile')) && is_subclass_of($className, CmsFile::class);
    }

    public function getObjectParents($object)
    {
        $objectClass = get_class($object);

        // if object is a Doctrine Proxy
        if ($object instanceof \Doctrine\Common\Persistence\Proxy) {
            $objectClass = $this->registry->getManager()->getClassMetadata(get_class($object))->rootEntityName;
        }

        // CmsFile declared inside Frontoffice namespace inherit their access rights from the page they belong
        if (0 === strpos($objectClass, 'Azimut\Bundle\FrontofficeBundle\Entity\CmsFile')) {
            $manager = $this->localRegistry->getManager();
            $zoneCmsFileAttachmentRepository = $manager->getRepository(ZoneCmsFileAttachment::class);

            /** @var ZoneCmsFileAttachment $zoneCmsFileAttachment */
            $zoneCmsFileAttachment = $zoneCmsFileAttachmentRepository->findOneBy([
                'cmsFile' => $object
            ]);
            $zone = $zoneCmsFileAttachment->getZone();
            $pageContent = $zone->getPageContent();

            return [$pageContent];
        }
        return parent::getObjectParents($object);
    }
}
