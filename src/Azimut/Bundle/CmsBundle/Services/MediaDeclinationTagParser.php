<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-06-25 11:38:22
 */

namespace Azimut\Bundle\CmsBundle\Services;

use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/*
 * Transform a specific media-declination tag into an html element
 * displaying the mediaDeclination file
 *
 * Tag examples:
 *     ## media-declination-12 | {"width": "120", "height": "50"} ## => <img src="/mediacenter/uploads/my-image.jpg" alt="my image" width="120" height="50" />
 *     ## media-declination-8 ## => <img src="/mediacenter/uploads/my-other-image.jpg" alt="my image" />
 *     ## media-declination-12 | {"width": "120", "height": "50", "style": "margin: 0 auto"} ## => <img src="/mediacenter/uploads/my-image.jpg" alt="my image" width="120" height="50" style="margin: 0 auto" />
 */
class MediaDeclinationTagParser
{
    private $registry;
    private $urlGenerator;
    private $tagRegex;
    private $filterSets = [];

    public function __construct(RegistryInterface $registry, UrlGeneratorInterface $urlGenerator, array $filterSets)
    {
        $this->registry = $registry;
        $this->urlGenerator = $urlGenerator;
        $this->tagRegex = '/## media-declination-(\d+)( \| (.*?))? ##/';

        // order filterSets keeping a reference to their name
        foreach ($filterSets as $key => $filterSet) {
            $filterSets[$key]['name'] = $key;
        }
        usort($filterSets, function ($a, $b) {
            return ($a['filters']['thumbnail']['size'][0] > $b['filters']['thumbnail']['size'][0]) ? -1 : 1;
        });

        $this->filterSets = $filterSets;
    }

    public function parse($text)
    {
        $foundTags = [];
        preg_match_all($this->tagRegex, $text, $foundTags);

        foreach ($foundTags[0] as $key => $tag) {
            $mediaDeclinationId = trim($foundTags[1][$key]);
            $options = json_decode($foundTags[3][$key], true)?:[];

            // transform the found tag into html
            $text = strtr($text, [
                $foundTags[0][$key] => $this->generateHtmlTag($mediaDeclinationId, $options)
            ]);
        }

        return $text;
    }

    private function generateHtmlTag($mediaDeclinationId, array $options)
    {

        /** @var MediaDeclination $mediaDeclination */
        $mediaDeclination = $this->registry->getManager()->getRepository('AzimutMediacenterBundle:MediaDeclination')->find($mediaDeclinationId);
        if (null === $mediaDeclination) {
            return '';
        }

        $media = $mediaDeclination->getMedia();
        $mediaType = $media::getMediaType();

        if ('image' == $mediaType || 'video' == $mediaType) {
            // Always "Figure" class on image and video media wrapper
            $options['class'] = (isset($options['class']) ? $options['class'].' ' : '') . 'Figure';

            $caption = $media->getCaption() ? sprintf('<figcaption class="Figure-caption">%s</figcaption>', $media->getCaption()) : null;
            $copyright = $media->getCopyright() ? sprintf('<footer class="Figure-copyright"><small>© %s</small></footer>', $media->getCopyright()) : null;
        }

        $parsedOptions = $this->formatOptions($options);
        $wrapperParsedOptions = $this->formatWrapperOptions($options);

        if (null != $mediaDeclination->getPath()) {
            $path = $this->urlGenerator->generate('azimut_mediacenter_file_proxy', ['filepath' => $mediaDeclination->getPath()]);
        }


        if ('audio' == $mediaType) {
            return sprintf('<audio src="%s" controls="controls" %s><span class="glyphicon glyphicon-%s"></span> <a href="%s">%s</a></audio>', $path, $parsedOptions, $media->getCssIcon(), $path, $mediaDeclination->getName());
        }

        if ('video' == $mediaType) {
            if (null == $mediaDeclination->getPath()) {
                $embedHtml = $mediaDeclination->getEmbedHtml();

                if (isset($options['width'])) {
                    $embedHtml = str_replace('width="100%"', 'width="'.$options['width'].'"', $embedHtml);
                }
                if (isset($options['height'])) {
                    $embedHtml = str_replace('height="100%"', 'height="'.$options['height'].'"', $embedHtml);
                }
                if (isset($options['style'])) {
                    $embedHtml = str_replace('<iframe ', '<iframe style="'.$options['style'].'"', $embedHtml);
                }
                $embedHtml = str_replace('<iframe', '<iframe class="Figure-iframe embed-media"', $embedHtml);

                return sprintf('<figure %s>%s</figure>', $wrapperParsedOptions, $embedHtml . $caption . $copyright);
            }
            else {
                $posterPath = $this->urlGenerator->generate('azimut_mediacenter_file_proxy_thumb', [
                    'filepath' => $mediaDeclination->getPath(),
                    'size' => 'xxl'
                ]);

                $video = sprintf('<video src="%s" controls="controls" poster="%s" class="Figure-video" %s><span class="glyphicon glyphicon-%s"></span> <a href="%s">%s</a></video>', $path, $posterPath, $parsedOptions, $media->getCssIcon(), $path, $mediaDeclination->getName());

                return sprintf('<figure %s>%s</figure>', $wrapperParsedOptions, $image . $caption . $copyright);
            }
        }

        if ('image' == $mediaType) {
            // use xl filter set by default
            $imageSize = 'xl';

            //if width is set, auto select the closest filter set
            if (isset($options['width'])) {
                $width = (int)$options['width'];
                foreach ($this->filterSets as $filterSet) {
                    // exclude square filter sets
                    if (
                        false === strrpos($filterSet['name'], 'sq')
                        && false === strrpos($filterSet['name'], 'f')
                        && false === strrpos($filterSet['name'], 'c')
                    ) {
                        if ($width + 200 < $filterSet['filters']['thumbnail']['size'][0]) {
                            $imageSize = $filterSet['name'];
                        }
                    }
                }
            }

            $path = $this->urlGenerator->generate('azimut_mediacenter_file_proxy_thumb', [
                'filepath' => $mediaDeclination->getPath(),
                'size' => $imageSize
            ]);

            $image = sprintf('<img src="%s" alt="%s" class="Figure-image" %s/>', $path, $media->getAltText(), $parsedOptions);

            return sprintf('<figure %s>%s</figure>', $wrapperParsedOptions, $image . $caption . $copyright);
        }

        return sprintf('<span class="glyphicon glyphicon-%s"></span> <a href="%s" target="_blank">%s</a>', $media->getCssIcon(), $path, $mediaDeclination->getName());
    }

    private function formatOptions(array $options)
    {
        $parsedOptions = '';

        if (isset($options['width'])) {
            $parsedOptions = 'width="'.$options['width'].'" ';
        }
        if (isset($options['height'])) {
            $parsedOptions .= 'height="'.$options['height'].'" ';
        }
        if (isset($options['style'])) {
            $parsedOptions .= 'style="'.$options['style'].'" ';
        }

        return $parsedOptions;
    }

    private function formatWrapperOptions(array $options)
    {
        $parsedOptions = '';

        if (isset($options['class'])) {
            $parsedOptions .= 'class="'.$options['class'].'"';
        }

        return $parsedOptions;
    }

    public function stripTags($text)
    {
        return preg_replace($this->tagRegex, '', $text);
    }

    /*
     * Extracts a list of all media declinations in text
     */
    public function extractMediaDeclinations($text)
    {
        $foundTags = [];
        preg_match_all($this->tagRegex, $text, $foundTags);

        $mediaDeclinations = [];

        foreach ($foundTags[0] as $key => $tag) {
            $mediaDeclinationId = trim($foundTags[1][$key]);
            $mediaDeclination = $this->registry->getManager()->getRepository('AzimutMediacenterBundle:MediaDeclination')->find($mediaDeclinationId);

            if (null != $mediaDeclination) {
                $mediaDeclinations[] = $this->registry->getManager()->getRepository('AzimutMediacenterBundle:MediaDeclination')->find($mediaDeclinationId);
            }
        }

        return $mediaDeclinations;
    }
}
