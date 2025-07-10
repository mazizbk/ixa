<?php
/**
 * Created by mikaelp on 25-Jul-18 10:38 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Traits;


use Symfony\Component\HttpFoundation\File\UploadedFile;

trait UploadableEntity
{
    /**
     * @var UploadedFile
     *
     * @Assert\File(maxSize="10Mi")
     */
    private $uploadedFile;

    /**
     * @var string
     *
     * @ORM\Column(name="upload_filename", type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="upload_original_name", type="string", length=255, nullable=true)
     */
    private $originalName;

    /**
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return $this
     */
    public function setUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @param string $originalName
     * @return $this
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

}
