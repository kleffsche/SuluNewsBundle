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

namespace TheCadien\Bundle\SuluNewsBundle\Entity;

interface NewsInterface
{
    public function getId(): ?int;

    public function isEnabled(): bool;

    public function getTitle(): ?string;

    public function getBlocks();

    public function getCreated(): ?\DateTime;

    public function getCreator();
}
