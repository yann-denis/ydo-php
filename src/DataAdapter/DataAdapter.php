<?php

namespace YannDenis\YDO\DataAdapter;

/**
 * @author Yann DENIS <denis.yann@gmail.com>
 */
abstract class DataAdapter
{
    /**
     * @var mixed
     */
    private $_mValue;

    /**
     * Constructor.
     *
     * @param mixed $mValue Value to encode.
     */
    final public function __construct($mValue = null)
    {
        $this->_mValue = $mValue;
    }

    /**
     * Encode value for PDO.
     *
     * @param mixed $mValue Value to encode.
     *
     * @return string Encoded value.
     */
    abstract protected function _encode($mValue): string;

    /**
     * Decode value from PDO.
     *
     * @param string    $sValue Value to decode.
     *
     * @return mixed Decoded value.
     */
    abstract protected function _decode(string $sValue);

    /**
     * Encode value for PDO.
     *
     * @param mixed $mValue Value to encode.
     *
     * @return DataAdapter Adapter for encoded value.
     */
    final public static function encode($mValue): DataAdapter
    {
        return new static($mValue);
    }

    /**
     * Decode value from PDO.
     *
     * @param string    $sValue Value to decode.
     *
     * @return mixed Decoded value.
     */
    final public static function decode(string $sValue)
    {
        static $saAdapters = [];

        if (!isset($saAdapters[static::class])) {
            $saAdapters[static::class] = new static();
        }

        return $saAdapters[static::class]->processDecode($sValue);
    }

    /**
     * Process encode process.
     *
     * @return string Encoded value.
     */
    final public function processEncode(): string
    {
        return $this->_encode($this->_mValue);
    }

    /**
     * Process decode value from PDO.
     *
     * @param string    $sValue Value to decode.
     *
     * @return mixed Decoded value.
     */
    final public function processDecode(string $mValue)
    {
        return $this->_decode($mValue);
    }
}
