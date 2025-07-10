<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-12-04 10:52:39
 */

namespace Azimut\Bundle\MediacenterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Azimut\Component\HttpFoundationExtra\BinaryMediaFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;
use Symfony\Component\HttpFoundation\File\File;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

abstract class AbstractFileProxyController extends Controller
{
    const SERVE_PUBLIC_MEDIA = false;

    public function getFileAction(Request $request, $filepath)
    {
        $mediaDeclination = $this->getDoctrine()
            ->getRepository(MediaDeclination::class)
            ->findOneBy([
                'path' => $filepath
            ])
        ;

        if (!$this->isGrantedMediaDeclination($mediaDeclination) && !$this->isPublicMediaDeclination($mediaDeclination)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $uploadPath = $this->container->getParameter('uploads_dir');
        return new BinaryFileResponse($uploadPath.'/mediacenter/'.$filepath);
    }

    public function getFileThumbAction(Request $request, $filepath, $size)
    {
        $uploadPath = realpath($this->container->getParameter('uploads_dir'));
        $thumbsPath = $uploadPath.'/thumbs/';

        $fullFilepath = $uploadPath.'/mediacenter/'.$filepath;

        $mediaDeclination = $this->getDoctrine()
            ->getRepository(MediaDeclination::class)
            ->findOneBy([
                'path' => $filepath
            ])
        ;

        if (!file_exists($fullFilepath)) {
            throw $this->createNotFoundException(sprintf('File "%s" does not exists', $fullFilepath));
        }

        // Check access rights
        $isGranted = $this->isGrantedMediaDeclination($mediaDeclination);
        $isPublic = false;
        // Note : because isPublicMediaDeclination method has a hight cost, we do not trigger it when user
        // is explicitely granted
        if (!$isGranted && true === static::SERVE_PUBLIC_MEDIA) {
            // Only serve public media if allowed
            $isPublic = $this->isPublicMediaDeclination($mediaDeclination);
        }
        if (!$isGranted && !$isPublic) {
            //throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
            return $this->getUnauthorizedMediaThumb($request->getLocale(), $size);
        }

        $file = new File($fullFilepath);

        // if video mime type
        if (preg_match('#^video/[-\w\.]+$#', $file->getMimeType())) {
            if (!file_exists($fullFilepath.'.jpg')) {
                // generate video poster
                $ffmpeg = FFMpeg::create();
                $video = $ffmpeg->open($fullFilepath);
                $frame = $video->frame(TimeCode::fromSeconds(2));
                $frame->save($fullFilepath.'.jpg');
            }

            $filepath .= '.jpg';
        }

        $imagemanagerResponse = $this->container
            ->get('liip_imagine.controller')
                ->filterAction(
                    new Request(),
                    '/mediacenter/'.$filepath,
                    $size
        );

        $response = new BinaryMediaFileResponse($thumbsPath.$size.'/mediacenter/'.$filepath);

        $response->setMaxAge($this->container->getParameter('media_thumb_cache_max_age'));
        if (true === $isPublic) {
            $response->setSharedMaxAge($this->container->getParameter('media_thumb_cache_shared_max_age'));
        }
        // Set the flag so the reverse proxy will store response file in cache if media is public
        $response->isPublicMedia($isPublic);

        return $response;
    }

    protected function isGrantedMediaDeclination($mediaDeclination)
    {
        if (!($mediaDeclination instanceof MediaDeclination)) {
            throw $this->createNotFoundException('File not found');
        }

        if ($this->isGranted('VIEW', $mediaDeclination->getMedia())) {
            return true;
        }

        return false;
    }

    /**
     * Check if media file is public (published on an active web page)
     */
    protected function isPublicMediaDeclination($mediaDeclination)
    {
        $pages = $this->getDoctrine()
            ->getRepository(MediaDeclination::class)
            ->getFrontofficePublicationPages($mediaDeclination->getId(), $this->get('azimut_frontoffice.search_engine_provider_chain'))
        ;

        foreach ($pages as $page) {
            if ($this->isGranted('view', $page)) {
                return true;
            }
        }

        return false;
    }

    protected function getUnauthorizedMediaThumb($locale, $size)
    {
        $uploadPath = realpath($this->container->getParameter('uploads_dir'));
        $thumbsPath = $uploadPath.'/thumbs/';

        if (!file_exists('bundles/azimutmediacenter/img/notAllowed_'. $locale .'.png')) {
            $locale = 'en';
        }

        // Copy the source file (imagine controller can not access files outside its root directory : uploads)
        $sourcepath = 'bundles/azimutmediacenter/img/notAllowed_'. $locale .'.png';
        $targetFile = $uploadPath.'/notAllowed_'. $locale .'.png';
        if (!file_exists($targetFile)) {
            copy($sourcepath, $targetFile);
        }
        $filepath = 'notAllowed_'. $locale .'.png';

        $imagemanagerResponse = $this->container
            ->get('liip_imagine.controller')
                ->filterAction(
                    new Request(),
                    $filepath,
                    $size
        );

        return new BinaryFileResponse($thumbsPath.$size.'/'.$filepath);
    }
}
