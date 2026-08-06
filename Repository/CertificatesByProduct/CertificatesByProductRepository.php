<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\CertificatesByProduct;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Event\Certificate\Date\ProductCertificateDate;
use BaksDev\Products\Product\Entity\Event\Certificate\Match\ProductCertificateMatch;
use BaksDev\Products\Product\Entity\Event\Certificate\Number\ProductCertificateNumber;
use BaksDev\Products\Product\Entity\Event\Certificate\ProductCertificate;
use BaksDev\Products\Product\Entity\Event\Certificate\Title\ProductCertificateTitle;
use BaksDev\Products\Product\Entity\Event\Certificate\Type\ProductCertificateType;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Id\ProductUid;
use Generator;


final class CertificatesByProductRepository implements CertificatesByProductInterface
{
    private ProductUid $product;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function forProduct(ProductUid $product): self
    {

        $this->product = $product;

        return $this;
    }

    /**
     * Метод возвращает информацию о сертификатах продукта
     *
     * @return Generator<CertificatesByProductResult>|bool
     */
    public function findAll(): Generator|bool
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(Product::class, 'product')
            ->where('product.id = :product')
            ->setParameter(
                key: 'product',
                value: $this->product,
                type: ProductUid::TYPE,
            );

        $dbal
            ->addSelect("CONCAT('/upload/".$dbal->table(ProductCertificate::class)."' , '/', product_certificate.name) AS name")
            ->addSelect('product_certificate.ext')
            ->addSelect('product_certificate.cdn')
            ->join(
                'product',
                ProductCertificate::class,
                'product_certificate',
                'product_certificate.event = product.event',
            );

        $dbal
            ->addSelect('product_certificate_number.value AS number')
            ->leftJoin(
                'product',
                ProductCertificateNumber::class,
                'product_certificate_number',
                'product_certificate_number.certificate = product_certificate.id',
            );

        $dbal
            ->addSelect('product_certificate_title.value AS title')
            ->leftJoin(
                'product',
                ProductCertificateTitle::class,
                'product_certificate_title',
                'product_certificate_title.certificate = product_certificate.id',
            );


        $dbal
            ->addSelect('product_certificate_date.issue')
            ->addSelect('product_certificate_date.expire')
            ->leftJoin(
                'product',
                ProductCertificateDate::class,
                'product_certificate_date',
                'product_certificate_date.certificate = product_certificate.id',
            );

        $dbal
            ->addSelect('product_certificate_type.value AS type')
            ->leftJoin(
                'product',
                ProductCertificateType::class,
                'product_certificate_type',
                'product_certificate_type.certificate = product_certificate.id',
            );

        $dbal
            ->addSelect('product_certificate_match.value AS match')
            ->leftJoin(
                'product',
                ProductCertificateMatch::class,
                'product_certificate_match',
                'product_certificate_match.certificate = product_certificate.id',
            );

        return $dbal
            // ->enableCache('Namespace', 3600)
            ->fetchAllHydrate(CertificatesByProductResult::class);
    }
}