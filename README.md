# AgroZamin Integrations


### Loyihalar-aro "Agrozamin" tizimlaridan foydalanishga mo'ljallangan kutubxonalar uchun asos

### O'rnatish

Ushbu kengaytmani o'rnatishning afzal usuli - [composer](http://getcomposer.org/download/) orqali.

O'rnatish uchun quyidagi buyruqni ishga tushiring:

```
php composer require --prefer-dist agrozamin/integrations "1.0.0"
```

Agar Siz composer global o'rnatgan bo'lsangiz, quyidagi buyruqni ishga tushiring:

```
composer require --prefer-dist agrozamin/integrations "1.0.0"
```

Yoki quyidagi qatorni `composer.json` faylga qo'shing:

```
"agrozamin/integrations": "^1.0.0"
```

### Sinf (class)-lar tavsifi

|  Sinf nomi   | Mavhum (abstract) | Tavsifi                                                                                                                                                                                          |
|:------------:|:-----------------:|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Integration  |        Xa         | Tizim bilan bog'lanish (integration) uchun yaratish kerak bo'lgan sinf (class) uchun mavhum-asos sinf                                                                                            |
| RequestModel |        Xa         | So'rov modelining yaratish uchun mavhum-asos sinf                                                                                                                                                |
|     DTO      |        Xa         | So'rov javobini obyekt ko'rinishida shakllantirish uchun mavhum-asos sinf                                                                                                                        |
| RequestData  |        Xa         | So'rov ma'lumotlari, so'rov yuborilishidan avval va keyin va so'rov yuborishda yuz bergan xatolik vaqtida qayta chaqiriladigan (callback) uslub (method)-lar ushbu ma'lumotlar bilan chaqiriladi |

### Na'muna

Bir tizim bilan bog'lanish (integration) uchun asosiy sinf (class). Misol tariqasida tizimning nomi `Example`

```php
namespace App\Integration\Example;

use AgroZamin\Integrations\Integration;
use AgroZamin\Integrations\Helper\Json;
use AgroZamin\Integrations\Integration;
use AgroZamin\Integrations\RequestData;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Example extends Integration {
    protected const AUTHORIZATION_HEADER_NAME = 'Authorization';

    protected string $contentType = 'application/json';

    private string $serviceToken;
    private ClientInterface $client;
    private LoggerInterface|null $logger;

    /**
     * @param string $serviceToken
     * @param ClientInterface $client
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $serviceToken, ClientInterface $client, LoggerInterface $logger = null) {
        $this->serviceToken = $serviceToken;
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    protected function serviceName(): string {
        return 'AgroZamin.Example';
    }

    /**
     * @return string
     */
    protected function apiVersion(): string {
        return '1.0.0';
    }

    /**
     * @return string[]
     */
    protected function defaultHeaders(): array {
        return [
            self::AUTHORIZATION_HEADER_NAME => $this->serviceToken,
            'Content-Type' => $this->contentType
        ];
    }

    /**
     * @return Client
     */
    protected function getClient(): Client {
        return $this->client;
    }

    /**
     * @param RequestData $requestData
     *
     * @return void
     */
    protected function onBeforeRequest(RequestData $requestData): void {
        $this->logger?->info($this->serviceName() . 'OnBeforeRequest', ['requestData' => $requestData]);
    }

    /**
     * @param RequestData $requestData
     *
     * @return void
     */
    protected function onAfterRequest(RequestData $requestData): void {
        $this->logger?->info($this->serviceName() . 'onAfterRequest', ['requestData' => $requestData]);
    }

    /**
     * @param RequestData $requestData
     *
     * @return void
     */
    protected function onThrow(RequestData $requestData): void {
        $this->logger?->error($this->serviceName() . 'onThrow', ['requestData' => $requestData]);
    }

    /**
     * @param ResponseInterface $response
     * @param Throwable $exception
     *
     * @return Throwable
     */
    protected function handleException(ResponseInterface $response, Throwable $exception): Throwable {
        $payload = Json::decode($response->getBody()->getContents());

        $message = $payload['body']['message'];
        $code = $payload['body']['code'];

        return match ($response->getStatusCode()) {
            401 => new \App\Exceptions\UnauthorizedHttpException($message, $code, $exception),
            404 => new \App\Exceptions\NotFoundHttpException($message, $code, $exception)
        };
    }
}
```

----

So'rov modelini yaratish. Misol tariqasida buyurtmalarni olish uchun `GetOrder` so'rov modeli.

```php
namespace App\Integration\Example\Request;

use AgroZamin\Integrations\RequestModel;
use App\Integration\Example\DTO\OrderDTO;

class GetOrder extends RequestModel {
    private array $_queryParams = [];

    /**
     * @return string
     */
    public function requestMethod(): string {
        return 'GET';
    }
    
    /**
     * @return string
     */
    public function url(): string {
        return 'https://example.com/api/get-order';
    }
    
    /**
     * @return array
     */
    public function queryParams(): array {
        return $this->_queryParams;
    }
    
    /**
     * @param array $data
     *
     * @return OrganizationDTO
     */
    public function buildDto(array $data): OrderDTO {
        return new OrderDTO($data['body']['Order']);
    }
    
    /**
     * @param int $id
     *
     * @return $this
     */
    public function byId(int $id): static {
        $this->_queryParams['id'] = $id;
        return $this;
    }
} 
```

---

Javob modeli `OrderDTO`-ni shakllantirish.

```php
namespace App\Integration\Example\DTO;

use AgroZamin\Integrations\DTO;

class OrderDTO extends DTO {
    public int $id;
    public float $amount;
    public float $discount;
    public array $products = [];
    
    /**
     * @return array[]
     */
    protected function properties(): array {
        return [
            'products' => [[ProductDTO::class], 'products']
        ];  
    }
}
```

----

`OrderDTO` modelining ichida joylashgan `ProductDTO` modelini shakllantirish

```php
namespace App\Integration\Example\DTO;

use AgroZamin\Integrations\DTO;

class ProductDTO extends DTO {
    public int $id;
    public string $title;
    public ImageDTO $cover;
    public float $amount;
    public string|null $description = null;
    
    /**
     * @return array[]
     */
    protected function properties(): array {
        return [
            'cover' => [ImageDTO::class, 'cover']
        ];  
    }
}
```

---

`ProductDTO` modelining ichida joylashgan `CoverDTO` modelini shakllantirish

```php
namespace App\Integration\Example\DTO;

use AgroZamin\Integrations\DTO;

class ImageDTO extends DTO {
    public string $thumbnail;
    public string $original;
}
```

### Foydalanish

Yuqoridagi na'munada ko'rsatilgan `Example` tizimidan bitta buyurtmani modelini olish.

```php
use App\Integration\Example\Example;
use App\Integration\Example\Request\GetOrder;
use App\Integration\Example\DTO\OrderDTO;
use GuzzleHttp\Client;

$exampleService = new Example('ABC...', new Client());

$orderId = 1872;

/** @var OrderDTO $orderDTO */
$orderDTO = $exampleService->requestModel((new GetOrder())->byId($orderId))->sendRequest();

// code
```