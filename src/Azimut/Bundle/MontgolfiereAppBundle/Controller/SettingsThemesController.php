<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Item;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber\UploadSubscriber;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ItemType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ThemeType;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ThemesManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class SettingsThemesController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var array
     */
    protected $questionnaireLocales;

    /**
     * @var ThemesManager
     */
    private $themesManager;

    public function __construct(TranslatorInterface $translator, array $questionnaireLocales, ThemesManager $themesManager)
    {
        $this->translator = $translator;
        $this->questionnaireLocales = $questionnaireLocales;
        $this->themesManager = $themesManager;
    }

    public function index(): Response
    {
        $themes = $this->themesManager->getThemes();

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Settings/themes.html.twig', [
            'themes' => $themes,
            'questionnaire_locales' => $this->questionnaireLocales,
        ]);
    }

    public function edit(Theme $theme, Request $request): Response
    {
        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', $this->translator->trans('montgolfiere.backoffice.settings.themes.theme_saved', ['%name%' => $theme->getName()[$request->getLocale()],]));

            return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_settings_themes');
        }

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Settings/theme_edit.html.twig', [
            'theme' => $theme,
            'form' => $form->createView(),
        ]);
    }

    public function move(Theme $theme, Request $request): Response
    {
        $direction = $request->query->get('direction');
        if($direction !== 'up' && $direction !== 'down') {
            $this->addFlash('danger', $this->translator->trans('montgolfiere.backoffice.common.invalid_query'));

            return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_settings_themes');
        }

        $offset = $direction === 'up' ? -1 : 1;

        $theme->setPosition($theme->getPosition() + $offset);
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash('success', $this->translator->trans('montgolfiere.backoffice.settings.themes.theme_moved'));

        return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_settings_themes');
    }

    public function editItem(Theme $theme, Item $item, Request $request): Response
    {
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', $this->translator->trans('montgolfiere.backoffice.settings.themes.item.item_saved', ['%name%' => $item->getName()[$request->getLocale()],]));

            return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_settings_themes');
        }

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Settings/item_edit.html.twig', [
            'theme' => $theme,
            'form' => $form->createView(),
        ]);
    }

    public function image(Theme $theme, UploadSubscriber $uploadSubscriber): Response
    {
        if(!$theme->getFilename()) {
            throw $this->createNotFoundException();
        }

        return $this->file($uploadSubscriber->getUploadsDir().DIRECTORY_SEPARATOR.$uploadSubscriber->getTargetDir().DIRECTORY_SEPARATOR.$theme->getFilename());
    }
}
