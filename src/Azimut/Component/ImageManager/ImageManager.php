<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-09 14:57:54
 */

namespace Azimut\Component\ImageManager;

use Symfony\Component\HttpFoundation\File\File;

class ImageManager
{
    protected $file;

    protected $meta_data;

    protected $meta_data_sections;

    private $auto_find_in_sections;

    public function __construct(File $file)
    {
        $mime_type = $file->getMimeType();

        $this->file = $file;

        $this->auto_find_in_sections = array();

        if ($mime_type == "image/jpeg") {
            $this->auto_find_in_sections = array('EXIF','IFD0','GPS');

            $this->meta_data = @exif_read_data($file, 0, true);
        }
    }

    public function getMetaDataField($field)
    {
        if ($field == 'GPSLongitude') {
            return $this->getGpsLongitude();
        }

        if ($field == 'GPSLatitude') {
            return $this->getGpsLatitude();
        }

        //find field in sections
        foreach ($this->auto_find_in_sections as $section) {
            if (isset($this->meta_data[$section][$field])) {
                return $this->meta_data[$section][$field];
            }
        }

        return null;
    }

    public function getGpsLongitude()
    {
        if (!isset($this->meta_data['GPS']) || !isset($this->meta_data['GPS']['GPSLongitude']) || !isset($this->meta_data['GPS']['GPSLatitude'])) {
            return null;
        }

        return $this->getGpsCoord($this->meta_data['GPS']['GPSLongitude'], $this->meta_data['GPS']['GPSLongitudeRef']);
    }

    public function getGpsLatitude()
    {
        if (!isset($this->meta_data['GPS']) || !isset($this->meta_data['GPS']['GPSLongitude']) || !isset($this->meta_data['GPS']['GPSLatitude'])) {
            return null;
        }

        return $this->getGpsCoord($this->meta_data['GPS']['GPSLatitude'], $this->meta_data['GPS']['GPSLatitudeRef']);
    }

    private function getGpsCoord($exifCoord, $hemi)
    {
        $degrees = count($exifCoord) > 0 ? $this->gps2Num($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? $this->gps2Num($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? $this->gps2Num($exifCoord[2]) : 0;

        $flip = ($hemi == 'W' || $hemi == 'S') ? -1 : 1;

        return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
    }

    private function gps2Num($coordPart)
    {
        $parts = explode('/', $coordPart);

        if (count($parts) <= 0) {
            return 0;
        }

        if (count($parts) == 1) {
            return $parts[0];
        }

        return floatval($parts[0]) / floatval($parts[1]);
    }
}
