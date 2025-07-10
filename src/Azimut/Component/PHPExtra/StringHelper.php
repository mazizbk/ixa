<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-01-14 16:14:17
 */

namespace Azimut\Component\PHPExtra;

class StringHelper
{
    /**
     * Replaces accented letter by their equivalent without accent
     */
    public static function replaceAccents($str)
    {
        $search = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,Ø,Å,Á,À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ú,Ù,Û,Ü,Ÿ,Ç,Æ,Œ");
        $replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,O,A,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,Y,C,AE,OE");

        return str_replace($search, $replace, $str);
    }

    /**
     * Removes accents, replace non alphanumeric characters by "-" and compress successive "-"
     */
    public static function slugify($str)
    {
        return preg_replace('/([^a-z0-9]+)/', '-', self::replaceAccents(mb_strtolower($str)));
    }
}
