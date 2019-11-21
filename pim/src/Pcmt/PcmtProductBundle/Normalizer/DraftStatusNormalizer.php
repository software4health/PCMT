<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\DraftStatus;
use Pcmt\PcmtProductBundle\Service\DraftStatusTranslatorService;
use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DraftStatusNormalizer implements NormalizerInterface
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DraftStatusTranslatorService
     */
    private $draftStatusService;

    public function __construct(LoggerInterface $logger, DraftStatusTranslatorService $draftStatusService)
    {
        $this->logger = $logger;
        $this->draftStatusService = $draftStatusService;
    }

    /**
     * @param DraftStatus $draftStatus
     * @param null $format
     * @param array $context
     * @return array
     */
    public function normalize($draftStatus, $format = null, array $context = []): array
    {
        $statusId = $draftStatus->getId();
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
            case AbstractProductDraft::STATUS_NEW :
                return 'AknBadge--warning';
            case AbstractProductDraft::STATUS_APPROVED :
                return 'AknBadge--success';
            case AbstractProductDraft::STATUS_REJECTED :
                return 'AknBadge--important';
            default:
                return 'AknBadge--grey';
        }
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof DraftStatus;
    }
}