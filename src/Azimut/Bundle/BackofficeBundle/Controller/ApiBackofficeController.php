<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-04 14:52:15
 */

namespace Azimut\Bundle\BackofficeBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

class ApiBackofficeController extends FOSRestController
{
    /**
     * Get disk quota action
     * @var Request $request
     * @return array
     *
     * @ApiDoc(
     *  section="Backoffice",
     *  resource=true,
     *  description="Backoffice : Get disk quota"
     * )
     *
     * @PreAuthorize("isAuthenticated()")
     */
    public function getDiskquotaAction()
    {
        $diskUnit = 'Mo';

        $diskQuotaManager = $this->container->get('azimut_mediacenter.disk_quota_manager');

        $diskQuota = $diskQuotaManager->getDiskQuota($diskUnit);

        $diskUsage = $diskQuotaManager->getDiskUsage($diskUnit);

        if (0 == $diskQuota) {
            $diskUsagePercent = 0;
        } else {
            $diskUsagePercent = ceil($diskUsage * 100 / $diskQuota);
        }


        return array(
            'diskQuota' => $diskQuota,
            'diskUsage' => $diskUsage,
            'diskUsagePercent' => $diskUsagePercent,
            'diskUnit' => $diskUnit
        );
    }

    /**
     * Send email to superadmin
     * @var Request $request
     * @return array
     *
     * @ApiDoc(
     *  section="Backoffice",
     *  resource=true,
     *  description="Backoffice : send email to superadmin"
     * )
     *
     * @PreAuthorize("isAuthenticated()")
     */
    public function postEmailbugreportAction(Request $request)
    {
        $bugNumber = time();
        $email = $request->request->get('email');

        if (null != $email) {
            $message = \Swift_Message::newInstance()
                ->setSubject('['.$bugNumber.'] Azimut System bug report')
                ->setFrom('yoann.lecrom@azimut.net')
                ->setTo($email)
                ->setBody($request->request->get('message'))
            ;
            $this->get('mailer')->send($message);
        } else {
            $email = 'formulaire@azimut.net';
        }

        $message = \Swift_Message::newInstance()
            ->setSubject('['.$bugNumber.'] Azimut System bug report')
            ->setFrom($email)
            ->setTo('yoann.lecrom@azimut.net')
            ->setBody($request->request->get('message')."\n\n".$request->request->get('trace'))
        ;
        $this->get('mailer')->send($message);

        return [
            'bugNumber' => $bugNumber
        ];
    }
}
