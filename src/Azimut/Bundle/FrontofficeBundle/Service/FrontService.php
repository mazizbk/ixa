<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-26 10:36:13
 */

namespace Azimut\Bundle\FrontofficeBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FrontService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Site|null
     */
    private $site;

    /**
     * @var string
     */
    private $firewallName;

    public function __construct(EntityManagerInterface $entityManager, FirewallMapInterface $firewallMap, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;

        $request = $requestStack->getCurrentRequest();
        if (null != $request) {
            $this->firewallName = $firewallMap->getFirewallConfig($request)
                ->getName()
            ;
        }
    }

    /**
     * Get current Site
     *
     * @return Site
     */
    public function getCurrentSite()
    {
        // If we are not inside frontoffice firewall, there is no site to be found
        if ('frontoffice' != $this->firewallName) {
            return null;
        }

        // Return site object from service cache if exists
        if (null != $this->site) {
            return $this->site;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null == $request) {
            return null;
        }

        $siteRepository = $this->entityManager->getRepository(Site::class);
        $this->site = $siteRepository->findOneActiveByDomainName($request->getHost(), $request->getLocale());

        return $this->site;
    }

    /**
     * Get current user locale from request
     *
     * @return string
     */
    public function getLocale()
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null == $request) {
            return null;
        }

        return $request->getLocale();
    }
}
