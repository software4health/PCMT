<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;

interface DraftInterface
{
    public const DRAFT_VERSION_NEW = 1;

    public function getId(): int;

    public function getType(): string;

    public function getAuthor(): UserInterface;
}