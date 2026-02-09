<?php

// src/Repository/Supplier/SupplierDataRepository.php

namespace App\Repository\Supplier;

use App\Entity\Supplier\SupplierData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SupplierData|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupplierData|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupplierData[]    findAll()
 * @method SupplierData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupplierDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $_em)
    {
        parent::__construct($registry, SupplierData::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(SupplierData $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(SupplierData $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
