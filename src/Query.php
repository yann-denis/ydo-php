<?php

namespace YannDenis\YDO;

use YannDenis\YDO\Exception\Query\EmptyINException;

/**
 * @author Yann DENIS <denis.yann@gmail.com>
 */
class Query
{
    const TYPE_SELECT       = 0;
    const TYPE_INSERT       = 1;
    const TYPE_UPDATE       = 2;
    const TYPE_DELETE       = 3;

    /**
     * Format query and values to bind.
     *
     * @param string    $sSql       Query to format.
     * @param array     $aValues    Values to format.
     * @param int       $iType      Type of query (select, insert, update or delete).
     *
     * @return array Array of formatted query and values.
     */
    public static function prepareQueryAndValues(string $sSql, array $aValues, int $iType): array
    {
        $rsSql = $sSql;
        $raValues = [];

        $fnReplaceNullValues = function($sSqlPart, $sValueName) {
            $rsSqlPart = $sSqlPart;

            $rsSqlPart = preg_replace('/[ ]*(!=|<>)[ ]*:('.$sValueName.')(\n| |,)/', ' IS NOT NULL ', $rsSqlPart);
            $rsSqlPart = preg_replace('/[ ]*(!=|<>)[ ]*:('.$sValueName.')[ ]*\)/', ' IS NOT NULL)', $rsSqlPart);
            $rsSqlPart = preg_replace('/[ ]*(!=|<>)[ ]*:('.$sValueName.')$/', ' IS NOT NULL', $rsSqlPart);

            $rsSqlPart = preg_replace('/[ ]*=[ ]*:('.$sValueName.')(\n| |,)/', ' IS NULL ', $rsSqlPart);
            $rsSqlPart = preg_replace('/[ ]*=[ ]*:('.$sValueName.')[ ]*\)/', ' IS NULL)', $rsSqlPart);
            $rsSqlPart = preg_replace('/[ ]*=[ ]*:('.$sValueName.')$/', ' IS NULL', $rsSqlPart);

            return $rsSqlPart;
        };

        foreach ($aValues as $sValueName=>$mValue) {
            if (is_iterable($mValue) && is_countable($mValue) && preg_match('/\([ ]*:('.$sValueName.')[ ]*\)/i', $rsSql)) {
                if (count($mValue) > 0) {
                    $sNewValueName = '';

                    $i = 0;

                    foreach ($mValue as $mSubValue) {
                        if ($sNewValueName !== '') {
                            $sNewValueName .= ', ';
                        }

                        $sNewValueName .= ':'.$sValueName.'_'.$i;

                        $raValues[$sValueName.'_'.$i] = Data::encode($mSubValue);

                        $i++;
                    }

                    $rsSql = preg_replace('/\([ ]*:('.$sValueName.')[ ]*\)/', '('.$sNewValueName.')', $rsSql);
                } else {
                    throw new EmptyINException('Can\'t bind IN query with an empty array ('.$sValueName.').');
                }
            } else if ($mValue === null) {
                switch ($iType) {
                    case Query::TYPE_SELECT:
                        $rsSql = $fnReplaceNullValues($rsSql, $sValueName);
                        break;
                    case Query::TYPE_INSERT:
                        // TODO Fix INSERT .. SELECT queries
                        $raValues[$sValueName] = Data::encode($mValue);
                        break;
                    case Query::TYPE_UPDATE:
                        $rsSql = preg_replace_callback('/^([\n ]*)UPDATE(.*)(\n| )SET(\n| )/is', function($aMatch) use ($fnReplaceNullValues, $sValueName) {
                            return $aMatch[1].'UPDATE'.$fnReplaceNullValues($aMatch[2], $sValueName).'SET'.$aMatch[3].$aMatch[4];
                        }, $rsSql);

                        $rsSql = preg_replace_callback('/(\n| )WHERE(\n| )(.*)/is', function($aMatch) use ($fnReplaceNullValues, $sValueName) {
                            return $aMatch[1].'WHERE'.$aMatch[2].$fnReplaceNullValues($aMatch[3], $sValueName);
                        }, $rsSql);

                        if (preg_match('/:('.$sValueName.')(\n| |,)/', $rsSql)) {
                            $raValues[$sValueName] = Data::encode($mValue);
                        }
                        break;
                    case Query::TYPE_DELETE:
                        $rsSql = $fnReplaceNullValues($rsSql, $sValueName);
                        break;
                }
            } else {
                $raValues[$sValueName] = Data::encode($mValue);
            }
        }

        return [
            $rsSql,
            $raValues,
        ];
    }
}
