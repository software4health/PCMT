<?php
declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;

class XmlReferenceDataDownload implements ConstraintCollectionProviderInterface
{
    /** @var array $supportedJobNames */
    protected $supportedJobNames;

    public function __construct(
        array $supportedJobNames
    )
    {
        $this->supportedJobNames = $supportedJobNames;
    }


    public function getConstraintCollection()
    {
        return new Collection([ 'fields' => [
            'filePath' => []
        ] ]);
    }

    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}