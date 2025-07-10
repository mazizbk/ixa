<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-05 17:45:17
 */

namespace Azimut\Bundle\ModerationBundle\Controller;

use Azimut\Bundle\ModerationBundle\Entity\CmsFileBuffer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Azimut\Bundle\ModerationBundle\Form\Type\CmsFileBufferType;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;

class FrontofficeController extends Controller
{
    public function cmsFileBufferFormAction($class, $targetZone, Request $originalRequest)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var CmsFileBuffer $cmsFileBuffer */
        $cmsFileBuffer = new $class;
        $cmsFileBuffer->setTargetZone($targetZone);

        $formOptions = [];

        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($user instanceof FrontofficeUser) {
            $cmsFileBuffer
                ->setUserEmail($user->getEmail())
                ->setUser($user)
            ;
            $formOptions['with_user_email'] = false;
        }

        $form = $this->createForm(CmsFileBufferType::class, $cmsFileBuffer, $formOptions);
        $form->handleRequest($originalRequest);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($file = $cmsFileBuffer->getFile()) {
                $fileName = md5(uniqid()).'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('uploads_dir').'/moderation',
                    $fileName
                );

                $cmsFileBuffer->setFilePath($fileName);
            }

            $cmsFileBuffer
                ->setUserIp($originalRequest->getClientIp())
                ->setDomainName($originalRequest->getHost())
                ->setUserLocale($originalRequest->getLocale())
            ;

            $em->persist($cmsFileBuffer);
            $em->flush();

            $mailer = $this->get('azimut_moderation.mailer');
            $mailer->sendUserCmsFileBufferCreated($cmsFileBuffer->getUserEmail(), $cmsFileBuffer->getUserLocale(), $originalRequest->getHost(), $cmsFileBuffer);
            $mailer->sendAdminCmsFileBufferCreated($originalRequest->getHost(), $this->container->getParameter('locale'), $cmsFileBuffer);
        }

        return $this->render('AzimutModerationBundle:Frontoffice:cms_file_buffer_form.html.twig', [
            'form' => $form->createView(),
            'cmsFileSaved' => $cmsFileBuffer->getId()?true:false,
        ]);
    }
}
