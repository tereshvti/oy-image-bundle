<?php

namespace Olabs\MIRBundle\Service;


use Ant\ImageResizeBundle\Image\Resizer;
use Doctrine\ORM\EntityManager;
use Olabs\MIRBundle\Entity\Image;

class ImageHandler
{
    /** @var  EntityManager */
    private $em;

    /** @var Resizer  */
    private $resizer;

    public function __construct(EntityManager $em, Resizer $resizer)
    {
        $this->em = $em;
        $this->resizer = $resizer;
    }

//    public function uploadSmallImage(Image $image, $subdirectory, $resizeSizes = null)
//    {
//        if (!$resizeSizes) {
//            $resizeSizes = array();
//        }
//        $this->uploadImage($image, $subdirectory, $resizeSizes);
//    }

    public function uploadImage(Image $image, $subdirectory, $resizeSizes = null)
    {
        if (null === $image->getFile()) {
            throw new \RuntimeException("File doesn't exist");
        }

        $extension = $image->getFile()->guessExtension();
        $filename = $this->generateFileName() . '.' . $extension;
        $image->setPath($subdirectory . '/' . $filename);

//        $webDirectory = $this->get('kernel')->getRootDir() . '/../web';
//        $imageDirectory = $webDirectory . '/' . $image->getUploadRootDir() . '/' . $entityPrefix;
        $destinationDirectory = $image->getUploadRootDir() . '/' . $subdirectory;
        if (!file_exists($destinationDirectory)) {
            mkdir($destinationDirectory, 0777, true);
        }

        $image->getFile()->move($destinationDirectory, $filename);

        //resize if needed
        if ($resizeSizes) {
            $width = $resizeSizes['width'];
            $height = $resizeSizes['height'];
            /** @var \Imagine\Gd\Image $gdImage */
            $gdImage = $this->createResizedImage(
                $image->getAbsolutePath(),
                $image->getAbsolutePath(),
                $width,
                $height
            );
        } else {
            list($width, $height) = getimagesize($image->getAbsolutePath());
        }

        $image->setWidth($width);
        $image->setHeight($height);

//        dumpDie($image);

        //empty file
        $image->setFile();
    }

    public function createResizedImage($originalPath, $newPath, $width, $height)
    {
        /** @var \Imagine\Gd\Image $resizedImage */
        $resizedImage = $this->resizer->resize($originalPath, $width, $height, 'proportional');
        $resizedImage->save($newPath);
        return $resizedImage;
    }

    public function generateFileName()
    {
        return sha1(uniqid(mt_rand(), true));
    }

    public function deleteImage(Image $image)
    {
        //unset from file system
        //need to log unlink error
        @unlink($image->getAbsolutePath());
        $this->em->remove($image);
    }
}