<?php

namespace Pcmt\PcmtProductBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Service\DraftApprover;
use Pcmt\PcmtProductBundle\Service\ProductFromDraftCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DraftApproverTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testApprove(): void
    {
        $creator = $this->createMock(ProductFromDraftCreator::class);
        $creator->expects($this->once())->method('getProductToSave')->willReturn(new Product());
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $user = $this->createMock(UserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $productValidator = $this->createMock(ValidatorInterface::class);

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(0);
        $productValidator->method('validate')->willReturn($violations);

        $productSaver = $this->createMock(ProductSaver::class);

        $service = new DraftApprover($creator, $entityManager, $tokenStorage, $productSaver, $productValidator);

        $attribute1 = 'attribute1';
        $productData = [
            $attribute1 => 'NEW',
            'attribute2' => 123,
        ];
        $author = new User();
        $author->setFirstName('Alfred');
        $author->setLastName('Nobel');
        $created = new \DateTime();
        $draft = new NewProductDraft($productData, $author, $created, AbstractProductDraft::STATUS_NEW);

        $service->approve($draft);

        $this->assertEquals(AbstractProductDraft::STATUS_APPROVED, $draft->getStatus());
    }
}