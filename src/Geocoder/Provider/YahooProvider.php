<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Provider\ProviderInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class YahooProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param string $apiKey
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey, $locale = null)
    {
        parent::__construct($adapter, $locale);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getData($value, $reversed = false)
    {
        if (null === $this->apiKey) {
            throw new \RuntimeException('No API Key provided');
        }

        if ('127.0.0.1' === $value) {
            return array(
                'city'      => 'localhost',
                'region'    => 'localhost',
                'country'   => 'localhost'
            );
        }

        if ($reversed && is_array($value)) {
            $query = sprintf('http://where.yahooapis.com/geocode?q=%s,+%s&gflags=R&flags=J&appid=%s', $value[0], $value[1], $this->apiKey);
        } elseif (!is_array($value)) {
            $query = sprintf('http://where.yahooapis.com/geocode?q=%s&flags=J&appid=%s', urlencode($value), $this->apiKey);
        } else {
            return array();
        }

        if (null !== $this->getLocale()) {
            $query = sprintf('%s&locale=%s', $query, $this->getLocale());
        }

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'yahoo';
    }

    /**
     * @param string $query
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);
        $data = (array)json_decode($content)->ResultSet->Results[0];

        return array(
            'latitude'  => $data['latitude'],
            'longitude' => $data['longitude'],
            'city'      => $data['city'],
            'zipcode'   => $data['postal'],
            'region'    => $data['state'],
            'country'   => $data['country']
        );
    }
}
