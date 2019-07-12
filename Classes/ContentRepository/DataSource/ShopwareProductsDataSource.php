<?php
declare(strict_types = 1);
namespace Networkteam\Neos\ShopwareIntegration\ContentRepository\DataSource;

use Neos\Flow\Annotations as Flow;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Domain\Model\NodeInterface;

final class ShopwareProductsDataSource extends AbstractDataSource
{
    static protected $identifier = 'shopware-products';

    /**
     * @var GuzzleClient
     */
    private $guzzle;

    /**
     * @Flow\InjectConfiguration(package="Networkteam.Neos.ShopwareIntegration")
     * @var array
     */
    protected $shopwareSettings;

    /**
     * @param NodeInterface $node The node that is currently edited (optional)
     * @param array $arguments
     * @return array
     */
    public function getData(NodeInterface $node = null, array $arguments): array
    {

        $this->guzzle = new GuzzleClient([
            'base_uri' => $this->shopwareSettings['api'],
            'headers' => [
                'Accept' => 'application/json',
                'SW-Access-Key' => $this->shopwareSettings['key']
            ],
            'query' => [
                'sort' => 'name',
                'limit' => 500
            ],

        ]);

        try {
            $response = $this->guzzle->request('GET', 'sales-channel-api/v1/product');
        } catch (GuzzleException $exception) {
            throw new \RuntimeException(sprintf('Uri Getter: %s', $exception->getMessage()), 1560856269, $exception);
        }

        $data = $this->parseJsonResponse($response)['data'];
        $options = array_map(function($product) {
            var_dump($product['id']);
            return [
                'value' => $product['id'],
                'label' => $product['translated']['name'],
                'icon' => !$product['displayInListing'] ? 'fas fa-eye-slash' : ''
            ];
        }, $data);

        return $options;
    }

    private function parseJsonResponse(ResponseInterface $response): array
    {
        $responseBody = $response->getBody()->getContents();
        $responseData = json_decode($responseBody, true);
        if ($responseData === null) {
            throw new \RuntimeException(sprintf('Error decoding response from EMS endpoint: %s (%d)', json_last_error_msg(), json_last_error()), 1558971370);
        }
        return $responseData;
    }
}
