<?php
/**
 * User: goulven
 * Date: 03/08/2022
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipationOpinion;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\WBEText;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ButtonLinkType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ButtonsType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\OpinionType;
use Azimut\Bundle\MontgolfiereAppBundle\Util\WBEManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class PersonalAreaController extends AbstractController
{

    /**
     * @var WBEManager
     */
    private $WBEManager;

    public function __construct(WBEManager $WBEManager)
    {
        $this->WBEManager = $WBEManager;
    }

    public function IndexAction(Request $request)
    {
        if(!$request->getSession()->has('personal_area_email')) {
            return $this->render('@AzimutMontgolfiereApp/PersonalArea/error.html.twig', [
                'error' => 'missing_connection',
            ]);
        }
        return $this->redirectToRoute('azimut_montgolfiere_personal_area_profil', ['token' => $request->getSession()->get('personal_area_participation_token')]);
    }

    public function BEEProfilAction(Request $request, CampaignParticipation $participation)
    {
        if(!$request->getSession()->has('personal_area_email')){
            $request->getSession()->set('personal_area_email', $participation->getEmailAddress());
            $request->getSession()->set('personal_area_participation_token', $participation->getToken());
        }
        if($participation->getEmailAddress() != $request->getSession()->get('personal_area_email')){
            return $this->render('@AzimutMontgolfiereApp/PersonalArea/error.html.twig', [
                'error' => 'incorrect_email',
            ]);
        }

        $wbeText = $this->getDoctrine()->getRepository(WBEText::class)->findOneBy([
            'locale' => $request->getLocale(),
            'engagementProfile' => $this->WBEManager::getEngagementProfileFromParticipation($participation),
            'wellBeingProfile' => $this->WBEManager::getWellBeingProfileFromParticipation($participation),
        ]);

        return $this->render('AzimutMontgolfiereAppBundle:PersonalArea:BEEProfil.html.twig', [
            'participation' => $participation,
            'wbeText' => $wbeText,
        ]);
    }

    public function logoutAction(Request $request)
    {
        $request->getSession()->remove('personal_area_email');
        $request->getSession()->remove('personal_area_participation_token');
        return $this->redirectToRoute('azimut_montgolfiere_personal_area_home');
    }

    public function opinionFormAction(Request $request)
    {
        if(!$request->getSession()->has('personal_area_email')){
            return $this->redirectToRoute('azimut_montgolfiere_personal_area_home');
        }
        $em = $this->getDoctrine()->getManager();
        $participation = $em->getRepository(CampaignParticipation::class)->findOneBy(['token' => $request->getSession()->get('personal_area_participation_token')]);
        if($participation->getEmailAddress() != $request->getSession()->get('personal_area_email')){
            return $this->render('@AzimutMontgolfiereApp/PersonalArea/error.html.twig', [
                'error' => 'incorrect_email',
            ]);
        }
        $message  = '';
        $opinion = $em->getRepository(CampaignParticipationOpinion::class)->findOneBy(['participation' => $participation]);
        if($opinion){
            return $this->render('@AzimutMontgolfiereApp/PersonalArea/opinionForm.html.twig', [
                'message' => 'opinion_already_recorded',
                'participation' => $participation,
            ]);
        }
        $participationOpinion = new CampaignParticipationOpinion();
        $participationOpinion->setParticipation($participation);
        $form = $this->createForm(OpinionType::class, $participationOpinion);
        $form->add('buttons', ButtonsType::class);
        $form->get('buttons')
            ->add('cancel', ButtonLinkType::class, [
                'color' => 'default',
                'text' => 'montgolfiere.backoffice.common.cancel',
                'route' => 'azimut_montgolfiere_personal_area_profil',
                'attr' => ['class' => 'Btn Btn--off'],
                'route_params' => ['token' => $participation->getToken()],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'Btn',],
                'label' => 'montgolfiere.backoffice.common.save',
            ])
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $em->persist($participationOpinion);
            $em->flush();
            $message = 'opinion_recorded';
        }
        return $this->render('AzimutMontgolfiereAppBundle:PersonalArea:opinionForm.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
            'participation' => $participation,
        ]);
    }

    public function participationsAction(Request $request)
    {
        if(!$request->getSession()->has('personal_area_email')){
            return $this->render('@AzimutMontgolfiereApp/PersonalArea/error.html.twig', [
                'error' => 'missing_connection',
            ]);
        }

        $emailAddress = $request->getSession()->get('personal_area_email');
        if(empty($emailAddress)) {
            return $this->render('@AzimutMontgolfiereApp/PersonalArea/error.html.twig', [
                'error' => 'no_email_address',
            ]);
        }

        $participations = $this->getDoctrine()->getRepository(CampaignParticipation::class)->findBy(['emailAddress' => $emailAddress], ['id' => 'desc']);

        return $this->render('AzimutMontgolfiereAppBundle:PersonalArea:participations.html.twig', [
            'participations' => $participations,
        ]);
    }

    public function charterAction(Request $request)
    {
        return $this->render('@AzimutMontgolfiereApp/PersonalArea/commitmentCharter.html.twig');
    }
}
