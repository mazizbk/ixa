<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Tooltip;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\TooltipType;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ThemesManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class SettingsTooltipsController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string[]
     */
    protected $questionnaireLocales;

    /**
     * @var ThemesManager
     */
    protected $themesManager;

    public function __construct(TranslatorInterface $translator, array $questionnaireLocales, ThemesManager $themesManager)
    {
        $this->translator = $translator;
        $this->questionnaireLocales = $questionnaireLocales;
        $this->themesManager = $themesManager;
    }

    public function index(Request $request): Response
    {
        $locale = $request->query->get('locale', 'fr');
        if(!in_array($locale, $this->questionnaireLocales)) {
            $locale = 'fr';
        }

        $form = $this->getForm($locale);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', $this->translator->trans('montgolfiere.backoffice.settings.tooltips_texts_saved'));
        }

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Settings/tooltips.html.twig', [
            'form' => $form->createView(),
            'currentLocale' => $locale,
            'availableLocales' => $this->questionnaireLocales,
        ]);
    }

    protected function getForm(string $locale): FormInterface
    {
        $tooltips = $this->getData($locale);

        return $this->createFormBuilder(['tooltips' => $tooltips])
            ->add('tooltips', CollectionType::class, [
                'entry_type' => TooltipType::class,
                'label' => false,
                'prototype' => false,
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->getForm()
        ;
    }

    /**
     * @return Tooltip[]
     */
    private function getData(string $locale): array
    {
        $em = $this->getDoctrine()->getManager();
        $databaseData = $em->getRepository(Tooltip::class)->findBy(['locale' => $locale]);
        $result = [];
        foreach($this->themesManager->getThemes() as $theme) {
            if($theme->isVirtual()) {
                continue;
            }
            foreach($theme->getItems() as $item) {
                foreach ($databaseData as $databaseDatum) {
                    if($databaseDatum->getItem() === $item) {
                        $result[] = $databaseDatum;
                        continue 2;
                    }
                }
                $newTooltip = new Tooltip();
                $newTooltip
                    ->setItem($item)
                    ->setLocale($locale)
                ;
                $em->persist($newTooltip);
                $result[] = $newTooltip;
            }
        }

        return $result;
    }
}
