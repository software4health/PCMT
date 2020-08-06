<?php
declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Asserter;
use Behatch\Context\RestContext;
use Behatch\HttpCall\Request;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Psr\Http\Message\ResponseInterface;

class BaseApiContext extends RestContext implements Context
{
    /** @var string */
    private $accessToken;

    /** @var ResponseInterface */
    private $lastResponse;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $clientIdSecret;

    use Asserter;

    use PHPMatcherAssertions;

    public function __construct(Request $request, array $parameters)
    {
        $this->username = $parameters['username'];
        $this->password = $parameters['password'];
        $this->clientIdSecret = $parameters['client_id_secret'];
        parent::__construct($request);
    }

    /**
     * @When I send a :requestMethod request to :url endpoint
     */
    public function iSendARequestToEndpoint(string $requestMethod, string $url, PyStringNode $body = null): void
    {
        $client = new GuzzleHttp\Client();
        $this->lastResponse = $client->request($requestMethod, $this->locatePath($url), [
            'headers' => [
                'Content-Type' => 'application/json',
                "Authorization" => "Bearer " . $this->accessToken,
            ],
        ]);
    }

    /**
     * @Given I receive token
     */
    public function iReceiveToken(): void
    {
        $body = json_encode([
            "username" => $this->username,
            "password" => $this->password,
            "grant_type" => "password",
        ]);
        $client = new GuzzleHttp\Client();
        $res = $client->request('POST', $this->locatePath('/api/oauth/v1/token'), [
            'headers' => [
                'Content-Type' => 'application/json',
                "Authorization" => "Basic " . $this->clientIdSecret,
            ],
            'body' => $body,
        ]);
        $resultBody = $res->getBody()->getContents();
        $data = json_decode($resultBody, true);
        $this->accessToken = $data['access_token'] ?? '';
        if (!$this->accessToken) {
            throw new Exception('No access token received.');
        }
    }

    /**
     * @Then The response status code should be :code
     */
    public function theResponseStatusCodeShouldBe(int $code)
    {
        $actual = $this->lastResponse->getStatusCode();
        $this->assertSame(
             $code,
             $actual,
            'Expected status code of ' . $code . ' does not match ' . $actual
        );
    }
}
