<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-09-24
 */

namespace Azimut\Bundle\MediacenterBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Azimut\Bundle\MediacenterBundle\Entity\Folder;
use Azimut\Bundle\MediacenterBundle\Form\Type\FolderType;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_MEDIACENTER')")
 */
class ApiFolderController extends FOSRestController
{
    /**
     * Get all action
     * @var Request $request
     * @return array
     *
     * @Rest\View(serializerGroups={"list_folders"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  resource=true,
     *  description="Mediacenter : Get all folders"
     * )
     */
    public function getFoldersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $folders = $em->getRepository('AzimutMediacenterBundle:Folder')
            ->findRootFoldersOrderedByName();

        //return $folders;
        return array(
            'folders' => $this->get('azimut_security.filter')->serializeGroup($folders, ['list_folders']),
        );
    }

    /**
     * Get action
     * @var integer $id Id of the folder
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_folder"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Get folder"
     * )
     */
    public function getFolderAction($id)
    {
        $folder = $this->getFolderEntity($id);

        if (!$this->isGranted('VIEW', $folder)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        return array(
            'folder' => $this->get('azimut_security.filter')->serializeGroup($folder, ['detail_folder']),
        );
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Create new folder",
     *  input="folder",
     *  output="Azimut\Bundle\MediacenterBundle\Entity\Folder"
     * )
     */
    public function postFoldersAction(Request $request)
    {
        if (!$request->request->get('folder')) {
            throw new HttpException(400, "Folder not found in posted datas.");
        }

        $folder = new Folder();
        $form = $this->createForm(FolderType::class, $folder, array(
            'csrf_protection' => false
        ));

        if ($form->handleRequest($request)->isValid()) {
            if (!$this->isGranted('WRITE', $folder->getParentFolder())) {
                $folderName = mb_strtolower($folder->getParentFolder()->getName());
                if ('my.library' == $folderName) {
                    $folderName = $this->container->get('translator')->trans('my.library');
                }
                throw $this->createAccessDeniedException($this->get('translator')->trans('security.folder.creation.denied.can.not.write.%folder%', ['%folder%' => $folderName]));
            }

            //check if a file or folder has the same name
            if ($newName = $this->checkExistingName($folder)) {
                $folder->setName($newName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($folder);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl(
                    'azimut_mediacenter_api_get_folder',
                    array('id' => $folder->getId())
                )
            );
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the folder
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_folder"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Update folder",
     *  input="folder",
     *  output="Azimut\Bundle\MediacenterBundle\Entity\Folder"
     * )
     */
    public function putFolderAction(Request $request, $id)
    {
        if (!$request->request->get('folder')) {
            throw new HttpException(400, "Folder not found in posted datas.");
        }

        $folder = $this->getFolderEntity($id);

        if (!$this->isGranted('WRITE', $folder->getParentFolder())) {
            $folderName = mb_strtolower($folder->getParentFolder()->getName());
            if ('my.library' == $folderName) {
                $folderName = $this->container->get('translator')->trans('my.library');
            }
            throw $this->createAccessDeniedException(
                $this->get('translator')->trans('security.folder.%folder1%.update.denied.can.not.write.%folder2%',
                    [
                        '%folder1%' => $folder->getName(),
                        '%folder2%' => $folderName
                    ]
                )
            );
        }

        $form = $this->createForm(FolderType::class, $folder, array(
            'method' => 'PUT',
            'csrf_protection' => false
        ));

        return $this->updateFolder($request, $folder, $form);
    }

    /**
     * Patch action
     * @var Request $request
     * @var integer $id Id of the folder
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_folder"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Update folder",
     *  input="folder",
     *  output="Azimut\Bundle\MediacenterBundle\Entity\Folder"
     * )
     */
    public function patchFolderAction(Request $request, $id)
    {
        if (!$request->request->get('folder')) {
            throw new HttpException(400, "Folder not found in posted datas.");
        }

        $folder = $this->getFolderEntity($id);

        $parentFolder = (!$folder->isTrashed()) ? $folder->getParentFolder() : $folder->getTrashedParentFolder();

        if (!$this->isGranted('WRITE', $parentFolder)) {
            $folderName = mb_strtolower($parentFolder->getName());
            if ('my.library' == $folderName) {
                $folderName = $this->container->get('translator')->trans('my.library');
            }
            throw $this->createAccessDeniedException(
                $this->get('translator')->trans('security.folder.%folder1%.update.denied.can.not.write.%folder2%',
                    [
                        '%folder1%' => $folder->getName(),
                        '%folder2%' => $folderName
                    ]
                )
            );
        }

        $form = $this->createForm(FolderType::class, $folder, array(
            'method' => 'PATCH',
            'csrf_protection' => false
        ));

        return $this->updateFolder($request, $folder, $form);
    }

    /**
     * Delete action
     * @var integer $id Id of the folder
     * @return View
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Delete folder"
     * )
     */
    public function deleteFolderAction($id)
    {
        $folder = $this->getFolderEntity($id);

        $parentFolder = (!$folder->isTrashed()) ? $folder->getParentFolder() : $folder->getTrashedParentFolder();

        if (!$this->isGranted('WRITE', $parentFolder)) {
            $folderName = mb_strtolower($parentFolder->getName());
            if ('my.library' == $folderName) {
                $folderName = $this->container->get('translator')->trans('my.library');
            }
            throw $this->createAccessDeniedException(
                $this->get('translator')->trans('security.folder.%folder1%.deletion.denied.can.not.write.%folder2%',
                    [
                        '%folder1%' => $folder->getName(),
                        '%folder2%' => $folderName
                    ]
                )
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($folder);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Private : get folder entity instance
     * @var integer $id Id of the entity
     * @return Folder
     */
    protected function getFolderEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $folder = $em->getRepository('AzimutMediacenterBundle:Folder')->find($id);

        if (!$folder) {
            throw $this->createNotFoundException('Unable to find folder '.$id);
        }

        return $folder;
    }

    protected function updateFolder($request, $folder, $form)
    {

        //check if parent folder id the same as the folder id (if defined in form)
        if (isset($request->request->get('folder')['parentFolder']) && $request->query->get('folder')['id'] == $request->request->get('folder')['parentFolder']) {
            return $this->view(null, 400);
        }

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $parentFolder = $folder->getParentFolder();

            if ($parentFolder && !$this->isGranted('WRITE', $parentFolder)) {
                $folderName = mb_strtolower($folder->getParentFolder()->getName());
                if ('my.library' == $folderName) {
                    $folderName = $this->container->get('translator')->trans('my.library');
                }
                $message = $this->get('translator')->trans('security.folder.update.denied.can.not.write.%folder%', ['%folder%' => $folderName]);
                throw $this->createAccessDeniedException($message);
            }

            // if restoring a folder from trash
            if (!$folder->isTrashed() && !$parentFolder) {
                // restore folder in it's original parent folder path
                $parents = explode('/', $folder->getTrashedFolderPath());

                $folderRepository = $em->getRepository('AzimutMediacenterBundle:Folder');

                $parentParentFolder = null;

                // recreate folder structure
                $parentsCount = count($parents);
                for ($i=0;$i<$parentsCount;$i++) {
                    if ($parentFolderName = $parents[$i]) {
                        if (!$parentFolder = $folderRepository->findOneNotTrashedByNameInFolder($parentFolderName, $parentParentFolder)) {
                            $parentFolder = new Folder();
                            $parentFolder->setName($parentFolderName);
                            $parentFolder->setParentFolder($parentParentFolder);
                        }
                        $parentParentFolder = $parentFolder;
                    }
                }
                $parentFolder->addSubfolder($folder);
                $folder->setTrashedFolderPath(null);
            }

            //check if a file or folder has the same name
            if ($newName = $this->checkExistingName($folder)) {
                $folder->setName($newName);
            }

            $em->flush();

            return array(
                'folder' => $folder
            );
        }

        return array(
            'form' => $form,
        );
    }

    protected function checkExistingName($folder)
    {
        $name = $folder->getName();
        $excludeId = $folder->getId();
        $parentFolderId = $folder->getParentFolderId();

        $em = $this->getDoctrine()->getManager();
        $folderRepository = $em->getRepository('AzimutMediacenterBundle:Folder');
        $mediaRepository = $em->getRepository('AzimutMediacenterBundle:Media');

        $i = 0;
        $newName = $name;

        do {
            $folder = $folderRepository->findOneByNameInFolderExcludingFolder($newName, $parentFolderId, $excludeId);
            $media = $mediaRepository->findOneByNameInFolder($newName, $parentFolderId);

            if ($folder || $media) {
                $i++;
                $newName = "$name ($i)";
            }
        } while ($folder || $media);

        return $newName;
    }
}
