#REST API Component

A REST(Representational State Transfer) library to help rapidly develop RESTful web service SDKs. Supports
serilization of response and request to objects.

##Installation

```bash
composer require cct-marketing/rest
```

###Serilizer Libraries (Optional)
If no serilizer is install all responses will be returned as an array

**Recommended** [JMS Seriliser](https://github.com/schmittjoh/serializer)
```bash
composer require jms/serializer
```

**Alternative** [Symfony Seriliser](https://symfony.com/doc/current/components/serializer.html)
```bash
composer require symfony/serializer
composer require symfony/property-access
```

##Usage
To use the library you just need to extend the AbstractClient and AbstractRequest classes


```php
use CCT\Component\Rest\Config;
use CCT\Component\Rest\Http\Definition\RequestHeaders;
use CCT\Component\Rest\Http\Request;
use CCT\Component\Rest\Serializer\Context\Context;

class MyRequest extends Request
{
    protected function setUp()
    {
        $this->config->set(Config::URI_PREFIX, '/test/');
    }

    public function apiCall(QueryParams $queryParams = null)
    {
        $this->config->set('serialization_context', Context::create()->setGroups(['read']));

        $headers = RequestHeaders::create(
            [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $this->setHeaders($headers);

        return parent::requestGet($this->getUri(), $queryParams);
    }
}
```

```php
use CCT\Component\Rest\AbstractClient;
use CCT\Component\Rest\Config;

class RESTClient extends AbstractClient
{
    /**
     * @return ScrapeRequest
     */
    public function myAPI(): MyRequest
    {
        $config = clone $this->config;
        $modelClass = TestModel::class;

        $serializer = $this->getBuiltSerializer($config);
        if ($this->shouldUseDefaultResponseTransformers() && null !== $serializer) {
            $this->applyDefaultResponseTransformers($config, $serializer, $modelClass);
        }

        return $this->createRequestInstance(TestRequest::class, $config, null);
    }
}
```

To run:

```PHP
$config = new \CCT\Component\Rest\Config([
    \CCT\Component\Rest\Config::ENDPOINT => 'https://web-scraping.cct.marketing/',
    \CCT\Component\Rest\Config::DEBUG => true,
]);

/**
 * Send Request
 */
$client = new Client($config);
$query = new QueryParams();
$query->set('message', 'hello');
$response = $client->myAPI()->apiCall($query);

var_dump($response->getData());
```
