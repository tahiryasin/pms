<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Http\Encoder;

use ActiveCollab\Authentication\AuthenticationResult\Transport\TransportInterface;
use Angie\Http\Response\FileDownload\FileDownloadInterface;
use Angie\Http\Response\MovedResource\MovedResourceInterface;
use Angie\Http\Response\StaticHtmlFile\StaticHtmlFileInterface;
use Angie\Http\Response\StatusResponse\StatusResponseInterface;
use Angie\Inflector;
use AngieApplication;
use Countable;
use DataObject;
use DataObjectCollection;
use DBResult;
use ImpossibleCollectionError;
use InvalidArgumentException;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Zend\Diactoros\Stream;

/**
 * @package Angie\Http
 */
class Encoder implements EncoderInterface
{
    /**
     * @var bool
     */
    private $is_debug_or_development;

    /**
     * @param bool $is_debug_or_development
     */
    public function __construct($is_debug_or_development = false)
    {
        $this->is_debug_or_development = (bool) $is_debug_or_development;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($value, ServerRequestInterface $request, ResponseInterface $response)
    {
        if (is_int($value)) {
            $response = $response->withStatus($value);
        } elseif ($value instanceof TransportInterface) {
            $response = $this->encode($value->getPayload(), $request, $response)[1];
        } elseif ($value instanceof StaticHtmlFileInterface) {
            $response = $response
                ->withHeader('Content-Type', $this->getContentTypeHeader('text/html'))
                ->withBody($this->createBodyFromText($value->getContent()));
        } elseif ($value instanceof FileDownloadInterface) {
            $transliterated_filename = Inflector::transliterate($value->getName());

            if (empty($transliterated_filename)) {
                $transliterated_filename = 'file-transfer';
            }

            $response = $response
                ->withHeader('Content-Type', $value->getMimeType())
                ->withHeader('Content-Disposition', "{$value->getDisposition()}; filename=\"{$transliterated_filename}\"")
                ->withHeader('Content-Length', (string) filesize($value->getPath()))
                ->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Pragma', 'public')
                ->withHeader('Expires', '0')
                ->withHeader('Cache-Control', 'must-revalidate')
                ->withBody(new Stream($value->getPath()));
        } elseif ($value instanceof MovedResourceInterface) {
            $response = $response
                ->withHeader('Location', $value->getUrl())
                ->withStatus($value->getStatusCode());
        } elseif ($value instanceof StatusResponseInterface) {
            if ($value->getPayload() !== null) {
                [$request, $response] = $this->encode($value->getPayload(), $request, $response);
            }

            /** @var ResponseInterface $response */
            $response = $response->withStatus($value->getStatusCode(), $value->getReasonPhrase());
        } else {
            $response = $this->encodeDataToJson($value, $response);
        }

        return [$request, $response];
    }

    /**
     * @param  mixed             $value
     * @param  ResponseInterface $response
     * @return ResponseInterface
     */
    private function encodeDataToJson($value, ResponseInterface $response)
    {
        /** @var ResponseInterface $response */
        $response = $response->withHeader('Content-Type', $this->getContentTypeHeader('application/json'));

        if ($value instanceof DataObject) {
            $response = $response->withBody($this->createBodyFromText($this->encodeSingleDataObject($value)));
        } elseif ($value instanceof DataObjectCollection) {
            $json_records_count = 0;
            $json = $this->encodeDataCollection($value, $json_records_count);

            // For paginated collections, run count, so we have the total number of records
            if ($value->getCurrentPage() && $value->getItemsPerPage()) {
                /** @var ResponseInterface $response */
                $response = $response
                    ->withHeader('X-Angie-PaginationCurrentPage', (string) $value->getCurrentPage())
                    ->withHeader('X-Angie-PaginationItemsPerPage', (string) $value->getItemsPerPage())
                    ->withHeader('X-Angie-PaginationTotalItems', (string) $value->count());
            }

            $response = $response->withBody($this->createBodyFromText($json));
        } elseif ($value instanceof \Exception || $value instanceof \Throwable) {
            /** @var ResponseInterface $response */
            $response = $response
                ->withStatus(500)
                ->withBody($this->createBodyFromText($this->encodeException($value)));
        } elseif ($value instanceof JsonSerializable) {
            /** @var ResponseInterface $response */
            $response = $response->withBody(
                $this->createBodyFromText($this->encodeJson($value))
            );
        } elseif (is_array($value) || $value instanceof \ArrayAccess) {
            /** @var ResponseInterface $response */
            $response = $response->withBody(
                $this->createBodyFromText($this->encodeJson($value))
            );
        }

        return $response;
    }

    /**
     * @param  string $content_type
     * @return string
     */
    private function getContentTypeHeader($content_type)
    {
        return sprintf('%s; charset=utf-8', $content_type);
    }

    /**
     * Create the message body.
     *
     * @param  string|StreamInterface   $text
     * @return StreamInterface
     * @throws InvalidArgumentException if $html is neither a string or stream
     */
    private function createBodyFromText($text)
    {
        if ($text instanceof StreamInterface) {
            return $text;
        }

        if (!is_string($text)) {
            throw new InvalidArgumentException(sprintf('Invalid content (%s) provided to %s', (is_object($text) ? get_class($text) : gettype($text)), __CLASS__));
        }

        $body = new Stream('php://temp', 'wb+');
        $body->write($text);
        $body->rewind();

        return $body;
    }

    /**
     * Encode individual data object.
     *
     * @param  DataObject $object
     * @param  bool       $use_cache
     * @return string
     */
    public function encodeSingleDataObject(DataObject &$object, $use_cache = true)
    {
        return AngieApplication::cache()->getByObject($object, '__json_single', function () use (&$object, $use_cache) {
            $result = [];
            $object->describeSingleForFeather($result);

            if (empty($result)) {
                return '{"single":' . $this->encodeDataObject($object, $use_cache) . '}';
            }

            return '{"single":' . $this->encodeDataObject($object, $use_cache) . ',' . substr($this->encodeJson($result), 1);
        }, empty($use_cache));
    }

    /**
     * Encode data collection to JSON.
     *
     * @param  DataObjectCollection $collection
     * @param  int                  $records_count
     * @param  bool                 $use_cache
     * @return string
     */
    public function encodeDataCollection(DataObjectCollection &$collection, &$records_count, $use_cache = true)
    {
        try {
            $collection_result = $collection->execute();
        } catch (ImpossibleCollectionError $e) {
            $collection_result = [];
        }

        $records_count = $this->recordCountFromCollectionResult($collection_result);

        if ($collection_result instanceof DBResult) {
            $result = [];

            foreach ($collection_result as $record) {
                if ($record instanceof DataObject) {
                    $result[] = AngieApplication::cache()->getByObject(
                        $record,
                        '__json',
                        function () use ($record) {
                            return $this->encodeJson($record);
                        },
                        !$use_cache
                    );
                } else {
                    $result[] = $this->encodeJson($record);
                }
            }

            return '[' . implode(',', $result) . ']';
        } elseif ($collection_result === null) {
            return '[]';
        } elseif ($collection_result instanceof DataObject) {
            return AngieApplication::cache()->getByObject(
                $collection_result,
                '__json',
                function () use ($collection_result) {
                    return $this->encodeJson($collection_result);
                },
                !$use_cache
            );
        } else {
            return $this->encodeJson($collection_result);
        }
    }

    /**
     * Notify client on system exception.
     *
     * @param  \Exception|\Throwable $exception
     * @return string
     */
    public function encodeException($exception)
    {
        $data_to_encode = $this->exceptionToArray($exception);

        if ($exception->getPrevious()) {
            $data_to_encode['previous'] = $this->exceptionToArray($exception->getPrevious());
        }

        return $this->encodeJson($data_to_encode);
    }

    /**
     * @param  \Exception|\Throwable $exception
     * @return array
     */
    private function exceptionToArray($exception)
    {
        $result = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];

        if ($exception instanceof JsonSerializable) {
            $result = array_merge($result, $exception->jsonSerialize());
        }

        if ($this->isDebugOrDevelopment()) {
            $result = array_merge($result, [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        return $result;
    }

    /**
     * Return number of records based on collection result.
     *
     * @param  mixed $collection_result
     * @return int
     */
    private function recordCountFromCollectionResult(&$collection_result)
    {
        if ($collection_result instanceof DBResult) {
            return $collection_result->count();
        }

        if (is_array($collection_result) || $collection_result instanceof Countable) {
            return count($collection_result);
        }

        return 0;
    }

    /**
     * Encode data object.
     *
     * @param  DataObject $object
     * @param  bool       $use_cache
     * @return string
     */
    private function encodeDataObject(DataObject &$object, $use_cache = true)
    {
        return AngieApplication::cache()->getByObject($object, '__json', function () use (&$object) {
            return $this->encodeJson($object);
        }, empty($use_cache));
    }

    /**
     * Encode value to JSON.
     *
     * @param  mixed  $value
     * @return string
     */
    private function encodeJson($value)
    {
        $result = json_encode($value, JSON_PRESERVE_ZERO_FRACTION);

        if (json_last_error() !== JSON_ERROR_NONE) {
            if (is_object($value)) {
                $verbose_value_type = 'instance of ' . get_class($value) . ' class';
            } elseif (is_scalar($value)) {
                $verbose_value_type = gettype($value) . ' value "' . $value . '"';
            } elseif (is_array($value)) {
                $verbose_value_type = 'array';

                foreach ($value as $k => $v) {
                    json_encode($v);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $verbose_value_type = 'array at key "' . $k . '"';
                        break;
                    }
                }
            } else {
                $verbose_value_type = gettype($value) . ' value';
            }

            throw new RuntimeException(
                "Failed to encode {$verbose_value_type} to JSON. Reason: " . json_last_error_msg()
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isDebugOrDevelopment()
    {
        return $this->is_debug_or_development;
    }
}
