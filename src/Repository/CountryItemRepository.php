<?php

namespace App\Repository;

use App\Entity\Country;
use App\Entity\CountryItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CountryItem>
 *
 * @method CountryItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method CountryItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method CountryItem[]    findAll()
 * @method CountryItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CountryItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CountryItem::class);
    }

    public function save(CountryItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CountryItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function removeBy(array $criteria, bool $flush = false): void
    {
        $entities = $this->findBy($criteria);

        foreach ($entities as $ent) {
            $this->getEntityManager()->remove($ent);
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

//    public function removeByItemType(Country $country, string $type, bool $flush = false): void
//    {
//        $qb = new QueryBuilder($this->getEntityManager());
//
//        $qb->delete()->from('country_item', 'ci')
//            ->innerJoin('item_type', 'it', 'ci.item_type_id = it.id')
//            ->where('it.type = ' . $type . 'AND ci.country_id == ' . $country->getId());
//
//        $qb->getQuery()->getResult();
//    }

//    /**
//     * @return CountryItem[] Returns an array of CountryItem objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CountryItem
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
