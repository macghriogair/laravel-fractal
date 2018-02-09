<?php
/**
 * @date    2017-07-19
 * @file    Fractal.php
 * @author  Patrick Mac Gregor <macgregor.porta@gmail.com>
 */

namespace Macgriog\Fractal;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

/**
 * A fluent decorator for \League\Fractal\Manager
 *
 * Heavily inspired by Freek Van der Herten's Fractalistic class,
 * but taking a more simplistic approach.
 * https://github.com/spatie/fractalistic/blob/master/src/Fractal.php
 */
class Fractal implements \JsonSerializable
{
    protected $data;

    /** @var  TransformerAbstract */
    protected $transformer;

    /** @var SerializerAbstract */
    protected $serializer;

    /** @var Manager */
    protected $manager;

    /** @var Collection|Item */
    protected $resource;

    /** @var int */
    protected $recursionLimit = 10;

    public static function create($data = null, $transformer = null, $serializer = null) : self
    {
        $instance = new static(new Manager());

        $instance->data = $data ?: null;

        $instance->transformer = $transformer;
        $instance->serializer = $serializer ?: null;

        return $instance;
    }

    /** @param \League\Fractal\Manager $manager */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function parseIncludes($params = []) : self
    {
        if (! empty($params)) {
            $this->manager->parseIncludes($params);
        }

        return $this;
    }

    public function getRequestedIncludes()
    {
        return $this->manager->getRequestedIncludes();
    }

    public function collection($data = [], TransformerAbstract $transformer = null) : self
    {
        $this->resource = new Collection($data, $transformer);

        return $this;
    }

    public function item($data = null, TransformerAbstract $transformer = null) : self
    {
        $this->resource = new Item($data, $transformer);

        return $this;
    }

    public function transformWith(TransformerAbstract $transformer) : self
    {
        $this->transformer = $transformer;

        return $this;
    }

    public function serializeWith(SerializerAbstract $serializer) : self
    {
        $this->serializer = $serializer;

        return $this;
    }

    public function setPaginator($paginator) : self
    {
        if ($paginator instanceof LengthAwarePaginator) {
            $paginator = new IlluminatePaginatorAdapter($paginator);
        }

        $this->resource->setPaginator($paginator);

        return $this;
    }

    /**
     * Return a new JSON response.
     *
     * @param  callable|int $statusCode
     * @param  callable|array $headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond($statusCode = 200, $headers = [])
    {
        $response = new JsonResponse();
        $response->setData($this->createData()->toArray());
        if (is_int($statusCode)) {
            $statusCode = function (JsonResponse $response) use ($statusCode) {
                return $response->setStatusCode($statusCode);
            };
        }
        if (is_array($headers)) {
            $headers = function (JsonResponse $response) use ($headers) {
                return $response->withHeaders($headers);
            };
        }
        if (is_callable($statusCode)) {
            $statusCode($response);
        }
        if (is_callable($headers)) {
            $headers($response);
        }
        return $response;
    }

    /**
     * Perform the transformation to json.
     *
     * @return string
     */
    public function toJson()
    {
        return $this->createData()->toJson();
    }
    /**
     * Perform the transformation to array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->createData()->toArray();
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function createData()
    {
        if (is_null($this->transformer)) {
            throw new \Exception("No transformer specified.");
        }
        if (! is_null($this->serializer)) {
            $this->manager->setSerializer($this->serializer);
        }
        $this->manager->setRecursionLimit($this->recursionLimit);

        $this->resource->setTransformer($this->transformer);

        return $this->manager->createData($this->resource);
    }
}
