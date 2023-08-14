<?php

namespace Greenreader9\MofhClient\Tests;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Greenreader9\MofhClient\Client;
use Greenreader9\MofhClient\Exception\MofhClientHttpException;
use Greenreader9\MofhClient\Message\AvailabilityResponse;
use Greenreader9\MofhClient\Message\CreateAccountResponse;
use Greenreader9\MofhClient\Message\GetDomainUserResponse;
use Greenreader9\MofhClient\Message\GetUserDomainsResponse;
use Greenreader9\MofhClient\Message\PasswordResponse;
use Greenreader9\MofhClient\Message\SuspendResponse;
use Greenreader9\MofhClient\Message\UnsuspendResponse;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var Generator
     */
    public $faker;

    /**
     * @var MockHandler
     */
    public $guzzleMockHandler;

    /**
     * @var array
     */
    public $historyContainer;

    /**
     * @var string
     */
    public $apiUsername;

    /**
     * @var string
     */
    public $apiPassword;

    public function setUp(): void
    {
        $this->faker = FakerFactory::create();
        $this->apiUsername = base64_encode($this->faker->randomNumber());
        $this->apiPassword = base64_encode($this->faker->randomNumber());

        $this->guzzleMockHandler = new MockHandler();
        $this->historyContainer = [];

        $handlerStack = HandlerStack::create($this->guzzleMockHandler);
        $handlerStack->push(Middleware::history($this->historyContainer));
        $guzzle = new Guzzle(['handler' => $handlerStack]);

        $this->client = new Client($this->apiUsername, $this->apiPassword, 'https://panel.myownfreehost.net/xml-api/', $guzzle);
    }

    public function testCreateAccount()
    {
        $username = $this->faker->lexify('????????');
        $password = $this->faker->regexify('[a-zA-Z0-9]{8,15}');
        $email = $this->faker->email();
        $domain = $this->faker->domainName();
        $plan = $this->faker->word();

        $this->guzzleMockHandler->append(new Response());

        $response = $this->client->createAccount($username, $password, $email, $domain, $plan);
        $this->assertInstanceOf(CreateAccountResponse::class, $response);

        $this->assertCount(1, $this->historyContainer);
        $request = $this->historyContainer[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/xml-api/createacct', $request->getUri()->getPath());
        parse_str($request->getBody(), $postData);
        $this->assertEquals([
            'username' => $username,
            'password' => $password,
            'contactemail' => $email,
            'domain' => $domain,
            'plan' => $plan,
        ], $postData);
    }

    public function testSuspend()
    {
        $username = $this->faker->bothify('????_########');
        $reason = $this->faker->sentence();

        $this->guzzleMockHandler->append(new Response());

        $response = $this->client->suspend($username, $reason, true);
        $this->assertInstanceOf(SuspendResponse::class, $response);

        $this->assertCount(1, $this->historyContainer);
        $request = $this->historyContainer[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/xml-api/suspendacct', $request->getUri()->getPath());
        parse_str($request->getBody(), $postData);
        $this->assertEquals([
            'user' => $username,
            'reason' => $reason,
            'linked' => '1',
        ], $postData);
    }

    public function testUnsuspend()
    {
        $username = $this->faker->bothify('????_########');

        $this->guzzleMockHandler->append(new Response());

        $response = $this->client->unsuspend($username);
        $this->assertInstanceOf(UnsuspendResponse::class, $response);

        $this->assertCount(1, $this->historyContainer);
        $request = $this->historyContainer[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/xml-api/unsuspendacct', $request->getUri()->getPath());
        parse_str($request->getBody(), $postData);
        $this->assertEquals([
            'user' => $username,
        ], $postData);
    }

    public function testPassword()
    {
        $username = $this->faker->bothify('????_########');
        $password = $this->faker->regexify('[a-zA-Z0-9]{8,15}');

        $this->guzzleMockHandler->append(new Response());

        $response = $this->client->password($username, $password);
        $this->assertInstanceOf(PasswordResponse::class, $response);

        $this->assertCount(1, $this->historyContainer);
        $request = $this->historyContainer[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/xml-api/passwd', $request->getUri()->getPath());
        parse_str($request->getBody(), $postData);
        $this->assertEquals([
            'user' => $username,
            'pass' => $password,
        ], $postData);
    }

    public function testPasswordHttpError()
    {
        $username = $this->faker->bothify('????_########');
        $password = $this->faker->regexify('[a-zA-Z0-9]{8,15}');

        $this->guzzleMockHandler->append(new Response(500));

        $this->expectException(MofhClientHttpException::class);
        $this->client->password($username, $password);
    }

    public function testAvailability()
    {
        $domain = $this->faker->domainName();

        $this->guzzleMockHandler->append(new Response());

        $response = $this->client->availability($domain);
        $this->assertInstanceOf(AvailabilityResponse::class, $response);

        $this->assertCount(1, $this->historyContainer);
        $request = $this->historyContainer[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/xml-api/checkavailable', $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $queryData);
        $this->assertEquals([
            'api_user' => $this->apiUsername,
            'api_key' => $this->apiPassword,
            'domain' => $domain,
        ], $queryData);
    }

    public function testAvailabilityHttpError()
    {
        $domain = $this->faker->domainName();

        $this->guzzleMockHandler->append(new Response(403));

        $this->expectException(MofhClientHttpException::class);
        $this->client->availability($domain);
    }

    public function testGetUserDomains()
    {
        $username = $this->faker->bothify('????_########');

        $this->guzzleMockHandler->append(new Response());

        $response = $this->client->getUserDomains($username);
        $this->assertInstanceOf(GetUserDomainsResponse::class, $response);

        $this->assertCount(1, $this->historyContainer);
        $request = $this->historyContainer[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/xml-api/getuserdomains', $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $queryData);
        $this->assertEquals([
            'api_user' => $this->apiUsername,
            'api_key' => $this->apiPassword,
            'username' => $username,
        ], $queryData);
    }

    public function testGetDomainUser()
    {
        $domain = $this->faker->domainName();

        $this->guzzleMockHandler->append(new Response());

        $response = $this->client->getDomainUser($domain);
        $this->assertInstanceOf(GetDomainUserResponse::class, $response);

        $this->assertCount(1, $this->historyContainer);
        $request = $this->historyContainer[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/xml-api/getdomainuser', $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $queryData);
        $this->assertEquals([
            'api_user' => $this->apiUsername,
            'api_key' => $this->apiPassword,
            'domain' => $domain,
        ], $queryData);
    }
}
