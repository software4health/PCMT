<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\ProductAbstractDraft;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DraftStatusNormalizer implements NormalizerInterface
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ProductAbstractDraft $productDraft
     * @param null $format
     * @param array $context
     * @return array
     */
    public function normalize($productDraft, $format = null, array $context = []): array
    {
        $statusId = $productDraft->getStatus();
        try {
            $name = $this->getName($statusId);
        } catch (\Exception $e) {
            $name = 'Unknown';
            $this->logger->error($e->getMessage());
        }

        return [
            'id' => $statusId,
            'name' => $name,
            'class' => $this->getCssClass($statusId),
        ];
    }

    private function getName(int $draftStatusId): string
    {
        switch ($draftStatusId) {
            case ProductDraftInterface::STATUS_NEW :
                return 'pcmt_product.draft.status_new';
            case ProductDraftInterface::STATUS_APPROVED :
                return 'pcmt_product.draft.status_approved';
            case ProductDraftInterface::STATUS_REJECTED :
                return 'pcmt_product.draft.status_rejected';
            default:
                throw new \Exception("No status name for: " . $draftStatusId);
        }
    }

    private function getCssClass(int $draftStatusId): string
    {
        switch ($draftStatusId) {
            case ProductDraftInterface::STATUS_NEW :
                return 'AknBadge--warning';
            case ProductDraftInterface::STATUS_APPROVED :
                return 'AknBadge--success';
            case ProductDraftInterface::STATUS_REJECTED :
                return 'AknBadge--important';
            default:
                return 'AknBadge--grey';
        }
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return false;
    }
}