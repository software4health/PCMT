<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class DraftViolationException extends UnprocessableEntityHttpException
{
    /** @var ConstraintViolationListInterface */
    protected $violations;

    /** @var ProductInterface */
    private $product;

    public function __construct(
        ConstraintViolationListInterface $violations,
        ProductInterface $product,
        $message = 'Validation failed.',
        ?\Throwable $previous = null,
        $code = 0
    ) {
        parent::__construct($message, $previous, $code);

        $this->violations = $violations;
        $this->product = $product;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }
}