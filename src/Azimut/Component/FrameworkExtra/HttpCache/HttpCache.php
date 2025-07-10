<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-02-21 15:28:15
 */

namespace Azimut\Component\FrameworkExtra\HttpCache;

use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache as BaseHttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Azimut\Component\HttpFoundationExtra\BinaryMediaFileResponse;

abstract class HttpCache extends BaseHttpCache
{
    /**
     * {@inheritdoc}
     */
    protected function store(Request $request, Response $response)
    {
        if ($response instanceof BinaryMediaFileResponse) {
            if (true === $response->isPublicMedia()) {
                parent::store($request, $response);
            }
        }

        // Do not cache BinaryFileResponse
        if ($response instanceof BinaryFileResponse) {
            return false;
        }

        parent::store($request, $response);
    }
}
