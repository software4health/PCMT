<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductModelController;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Pcmt\PcmtProductBundle\Entity\AbstractDraft;
use Pcmt\PcmtProductBundle\Entity\NewProductModelDraft;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PcmtProductModelController extends ProductModelController
{
    /** @var UserContext */
    protected $userContextProtected;

    /** @var NormalizerInterface */
    protected $normalizerProtected;

    /** @var SaverInterface */
    protected $draftSaver;

    public function setUserContextProtected(UserContext $userContextProtected): void
    {
        $this->userContextProtected = $userContextProtected;
    }

    public function setNormalizerProtected(NormalizerInterface $normalizerProtected): void
    {
        $this->normalizerProtected = $normalizerProtected;
    }

    public function setDraftSaver(SaverInterface $draftSaver): void
    {
        $this->draftSaver = $draftSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);

        /**
         * at this stage we create NewDraft, populate it with data (which we will later use to create Product itself)
         * and prevent Product from being created.
         **/
        $draft = new NewProductModelDraft(
            $data,
            $this->userContextProtected->getUser(),
            new \DateTime(),
            AbstractDraft::STATUS_NEW
        );

        $this->draftSaver->save($draft);

        $normalizationContext = $this->userContextProtected->toArray() + [
            'filter_types' => [],
        ];

        return new JsonResponse($this->normalizerProtected->normalize(
            $draft->getProductModel(),
            'internal_api',
            $normalizationContext
        ));
    }
}