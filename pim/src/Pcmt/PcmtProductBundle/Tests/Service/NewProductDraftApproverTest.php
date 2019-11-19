<?php


namespace Pcmt\PcmtProductBundle\Tests\Service;


use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Service\DraftApprover\NewProductDraftApprover;
use Pcmt\PcmtProductBundle\Service\NewProductFromDraftCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NewProductDraftApproverTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testApprove(): void
    {
        $creator = $this->createMock(NewProductFromDraftCreator::class);
        $creator->expects($this->once())->method("create");
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $user = $this->createMock(UserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $service = new NewProductDraftApprover($creator, $entityManager, $tokenStorage);

        $attribute1 = "attribute1";
        $productData = [
            $attribute1 => "NEW",
            "attribute2" => 123
        ];
        $author = new User();
        $author->setFirstName('Alfred');
        $author->setLastName('Nobel');
        $created = new \DateTime();
        $draft = new NewProductDraft($productData, $author, $created, 0, AbstractProductDraft::STATUS_NEW);

        $service->approve($draft);

        $this->assertEquals(AbstractProductDraft::STATUS_APPROVED, $draft->getStatus());
    }


}