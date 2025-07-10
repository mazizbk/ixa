<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-17 10:56:55
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Security;

use phpDocumentor\Reflection\Types\Parent_;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;

class FrontofficePageVoter extends Voter
{
    const VIEW = 'view';
    /**
     * Allows to test if a user might have access to a page, not considering the unique password
     * In other words, allows to know whether it is relevant to show the unique password form or not
     * (If the user is logged in, but not a member of the page's groups, having the password is not sufficient)
     */
    const VIEW_IGNORE_UNIQUE_PASSWORD = 'view_ignore_unique_password';
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * FrontofficePageVoter constructor.
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    protected function supports($attribute, $subject)
    {
        if ($attribute != self::VIEW && $attribute != self::VIEW_IGNORE_UNIQUE_PASSWORD) {
            return false;
        }

        if (!$subject instanceof Page) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if(self::VIEW_IGNORE_UNIQUE_PASSWORD !== $attribute && !$this->canUniquePasswordAccess($subject)){
            return false;
        }

        if (!$user instanceof FrontofficeUser) {
            return $this->canAnonymousView($subject);
        }

        return $this->canView($subject, $user);
    }

    private function canView(Page $page, FrontofficeUser $user)
    {
        // check access right on parent page
        if (null != $parentPage = $page->getParentPage()) {
            if (false == $this->canView($parentPage, $user)) {
                return false;
            }
        }

        // if page has no restriction, grant access
        if (count($page->getUserRoles()) == 0) {
            return true;
        }

        return count(array_intersect($page->getUserRoles(), $user->getRoles())) > 0;
    }

    private function canAnonymousView(Page $page)
    {
        // check access right on parent page
        if (null != $parentPage = $page->getParentPage()) {
            if (!$this->canAnonymousView($parentPage)) {
                return false;
            }
        }

        // if page has no restriction, grant access to anonymous user
        return (count($page->getUserRoles()) == 0);
    }

    private function canUniquePasswordAccess(Page $page){
        //if the page has no unique password defined, so no constraint
        if(null === $page->getUniquePasswordAccess()) {
            if (null !== ($parentPage = $page->getParentPage())) {
                if (false === $this->canUniquePasswordAccess($parentPage)) {
                    return false;
                }
            }
            return true;
        }
        elseif(in_array($page->getUniquePasswordAccess(), $this->session->get('unique_password', []))) {
            return true;
        }
        
        return false;
    }
}