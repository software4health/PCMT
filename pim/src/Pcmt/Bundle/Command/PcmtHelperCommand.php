<?php
declare(strict_types=1);

namespace Pcmt\Bundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\ProductAbstractDraft;
use Pcmt\PcmtProductBundle\Entity\ProductDraftHistory;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;


class PcmtHelperCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console pcmt:command
     */
    protected static $defaultName = 'pcmt:command';

    public function configure()
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $attrRepo = $this->getContainer()->get('pim_catalog.repository.attribute');
        $productRepo = $this->getContainer()->get('pim_catalog.repository.product');
        $productUpdater = $this->getContainer()->get('pim_catalog.updater.product');
        $productSaver = $this->getContainer()->get('pim_catalog.saver.product');
        $productValidator = $this->getContainer()->get('pim_catalog.validator.product');
        $productValuesUpdater = $this->getContainer()->get('pcmt_catalog.updater.product_value_concatenated');
        $productDraftSaver = $this->getContainer()->get('pcmt_product.save.productDraft');
        $userRepository = $this->getContainer()->get('pim_user.repository.user');
        $draftRepository = $this->getEntityManager()->getRepository(ProductAbstractDraft::class);

        $user = $userRepository->find('1');

        $drafts = $draftRepository->getUserDrafts($user);
        dump($drafts);
        die;

    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }
}