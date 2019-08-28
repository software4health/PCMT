<?php
declare(strict_types=1);

namespace Pcmt\Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class PcmtGenerateRefDataCommand extends ContainerAwareCommand
{
  /**
   * run inside terminal in fpm docker: bin/console $defaultName
   */
  protected static $defaultName = 'pcmt:generate-ref-data';

  public function configure()
  {
    parent::configure();
    $this->addArgument('ref-data-name', InputArgument::REQUIRED, 'The name of the reference data.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $output->writeln([
      'Reference Data Creator',
      '============',
    ]);
    // retrieve the argument value using getArgument()
    $refDataName = $input->getArgument('ref-data-name');
    $output->writeln('Reference Data: '.$refDataName);
    // set Bundle directory
    $bundleDir = dirname(__FILE__).'/../';
    // generate Entity Class
    $r = $this->createFile(
      $refDataName.'.php',
      $bundleDir.'Entity/',
      $this->generateEntity($refDataName));
    $output->writeln($r);
    // generate doctrine
    $r = $this->createFile(
      $refDataName.'.orm.yml',
      $bundleDir.'Resources/config/doctrine/',
      $this->generateDoctrine($refDataName));
    $output->writeln($r);
    // create as Attribute
    $command = $this->getApplication()->find('pcmt:generate-ref-data-attr');
    $arguments = [
      'command' => 'pcmt:generate-ref-data-attr',
      'ref-data-name'    => $refDataName
    ];
    $greetInput = new ArrayInput($arguments);
    $returnCode = $command->run($greetInput, $output);
  }

  private function createFile($fileName, $path, $content) {
    $myfile = fopen($path.$fileName, "w") or die("Unable to open file! ".$path.$fileName);
    fwrite($myfile, $content);
    fclose($myfile);
    return $path.$fileName.' created';
  }

  private function generateEntity($refDataName) {
    return
      '<?php
    declare(strict_types=1);
    
    namespace Pcmt\Bundle\Entity;
    
    use Akeneo\Pim\Structure\Component\AttributeTypes;
    
    class '.$refDataName.' extends GS1Code
    {
      public function getReferenceDataEntityType(): string
      {
        return AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT;
      }
    
      protected static function getClass(): string
      {
        return \''.$refDataName.'\';
      }
    }';
  }

  private function generateDoctrine($refDataName) {
    return
      'Pcmt\Bundle\Entity\\'.$refDataName.':
    type: entity
    repositoryClass: Pim\Bundle\CustomEntityBundle\Entity\Repository\CustomEntityRepository';
  }

}