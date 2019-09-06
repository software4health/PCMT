<?php
declare(strict_types=1);

namespace Pcmt\Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Exception\Exception;
use Pcmt\Bundle\Helper\GsCodesHelper;


class PcmtAssignAttributesToFamilyCommand extends ContainerAwareCommand
{
  /**
   * run inside terminal in fpm docker: bin/console $defaultName
   */
  protected static $defaultName = 'pcmt:assign-attr-to-family-all';

  public function configure()
  {
    parent::configure();
    $this->addArgument('family_code', InputArgument::REQUIRED, 'The code of the family.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $codeList = GsCodesHelper::getGsCodes();
    // retrieve the argument value using getArgument()
    $family_code = $input->getArgument('family_code');
    $output->writeln([
      'Assign all Reference Data Attribute to family '.$family_code,
      '============',
    ]);
    foreach ($codeList as $gsCode) {
      $output->writeln($gsCode);
      try {
        $family = $this->getContainer()->get('pim_catalog.repository.family')->findOneByIdentifier($family_code);
        $attribute = $this->getContainer()->get('pim_catalog.repository.attribute')->findOneByIdentifier($gsCode);
        $family->addAttribute($attribute);
        $this->getContainer()->get('pim_catalog.saver.family')->save($family);
      } catch (Exception $e) {
        $output->writeln($e);
      }
    }
    $output->writeln([
      'done',
      '============'
    ]);
  }

}