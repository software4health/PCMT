<?php
declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;
use Context\Spin\SpinException;
use PcmtDraftBundle\DataFixtures\NewDraftFixture;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Asserter;
use Symfony\Component\HttpKernel\KernelInterface;
use Coduo\PHPMatcher\Factory\SimpleFactory;
use Symfony\Component\DependencyInjection\Container;

class FeatureContext extends MinkContext implements Context
{
    use Asserter;

    /** @var Container */
    private static $container;

    /** @var KernelInterface */
    protected $kernel;

    public function __construct()
    {
        $this->kernel = new AppKernel('test', true);
        $this->kernel->boot();
    }

    /**
     * @Given I am logged in as :username with password :password
     */
    public function iAmLoggedInAs($username, $password): void
    {
        $this->getSession()->visit($this->locatePath('/user/logout'));
        $this->getSession()->visit($this->locatePath('/user/login'));

        $this->spin(function (){
            return $this->getSession()->getPage()->find('css', '.AknLogin-title');
        }, 'Cannot load the log in page.');

        $this->spin(function () use ($password, $username) {
            $this->getSession()->getPage()->fillField('_username', $username);
            $this->getSession()->getPage()->fillField('_password', $password);
            $signInButton = $this->getSession()->getPage()->findButton('_submit');
            $signInButton->press();
            return $signInButton;
        }, sprintf('Cannot log in as %d.', $username));

        $this->spin(function () {
            $this->getSession()->visit($this->locatePath('/dashboard'));
            return true;
        }, 'Cant access localhost now.');
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
    public function theResponseStatusCodeShouldBe(int $code): void
    {
        $actual = $this->getSession()->getStatusCode();
        $this->assertSame(
             $code,
             $this->getSession()->getStatusCode(),
            'Expected status code of ' . $code . ' does not match ' . $actual
        );
    }

    public function spin($callable, $message)
    {
        $start   = microtime(true);
        $timeout = 7;
        $end     = $start + $timeout;

        $logThreshold      = (int) $timeout * 0.8;
        $previousException = null;
        $result            = null;
        $looping           = false;

        do {
            if ($looping) {
                sleep(1);
            }
            try {
                $result = $callable($this);
            } catch (\Exception $e) {
                $previousException = $e;
            }
            $looping = true;
        } while (
            microtime(true) < $end &&
            (null === $result || false === $result || [] === $result)
        );

        if ($previousException instanceof SpinException) {
            $message = $previousException->getMessage();
        }

        if (null === $message) {
            $message = (null !== $previousException) ? $previousException->getMessage() : 'no message';
        }

        if (null === $result || false === $result || [] === $result) {
            $infos = sprintf('Spin : timeout of %d excedeed, with message : %s', $timeout, $message);
            throw new Exception($infos, 0, $previousException);
        }

        $elapsed = microtime(true) - $start;
        if ($elapsed >= $logThreshold) {
            printf('[%s] Long spin (%d seconds) with message : %s', date('y-md H:i:s'), $elapsed, $message);
        }

        return $result;
    }

    /**
     * @BeforeScenario
     */
    private function authentication(): array
    {
        return ['PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'Admin123'];
    }
}
