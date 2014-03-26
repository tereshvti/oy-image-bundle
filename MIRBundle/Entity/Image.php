<?php

namespace Olabs\MIRBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Info
 *
 * @ORM\Table(name="image")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("all")
*/
class Image
{
    /**
     * Image's identifier
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Image path
     * @var string
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @Assert\File(maxSize="6000000")
     */
    private $file;


    /**
     * Width in px
     * @var int
     * @ORM\Column(name="width", type="integer")
     * @Assert\NotBlank
     * @JMS\Expose
     * @JMS\Groups({"info_list", "info_get"})
     */
    private $width;

    /**
     * Height in px
     * @var int
     * @ORM\Column(name="height", type="integer")
     * @Assert\NotBlank
     * @JMS\Expose
     * @JMS\Groups({"info_list", "info_get"})
     */
    private $height;

    /**
     * Creation date
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_date;

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"info_list", "info_get"})
     * @JMS\SerializedName("url")
     * @JMS\Type("string")
     * @return null|string
     */
    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir() . '/' . $this->path;
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web' . $this->getUploadDir();
    }

    public function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return '/images/uploads';
    }

    public function getExtension()
    {
        $name = $this->getName();
        $nameParts = explode('.', $this->getName());
        if (isset($nameParts[1])) {
            return $nameParts[1];
        }

        return null;
    }

    public function getName()
    {
        if ($this->getPath()) {
            $pathParts = explode('/', $this->getPath());
            return array_pop($pathParts);

        }

        return null;
    }

    public function getAbsolutePathDirectory()
    {
        if ($this->getAbsolutePath()) {
            $directoriesParts = explode('/', $this->getAbsolutePath());
            array_pop($directoriesParts);
            return implode('/', $directoriesParts);
        }

        return null;
    }

    public function getPathWithoutFileName()
    {
        if ($this->getPath()) {
            $pathParts = explode('/', $this->getPath());
            array_pop($pathParts);
            return implode('/', $pathParts);
        }

        return null;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $currentDateTime = new \DateTime();
        if (!$this->getCreatedDate()) {
            $this->setCreatedDate($currentDateTime);
        }
    }



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Image
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set width
     *
     * @param integer $width
     * @return Image
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer 
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     * @return Image
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer 
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set created_date
     *
     * @param \DateTime $createdDate
     * @return Image
     */
    public function setCreatedDate($createdDate)
    {
        $this->created_date = $createdDate;

        return $this;
    }

    /**
     * Get created_date
     *
     * @return \DateTime 
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }
    /**
     * @param UploadedFile $file
     * @return $this
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

}
