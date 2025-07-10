<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\WBEText;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\WBETextType;
use Azimut\Bundle\MontgolfiereAppBundle\Util\WBEManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class SettingsWBEController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var array
     */
    protected $availableLocales;

    public function __construct(TranslatorInterface $translator, array $questionnaireLocales)
    {
        $this->translator = $translator;
        $this->availableLocales = $questionnaireLocales;
    }

    public function index(Request $request)
    {
        $wellBeingProfile = $request->query->getInt('wellbeingprofile', 1);
        $locale = $request->query->get('locale', 'fr');
        if(!in_array($locale, $this->availableLocales)) {
            $locale = 'fr';
        }
        if($wellBeingProfile < 1 || $wellBeingProfile > WBEManager::getWellBeingProfilesCount()) {
            $wellBeingProfile = 1;
        }

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(WBEText::class);
        /** @var WBEText[] $wbeTexts */
        $wbeTexts = $repository->findBy([
            'wellBeingProfile' => $wellBeingProfile,
            'locale' => $locale,
        ]);
        $data = [];
        for($i = 1; $i < WBEManager::getEngagementProfilesCount()+1; $i++) {
            $text = array_filter($wbeTexts, function(WBEText $text) use($i) { return $text->getEngagementProfile() === $i; });
            if(count($text) > 0) {
                $text = array_values($text)[0];
            }
            else {
                $text = new WBEText();
                $text
                    ->setWellBeingProfile($wellBeingProfile)
                    ->setEngagementProfile($i)
                    ->setLocale($locale)
                ;
                $em->persist($text);
            }
            $data[$i] = $text;
        }

        $form = $this->getForm($data);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', $this->translator->trans('montgolfiere.backoffice.settings.wbe_texts.texts_saved'));
        }

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Settings/wbe_texts.html.twig', [
            'form' => $form->createView(),
            'currentWBProfile' => $wellBeingProfile,
            'currentLocale' => $locale,
            'availableLocales' => $this->availableLocales,
            'availableWBProfiles' => array_combine(
                range(1, WBEManager::getWellBeingProfilesCount()),
                array_map(function(int $profile):string {return $this->translator->trans('montgolfiere.backoffice.common.wbe_profiles.well_being.'.$profile);}, range(1, WBEManager::getWellBeingProfilesCount()))
            ),
        ]);
    }

    protected function getForm(array $data)
    {
        $form = $this->createFormBuilder($data);
        foreach ($data as $i => $datum) {
            $form->add($i, WBETextType::class, [
                'label' => $this->translator->trans('montgolfiere.backoffice.common.wbe_profiles.engagement.'.$i),
                'required' => false,
            ]);
        }

        $form->add('submit', SubmitType::class, [
            'label' => 'montgolfiere.backoffice.common.submit',
        ]);

        return $form->getForm();
    }

}
