<?php

namespace Symfony\Cmf\Bundle\MediaBundle\Tests\Unit\Form\DataTransformer;

use Symfony\Cmf\Bundle\MediaBundle\Doctrine\Phpcr\File;
use Symfony\Cmf\Bundle\MediaBundle\File\UploadFileHelperInterface;
use Symfony\Cmf\Bundle\MediaBundle\Form\DataTransformer\ModelToFileTransformer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class ModelToFileTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UploadFileHelperInterface
     */
    private $uploadFileHelper;

    public function setUp()
    {
        $this->uploadFileHelper = $this->getMock(UploadFileHelperInterface::class);
    }

    public function testTransformPassFileOnly()
    {
        $transformer = new ModelToFileTransformer($this->uploadFileHelper, []);
        $file = new \stdClass();

        $result = $transformer->transform($file);

        $this->assertEquals($file, $result);
    }

    public function testReverseTransformPassNonUploadFiles()
    {
        $transformer = new ModelToFileTransformer($this->uploadFileHelper, []);
        $file = new \stdClass();

        $result = $transformer->reverseTransform($file);

        $this->assertEquals($file, $result);
    }

    public function testReverseTransformReturnHandlesFile()
    {
        $file = new File();
        $uploadFile = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $this->uploadFileHelper
            ->expects($this->once())
            ->method('handleUploadedFile')
            ->with($this->equalTo($uploadFile), $this->equalTo('Some\\Class'))
            ->will($this->returnValue($file));
        $transformer = new ModelToFileTransformer($this->uploadFileHelper, ['data_class' => 'Some\\Class']);
        $result = $transformer->reverseTransform($uploadFile);

        $this->assertEquals($file, $result);
    }

    public function testRespectOfEmptyDataFile()
    {
        $uploadFile = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $createdFile = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();
        $emptyDataFile = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();

        $this->uploadFileHelper
            ->expects($this->once())
            ->method('handleUploadedFile')
            ->with($this->equalTo($uploadFile), $this->equalTo('Some\\Class'))
            ->will($this->returnValue($createdFile));

        $createdFile->expects($this->once())->method('getContentAsStream')->will($this->returnValue('some-stream'));
        $emptyDataFile->expects($this->once())->method('setContentFromStream')->with($this->equalTo('some-stream'));
        $createdFile->expects($this->once())->method('setName')->with($this->equalTo('someNodeName'));

        $transformer = new ModelToFileTransformer($this->uploadFileHelper, [
            'data_class' => 'Some\\Class',
            'empty_data' => $emptyDataFile,
            'child_of_node' => 'someNodeName'
        ]);

        $result = $transformer->reverseTransform($uploadFile);

        $this->assertEquals($emptyDataFile, $result);
    }
}
