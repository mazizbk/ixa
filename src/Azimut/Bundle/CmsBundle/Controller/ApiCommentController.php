<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-18 10:13:05
 */

namespace Azimut\Bundle\CmsBundle\Controller;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\CmsBundle\Entity\Comment;
use Azimut\Bundle\CmsBundle\Form\Type\CommentType;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;

/**
 * @PreAuthorize("isAuthenticated() && (isAuthorized('APP_CMS') || isAuthorized('APP_CMS_*') || isAuthorized('APP_MODERATION'))")
 */
class ApiCommentController extends FOSRestController
{
    /**
     * Get all action
     * @var Request $request
     * @return array
     *
     * @Rest\View(serializerGroups={"list_comments"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  resource=true,
     *  description="CMS : Get all comments"
     * )
     * @Rest\QueryParam(
     *  name="cmsFileId", requirements="\d+", strict=true, nullable=true,
     *  description="id of the cmsfile on wich comments are attached"
     * )
     */
    public function getCommentsAction($cmsFileId = null)
    {
        TranslationProxy::setDefaultLocale('all');

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Comment::class);

        if (null != $cmsFileId) {
            $comments = $repository->findByCmsFile($cmsFileId);
        }
        else {
            $comments = $repository->findAll();
        }

        // User allowed to access Moderation app can view all comments
        if (!$this->isGranted('APP_MODERATION')) {
            $comments = $this->get('azimut_security.filter')->serializeGroup($comments, ['list_comments']);
        }


        return [
            'comments' => $comments,
        ];
    }

    /**
     * Get action
     * @var integer $id Id of the comment
     * @return array
     *
     * @Rest\View(serializerGroups={"always", "detail_comment"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Get comment"
     * )
     * @Rest\QueryParam(
     *  name="cmsFileId", requirements="\d+", strict=true, nullable=true,
     *  description="id of the cmsfile on wich comments are attached"
     * )
     */
    public function getCommentAction($id, $cmsFileId = null)
    {
        TranslationProxy::setDefaultLocale('all');

        $comment = $this->getCommentEntity($id, $cmsFileId);
        if (!$this->isGranted('APP_MODERATION') && !$this->isGranted('VIEW', $comment)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        return [
            'comment' => $comment,
        ];
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Create new comment",
     *  input="Azimut\Bundle\CmsBundle\Form\Type\CommentType",
     *  output="Azimut\Bundle\CmsBundle\Entity\Comment"
     * )
     */
    public function postCommentsAction(Request $request)
    {
        TranslationProxy::setDefaultLocale('all');

        if (!$request->request->get('comment')) {
            throw new \InvalidArgumentException("Comment not found in posted datas.");
        }
        if (!isset($request->request->get('comment')['cmsFile'])) {
            throw new \InvalidArgumentException("Comment's CMS file not found in posted datas.");
        }

        $em = $this->getDoctrine()->getManager();
        $cmsFile = $em->getRepository(CmsFile::class)->find($request->request->get('comment')['cmsFile']);

        if (!$this->isGranted('APP_MODERATION') && !$this->isGranted('EDIT', Comment::class) && !$this->isGranted('EDIT', $cmsFile)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment, [
            'csrf_protection'      => false,
            'with_is_visible'      => true,
            'with_rating'          => true,
            'with_hidden_cms_file' => true,
            'with_captcha'         => false,
        ]);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl('azimut_cms_api_get_comment', ['id' => $comment->getId()])
            );
        }

        return [
            'form' => $form,
        ];
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the comment
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_comment"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Update comment",
     *  input="Azimut\Bundle\CmsBundle\Form\Type\CommentType",
     *  output="Azimut\Bundle\CmsBundle\Entity\Comment"
     * )
     */
    public function putCommentAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');

        if (!$request->request->get('comment')) {
            throw new \InvalidArgumentException("Comment not found in posted datas.");
        }

        $comment = $this->getCommentEntity($id);

        if (!$this->isGranted('APP_MODERATION') && !$this->isGranted('EDIT', $comment)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(CommentType::class, $comment, [
            'method'          => 'PUT',
            'csrf_protection' => false,
            'with_is_visible' => true,
            'with_rating'     => true,
            'with_captcha'    => false,
        ]);

        return $this->updateComment($request, $comment, $form, $id);
    }

    /**
     * Patch action
     * @var Request $request
     * @var integer $id Id of the comment
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_comment"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Update comment (only fields that are submitted)",
     *  input="Azimut\Bundle\CmsBundle\Form\Type\CommentType",
     *  output="Azimut\Bundle\CmsBundle\Entity\Comment"
     * )
     */
    public function patchCommentAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');

        if (!$request->request->get('comment')) {
            throw new \InvalidArgumentException("Comment not found in posted datas.");
        }

        $comment = $this->getCommentEntity($id);

        if (!$this->isGranted('APP_MODERATION') && !$this->isGranted('EDIT', $comment)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(CommentType::class, $comment, [
            'method'          => 'PATCH',
            'csrf_protection' => false,
            'with_is_visible' => true,
            'with_rating'     => true,
            'with_captcha'    => false,
        ]);

        return $this->updateComment($request, $comment, $form, $id);
    }

    /**
     * Delete action
     * @var integer $id Id of the comment
     * @return View
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Delete comment"
     * )
     */
    public function deleteCommentAction($id)
    {
        $comment = $this->getCommentEntity($id);
        if (!$this->isGranted('APP_MODERATION') && !$this->isGranted('EDIT', $comment)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get comment entity instance
     * @var integer $id Id of the entity
     * @return Comment
     */
    protected function getCommentEntity($id, $cmsFileId = null)
    {
        $em = $this->getDoctrine()->getManager();

        $findByParams = ['id' => $id];
        if (null != $cmsFileId) {
            $findByParams['cmsFile'] = $cmsFileId;
        }

        $comment = $em->getRepository(Comment::class)->findOneBy($findByParams);

        if (!$comment) {
            throw $this->createNotFoundException('Unable to find comment '.$id);
        }

        return $comment;
    }

    protected function updateComment($request, $comment, FormInterface $form, $id)
    {
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return [
                'comment' => $comment,
            ];
        }

        return [
            'form' => $form,
        ];
    }
}
