<?php

namespace App\Repository;

use App\Entity\Operation;
use App\Service\Utils;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Operation>
 *
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    public function save(Operation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Operation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTotalOperationDetails(string $operationLabel){
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT
        SUM(amount) AS total_amount,
        COUNT(id) AS total_nbr
      FROM operation
      WHERE name = :operationLabel
      ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['operationLabel'=>$operationLabel]);
        return $resultSet->fetchAssociative();
    }

    public function getTotalOperationDetailsPerDate(string $operationLabel, $startDate="", $endDate=""){
        if($startDate=="" || $endDate==""){
            //GET TODAY'S OPERATION WHEN DATE PARAMETERS ARE NOT PASSED
            $currentDate = new DateTimeImmutable();
            $startDate = $currentDate->setTime(0, 0, 0)->format('Y-m-d H:i:s');
            $endDate = $currentDate->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        }
        //$debitLabel = Utils::getDebitOperationName();
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT
        SUM(amount) AS total_amount,
        COUNT(id) AS total_nbr
      FROM operation
      WHERE name = :operationLabel
        AND created_at >= :startDate AND created_at <= :endDate
      ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['startDate'=>$startDate, 
        'endDate'=>$endDate, 'operationLabel'=>$operationLabel]);
        return $resultSet->fetchAssociative();
    }

    public function getTeamBalance(string|int $teamID){
        $creditLabel = Utils::getCreditOprationName();
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT SUM(CASE WHEN name = :creditLabel THEN AMOUNT ELSE -AMOUNT END) AS balance FROM operation WHERE team_id = :teamID';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['creditLabel'=>$creditLabel, 'teamID'=>$teamID]);
        return $resultSet->fetchOne()
      ;
    }

//    /**
//     * @return Operation[] Returns an array of Operation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Operation
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
