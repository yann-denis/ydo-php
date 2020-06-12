<?php

namespace Yann\YDO\DataAdapter\Doctrine;

use Yann\YDO\DataAdapter\DataAdapter;

/**
 * @author Yann DENIS <denis.yann@gmail.com>
 */
class SimpleArrayAdapter extends DataAdapter
{
    /**
     * {@inheritDoc}
     */
    protected function _encode($aValue): string
    {
        return implode(',', $aValue);
    }

    /**
     * {@inheritDoc}
     */
    protected function _decode(string $sValue)
    {
        $raValue = [];

        foreach (explode(',', $sValue) as $sSubValue) {
            if (((string)intval($sSubValue)) === ((string)$sSubValue)) {
                $raValue[] = intval($sSubValue);
            } else {
                $raValue[] = (string)$sSubValue;
            }
        }

        return $raValue;
    }
}
