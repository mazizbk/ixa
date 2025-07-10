<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-29
 */

namespace Azimut\TestContext;

use Azimut\Behat\KernelExtension\KernelAwareInterface;
use Azimut\Behat\KernelExtension\KernelFactory;
use Behat\Behat\Context\BehatContext;

class MediacenterContext extends BehatContext implements KernelAwareInterface
{
    private $kernelFactory;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize contexts
    }

    public function setKernelFactory(KernelFactory $factory)
    {
        $this->kernelFactory = $factory;
    }

    public function run(\Closure $callback)
    {
        if (null === $this->kernelFactory) {
            throw new \RuntimeException('Kernel factory missing from context.');
        }

        return $this->kernelFactory->run($callback);
    }

    /**
     * Get Mink session from MinkContext
     */
    public function getSession($name = null)
    {
        return $this->getMainContext()->getSession($name);
    }

    /**
    * @param array $params [name,id,parent_folder_id,is_root_folder,has_subfolders,has_medias]
    */
    public function getFolder($params)
    {
        $mink_session = $this->getSession();

        $folder = $this->run(function ($app) use ($params, $mink_session) {
            $repository = $app->getContainer()->get('doctrine')->getManager()->getRepository('AzimutMediacenterBundle:Folder');

            $name = !empty($params['name']) ? $params['name'] : null;
            $id = !empty($params['id']) ? $params['id'] : null;
            $is_root_folder = isset($params['is_root_folder']) ? $params['is_root_folder'] : null;
            $has_subfolders = isset($params['has_subfolders']) ? $params['has_subfolders'] : null;
            $has_medias = isset($params['has_medias']) ? $params['has_medias'] : null;
            $parent_folder_id = !empty($params['parent_folder_id']) ? $params['parent_folder_id'] : null;

            //find by name
            if ($name) {
                $folder = $repository->findOneByName($name);
                $folder_identifier_message = "Folder with name '$name'";
            }

            //find by id
            elseif ($id) {
                $folder = $repository->find($id);
                $folder_identifier_message ="Folder with id '$id'";
            } else {
                throw new \Exception("Cannot find folder, param id or name must be specified");
            }

            if (null === $folder) {
                throw new \Behat\Mink\Exception\ExpectationException("$folder_identifier_message not found.", $mink_session);
            }

            if ($id) {
                $folder_id = $folder->getId();
                if ($id != $folder_id) {
                    throw new \Behat\Mink\Exception\ExpectationException("$folder_identifier_message has not id '$id' but '$folder_id'.", $mink_session);
                }
            }

            if ($parent_folder_id) {
                $parent_folder = $folder->getParentFolder();
                if (null === $parent_folder) {
                    throw new \Behat\Mink\Exception\ExpectationException("$folder_identifier_message has no parent folder.", $mink_session);
                }

                $folder_parent_folder_id = $parent_folder->getId();
                if ($folder_parent_folder_id != $parent_folder_id) {
                    throw new \Behat\Mink\Exception\ExpectationException("$folder_identifier_message is not in folder '$parent_folder_id' but '$folder_parent_folder_id'.", $mink_session);
                }
            }

            if (null !== $is_root_folder) {
                $folder_is_root_folder = $folder->isRootFolder();
                if ($folder_is_root_folder != $is_root_folder) {
                    throw new \Behat\Mink\Exception\ExpectationException("$folder_identifier_message ".($folder_is_root_folder ? "is" : "is not")." a root folder'.", $mink_session);
                }
            }

            if (null !== $has_subfolders) {
                $folder_has_subfolders = $folder->HasSubfolders();
                if ($folder_has_subfolders != $has_subfolders) {
                    throw new \Behat\Mink\Exception\ExpectationException("$folder_identifier_message ".($folder_has_subfolders ? "has" : "has no")." subfolders'.", $mink_session);
                }
            }

            if (null !== $has_medias) {
                $folder_has_medias = $folder->HasMedias();
                if ($folder_has_medias != $has_medias) {
                    throw new \Behat\Mink\Exception\ExpectationException("$folder_identifier_message ".($folder_has_medias ? "has" : "has no")." medias'.", $mink_session);
                }
            }

            return $folder;
        });

        return $folder;
    }

    /**
    * @param array $params [type,name,id,folder_id,has_declinations,alt_text,copyright]
    */
    public function getMedia($params)
    {
        $mink_session = $this->getSession();

        $media = $this->run(function ($app) use ($params, $mink_session) {
            $repository = $app->getContainer()->get('doctrine')->getManager()->getRepository('AzimutMediacenterBundle:Media');

            $name = !empty($params['name']) ? $params['name'] : null;
            $id = !empty($params['id']) ? $params['id'] : null;
            $type = !empty($params['type']) ? $params['type'] : null;
            $folder_id = !empty($params['folder_id']) ? $params['folder_id'] : null;
            $has_declinations = isset($params['has_declinations']) ? $params['has_declinations'] : null;
            $alt_text = !empty($params['alt_text']) ? $params['alt_text'] : null;
            $copyright = !empty($params['copyright']) ? $params['copyright'] : null;

            //find by name
            if ($name) {
                $media = $repository->findOneByName($name);
                $media_identifier_message = "Media with name '$name'";
            }

            //find by id
            elseif ($id) {
                $media = $repository->find($id);
                $media_identifier_message ="Media with id '$id'";
            } else {
                throw new \Exception("Cannot find media, param id or name must be specified");
            }

            if (null === $media) {
                throw new \Behat\Mink\Exception\ExpectationException("$media_identifier_message not found.", $mink_session);
            }

            if ($id) {
                $media_id = $media->getId();
                if ($id != $media_id) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_identifier_message has not id '$id' but '$media_id'.", $mink_session);
                }
            }

            if ($type) {
                $media_type = $media::getMediaType();
                if ($type != $media_type) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_identifier_message is not of type '$type' but '$media_type'.", $mink_session);
                }
            }

            if ($folder_id) {
                $folder = $media->getFolder();
                if (null === $folder) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_identifier_message has no parent folder.", $mink_session);
                }

                $media_folder_id = $folder->getId();
                if ($folder_id != $media_folder_id) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_identifier_message is not in folder '$folder_id' but '$media_folder_id'.", $mink_session);
                }
            }

            if ($alt_text) {
                $media_alt_text = $media->getAltText();
                if ($alt_text != $media_alt_text) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_identifier_message has not alt_text '$alt_text' but '$media_alt_text'.", $mink_session);
                }
            }

            if ($copyright) {
                $media_copyright = $media->getCopyright();
                if ($copyright != $media_copyright) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_identifier_message has not copyright '$copyright' but '$media_copyright'.", $mink_session);
                }
            }

            if (null !== $has_declinations) {
                $media_has_declinations = $media->hasMediaDeclinations();
                if ($media_has_declinations != $has_declinations) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_identifier_message ".($media_has_declinations ? "has" : "has no")." declinations'.", $mink_session);
                }
            }

            return $media;
        });

        return $media;
    }

    /**
    * @param array $params [type,name,id,media_id,pixel_width]
    */
    public function getMediaDeclination($params)
    {
        $mink_session = $this->getSession();

        $media_declination = $this->run(function ($app) use ($params, $mink_session) {
            $repository = $app->getContainer()->get('doctrine')->getManager()->getRepository('AzimutMediacenterBundle:MediaDeclination');

            $name = !empty($params['name']) ? $params['name'] : null;
            $id = !empty($params['id']) ? $params['id'] : null;
            $type = !empty($params['type']) ? $params['type'] : null;
            $media_id = !empty($params['media_id']) ? $params['media_id'] : null;
            $pixel_width = !empty($params['pixel_width']) ? $params['pixel_width'] : null;

            //find by name
            if ($name) {
                $media_declination = $repository->findOneByName($name);
                $media_declination_identifier_message = "Media declination with name '$name'";
            }

            //find by id
            elseif ($id) {
                $media_declination = $repository->find($id);
                $media_declination_identifier_message ="Media declination with id '$id'";
            } else {
                throw new \Exception("Cannot find media declination, param id or name must be specified");
            }

            if (null === $media_declination) {
                throw new \Behat\Mink\Exception\ExpectationException("$media_declination_identifier_message not found.", $mink_session);
            }

            if ($id) {
                $media_declination_id = $media_declination->getId();
                if ($id != $media_declination_id) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_declination_identifier_message has not id '$id' but '$media_declination_id'.", $mink_session);
                }
            }

            if ($type) {
                $media_declination_type = $media_declination::getMediaDeclinationType();
                if ($type != $media_declination_type) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_declination_identifier_message is not of type '$type' but '$media_declination_type'.", $mink_session);
                }
            }

            if ($media_id) {
                $media = $media_declination->getMedia();
                if (null === $media) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_declination_identifier_message has no parent media.", $mink_session);
                }

                $media_declination_media_id = $media->getId();
                if ($media_id != $media_declination_media_id) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_declination_identifier_message is not in media '$media_id' but '$media_declination_media_id'.", $mink_session);
                }
            }

            if ($pixel_width) {
                $media_declination_pixel_width = $media_declination->getPixelWidth();
                if ($pixel_width != $media_declination_pixel_width) {
                    throw new \Behat\Mink\Exception\ExpectationException("$media_declination_identifier_message has not a pixel width of '$pixel_width' but '$media_declination_pixel_width'.", $mink_session);
                }
            }

            return $media_declination;
        });

        return $media_declination;
    }

    /**
     * @Given /^a folder with id "([^"]*)" that does not exist$/
     */
    public function aFolderWithIdThatDoesNotExist($id)
    {
        $mink_session = $this->getSession();

        $this->run(function ($app) use ($id, $mink_session) {
            $folder = $app->getContainer()->get('doctrine')->getManager()->getRepository('AzimutMediacenterBundle:Folder')->find($id);
            if (null !== $folder) {
                throw new \Behat\Mink\Exception\ExpectationException("Folder with id '$id' found.", $mink_session);
            }
        });
    }

    /**
     * @Given /^a media with id "([^"]*)" that does not exist$/
     */
    public function aMediaWithIdThatDoesNotExist($id)
    {
        $mink_session = $this->getSession();

        $this->run(function ($app) use ($id, $mink_session) {
            $media = $app->getContainer()->get('doctrine')->getManager()->getRepository('AzimutMediacenterBundle:Media')->find($id);
            if (null !== $media) {
                throw new \Behat\Mink\Exception\ExpectationException("Media with id '$id' found.", $mink_session);
            }
        });
    }

    /**
     * @Given /^a folder with id "([^"]*)"$/
     */
    public function aFolderWithId($id)
    {
        $this->getFolder(array(
            'id' => $id
        ));
    }

    /**
     * @Given /^a folder named "([^"]*)" with id "([^"]*)"$/
     */
    public function aFolderNamedWithId($name, $id)
    {
        $this->getFolder(array(
            'name' => $name,
            'id' => $id
        ));
    }

    /**
     * @Given /^a root folder named "([^"]*)"$/
     */
    public function aRootFolderNamed($name)
    {
        $this->getFolder(array(
            'name' => $name,
            'is_root_folder' => true
        ));
    }

    /**
     * @Given /^a root folder with id "([^"]*)"$/
     */
    public function aRootFolderWithId($id)
    {
        $this->getFolder(array(
            'id' => $id,
            'is_root_folder' => true
        ));
    }

    /**
     * @Given /^a folder with id "([^"]*)" subfolder of "([^"]*)"$/
     */
    public function aFolderWithIdSubfolderOf($id, $parent_folder_id)
    {
        $this->getFolder(array(
            'id' => $id,
            'parent_folder_id' => $parent_folder_id
        ));
    }

    /**
     * @Given /^a folder named "([^"]*)" subfolder of "([^"]*)"$/
     */
    public function aFolderNamedSubfolderOf($name, $parent_folder_id)
    {
        $this->getFolder(array(
            'name' => $name,
            'parent_folder_id' => $parent_folder_id
        ));
    }

    /**
     * @Given /^a folder named "([^"]*)" with id "([^"]*)" subfolder of "([^"]*)"$/
     */
    public function aFolderNamedWithIdSubfolderOf($name, $id, $parent_folder_id)
    {
        $this->getFolder(array(
            'name' => $name,
            'id' => $id,
            'parent_folder_id' => $parent_folder_id
        ));
    }

    /**
     * @Given /^a folder with id "([^"]*)" that has subfolders$/
     */
    public function aFolderWithIdThatHasSubfolders($id)
    {
        $this->getFolder(array(
            'id' => $id,
            'has_subfolders' => true
        ));
    }

    /**
     * @Given /^a folder with id "([^"]*)" that has no subfolders$/
     */
    public function aFolderWithIdThatHasNoSubfolders($id)
    {
        $this->getFolder(array(
            'id' => $id,
            'has_subfolders' => false
        ));
    }

    /**
     * @Given /^a folder with id "([^"]*)" that has medias$/
     */
    public function aFolderWithIdThatHasMedias($id)
    {
        $this->getFolder(array(
            'id' => $id,
            'has_medias' => true
        ));
    }

    /**
     * @Given /^a folder with id "([^"]*)" that has no medias$/
     */
    public function aFolderWithIdThatHasNoMedias($id)
    {
        $this->getFolder(array(
            'id' => $id,
            'has_medias' => false
        ));
    }

    /**
     * @Given /^a media with id "([^"]*)"$/
     */
    public function aMediaWithId($id)
    {
        $this->getMedia(array(
            'id' => $id
        ));
    }

    /**
     * @Given /^a media with id "([^"]*)" in folder "([^"]*)"$/
     */
    public function aMediaWithIdInFolder($id, $folder_id)
    {
        $this->getMedia(array(
            'id' => $id,
            'folder_id' => $folder_id
        ));
    }

    /**
     * @Given /^a media of type "([^"]*)" with id "([^"]*)" in folder "([^"]*)"$/
     */
    public function aMediaOfTypeWithIdInFolder($type, $id, $folder_id)
    {
        $this->getMedia(array(
            'type' => $type,
            'id' => $id,
            'folder_id' => $folder_id
        ));
    }

    /**
     * @Given /^a media of type "([^"]*)" named "([^"]*)" with id "([^"]*)"$/
     */
    public function aMediaOfTypeNamedWithId($type, $name, $id)
    {
        $this->getMedia(array(
            'type' => $type,
            'name' => $name,
            'id' => $id
        ));
    }

    /**
     * @Given /^a media of type "([^"]*)" named "([^"]*)" with id "([^"]*)" and alt_text "([^"]*)"$/
     */
    public function aMediaOfTypeNamedWithIdAndAltText($type, $name, $id, $alt_text)
    {
        $this->getMedia(array(
            'type' => $type,
            'name' => $name,
            'id' => $id,
            'alt_text' => $alt_text
        ));
    }

    /**
     * @Given /^a media of type "([^"]*)" named "([^"]*)" with id "([^"]*)" and copyright "([^"]*)"$/
     */
    public function aMediaOfTypeNamedWithIdAndCopyright($type, $name, $id, $copyright)
    {
        $this->getMedia(array(
            'type' => $type,
            'name' => $name,
            'id' => $id,
            'copyright' => $copyright
        ));
    }

    /**
     * @Given /^a media named "([^"]*)" in folder "([^"]*)"$/
     */
    public function aMediaNamedInFolder($name, $folder_id)
    {
        $this->getMedia(array(
            'name' => $name,
            'folder_id' => $folder_id
        ));
    }

    /**
     * @Given /^a media of type "([^"]*)" named "([^"]*)" with id "([^"]*)" in folder "([^"]*)"$/
     */
    public function aMediaOfTypeNamedWithIdInFolder($type, $name, $id, $folder_id)
    {
        $this->getMedia(array(
            'type' => $type,
            'name' => $name,
            'id' => $id,
            'folder_id' => $folder_id
        ));
    }

    /**
     * @Given /^a media with id "([^"]*)" that has declinations$/
     */
    public function aMediaWithIdThatHasDeclinations($id)
    {
        $this->getMedia(array(
            'id' => $id,
            'has_declinations' => true
        ));
    }

     /**
     * @Given /^a media declination with id "([^"]*)"$/
     */
    public function aMediaDeclinationWithId($id)
    {
        $this->getMediaDeclination(array(
            'id' => $id
        ));
    }

    /**
     * @Given /^a media declination named "([^"]*)" with id "([^"]*)"$/
     */
    public function aMediaDeclinationNamedWithId($name, $id)
    {
        $this->getMediaDeclination(array(
            'name' => $name,
            'id' => $id
        ));
    }

    /**
     * @Given /^a media declination of type "([^"]*)" named "([^"]*)" with id "([^"]*)"$/
     */
    public function aMediaDeclinationOfTypeNamedWithId($type, $name, $id)
    {
        $this->getMediaDeclination(array(
            'type' => $type,
            'name' => $name,
            'id' => $id
        ));
    }

    /**
     * @Given /^a media declination named "([^"]*)" with id "([^"]*)" and pixel width "([^"]*)"$/
     */
    public function aMediaDeclinationNamedWithIdAndPixelWidth($name, $id, $pixel_width)
    {
        $this->getMediaDeclination(array(
            'name' => $name,
            'id' => $id,
            'pixel_width' => $pixel_width
        ));
    }

    /**
     * @Given /^a media declination with id "([^"]*)" that does not exist$/
     */
    public function aMediaDeclinationWithIdThatDoesNotExist($id)
    {
        $mink_session = $this->getSession();

        $this->run(function ($app) use ($id, $mink_session) {
            $media_declination = $app->getContainer()->get('doctrine')->getManager()->getRepository('AzimutMediacenterBundle:MediaDeclination')->find($id);
            if (null !== $media_declination) {
                throw new \Behat\Mink\Exception\ExpectationException("Media declination with id '$id' found.", $mink_session);
            }
        });
    }

    /**
     * @Given /^a media declination named "([^"]*)" in media "([^"]*)"$/
     */
    public function aMediaDeclinationNamedInMedia($name, $media_id)
    {
        $this->getMediaDeclination(array(
            'name' => $name,
            'media_id' => $media_id
        ));
    }

    /**
     * @Given /^a media declination with id "([^"]*)" in media "([^"]*)"$/
     */
    public function aMediaDeclinationWithIdInMedia($id, $media_id)
    {
        $this->getMediaDeclination(array(
            'id' => $id,
            'media_id' => $media_id
        ));
    }

     /**
     * @Given /^a media declination named "([^"]*)" with id "([^"]*)" in media "([^"]*)"$/
     */
    public function aMediaDeclinationNamedWithIdInMedia($name, $id, $media_id)
    {
        $this->getMediaDeclination(array(
            'name' => $name,
            'id' => $id,
            'media_id' => $media_id
        ));
    }

    /**
     * @Then /^I read FOLDER_ID in response$/
     */
    public function iReadFolderIdInResponse()
    {
        $json = $this->getMainContext()->getSubContext('JsonContext')->getJson();

        //get JSON node folder>id in response
        $folder_id = $this->getMainContext()->getSubContext('JsonContext')->evaluateJson($json, "folder.id");

        //set variable in context
        $this->getMainContext()->getSubContext('VariableContext')->setVariable('FOLDER_ID', $folder_id);
    }
}
