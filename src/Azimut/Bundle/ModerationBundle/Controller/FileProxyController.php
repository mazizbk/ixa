<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-06 16:23:25
 */

namespace Azimut\Bundle\ModerationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

class FileProxyController extends Controller
{
    public function getFileThumbAction($filepath, $size)
    {
        $uploadPath = realpath($this->container->getParameter('uploads_dir'));
        $basePath = $uploadPath.'/thumbs/';

        $fullFilepath = $uploadPath.'/moderation/'.$filepath;

        if (!file_exists($fullFilepath)) {
            throw $this->createNotFoundException(sprintf('File "%s" does not exists', $fullFilepath));
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
                    '/moderation/'.$filepath,
                    $size
        );

        return new BinaryFileResponse($basePath.$size.'/moderation/'.$filepath);
    }
}
