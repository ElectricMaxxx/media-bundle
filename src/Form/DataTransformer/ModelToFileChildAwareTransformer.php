<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\MediaBundle\Form\DataTransformer;

use Doctrine\ODM\PHPCR\Document\File;
use Symfony\Cmf\Bundle\MediaBundle\File\UploadFileHelperInterface;
use Symfony\Cmf\Bundle\MediaBundle\FileInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ModelToFileChildAwareTransformer extends ModelToFileTransformer
{
    private $helper;

    /**
     * @var
     */
    private $emptyData;
    /**
     * @var
     */
    private $childOfNode;
    /**
     * @var array
     */
    private $class;

    /**
     * @param UploadFileHelperInterface $helper
     * @param $class
     * @param $emptyData
     * @param $childOfNode
     */
    public function __construct(UploadFileHelperInterface $helper, $class, $emptyData = null, $childOfNode = null)
    {
        parent::__construct($helper, $class);

        $this->helper = $helper;
        $this->class = $class;
        $this->emptyData = $emptyData;
        $this->childOfNode = $childOfNode;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($uploadedFile)
    {
        $file = parent::reverseTransform($uploadedFile);

        if (!$file instanceof FileInterface) {
            return $file;
        }

        if (!$this->emptyData instanceof FileInterface) {
            return $file;
        }

        if (null !== $this->childOfNode) {
            // The file node will get the file name as node name else, which conflicts in phpcr
            $this->emptyData->setName($this->childOfNode);
        }
        $this->emptyData->setContentFromStream($file->getContentAsStream());

        return $this->emptyData;
    }
}
