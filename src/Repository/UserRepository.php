<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    /**
     * Check if a user with the given email or username exists
     * 
     * @param string $email
     * @param string $username
     * @return array Returns details about which fields already exist
     */
    public function checkExistingUser(string $email, string $username): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('
                CASE WHEN u.email = :email THEN true ELSE false END as emailExists,
                CASE WHEN u.username = :username THEN true ELSE false END as usernameExists
            ')
            ->where('u.email = :email OR u.username = :username')
            ->setParameter('email', $email)
            ->setParameter('username', $username)
            ->setMaxResults(1);
            
        $result = $qb->getQuery()->getOneOrNullResult();
        
        if (!$result) {
            return ['emailExists' => false, 'usernameExists' => false];
        }
        
        return $result;
    }
}