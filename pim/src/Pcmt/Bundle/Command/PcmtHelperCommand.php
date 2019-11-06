<?php
declare(strict_types=1);

namespace Pcmt\Bundle\Command;

use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\ProductAbstractDraft;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        $modelRepo = $this->getContainer()->get('pim_catalog.repository.product_model');
        $pcmtFamilyRepo = $this->getContainer()->get('pcmt_catalog.repository.family_variant');
        $pcmtFamilyRepo = $this->getEntityManager()->getRepository(FamilyVariant::class);

        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.concatenated_attribute');
        dump($converter);
        die;

        dump(get_class($pcmtFamilyRepo));
        die;
        $model = $modelRepo->find(1);
        $familyId = $pcmtFamilyRepo->getFamilyByFamilyVariant($model->getFamilyVariant());
        dump($familyId);
        die;

        $user = $userRepository->find('1');

        $drafts = $draftRepository->getUserDrafts($user);
        $return = [];

        foreach ($drafts as $draft){
            $return[$draft->getId()]['product'] = $draft->getProductData()['identifier'];
            $return[$draft->getId()]['createdAt'] = $draft->getCreatedAt();
            $return[$draft->getId()]['author'] = $user->getFirstName() . ' ' . $user->getLastName();
        }
        dump($return);
        die;

    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }
}