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

class ModelToFileTransformer implements DataTransformerInterface
{
    private $helper;
    private $options;

    /**
     * @param UploadFileHelperInterface $helper
     * @param array $options
     */
    public function __construct(UploadFileHelperInterface $helper, $options)
    {
        $this->helper = $helper;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($uploadedFile)
    {
        if (!$uploadedFile instanceof UploadedFile) {
            return $uploadedFile;
        }

        try {
            $file = $this->helper->handleUploadedFile($uploadedFile, $this->options['data_class']);
        } catch (UploadException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }

        if (isset($this->options['child_of_node']) && $this->options['child_of_node']) {
            $file->setName($this->options['child_of_node']);
        }

        if (!isset($this->options['empty_data']) || !$this->options['empty_data'] instanceof FileInterface) {
            return $file;
        }

        $emptyDataFile = $this->options['empty_data'];
        $emptyDataFile->setName($file->getName());
        $emptyDataFile->setContentFromStream($file->getContentAsStream());

        return $emptyDataFile;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($file)
    {
        return $file;
    }
}
