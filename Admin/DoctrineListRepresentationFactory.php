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

namespace TheCadien\Bundle\SuluNewsBundle\Admin;

use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;

class DoctrineListRepresentationFactory
{
    /**
     * @var RestHelperInterface
     */
    private $restHelper;

    /**
     * @var DoctrineListBuilderFactoryInterface
     */
    private $listBuilderFactory;

    /**
     * @var FieldDescriptorFactoryInterface
     */
    private $fieldDescriptorFactory;

    /**
     * @var MediaManagerInterface
     */
    private $mediaManager;

    public function __construct(
        RestHelperInterface $restHelper,
        DoctrineListBuilderFactoryInterface $listBuilderFactory,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        MediaManagerInterface $mediaManager
    ) {
        $this->restHelper = $restHelper;
        $this->listBuilderFactory = $listBuilderFactory;
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->mediaManager = $mediaManager;
    }

    /**
     * @param mixed[] $filters
     * @param mixed[] $parameters
     */
    public function createDoctrineListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = []
    ): PaginatedRepresentation {
        /** @var DoctrineFieldDescriptor[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors($resourceKey);

        $listBuilder = $this->listBuilderFactory->create($fieldDescriptors['id']->getEntityName());

        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        foreach ($parameters as $key => $value) {
            $listBuilder->setParameter($key, $value);
        }

        foreach ($filters as $key => $value) {
            $listBuilder->where($fieldDescriptors[$key], $value);
        }

        $list = $listBuilder->execute();

        $list = $this->addHeader($list, $parameters['locale']);

        return new PaginatedRepresentation(
            $list,
            $resourceKey,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            (int) $listBuilder->count()
        );
    }

    /**
     * Takes an array of contacts and resets the avatar containing the media id with
     * the actual urls to the avatars thumbnail.
     *
     * @param array  $news
     * @param string $locale
     *
     * @return array
     */
    private function addHeader($news, $locale)
    {
        $ids = array_filter(array_column($news, 'header'));
        $avatars = $this->mediaManager->getFormatUrls($ids, $locale);
        foreach ($news as $key => $oneNews) {
            if (\array_key_exists('header', $oneNews)
                && $oneNews['header']
                && \array_key_exists($oneNews['header'], $avatars)
            ) {
                $news[$key]['header'] = $avatars[$oneNews['header']];
            }
        }

        return $news;
    }
}
