<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-17 15:16:16
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Symfony\Component\HttpFoundation\ParameterBag;

class SearchEngineController extends Controller
{
    public function indexAction(Site $site, $paginationNumber, ParameterBag $requestQuery, $locale)
    {
        $searchEngine = $this->get('azimut_frontoffice.search_engine');
        $searchQuery = $requestQuery->get('query');

        $searchResults = $searchEngine->find($site, $searchQuery, $locale);

        $searchKeywords = $searchEngine->extractKeywords($searchQuery, $locale);

        $template = $site->getLayout()->getSearchResultTemplate() ?: 'search_result.html.twig';

        return $this->render('SiteLayout/'.$template, array(
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle' => $this->get('translator')->trans('search'),
            'pageDescription' => '',
            'site' => $site,
            'paginationNumber' => $paginationNumber,
            'pageLayoutOptions' => [
                'searchQuery' => $searchQuery,
                'searchResults' => $searchResults,
                'searchKeywords' => $searchKeywords,
            ],
        ));
    }
}
