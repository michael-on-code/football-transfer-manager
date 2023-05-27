<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 *
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function save(Team $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Team $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTotalTeams(){
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT
        COUNT(id) AS nbr
      FROM team
      ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchOne();
    }

    public function findExcept($teamID){
        $qb = $this->createQueryBuilder('t')
        ->where('t.id <> :teamId')
        ->setParameter('teamId', $teamID)
        ->getQuery();
        return $qb->getResult();
    }

    public function getAllTeamsForTeamsPage(){
        $conn = $this->getEntityManager()->getConnection();
        /* $sql = "SELECT t.id AS team_id, t.name AS team_name, p.id AS player_id, p.first_name, p.last_name
        FROM team t
        LEFT JOIN (
            SELECT id, team_id, first_name, last_name, created_at
            FROM player
            ORDER BY team_id DESC
        ) p ON t.id = p.team_id
        ORDER BY t.id, p.team_id DESC"; */
        /* $sql = "SELECT t.id AS team_id, t.name AS team_name, p.id AS player_id, p.first_name, p.last_name
        FROM team t
        LEFT JOIN (
            SELECT id, team_id, first_name, last_name, created_at
            FROM player
            ORDER BY team_id DESC
        ) p ON t.id = p.team_id 
        ORDER BY t.id, p.created_at DESC "; */
        $sql = "SELECT
        t.id AS team_id,
        t.name AS team_name,
        t.logo as team_logo,
        t.country as team_country,
        p.id AS player_id,
        p.surname,
        p.country as player_country,
        p.created_at as player_created_at,
        p.first_name as player_first_name,
        p.last_name as player_last_name,
        o.created_at as operation_created_at,
        p.photo,
        COUNT(DISTINCT p.id) AS player_count,
        COALESCE(SUM(CASE WHEN o.name = 'CREDIT' THEN o.amount ELSE -o.amount END), 0) AS total_balance
      FROM team t
      LEFT JOIN (
                  SELECT *
                  FROM player
              ) p ON t.id = p.team_id
      LEFT JOIN (
                  SELECT *
                  FROM operation
                  ORDER BY created_at DESC
              ) o ON t.id = o.team_id
      GROUP BY t.id, t.name,t.logo,t.country, p.id, p.surname, p.photo, p.country,p.first_name,p.last_name
      ORDER BY t.id DESC;
      ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }

//    /**
//     * @return Team[] Returns an array of Team objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Team
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
