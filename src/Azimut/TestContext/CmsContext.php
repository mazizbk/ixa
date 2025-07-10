<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-06 15:33:15
 */

namespace Azimut\TestContext;

use Azimut\Behat\KernelExtension\KernelAwareInterface;
use Azimut\Behat\KernelExtension\KernelFactory;
use Behat\Behat\Context\BehatContext;

class CmsContext extends BehatContext implements KernelAwareInterface
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
    * @param array $params [type,id,has_attachments,title,text,author]
    */
    public function getCmsFile($params)
    {
        $mink_session = $this->getSession();

        $cms_file = $this->run(function ($app) use ($params, $mink_session) {
            $repository = $app->getContainer()->get('doctrine')->getManager()->getRepository('AzimutCmsBundle:CmsFile');

            //$name = !empty($params['name']) ? $params['name'] : null;
            $id = !empty($params['id']) ? $params['id'] : null;
            $type = !empty($params['type']) ? $params['type'] : null;
            $has_attachments = isset($params['has_attachments']) ? $params['has_attachments'] : null;
            $title = !empty($params['title']) ? $params['title'] : null;
            $text = !empty($params['text']) ? $params['text'] : null;
            $author = !empty($params['author']) ? $params['author'] : null;

            //find by id
            if ($id) {
                $cms_file = $repository->find($id);
                $cms_file_identifier_message ="CmsFile with id '$id'";
            } else {
                throw new \Exception("Cannot find cms file, param id or name must be specified");
            }

            if (null === $cms_file) {
                throw new \Behat\Mink\Exception\ExpectationException("$cms_file_identifier_message not found.", $mink_session);
            }

            if ($id) {
                $cms_file_id = $cms_file->getId();
                if ($id != $cms_file_id) {
                    throw new \Behat\Mink\Exception\ExpectationException("$cms_file_identifier_message has not id '$id' but '$cms_file_id'.", $mink_session);
                }
            }

            if ($type) {
                $cms_file_type = $cms_file->getCmsFileType();
                if ($type != $cms_file_type) {
                    throw new \Behat\Mink\Exception\ExpectationException("$cms_file_identifier_message is not of type '$type' but '$cms_file_type'.", $mink_session);
                }
            }

            if ($title) {
                $cms_file_title = $cms_file->getTitle();
                if ($title != $cms_file_title) {
                    throw new \Behat\Mink\Exception\ExpectationException("$cms_file_identifier_message has not title '$title' but '$cms_file_title'.", $mink_session);
                }
            }

            if ($text) {
                $cms_file_text = $cms_file->getText();
                if ($text != $cms_file_text) {
                    throw new \Behat\Mink\Exception\ExpectationException("$cms_file_identifier_message has not text '$text' but '$cms_file_text'.", $mink_session);
                }
            }

            if ($author) {
                $cms_file_author = $cms_file->getAuthor();
                if ($author != $cms_file_author) {
                    throw new \Behat\Mink\Exception\ExpectationException("$cms_file_identifier_message has not author '$author' but '$cms_file_author'.", $mink_session);
                }
            }

            if (null !== $has_attachments) {
                $cms_file_has_attachments = $cms_file->hasAttachments();
                if ($cms_file_has_attachments != $has_attachments) {
                    throw new \Behat\Mink\Exception\ExpectationException("$cms_file_identifier_message ".($cms_file_has_attachments ? "has" : "has no")." attachments'.", $mink_session);
                }
            }

            return $cms_file;
        });

        return $cms_file;
    }

    /**
     * @Given /^a cms_file of type "([^"]*)" with id "([^"]*)" and title "([^"]*)"$/
     */
    public function aCmsFileOfTypeWithIdAnfTitle($type, $id, $title)
    {
        $this->getCmsFile(array(
            'type' => $type,
            'id' => $id,
            'title' => $title
        ));
    }

    /**
     * @Given /^a cms_file with id "([^"]*)" that does not exist$/
     */
    public function aCmsFileWithIdThatDoesNotExist($id)
    {
        $mink_session = $this->getSession();

        $this->run(function ($app) use ($id, $mink_session) {
            $cms_file = $app->getContainer()->get('doctrine')->getManager()->getRepository('AzimutCmsBundle:CmsFile')->find($id);
            if (null !== $cms_file) {
                throw new \Behat\Mink\Exception\ExpectationException("CmsFile with id '$id' found.", $mink_session);
            }
        });
    }

    /**
     * @Given /^a cms_file of type "([^"]*)" with id "([^"]*)" and author "([^"]*)"$/
     */
    public function aCmsFileOfTypeWithIdAndAuthor($type, $id, $author)
    {
        $this->getCmsFile(array(
            'type' => $type,
            'id' => $id,
            'author' => $author
        ));
    }

    /**
     * @Given /^a cms_file with id "([^"]*)" that has attachmentss$/
     */
    public function aCmsFileWithIdThatHasAttachmentss($id)
    {
        $this->getCmsFile(array(
            'id' => $id,
            'has_attachments' => true
        ));
    }

    /**
     * @Given /^a cms_file of type "([^"]*)" with id "([^"]*)" and title "([^"]*)" and author "([^"]*)"$/
     */
    public function aCmsFileOfTypeWithIdAndTitleAndAuthor($type, $id, $title, $author)
    {
        $this->getCmsFile(array(
            'type' => $type,
            'id' => $id,
            'title' => $title,
            'author' => $author
        ));
    }
}
