<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-03-17 15:44:26
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class TinymceConfigType extends AbstractType
{
    /**
     * @var string
     */
    private $script_url;

    /**
     * @var string
     */
    private $content_css_url;

    /**
     * @var string
     */
    private $templates_url;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct($script_url, $content_css_url, $templates_url, TranslatorInterface $translator)
    {
        $this->script_url = $script_url;
        $this->content_css_url = $content_css_url;
        $this->templates_url = $templates_url;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['configs'] = $options['configs'];
        $view->vars['script_url'] = $options['script_url'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $configs = [
            'script_url' => $this->script_url,
            'entity_encoding' => 'raw', // do not store HTML entities to DB
            'body_class' => 'richtext',
            'language' => $this->translator->getLocale(),
            'extended_valid_elements' => 'span[class|style]', // allow empty span with class, usefull for icon fonts
            'theme' => 'modern',
            'content_css' => $this->content_css_url,
            'menubar' =>  false,
            'statusbar' => true,
            'resize' => true,
            'elementpath' => false,
            'toolbar_items_size' => 'small',
            'convert_urls' => false,
            // Full list of toolbar buttons: http://www.tinymce.com/wiki.php/Controls
            // Plugin azembed : add a button to inject HTML embed codes (usefull for social networks snippets)
            'toolbar1' => 'styleselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent', // | azembed',
            'toolbar2' => 'undo redo | cut copy paste | removeformat | searchreplace | table | link charmap nonbreaking | forecolor backcolor | mediacenter', // | template',
            // plugins documentation: http://www.tinymce.com/wiki.php/Plugins
            'plugins' => [
                'advlist autolink link lists charmap, table',
                'nonbreaking, searchreplace',
                'contextmenu template paste textcolor',
                'azembed',
            ],
            'templates' => '/'.$this->templates_url,
            'paste_as_text' => true,
            'style_formats' => [
                ['title' => $this->translator->trans('tinymce.style_formats.headers'), 'items' => [
                    ['title' => $this->translator->trans('tinymce.style_formats.header').' 1', 'format' => 'h1'],
                    ['title' => $this->translator->trans('tinymce.style_formats.header').' 2', 'format' => 'h2'],
                    ['title' => $this->translator->trans('tinymce.style_formats.header').' 3', 'format' => 'h3'],
                    ['title' => $this->translator->trans('tinymce.style_formats.header').' 4', 'format' => 'h4'],
                    ['title' => $this->translator->trans('tinymce.style_formats.header').' 5', 'format' => 'h5'],
                    ['title' => $this->translator->trans('tinymce.style_formats.header').' 6', 'format' => 'h6']
                ]],
                ['title' => $this->translator->trans('tinymce.style_formats.inline'), 'items' => [
                    ['title' => $this->translator->trans('tinymce.style_formats.bold'), 'icon' => 'bold', 'format' => 'bold'],
                    ['title' => $this->translator->trans('tinymce.style_formats.italic'), 'icon' => 'italic', 'format' => 'italic'],
                    ['title' => $this->translator->trans('tinymce.style_formats.underline'), 'icon' => 'underline', 'format' => 'underline'],
                    ['title' => $this->translator->trans('tinymce.style_formats.strikethrough'), 'icon' => 'strikethrough', 'format' => 'strikethrough'],
                    ['title' => $this->translator->trans('tinymce.style_formats.superscript'), 'icon' => 'superscript', 'format' => 'superscript'],
                    ['title' => $this->translator->trans('tinymce.style_formats.subscript'), 'icon' => 'subscript', 'format' => 'subscript'],
                    ['title' => $this->translator->trans('tinymce.style_formats.code'), 'icon' => 'code', 'format' => 'code']
                ]],
                ['title' => $this->translator->trans('tinymce.style_formats.blocks'), 'items' => [
                    ['title' => $this->translator->trans('tinymce.style_formats.paragraph'), 'format' => 'p'],
                    ['title' => $this->translator->trans('tinymce.style_formats.blockquote'), 'format' => 'blockquote'],
                    // ['title' => 'Div', 'format' => 'div'],
                    // ['title' => 'Pre', 'format' => 'pre']
                ]],
                /*['title' => 'Alignment', 'items' => [
                    ['title' => 'Left', 'icon' => 'alignleft', 'format' => 'alignleft'],
                    ['title' => 'Center', 'icon' => 'aligncenter', 'format' => 'aligncenter'],
                    ['title' => 'Right', 'icon' => 'alignright', 'format' => 'alignright'],
                    ['title' => 'Justify', 'icon' => 'alignjustify', 'format' => 'alignjustify']
                ]],*/
                ['title' => $this->translator->trans('tinymce.style_formats.button'), 'selector' => 'a', 'classes' => 'btn btn-default'],

                ['title' => $this->translator->trans('tinymce.style_formats.media'), 'items' => [
                    ['title' => $this->translator->trans('tinymce.style_formats.no.float'), 'selector' => 'img', 'attributes' => ['class' => '']],
                    ['title' => $this->translator->trans('tinymce.style_formats.float.left'), 'selector' => 'img', 'attributes' => ['class' => 'media-pullLeft']],
                    ['title' => $this->translator->trans('tinymce.style_formats.float.right'), 'selector' => 'img', 'attributes' => ['class' => 'media-pullRight']],
                ]],

                ['title' => $this->translator->trans('tinymce.style_formats.clear.float'), 'selector' => '*', 'styles' => ['clear' => 'both']],

                //['title' => $this->translator->trans('tinymce.style_formats.clear.formatting'), 'icon' => 'removeformat', 'format' => 'removeformat'],
            ],
            // 'style_formats_autohide' => true, // Hides formats that do not apply to current selection
        ];

        $resolver
            ->setDefaults(array(
                'configs' => array(),
                'script_url' => $this->script_url,
                'required' => false // the textarea shouldn't have a required attribute because it will be hidden (this cause bug in chrome: not focusable)
            ))
            ->setAllowedTypes('configs', 'array')
            ->setAllowedTypes('script_url', 'string')
            ->setNormalizer('configs', function (Options $options, $value) use ($configs) {
                return array_merge($configs, $value);
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextareaType::class;
    }
}
