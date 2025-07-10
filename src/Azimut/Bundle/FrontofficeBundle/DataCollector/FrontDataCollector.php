<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-01-12 10:31:51
 */

namespace Azimut\Bundle\FrontofficeBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

class FrontDataCollector extends DataCollector
{
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        // This collector do not use collect because data does not
        // come from service but from frontcontroller
        // Use this method when main logic or controller has been moved to service
    }

    public function reset()
    {
        $this->data = [];
    }

    public function getName()
    {
        return 'azimut_frontoffice.front_data_collector';
    }

    public function setPageInfos(Page $page, Site $site)
    {
        $this->data['page'] = $page;
        $this->data['site'] = $site;
    }

    public function setCmsFileInfos(CmsFile $cmsFile, Page $cmsFileParentPage, Site $site, $cmsFileTemplate)
    {
        $this->data['cmsFile'] = $cmsFile;
        $this->data['cmsFileParentPage'] = $cmsFileParentPage;
        $this->data['site'] = $site;
        $this->data['cmsFileTemplate'] = $cmsFileTemplate;
    }

    public function getSite()
    {
        return isset($this->data['site']) ? $this->data['site'] : null;
    }

    public function getPage()
    {
        return isset($this->data['page']) ? $this->data['page'] : null;
    }

    public function getCmsFileParentPage()
    {
        return isset($this->data['cmsFileParentPage']) ? $this->data['cmsFileParentPage'] : null;
    }

    public function getCmsFile()
    {
        return isset($this->data['cmsFile']) ? $this->data['cmsFile'] : null;
    }

    public function getCmsFileTemplate()
    {
        return isset($this->data['cmsFileTemplate']) ? $this->data['cmsFileTemplate'] : null;
    }
}