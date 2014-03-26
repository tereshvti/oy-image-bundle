<?php

namespace Olabs\MIRBundle\Service;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Olabs\MIRBundle\Entity\Image;

class ImageGalleryManager
{
    /** @var  EntityManager */
    private $em;

    /** @var ImageHandler  */
    private $imageHandler;

    private $gallerySizes;

    public function __construct(EntityManager $em, ImageHandler $imageHandler, $gallerySizes = array())
    {
        $this->em = $em;
        $this->imageHandler = $imageHandler;
        $this->gallerySizes = $gallerySizes;
    }

    public function processEntityGallerySave($entity)
    {
        $originalImages = new ArrayCollection();

        //if update
        if ($entity->getId()) {
//            $entityRepo = $this->getEntityRepo($entity);
//            $originalImages = $entityRepo->getImages($entity->getId());
            $entityImageRepo = $this->em->getRepository('OlabsMIRBundle:EntityImage');
            $originalImages = $entityImageRepo->getImages($this->getEntityName($entity), $entity->getId());
        }

        //get new entity images
        $entityImages = $entity->getImages();

        $entityGallerySizes = $this->gallerySizes[$this->getEntityName($entity)];

        //add new images
        foreach ($entityImages as $entityImage) {
            if (false === $originalImages->contains($entityImage)) {
                //remove empty new images
                $image = $entityImage->getImage();
                if (!$image) {
                    $entity->removeImage($entityImage);
                    continue;
                }
                $uploadSubdirectory = strtolower($this->getEntityName($entity));
                $this->imageHandler->uploadImage($image, $uploadSubdirectory);
                //save resized copies
                $this->saveResizedImages($entityImage, $entityGallerySizes);
            }
        }

        //remove deleted entity images
        foreach ($originalImages as $entityImage) {
            if (false === $entityImages->contains($entityImage)) {
                $this->deleteEntityImage($entityImage);
            }
        }
    }

    /**
     * @todo create base class to all entityImages classes
     * @param $entityImage
     */
    protected function deleteEntityImage($entityImage)
    {
        //delete childs
        $childEntityImages = $entityImage->getChildren();
        foreach ($childEntityImages as $childEntityImage) {
            $this->deleteEntityImage($childEntityImage);
        }

        //delete image
        $this->imageHandler->deleteImage($entityImage->getImage());

        //delete the entity image
        $this->em->remove($entityImage);
    }

    protected function saveResizedImages($entityImage, $entityGallerySizes)
    {
        $originalImage = $entityImage->getImage();
        //put resizes image to the same directory as original's image one
        $originalDestinationDirectory = $originalImage->getAbsolutePathDirectory();
        $originalExtension = $originalImage->getExtension();

        foreach ($entityGallerySizes as $sizeCategory => $category) {
            if ($sizeCategory == 'main_thumbnail') { $category = ['thumbnail' => $category]; }
            foreach ($category as $sizeType => $size) {

                //create new resized image
                $resizedImage = new Image();
                $resizedImagePath = $originalImage->getPathWithoutFileName()
                    . '/' . $this->imageHandler->generateFileName()
                    . ($originalExtension ? '.' . $originalExtension : '')
                ;
                $resizedImage->setPath($resizedImagePath);

                //create in filesystem
                $gdImage = $this->imageHandler->createResizedImage(
                    $originalImage->getAbsolutePath(),
                    $resizedImage->getAbsolutePath(),
                    $size['width'],
                    $size['height']
                );

                //save real sizes of image
                $resizedImage->setWidth($gdImage->getSize()->getWidth());
                $resizedImage->setHeight($gdImage->getSize()->getHeight());

                //add resized image to new entityImage
                $entityImageClass = get_class($entityImage);
                $resizedEntityImage = new $entityImageClass();
                $resizedEntityImage->setParent($entityImage);
                $resizedEntityImage->setImage($resizedImage);
                $resizedEntityImage->setSizeCategory($sizeCategory);
                $resizedEntityImage->setSizeType($sizeType);

                $this->em->persist($resizedEntityImage);
            }
        }
    }

    protected function getEntityRepo($entity)
    {
        return $this->em->getRepository('OlabsMIRBundle:' . $this->getEntityName($entity));
    }

    protected function getEntityName($entity)
    {
        $nameParts = explode('\\', get_class($entity));
        return array_pop($nameParts);
    }

} 