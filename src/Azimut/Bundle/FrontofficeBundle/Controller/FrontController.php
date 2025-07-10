<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-28 14:39:10
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\PageContent;
use Azimut\Bundle\CmsBundle\Entity\Comment;
use Azimut\Bundle\CmsBundle\Form\Type\CommentType;
use Azimut\Bundle\FrontofficeBundle\Entity\PageLink;
use Azimut\Bundle\FrontofficeBundle\Entity\PagePlaceholder;
use Azimut\Bundle\FrontofficeSecurityBundle\Security\FrontofficePageVoter;

class FrontController extends AbstractFrontController
{
    protected static $defaultPaginationLimit = 10;

    public function indexAction($path, Request $request)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->enable('is_comment_visible');

        $pageRepository = $em->getRepository(Page::class);

        $userAgent = $request->headers->get('User-Agent', '');
        if(stripos($userAgent, 'curl') !== false || stripos($userAgent, 'zabbix') !== false) {
            $pageRepository->createQueryBuilder('p')->select('COUNT(p)')->getQuery()->getSingleScalarResult();

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $site = $this->getSite($request);

        // If site's scheme is https and current request is http, redirect
        if ('https' == $site->getScheme() && 'https' != $request->getScheme()) {
            return $this->redirect('https:'.$this->generateUrl('azimut_frontoffice', ['path' => $path], UrlGeneratorInterface::NETWORK_PATH));
        }

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }

        // If URL contains locale prefix
        if (count($this->getParameter('locales')) > 1 || true == $this->getParameter('use_front_url_locale_prefix_if_one_locale')) {
            // When the locale in the URL (locale as prefix) is not supported, locale in request object is changed to the default one but no redirection is not done before (because i18n route is not triggered). Do it if necessary.

            // If URL contains a locale prefix
            if (2 == strlen($path) || 2 == strpos($path, '/')) {
                // If locale in URL is not supported
                if (substr($path, 0, 2) != $request->getDefaultLocale()) {
                    // Redirect to default locale
                    return $this->redirect(
                        $this->generateUrl('azimut_frontoffice', ['path' => substr($path, 3), '_locale' => $request->getDefaultLocale()])
                    );
                }
            }
        }

        // search engine entry point
        if (true === $site->isSearchEngineActive() && $this->get('translator')->trans('search') == $path) {
            return $this->forward('AzimutFrontofficeBundle:SearchEngine:index', [
                'site' => $site,
                'paginationNumber' => $request->query->getInt('page', 1),
                'requestQuery' => $request->query,
                'locale' => $request->getLocale()
            ]);
        }

        // PageLayout cannot left-joined because we don't know that Page is a PageContent yet
        $page = $pageRepository->findOneActiveByPathAndSite($path, $site, $request->getLocale());

        $cmsFile = null;

        if (!$page) {
            // if no page found, try to find a cms file inside the parent page
            $cmsFilePath = $path;
            $parentPath = '';
            $lastSlashPosInPath = strrpos($path, '/');
            $cmsFile = null;

            if ($lastSlashPosInPath > -1) {
                $parentPath = mb_substr($cmsFilePath, 0, $lastSlashPosInPath);
                $cmsFilePath = mb_substr($cmsFilePath, $lastSlashPosInPath + 1 - mb_strlen($cmsFilePath));
            }

            $parentPage = $pageRepository->findOneActiveByPathAndSite($parentPath, $site);

            // if cms files inside parent page have standalone routes
            if (null != $parentPage && $parentPage instanceof PageContent && $parentPage->hasZoneHavingStandaloneCmsfilesRoutes()) {
                $cmsFileRepository = $em->getRepository(CmsFile::class);

                // Loop over zones to be able to apply permanent filters on each one independently
                foreach ($parentPage->getZones() as $zone) {
                    if ($zone->getZoneDefinition()->hasStandaloneCmsfilesRoutes()) {
                        $cmsFile = $cmsFileRepository
                            ->findPublishedOneByPathAndZoneAndLocale($cmsFilePath, $zone, $request->getLocale(), $request->query)
                        ;
                        if (null != $cmsFile) {
                            break;
                        }
                    }
                }

                if (null != $cmsFile) {
                    $cmsFileSlug = $cmsFile->getSlug();
                    $translatedParentPageSlug = $parentPage->getFullSlug();
                    $cmsFileFullSlug = $translatedParentPageSlug .(empty($translatedParentPageSlug) ? '' : '/'). $cmsFileSlug;

                    // if a page is found but the locale prefix is not in URL, redirect
                    if ($request->get('_route') != 'azimut_frontoffice' && $request->get('_route') != 'azimut_frontoffice_home') {
                        return $this->redirectToLocalizedRoute($cmsFileFullSlug, $request->getLocale());
                    }

                    // Translate the path slug
                    //   if the cmsfile slug for current locale is different from the path in url,
                    //   it is probably a call with a slug for a different locale
                    //   so we redirect to the translated url
                    if ($cmsFileSlug != $cmsFilePath) {
                        return $this->redirectToLocalizedRoute($cmsFileFullSlug, $request->getLocale());
                    }

                    $cmsFileTemplate = $parentPage->getTemplate();

                    $templateExtensionPos = strpos($cmsFileTemplate, '.');
                    $cmsFileTemplate = substr($cmsFileTemplate, 0, $templateExtensionPos).'_detail'.substr($cmsFileTemplate, $templateExtensionPos);

                    // find canonical cmsFile path in current site
                    $cmsFileCanonicalPath = $this
                        ->getDoctrine()
                        ->getRepository(CmsFile::class)
                        ->getCmsFileCanonicalPathInSite($cmsFile, $site, $request->getLocale(), $request->query)
                    ;
                    $cmsFile->setCanonicalPath($this->generateUrl('azimut_frontoffice', array('path' => $cmsFileCanonicalPath)));
                }
            }
        }

        if ($page) {
            if ($this->isGranted(FrontofficePageVoter::VIEW_IGNORE_UNIQUE_PASSWORD, $page) && !$this->isGranted(FrontofficePageVoter::VIEW, $page)) {
                // User can access to this page, but did not input the unique password
                // Display unique password form
                $accessToPageUniquePasswordResponse = $this->manageAccessUniquePassword($request,$page);
                if($accessToPageUniquePasswordResponse instanceof Response){
                    return $accessToPageUniquePasswordResponse;
                }
            }
            $this->denyAccessUnlessGranted('view', $page);

            // if a page is found but the locale prefix is not in URL, redirect
            if ($request->get('_route') != 'azimut_frontoffice' && $request->get('_route') != 'azimut_frontoffice_home') {
                return $this->redirectToLocalizedRoute($page->getFullSlug(), $request->getLocale());
            }

            $pageFullPath = strtok($request->getRequestUri(), '?');

            // Translate the path slug
            //   if the page slug for current locale is different from the path in url,
            //   it is probably a call with a slug for a different locale
            //   so we redirect to the translated url
            $pageFullSlug = $page->getFullSlug();

            if ($pageFullSlug != $path) {
                return $this->redirect(str_replace($path, $pageFullSlug, $pageFullPath), 301);
            }

            // add a trailing slash to the path
            if ('/' != mb_substr($pageFullPath, -1)) {
                $pageFullPath .= '/';
            }

            if ($page instanceof PagePlaceholder) {
                return $this->redirect(
                    $this->generateUrl('azimut_frontoffice', ['path' => $page->getParentPage()?$page->getParentPage()->getFullSlug():''])
                );
            }

            // handle link pages (302)
            if ($page instanceof PageLink) {
                //external link
                if (null != $page->getUrl()) {
                    return $this->redirect($page->getUrl());
                }
                // internal link
                else {
                    $this->get('router')->getContext()->setHost($page->getTargetPage()->getSite()->getMainDomainName()->getName());
                    return $this->redirect(
                        $this->generateUrl('azimut_frontoffice', array('path' => $page->getTargetPage()->getFullSlug()), UrlGeneratorInterface::ABSOLUTE_URL)
                    );
                }
            }

            if ($page instanceof PageContent && $page->getTemplate() == 'ixa/clientarea.html.twig'){
                $em->getFilters()->disable('softdeleteable');
            }

            /** @var PageContent $page */
            $response = $this->render('PageLayout/'.$page->getTemplate(), array(
                'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
                'pageLayoutOptions' => $page->getTemplateOptions(),
                'pageTitle' => $page->getMetaTitle(),
                'pageDescription' => $page->getMetaDescription(),
                'pagePath' => $pageFullPath,
                'page' => $page,
                'site' => $site,
                'paginationNumber' => $request->query->getInt('page', 1),
                'requestQuery' => $request->query
            ));

            $response->setMaxAge($this->container->getParameter('front_cache_max_age'));
            if ($page->isPrivate()) {
                $response->setPrivate();
            }
            else {
                $response->setSharedMaxAge($this->container->getParameter('front_cache_shared_max_age'));
            }

            $this->get('azimut_frontoffice.front_collector')->setPageInfos($page, $site);

            return $response;
        }

        if ($cmsFile) {
            $this->denyAccessUnlessGranted('view', $parentPage);

            $commentFormView = null;
            $displayComments = false;
            $isCommentSaved = false;
            if ($site->isCommentsActive() && $cmsFile->supportsComments()) {
                $displayComments = true;
                $comment = new Comment();
                $comment
                    ->setCmsFile($cmsFile)
                    ->isVisible(!$site->isCommentModerationActive())
                ;
                $commentForm = $this->createForm(CommentType::class, $comment, [
                    'with_rating' => $site->isCommentRatingActive(),
                ]);
                $commentForm->handleRequest($request);

                if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                    $em->persist($comment);
                    $em->flush();
                    $isCommentSaved = true;
                }
                $commentFormView = $commentForm->createView();
            }

            $response = $this->render('PageLayout/'.$cmsFileTemplate, [
                'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
                'pageLayoutOptions' => $parentPage->getTemplateOptions(),
                'pageTitle' => $cmsFile->getMetaTitle(),
                'pageDescription' => $cmsFile->getMetaDescription(),
                'cmsFile' => $cmsFile,
                'page' => $parentPage,
                'site' => $site,
                'displayComments' => $displayComments,
                'displayCommentRatings' => $site->isCommentRatingActive(),
                'commentForm' => $commentFormView,
                'isCommentSaved' => $isCommentSaved
            ]);
            $response->setMaxAge($this->container->getParameter('front_cache_max_age'));
            if ($parentPage->isPrivate()) {
                $response->setPrivate();
            }
            else {
                $response->setSharedMaxAge($this->container->getParameter('front_cache_shared_max_age'));
            }

            $this->get('azimut_frontoffice.front_collector')->setCmsFileInfos($cmsFile, $parentPage, $site, $cmsFileTemplate);
            return $response;
        }

        // if nothing found, look for a redirection
        $redirectionUrl = $request->getPathInfo();
        if($request->query->count()>0) {
            $redirectionUrl.= '?'.$request->getQueryString();
        }
        $redirectionUrl = ltrim($redirectionUrl, '/');
        $redirectedPage = $pageRepository->findOneByRedirectionPathAndSite($redirectionUrl, $site);
        if ($redirectedPage) {
            return $this->redirect(
                $this->generateUrl('azimut_frontoffice', array('path' => $redirectedPage->getFullSlug())),
                301
            );
        }

        // delegate subrouting if necessary

        // remove last path component until we find a page
        $pagePath = $path;
        while (null == $page && $slashPos = strrpos($pagePath, '/')) {
            $pagePath = mb_substr($pagePath, 0, $slashPos);
            $page = $pageRepository
                ->findOneActiveByPathAndSite($pagePath, $site)
            ;
        }

        if (null != $page && $page instanceof PageContent && null != $subrouterController = $page->getLayout()->getStandaloneRouterController()) {
            $this->denyAccessUnlessGranted('view', $page);
            $subPath = mb_substr($path, mb_strlen($pagePath)+1);
            $pageFullPath = $request->getRequestUri();
            $pageFullPath = mb_substr($pageFullPath, 0, strpos($pageFullPath, $subPath));

            // add a trailing slash to the path
            if ('/' != mb_substr($pageFullPath, -1)) {
                $pageFullPath .= '/';
            }

            return $this->forward($subrouterController, [
                'path' => $subPath,
                'site' => $site,
                'page' => $page,
                'pagePath' => $pageFullPath
            ]);
        }

        throw $this->createNotFoundException(sprintf('No page found at "%s".', $path));
    }

    public function removeTrailingSlashAction(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, 301);
    }

    /**
     * Render a zone containing CmsFiles
     *
     * NB: templateName is for rendering the zone itself (defaults to zone_default.html.twig), whether cmsFileTemplateName
     * is for rendering the CmsFiles inside the zone.
     * @param integer     $pageId
     * @param string      $zoneName
     * @param string      $templateName
     * @param array       $templateOptions
     * @param string|null $cmsFileTemplateName
     * @param string|null $pagePath
     * @param array       $pagination
     * @param string      $orderCmsFilesBy
     * @return Response
     * @deprecated since version 1.5.0 for performance reasons. Use the renderZone() Twig method instead
     */
    public function pageZoneCmsFilesAction($pageId, $zoneName, $templateName = 'zone_default', $templateOptions = [], $cmsFileTemplateName = null, $pagePath, $pagination = null, $orderCmsFilesBy = null)
    {
        @trigger_error('AzimutFrontofficeBundle:Front:pageZoneCmsFiles is deprecated since version 1.5.0 for performance reasons. Use the renderZone() Twig method instead.', E_USER_DEPRECATED);

        $renderer = $this->get('azimut_frontoffice.zone_renderer');
        $pageRepo = $this->getDoctrine()->getRepository(Page::class);
        $page = $pageRepo->find($pageId);

        $response = new Response();
        $response->setContent($renderer->renderZone($page, $zoneName, $pagePath, [
            'templateName' => $templateName,
            'templateOptions' => $templateOptions,
            'cmsFileTemplateName' => $cmsFileTemplateName,
            'pagination' => $pagination,
            'orderCmsFilesBy' => $orderCmsFilesBy,
        ]));

        return $response;
    }

    /**
     * @param integer $pageId
     * @param string $zoneName
     * @return Response
     * @deprecated since version 1.5.0 for performance reasons. Use the renderZoneForm() Twig method instead.
     */
    public function pageZoneFormAction($pageId, $zoneName)
    {
        @trigger_error('AzimutFrontofficeBundle:Front:pageZoneForm is deprecated since version 1.5.0 for performance reasons. Use the renderZoneForm() Twig method instead.', E_USER_DEPRECATED);

        $zoneRenderer = $this->get('azimut_frontoffice.zone_renderer');
        $pageRepo = $this->getDoctrine()->getRepository(Page::class);
        $page = $pageRepo->find($pageId);

        return $zoneRenderer->renderZoneForm($page, $zoneName);
    }

    /**
     * Render a zone containing cms file buffer form
     * @param $pageId
     * @param $zoneName
     * @return Response
     * @deprecated since version 1.5.0 for performance reasons. Use the renderZoneCmsFileBufferForm() Twig method instead.
     */
    public function pageZoneCmsFileBufferFormAction($pageId, $zoneName)
    {
        @trigger_error('AzimutFrontofficeBundle:Front:pageZoneForm is deprecated since version 1.5.0 for performance reasons. Use the renderZoneCmsFileBufferForm() Twig method instead.', E_USER_DEPRECATED);

        $zoneRenderer = $this->get('azimut_frontoffice.zone_renderer');
        $pageRepo = $this->getDoctrine()->getRepository(Page::class);
        $page = $pageRepo->find($pageId);

        return $zoneRenderer->renderZoneCmsFileBufferForm($page, $zoneName);
    }

    /**
     * @param string $path
     * @param        $requestLocale
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function redirectToLocalizedRoute($path, $requestLocale)
    {
        $locale = $requestLocale;
        // Check if locale is supported
        if (!in_array($requestLocale, $this->getParameter('locales'))) {
            // Use default locale
            $locale = $this->getParameter('locale');
        }

        if ('' == $path) {
            return $this->redirect(
                $this->generateUrl('azimut_frontoffice_home', [ '_locale' => $locale ]),
                Response::HTTP_MOVED_PERMANENTLY
            );
        }

        return $this->redirect(
            $this->generateUrl('azimut_frontoffice', [ 'path' => $path, '_locale' => $locale ]),
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    /**
     * @param Request $request
     * @return Response|null The Response to be displayed or null if access is granted
     */
    private function manageAccessUniquePassword(Request $request, Page $page){
        $site = $this->getSite($request);

        $template = $site->getLayout()->getUniquePasswordTemplate();
        if(!$template){
            $template = 'SiteLayout/unique_password.html.twig';
        }

        $form = $this->createFormBuilder()
            ->add('unique_password', PasswordType::class, ['label' => 'unique.password.label'])
            ->add('submit', SubmitType::class, [
                'label' => 'unique.password.access.contenu',
            ])
            ->getForm()
        ;

        $form->handleRequest($request);
        if($form->isSubmitted()) {
            $uniquePassword = $form->get('unique_password')->getData();
            $session = $request->getSession();
            $uniquePasswordSession = $session->get('unique_password', []);
            $uniquePasswordSession[] = $uniquePassword;
            $uniquePasswordSession = array_unique($uniquePasswordSession);
            $session->set('unique_password', $uniquePasswordSession);
        }

        if ($this->isGranted(FrontofficePageVoter::VIEW, $page)) {
            return null;
        }

        //if isGranted not OK, so password is not good, so remove the password from session
        if($form->isSubmitted()) {
            $form->get('unique_password')->addError(new FormError($this->get('translator')->trans('unique.password.incorrect',[],'validators')));
            //Remove the password from the session if it wasn't the good one
            $session = $request->getSession();
            $uniquePassword = $form->get('unique_password')->getData();
            $uniquePasswordSession = $session->get('unique_password', []);
            unset($uniquePasswordSession[array_search($uniquePassword,$uniquePasswordSession)]);
            $session->set('unique_password', $uniquePasswordSession);
        }

        return $this->render($template, [
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle' => $this->get('translator')->trans('login'),
            'pageDescription' => '',
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }
}
