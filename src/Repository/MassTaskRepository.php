<?php

namespace WechatOfficialAccountMassBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WechatOfficialAccountMassBundle\Entity\MassTask;

/**
 * @method MassTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method MassTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method MassTask[]    findAll()
 * @method MassTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MassTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MassTask::class);
    }
}
