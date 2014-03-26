<?php

namespace Olabs\MIRBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

/**
 * EntityImageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EntityImageRepository extends EntityRepository
{
    /**
     * @param int $entityName
     * @param int $entityId
     * @return ArrayCollection
     */
    public function getImages($entityName, $entityId)
    {
        //it's hard way. But we skip orm cache and such things like detach and merge of entity
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $qb
            ->select('id')
            ->from('entity_image', 'ei')
            ->where('ei.entity_name = :entity_name')
            ->andWhere('ei.entity_id = :entity_id')
            ->setParameter(':entity_name', strtolower($entityName))
            ->setParameter(':entity_id', $entityId)
        ;
        $imagesIds = $qb->execute()->fetchAll(\PDO::FETCH_COLUMN);

        $result = $this->getEntityManager()->getRepository('OlabsMIRBundle:EntityImage')->findById($imagesIds);
        $arrayCollection = new ArrayCollection($result);

        return $arrayCollection;
    }
}