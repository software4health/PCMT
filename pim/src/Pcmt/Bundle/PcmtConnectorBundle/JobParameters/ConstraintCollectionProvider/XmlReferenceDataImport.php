<?php
declare(strict_types=1);

namespace Pcmt\Bundle\PcmtConnectorBundle\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;

class XmlReferenceDataImport implements ConstraintCollectionProviderInterface
{
    /** @var array $supportedJobNames */
    protected $supportedJobNames;

    public function __construct(
        array $supportedJobNames
    )
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getConstraintCollection(): Collection
    {
        return new Collection([ 'fields' => [
            'filePath' => null
        ] ]);
    }

    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}

