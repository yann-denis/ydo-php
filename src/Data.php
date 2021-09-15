<?php

namespace YannDenis\YDO;

use YannDenis\YDO\DataAdapter\DataAdapter;
use YannDenis\YDO\Exception\Data\UnknowClassException;
use YannDenis\YDO\Exception\Data\UnknowTypeException;

/**
 * @author Yann DENIS <denis.yann@gmail.com>
 */
class Data
{
    /**
     * Encode value for PDO.
     *
     * @param mixed     $mValue     Value to encode.
     *
     * @return array Array of encoded value and PDO's type.
     */
    public static function encode($mValue): array
    {
        $raValueToBind = null;

        if ($mValue === null) {
            $raValueToBind = [ $mValue, \PDO::PARAM_INT ];
        } else if ($mValue instanceof DataAdapter) {
            $raValueToBind = [ $mValue->processEncode(), \PDO::PARAM_STR ];
        } else {
            switch (gettype($mValue)) {
                case 'array':
                    $raValueToBind = [ serialize($mValue), \PDO::PARAM_STR ];
                    break;
                case 'boolean':
                    $raValueToBind = [ (bool)$mValue, \PDO::PARAM_BOOL ];
                    break;
                case 'double':
                    $raValueToBind = [ (string)$mValue, \PDO::PARAM_STR ];
                    break;
                case 'integer':
                    $raValueToBind = [ (int)$mValue, \PDO::PARAM_INT ];
                    break;
                case 'object':
                    if ($mValue instanceof \DateTime) {
                        $raValueToBind = [ $mValue->format('Y-m-d H:i:s'), \PDO::PARAM_STR ];
                    } else {
                        throw new UnknowClassException('Unknow value for class ['.get_class($mValue).'].');
                    }
                    break;
                case 'string':
                    $raValueToBind = [ $mValue, \PDO::PARAM_STR ];
                    break;
                default;
                    throw new UnknowTypeException('Unknow value for type ['.gettype($mValue).'].');
                    break;
            }
        }

        return $raValueToBind;
    }

    /**
     * Decode value from PDO.
     *
     * @param string|null   $sValue Value to decode.
     * @param int           $mType Type of value.
     *
     * @return mixed Decoded value.
     */
    public static function decode(?string $sValue, $mType = null)
    {
        $rmValue = $sValue;

        if (($sValue !== null) && ($mType !== null)) {
            if (is_string($mType)) {
                $rmValue = call_user_func_array([ $mType, 'decode' ], [ $sValue ]);
            } else {
                switch ($mType) {
                    case YDO::TYPE_BOOL:
                    case YDO::TYPE_BOOLEAN:
                        $rmValue = (bool)$sValue;
                        break;
                    case YDO::TYPE_INT:
                    case YDO::TYPE_INTEGER:
                        $rmValue = (int)$sValue;
                        break;
                    case YDO::TYPE_DOUBLE:
                    case YDO::TYPE_FLOAT:
                        $rmValue = (float)$sValue;
                        break;
                    case YDO::TYPE_STR:
                    case YDO::TYPE_STRING:
                        $rmValue = (string)$sValue;
                        break;
                    case YDO::TYPE_SERIALIZE:
                    case YDO::TYPE_SERIALIZED:
                        $rmValue = unserialize($sValue);
                        break;
                    case YDO::TYPE_DATE:
                        $rmValue = \DateTime::createFromFormat('Y-m-d H:i:s', $sValue.' '.'00:00:00');
                        break;
                    case YDO::TYPE_DATETIME:
                        $rmValue = \DateTime::createFromFormat('Y-m-d H:i:s', $sValue);
                        break;
                }
            }
        }

        return $rmValue;
    }
}
