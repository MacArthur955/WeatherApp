<?php

namespace App\Repository;

use App\Entity\Cities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\WeatherDownloader;

/**
 * @extends ServiceEntityRepository<Cities>
 *
 * @method Cities|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cities|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cities[]    findAll()
 * @method Cities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CitiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, WeatherDownloader $weatherDownloader)
    {
        parent::__construct($registry, Cities::class);
        $this->weatherDownloader = $weatherDownloader;
    }

    public function add(Cities $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function create(
        string $name, float $lat, float $lon, ?string $country, ?string $en, ?string $pl, bool $hasUser = false, bool $def = false
        ): Cities
    {
        $city = new Cities();
        $city->setName($name);
        $city->setLat($lat);
        $city->setLon($lon);
        $city->setLon($lon);
        $city->setHasUser($hasUser);
        $city->setDef($def);
        if ($country) $city->setCountry($country);
        if ($en) $city->setEn($en);
        if ($pl) $city->setPl($pl);
        $this->add($city, true);
        $this->weatherDownloader->downloadWeather([$city]);
        return $city;
    }

    public function remove(Cities $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getDefaultCities()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT * FROM cities city
            WHERE city.def = TRUE
        ';
        $set = $conn->prepare($sql);
        $result = $set->executeQuery();
        return $result->fetchAllAssociative();
    }

    public function getUsingCities()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT city
                FROM App\Entity\Cities city
                WHERE city.def = 1
                OR city.hasUser = 1')
            ->getResult();
    }

    public function getCity(int $id)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT * FROM cities city
            WHERE city.id = :id
        ';
        $set = $conn->prepare($sql);
        $result = $set->executeQuery(['id' => $id]);
        return $result->fetchAllAssociative()[0];
    }

    public function getCitiesById(array $cities)
    {
        $ids = [];
        foreach ($cities as $city) $ids[] = (int) $city->getId();
        $conn = $this->getEntityManager()->getConnection();
        $sql = sprintf('
            SELECT * FROM cities city
            WHERE city.id IN (%s)
        ', substr(json_encode($ids), 1, -1));
        $set = $conn->prepare($sql);
        $result = $set->executeQuery();
        return $result->fetchAllAssociative();
    }

    public function createDefaultCities()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            INSERT INTO "cities" ("id", "name", "country", "lat", "lon", "pl", "en", "temp_c", "icon", "temp_f", "def", "has_user") VALUES
            (1, "London", "GB", 51.5073219, -0.1276474, "Londyn", "London", 16, "03d", 66, 1, 1),
            (2, "Tokyo", "JP", 35.6828387, 139.7594549, "Tokio", "Tokyo", 29, "03d", 83, 1, 1),
            (3, "New York", "US", 40.7127281, -74.0060152, "Nowy Jork", "New York", 26, "03d", 72, 1, 1),
            (4, "Paris", "FR", 48.8588897, 2.3200410217201, "Paryż", NULL, 19, "01n", 66, 1, 1),
            (5, "Shanghai", "CN", 31.2322758, 121.4692071, "Szanghaj", "Shanghai", 28, "01d", 102, 1, 1),
            (6, "Istanbul", "TR", 41.0096334, 28.9651646, "Stambuł", "Istanbul", 23, "01n", 66, 1, 1),
            (7, "Buenos Aires", "AR", -34.6075682, -58.4370894, "Buenos Aires", "Buenos Aires", 16, "03d", 46, 1, 1),
            (8, "Mexico City", "MX", 19.4326296, -99.1331785, "Meksyk", "Mexico City", 14, "11d", 66, 1, 1),
            (9, "Cairo", "EG", 30.0443879, 31.2357257, "Kair", "Cairo", 28, "01n", 76, 1, 1),
            (10, "Delhi", "IN", 28.6517178, 77.2219388, NULL, "Delhi", 28, "50n", 84, 1, 1),
            (11, "Madrid", "ES", 40.4167047, -3.7035825, "Madryt", "Madrid", 28, "01n", 79, 1, 1),
            (12, "Moscow", "RU", 55.7504461, 37.6174943, "Moskwa", "Moscow", 19, "03d", 70, 1, 1),
            (13, "Miami", "US", 25.7741728, -80.19362, "Miami", "Miami", 28, "02n", 82, 0, 0),
            (14, "Singapore", "SG", 1.2904753, 103.8520359, "Singapur", "Singapore", 27, "03d", 87, 1, 1),
            (15, "Kinshasa", "CD", -4.3217055, 15.3125974, "Kinszasa", "Kinshasa", 23, "03d", 68, 0, 1);
        ';
        $conn->executeQuery($sql);
    }

    public function getCityById(int $id)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT * FROM cities city
            WHERE city.id = :id
        ';
        $set = $conn->prepare($sql);
        $result = $set->executeQuery(['id' => $id]);
        return $result->fetchAllAssociative();
    }

    public function getCitiesByLocalization(float $lat, float $lon)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT * FROM cities city
            WHERE city.lat = :lat
            AND city.lon = :lon
        ';
        $set = $conn->prepare($sql);
        $result = $set->executeQuery(['lat' => $lat, 'lon' => $lon]);
        return $result->fetchAllAssociative();
    }

//    /**
//     * @return Cities[] Returns an array of Cities objects
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

//    public function findOneBySomeField($value): ?Cities
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
