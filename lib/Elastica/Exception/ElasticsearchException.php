<?php

namespace Elastica\Exception;

\trigger_error('Elastica\Exception\ElasticsearchException is deprecated. Use Elastica\Exception\ResponseException::getResponse::getFullError instead.', E_USER_DEPRECATED);

/**
 * Elasticsearch exception.
 *
 * @author Ian Babrou <ibobrik@gmail.com>
 */
class ElasticsearchException extends \Exception implements ExceptionInterface
{
    const REMOTE_TRANSPORT_EXCEPTION = 'RemoteTransportException';

    /**
     * @var string|null Elasticsearch exception name
     */
    private $_exception;

    /**
     * @var bool Whether exception was local to server node or remote
     */
    private $_isRemote = false;

    /**
     * @var array Error array
     */
    protected $_error = [];

    /**
     * Constructs elasticsearch exception.
     *
     * @param int    $code  Error code
     * @param string $error Error message from elasticsearch
     */
    public function __construct(int $code, string $error)
    {
        $this->_parseError($error);
        parent::__construct($error, $code);
    }

    /**
     * Parse error message from elasticsearch.
     *
     * @param string $error Error message
     */
    protected function _parseError(string $error)
    {
        $errors = \explode(']; nested: ', $error);

        if (1 === \count($errors)) {
            $this->_exception = $this->_extractException($errors[0]);
        } else {
            if (self::REMOTE_TRANSPORT_EXCEPTION === $this->_extractException($errors[0])) {
                $this->_isRemote = true;
                $this->_exception = $this->_extractException($errors[1]);
            } else {
                $this->_exception = $this->_extractException($errors[0]);
            }
        }
    }

    /**
     * Extract exception name from error response.
     *
     * @return string|null
     */
    protected function _extractException(string $error)
    {
        if (\preg_match('/^(\w+)\[.*\]/', $error, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Returns elasticsearch exception name.
     *
     * @return string|null
     */
    public function getExceptionName()
    {
        return $this->_exception;
    }

    /**
     * Returns whether exception was local to server node or remote.
     */
    public function isRemoteTransportException(): bool
    {
        return $this->_isRemote;
    }

    /**
     * @return array Error array
     */
    public function getError(): array
    {
        return $this->_error;
    }
}
