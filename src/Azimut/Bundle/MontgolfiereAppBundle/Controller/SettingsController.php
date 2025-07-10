<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\HelpText;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class SettingsController extends AbstractController
{
    /**
     * @var array
     */
    protected $questionnaireLocales;

    public function __construct(array $questionnaireLocales)
    {
        $this->questionnaireLocales = $questionnaireLocales;
    }

    public function indexAction()
    {
        return $this->render('AzimutMontgolfiereAppBundle:Backoffice/Settings:index.html.twig');
    }

    public function helpTextAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var HelpText[] $helpTexts */
        $helpTexts = $em->getRepository(HelpText::class)->findAll();
        $indexedHelpTexts = [];
        foreach ($helpTexts as $helpText) {
            $indexedHelpTexts['text_'.$helpText->getLocale()] = $helpText->getText();
        }

        $form = $this->createFormBuilder($indexedHelpTexts);
        foreach ($this->questionnaireLocales as $locale) {
            $form
                ->add('text_'.$locale, TinymceConfigType::class, [
                    'label' => 'montgolfiere.backoffice.settings.help_text_locale.'.$locale,
                ])
            ;
        }
        $form->add('submit', SubmitType::class, ['label' => 'save',]);
        $form = $form->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            foreach ($form as $input) {
                if(!preg_match('/^text_\w{2}$/', $input->getName())) {
                    continue;
                }
                $locale = substr($input->getName(), 5);
                $helpText = $em->getRepository(HelpText::class)->findOneBy(['locale' => $locale,]);
                if(!$helpText) {
                    $helpText = new HelpText();
                    $helpText->setLocale($locale);
                    $em->persist($helpText);
                }
                $helpText->setText($form->get('text_'.$helpText->getLocale())->getData());
            }
            $em->flush();
            $this->addFlash('success', 'Les textes ont été sauvegardés');

            return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_settings_index');
        }

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Settings/help_texts.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
