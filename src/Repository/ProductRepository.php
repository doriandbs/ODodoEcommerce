<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }


    public function findProductsGroupedByCategory(array $categoryIds = [], ?string $priceMin = null, ?string $priceMax = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p, c')
            ->join('p.category', 'c')
            ->where('p.deletedDate IS NULL')
            ->orderBy('c.name', 'ASC');

        if ($categoryIds) {
            $qb->andWhere('c.id IN (:categoryIds)')
                ->setParameter('categoryIds', $categoryIds);
        }

        if ($priceMin !== null) {
            $qb->andWhere('p.priceHT >= :priceMin')
                ->setParameter('priceMin', $priceMin);
        }

        if ($priceMax !== null) {
            $qb->andWhere('p.priceHT <= :priceMax')
                ->setParameter('priceMax', $priceMax);
        }


        $query = $qb->getQuery();
        $results = $query->getResult();

        $groupedProducts = [];
        foreach ($results as $result) {
            $category = $result->getCategory();
            if (!isset($groupedProducts[$category->getId()])) {
                $groupedProducts[$category->getId()] = [
                    'category' => $category,
                    'products' => []
                ];
            }
            $groupedProducts[$category->getId()]['products'][] = $result;
         }

    return $groupedProducts;
}

}
