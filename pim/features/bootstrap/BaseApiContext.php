<?php
declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Asserter;
use PcmtDraftBundle\Entity\AbstractDraft;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Symfony\Component\HttpKernel\KernelInterface;
use PcmtDraftBundle\DataFixtures\NewDraftFixture;
use Doctrine\ORM\EntityManagerInterface;

class BaseApiContext extends MinkContext implements Context
{
    use Asserter;

    use PHPMatcherAssertions;

    /** @var KernelInterface */
    protected $kernel;

    /** @var NewDraftFixture */
    protected $draftFixture;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var Coduo\PHPMatcher\Matcher */
    protected $matcher;

    public function __construct(
        PcmtDraftBundle\DataFixtures\NewDraftFixture $draftFixture
    )
    {
        $this->kernel = new \AppKernel('test', true);
        $this->kernel->boot();
        $this->draftFixture = $draftFixture;
        $this->matcher = (new Coduo\PHPMatcher\Factory\SimpleFactory())->createMatcher();
    }

    /**
     * @Given There is a draft with status :status
     */
    public function thereIsADraftWithStatus(string $status): void
    {
        $entityManager = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');
        (new NewDraftFixture())
            ->load($entityManager);
    }

    /**
     * @When I send a :requestMethod request to :url endpoint
     */
    public function iSendARequestToEndpoint(string $requestMethod, string $url, PyStringNode $body = null): void
    {
        $body !== null ? $body->getRaw() : [];
        $this->getSession()->setBasicAuth('admin', 'Admin123');
        $client = $this->getSession()->getDriver()->getClient();
        $client->request(
            $requestMethod,
            $this->locatePath($url),
            [],
            [],
            $this->authentication(),
            $body
        );
    }

    /**
     * @Then The response status code should be :code
     */
    public function theResponseStatusCodeShouldBe(int $code)
    {
        $actual = $this->getSession()->getStatusCode();
        $this->assertSame(
             $code,
             $this->getSession()->getStatusCode(),
            'Expected status code of ' . $code . ' does not match ' . $actual
        );
    }

    /**
     * @Then The response matches expected template:
     */
    public function theResponseMatchesExpectedTemplate(PyStringNode $json): void
    {
        $actual = $this->getSession()->getPage()->getContent();
        $this->assertMatchesPattern($json->getRaw(), $actual);
    }

    /**
     * @BeforeScenario
     * purges all drafts from database
     */
    public function clearSchema()
    {
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');
        $classMetaData = $em->getClassMetadata(AbstractDraft::class);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->beginTransaction();
        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $query = $dbPlatform->getTruncateTableSql($classMetaData->getTableName());
            $connection->executeUpdate($query);
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        }
        catch (\Exception $e) {
            $connection->rollback();
        }
    }

    private function authentication(): array
    {
        return ['PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'Admin123'];
    }
}
