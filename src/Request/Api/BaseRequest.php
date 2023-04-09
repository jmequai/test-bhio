<?php

declare(strict_types=1);

namespace App\Request\Api;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 *
 */
abstract class BaseRequest
{
    /**
     * Raw json body
     * @var string
     */
    private string $_raw;

    /**
     * Assoc array from json
     * @var array
     */
    private array $_body;

    /**
     * @var Request
     */
    private Request $_request;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->_request = new Request([], [], [], $_COOKIE, [], $_SERVER);

        $this->_raw = $this->_request->getContent();

        try {
            $this->_body = \json_decode($this->_raw, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new BadRequestException('invalid request');
        }

        $result = $validator->validate($this->_body, $this->rules());

        if (count($result) > 0) {
            throw new BadRequestException((string)$result);
        }

        $ref = new \ReflectionClass($this);

        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();

            if (array_key_exists($name, $this->_body)) {
                $type = $prop->getType();

                $val = $this->_body[$name];

                if ($type) {
                    \settype($val, $type->getName());
                }

                $this->$name = $val;
            } elseif (!isset($this->{$name})) {
                $this->$name = null;
            }
        }
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->_request;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->_body;
    }

    /**
     * @return string
     */
    public function getRawBody(): string
    {
        return $this->_raw;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [];

        foreach ((new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();

            $data[$name] = $this->$name;
        }

        return $data;
    }

    /**
     * @return Collection
     */
    abstract public function rules(): Collection;
}
