<?php

declare(strict_types=1);

/*
 * This file is part of TheCadien/SuluNewsBundle.
 *
 * (c) Oliver Kossin
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace TheCadien\Bundle\SuluNewsBundle\Tests\Unit\Repository;

use DateTime;
use Doctrine\ORM\EntityManager;
use Sulu\Bundle\TestBundle\Testing\PurgeDatabaseTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use TheCadien\Bundle\SuluNewsBundle\Entity\News;
use TheCadien\Bundle\SuluNewsBundle\Repository\NewsRepository;
use TheCadien\Bundle\SuluNewsBundle\Tests\Unit\Traits\Api\NewsTrait;

/**
 * @internal
 *
 * @coversNothing
 */
final class NewsRepositoryTest extends SuluTestCase
{
    use NewsTrait;
    use PurgeDatabaseTrait;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var NewsRepository
     */
    private $newsRepository;

    protected function setUp(): void
    {
        $this->em = $this->getEntityManager();
        $this->newsRepository = $this->em->getRepository(News::class);
        $this->purgeDatabase();
    }

    public function testSave(): void
    {
        $newsTestData = $this->generateNewsWithContent();
        $this->newsRepository->save($newsTestData);

        $newsResult = $this->newsRepository->findOneBy(['title' => $newsTestData->getTitle()]);

        static::assertSame($newsTestData->getTitle(), $newsResult->getTitle());
    }

    public function testGetPublishedNewsWithResult(): void
    {
        $newsTestData = $this->generateNewsWithContent();
        $this->newsRepository->save($newsTestData);
        $secondNewsTestData = $this->generateSecondNewsWithContent();
        $this->newsRepository->save($secondNewsTestData);

        $result = $this->newsRepository->getPublishedNews();

        static::assertSame($newsTestData->getTitle(), $result[0]->getTitle());
        static::assertSame($secondNewsTestData->getTitle(), $result[1]->getTitle());
    }

    public function testGetPublishedNewsWithEmptyDatabase(): void
    {
        $result = $this->newsRepository->getPublishedNews();

        static::assertSame([], $result);
    }

    public function testGetPublishedNewsWithoutPublishedResult(): void
    {
        /** not enabled example in   */
        $newsTestData = $this->generateNewsWithContent();
        $newsTestData->setEnabled(false);
        $this->newsRepository->save($newsTestData);

        /** enabled example in future */
        $secondNewsTestData = $this->generateSecondNewsWithContent();
        $secondNewsTestData->setPublishedAt(DateTime::createFromFormat('Y-m-d H:i:s', '3000-00-00 00:00:00'));
        $this->newsRepository->save($secondNewsTestData);

        $result = $this->newsRepository->getPublishedNews();

        static::assertSame([], $result);
    }
}
