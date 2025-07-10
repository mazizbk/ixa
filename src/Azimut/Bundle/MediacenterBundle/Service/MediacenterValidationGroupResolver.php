<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-12-11 16:58:07
 */

namespace Azimut\Bundle\MediacenterBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class MediacenterValidationGroupResolver
{
    /**
     * Detect a media declination inside the request and return validation groups :
     *  - embedHtmlRequired: if the isEmbeddedMedia checkbox is found and checked
     *  - fileRequired: by default
     *
     * $type: type of the media declination in request (if null, will try to guess)
     */
    public function getGroups(Request $request, $type = null)
    {
        $root = null;

        // find the form corresponding to the media declination

        // if request contains a media declination
        if (null != $request->request->get('media_declination')) {
            $root = $request->request->get('media_declination');
            $rootDeclination = $root;
        }

        // if request contains a media with an embedded declination
        if (null != $request->request->get('media') && isset($request->request->get('media')['mediaDeclinations']['0'])) {
            $root = $request->request->get('media');
            $rootDeclination = $request->request->get('media')['mediaDeclinations']['0'];
        }

        if (null != $root) {
            if (null == $type) {
                if (!isset($root['type'])) {
                    throw new \InvalidArgumentException("Media or declination type not found in request. Consider providing it explicitly as method argument.");
                }
                $type = $root['type'];
            }

            // set embedHtmlRequired group if isEmbeddedMedia checkbox is checked

            if ('video' == $type && isset($rootDeclination['mediaDeclinationType']) && isset($rootDeclination['mediaDeclinationType']['isEmbeddedMedia'])) {
                $isEmbedMedia = ('true' == $rootDeclination['mediaDeclinationType']['isEmbeddedMedia']);

                if (true == $isEmbedMedia) {
                    return ['embedHtmlRequired'];
                }
            }
        }

        // if it is an update, file is not required
        if (Request::METHOD_PUT == $request->getMethod()) {
            return [];
        }

        return ['fileRequired'];
    }
}
