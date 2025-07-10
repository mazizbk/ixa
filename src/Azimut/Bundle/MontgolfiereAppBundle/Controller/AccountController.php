<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Twig\Extension\PageExtension;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\MontgolfiereAppBundle\Form\AccountConfirmationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\TranslatorInterface;

class AccountController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    /**
     * @var PageExtension
     */
    protected $pageExtension;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator, TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher, PageExtension $pageExtension)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->pageExtension = $pageExtension;
    }

    public function validateAction($token, Request $request)
    {
        $repo = $this->entityManager->getRepository(FrontofficeUser::class);
        $account = $repo->findOneBy(['confirmEmailToken' => $token,]);

        $siteRepository = $this->entityManager->getRepository(Site::class);
        $site = $siteRepository->findOneActiveByDomainName($request->getHost(), $request->getLocale());

        $redirect = $this->redirectToRoute('azimut_frontoffice', ['path' => $this->pageExtension->findPageByTemplate([], 'ixa/clientarea.html.twig', $site)->getFullSlug(),]);
        if(!$account) {
            $this->addFlash('info', 'Votre compte a déjà été activé. Pour accéder à l\'espace client, veuillez saisir votre adresse email et votre mot de passe');

            return $redirect;
        }

        $form = $this->createForm(AccountConfirmationType::class, $account);
        $form->add('submit', SubmitType::class, [
            'label' => 'montgolfiere.frontoffice.account_creation.submit',
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $account
                ->setConfirmEmailToken(null)
                ->isEmailConfirmed(true)
            ;

            $this->entityManager->flush();
            $this->loginUser($request, $account);

            return $redirect;
        }


        return $this->render('@AzimutMontgolfiereApp/Frontoffice/account_confirmation.html.twig', [
            'siteLayout' => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle' => $this->translator->trans('montgolfiere.frontoffice.account_creation.page_title'),
            'pageDescription' => '',
            'site' => $site,
            'form' => $form->createView(),
            'user' => $account,
        ]);
    }

    private function loginUser(Request $request, FrontofficeUser $user)
    {
        $token = new UsernamePasswordToken($user, null, 'frontoffice', $user->getRoles());
        $this->tokenStorage->setToken($token);
        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch('security.interactive_login', $event);
    }

}
