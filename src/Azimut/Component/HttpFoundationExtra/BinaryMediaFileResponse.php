<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-02-21 10:04:40
 */

namespace Azimut\Component\HttpFoundationExtra;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * BinaryMediaFileResponse represents an HTTP response delivering a media file.
 */
class BinaryMediaFileResponse extends BinaryFileResponse
{
    /**
     * Flag that determine if response should be placed in public cache
     * @var bool
     */
    protected $isPublicMedia;

    /**
     * Get or set isPublicMedia
     *
     * @param bool $isPublicMedia|null
     *
     * @return self|bool
     */
    public function isPublicMedia($isPublicMedia = null)
    {
        if (null !== $isPublicMedia) {
            $this->isPublicMedia = $isPublicMedia;

            if (true == $isPublicMedia) {
                $this->setPublic();
            }
            else {
                $this->setPrivate();
            }

            return $this;
        }

        return $this->isPublicMedia;
    }

    public function getContent()
    {
        return file_get_contents($this->file->getPathname());
    }
}
