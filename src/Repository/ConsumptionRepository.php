<?php
namespace App\Repository;

use App\Entity\Consumption;
use App\Entity\ConsumptionCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Consumption|null find($id, $lockMode = null, $lockVersion = null)
 * @method Consumption|null findOneBy(array $criteria, array $orderBy = null)
 * @method Consumption[]    findAll()
 * @method Consumption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsumptionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Consumption::class);
    }

    public function findByDatetimeInterval(\DateTime $datetimeFrom, $datetimeTo): ConsumptionCollection
    {
        $result = $this->createQueryBuilder('c')
            ->andWhere('c.datetime BETWEEN :datetimeFrom AND :datetimeTo')
            ->setParameter('datetimeFrom', $datetimeFrom)
            ->setParameter('datetimeTo', $datetimeTo)
            ->orderBy('c.datetime', 'ASC')
            ->getQuery()
            ->getResult();

        $consumptionCollection = new ConsumptionCollection();
        foreach ($result as $consumption)
            $consumptionCollection->add($consumption);

        return $consumptionCollection;
    }
}
