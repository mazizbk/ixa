<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-18 11:48:43
 */

namespace Azimut\Bundle\CmsBundle\Controller;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\View\View;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\CmsBundle\Entity\ProductItem;
use Azimut\Bundle\CmsBundle\Form\Type\ProductItemType;

/**
 * @PreAuthorize("isAuthenticated() && (isAuthorized('APP_CMS') || isAuthorized('APP_CMS_*') || isAuthorized('APP_SHOP'))")
 */
class ApiProductItemController extends FOSRestController
{
    /**
     * Get all action
     * @var Request $request
     * @return array
     *
     * @Rest\View(serializerGroups={"list_product_items"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  resource=true,
     *  description="CMS : Get all productItems"
     * )
     * @Rest\QueryParam(
     *  name="cmsFileId", requirements="\d+", strict=true, nullable=true,
     *  description="id of the cmsfile on wich productItems are attached"
     * )
     * @Rest\QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getProductitemsAction($cmsFileId = null, $locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(ProductItem::class);

        if (null != $cmsFileId) {
            $productItems = $repository->findByCmsFile($cmsFileId);
        }
        else {
            $productItems = $repository->findAll();
        }

        // User allowed to access Moderation app can view all productItems
        if (!$this->isGranted('APP_SHOP')) {
            $productItems = $this->get('azimut_security.filter')->serializeGroup($productItems, ['list_product_items']);
        }


        return [
            'productItems' => $productItems,
        ];
    }

    /**
     * Get action
     * @var integer $id Id of the productItem
     * @return array
     *
     * @Rest\View(serializerGroups={"always", "detail_product_item"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Get productItem"
     * )
     * @Rest\QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getProductitemAction($id, $locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);
        $productItem = $this->getProductItemEntity($id);
        if (!$this->isGranted('APP_SHOP') && !$this->isGranted('VIEW', $productItem)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        return [
            'productItem' => $productItem,
        ];
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Create new productItem",
     *  input="Azimut\Bundle\CmsBundle\Form\Type\ProductItemType",
     *  output="Azimut\Bundle\CmsBundle\Entity\ProductItem"
     * )
     */
    public function postProductitemsAction(Request $request)
    {
        if (!$request->request->get('product_item')) {
            throw new HttpException(400, "Product item not found in posted datas.");
        }
        if (!isset($request->request->get('product_item')['cmsFile'])) {
            throw new HttpException(400, "ProductItem's CMS file not found in posted datas.");
        }

        $em = $this->getDoctrine()->getManager();
        $cmsFile = $em->getRepository(CmsFile::class)->find($request->request->get('product_item')['cmsFile']);

        if (!$this->isGranted('APP_SHOP') && !$this->isGranted('EDIT', ProductItem::class) && !$this->isGranted('EDIT', $cmsFile)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $productItem = new ProductItem();

        $form = $this->createForm(ProductItemType::class, $productItem, [
            'csrf_protection'      => false,
            'with_hidden_cms_file' => true,
        ]);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($productItem);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl('azimut_cms_api_get_productitem', ['id' => $productItem->getId()])
            );
        }

        return [
            'form' => $form,
        ];
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the productItem
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_product_item"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Update productItem",
     *  input="Azimut\Bundle\CmsBundle\Form\Type\ProductItemType",
     *  output="Azimut\Bundle\CmsBundle\Entity\ProductItem"
     * )
     */
    public function putProductitemAction(Request $request, $id)
    {
        $productItem = $this->getProductItemEntity($id);

        if (!$this->isGranted('APP_SHOP') && !$this->isGranted('EDIT', $productItem)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(ProductItemType::class, $productItem, [
            'method'          => 'PUT',
            'csrf_protection' => false,
        ]);

        return $this->updateProductItem($request, $productItem, $form, $id);
    }

    /**
     * Patch action
     * @var Request $request
     * @var integer $id Id of the productItem
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_product_item"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Update productItem (only fields that are submitted)",
     *  input="Azimut\Bundle\CmsBundle\Form\Type\ProductItemType",
     *  output="Azimut\Bundle\CmsBundle\Entity\ProductItem"
     * )
     */
    public function patchProductitemAction(Request $request, $id)
    {
        $productItem = $this->getProductItemEntity($id);

        if (!$this->isGranted('APP_SHOP') && !$this->isGranted('EDIT', $productItem)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(ProductItemType::class, $productItem, [
            'method'          => 'PATCH',
            'csrf_protection' => false,
        ]);

        return $this->updateProductItem($request, $productItem, $form, $id);
    }

    /**
     * Delete action
     * @var integer $id Id of the productItem
     * @return View
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Delete productItem"
     * )
     */
    public function deleteProductitemAction($id)
    {
        $productItem = $this->getProductItemEntity($id);
        if (!$this->isGranted('APP_SHOP') && !$this->isGranted('EDIT', $productItem)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($productItem);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get productItem entity instance
     * @var integer $id Id of the entity
     * @return ProductItem
     */
    protected function getProductItemEntity($id, $cmsFileId = null)
    {
        $em = $this->getDoctrine()->getManager();

        $findByParams = ['id' => $id];
        if (null != $cmsFileId) {
            $findByParams['cmsFile'] = $cmsFileId;
        }

        $productItem = $em->getRepository(ProductItem::class)->findOneBy($findByParams);

        if (!$productItem) {
            throw $this->createNotFoundException('Unable to find productItem '.$id);
        }

        return $productItem;
    }

    protected function updateProductItem($request, $productItem, FormInterface $form, $id)
    {
        if (!$request->request->get('product_item')) {
            throw new HttpException(400, "Product item not found in posted datas.");
        }

        TranslationProxy::setDefaultLocale('all');

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return [
                'productItem' => $productItem,
            ];
        }

        return [
            'form' => $form,
        ];
    }
}
