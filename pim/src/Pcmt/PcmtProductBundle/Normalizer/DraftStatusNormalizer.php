<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Service\DraftStatusService;
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
    /**
     * @var DraftStatusService
     */
    private $draftStatusService;

    public function __construct(LoggerInterface $logger, DraftStatusService $draftStatusService)
    {
        $this->logger = $logger;
        $this->draftStatusService = $draftStatusService;
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
            $name = $this->draftStatusService->getNameTranslated($statusId);
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