<?php

namespace ici\ici_tools;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WfsLayer
{
    private $base_path;
    private $layer_name;
    private $version;
    private $cql_filter;
    private $output_srs;
    private $sort_by;
    private $property_name;
    private $count;
    private $result_type = 'results';

    /**
     * WfsLayer constructor.
     * @param string $base_path
     * @param string $layer_name
     * @param string $version
     */
    public function __construct(string $base_path, string $layer_name, string $version = '2.0.0')
    {
        $this->base_path = $base_path;
        $this->layer_name = $layer_name;
        $this->version = $version;
    }

    /**
     * @param string $point
     * @param int $meters
     * @param string $geom_name
     * @return string
     */
    public static function filterByPointDistance(string $point, int $meters = 15, string $geom_name): string
    {
        return "DWITHIN(".$geom_name.", ".$point.", ".$meters.", meters)";
    }

    /**
     * @param string $cql_filter
     */
    public function setCqlFilter(string $cql_filter): void
    {
        $this->cql_filter = $cql_filter;
    }

    /**
     * @return int
     */
    public function getOutputSrs(): int
    {
        return $this->output_srs;
    }

    /**
     * @param int $output_srs
     */
    public function setOutputSrs(int $output_srs): void
    {
        $this->output_srs = $output_srs;
    }

    /**
     * @return string
     */
    public function getSortBy(): string
    {
        return $this->sort_by;
    }

    /**
     * @param string $sort_by
     */
    public function setSortBy($sort_by): void
    {
        $this->sort_by = $sort_by;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->property_name;
    }

    /**
     * @param string $property_name
     */
    public function setPropertyName(string $property_name): void
    {
        $this->property_name = $property_name;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    /**
     * @return string
     */
    public function getQueryUrl(): string
    {
        return $this->getBasePath().'?'.http_build_query($this->getQueryFields());
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->base_path;
    }

    /**
     * @param string $base_path
     */
    public function setBasePath(string $base_path): void
    {
        $this->base_path = $base_path;
    }

    /**
     * @return array
     */
    public function getQueryFields(): array
    {
        $fields = array(
            'service' => 'WFS',
            'version' => $this->getVersion(),
            'request' => 'GetFeature',
            'typeName' => $this->getLayerName(),
            'outputFormat' => 'json',
            'resultType' => $this->result_type,
        );

        if ($this->output_srs) {
            $fields['srsName'] = 'EPSG:'.$this->output_srs;
        }
        if ($this->sort_by) {
            $fields['sortBy'] = $this->sort_by;
        }
        if ($this->property_name) {
            $fields['propertyname'] = $this->property_name;
        }
        if ($this->cql_filter) {
            $fields['cql_filter'] = $this->cql_filter;
        }
        if ($this->count) {
            if ($this->getVersion()[0] > 1) {
                $fields['count'] = $this->count;
            } else {
                $fields['maxFeatures'] = $this->count;
            }
        }

        return $fields;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getLayerName(): string
    {
        return $this->layer_name;
    }

    /**
     * @param string $layer_name
     */
    public function setLayerName(string $layer_name): void
    {
        $this->layer_name = $layer_name;
    }

    /**
     * @return int|null
     */
    public function getHits(): ?int
    {
        $this->setResultType('hits');
        $httpClient = HttpClient::create();

        try {
            $response = $httpClient->request('POST', $this->getBasePath(), ['query' => $this->getQueryFields()]);
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return null;
            }
            $exp = explode('numberMatched="', $response->getContent());
            $hits = strtok($exp[1], '"');

        } catch (TransportExceptionInterface $e) {
            return null;
        }

        return $hits;
    }

    /**
     * @param string $result_type
     */
    public function setResultType(string $result_type): void
    {
        $this->result_type = $result_type;
    }

    /**
     * @param bool $geometry
     * @param bool $bbox
     * @return array
     */
    public function getPropertiesArray(bool $geometry = false, bool $bbox = false): array
    {
        $this->setResultType('results');

        $array = [];
        $n = 0;
        foreach ($this->getResults()->features as $feature) {
            foreach ($feature->properties as $prop => $value) {
                if ($bbox || $prop !== 'bbox') {
                    $array[$n][$prop] = $value;
                }
            };
            if ($geometry) {
                $array[$n]['geometry'] = $feature->geometry;
            }
            $n++;
        }

        return $array;
    }

    /**
     * @return \stdClass|null
     */
    public function getResults(): ?\stdClass
    {
        $httpClient = HttpClient::create();

        try {
            $response = $httpClient->request('POST', $this->getBasePath(), ['query' => $this->getQueryFields()]);
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return null;
            }
            $json = json_decode($response->getContent(), false);

            return $json;

        } catch (TransportExceptionInterface $e) {
            return null;
        }
    }

    public function toJson(): JsonResponse
    {
        return new JsonResponse($this);
    }

}
