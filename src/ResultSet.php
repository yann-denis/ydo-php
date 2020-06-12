<?php

namespace Yann\YDO;

/**
 * @author Yann DENIS <denis.yann@gmail.com>
 */
class ResultSet implements \Iterator, \Countable
{
    /**
     * @var \PDO
     */
    private $_oYDO;

    /**
     * @var string
     */
    private $_sSql;

    /**
     * @var array
     */
    private $_aParameters;

    /**
     * @var array
     */
    private $_aResultsTypes;

    /**
     * @var int
     */
    private $_iResultType;

    /**
     * @var \PDOStatement
     */
    private $_oPDOStatement;

    /**
     * @var int
     */
    private $_iCurrentIndex;

    /**
     * @var mixed
     */
    private $_mCurrentResult;

    /**
     * @var int
     */
    private $_iNumberOfResults;

    /**
     * Constructor.
     *
     * @param YDO           $oYDO                       YDO instance.
     * @param string        $sSql                       Query.
     * @param array         $aParameters                Parameters.
     * @param string|array  $mResultsTypesOrResultType  Result(s) type(s).
     */
    public function __construct(YDO &$oYDO, string $sSql, array $aParameters = [], $mResultsTypesOrResultType = null)
    {
        $this->_oYDO = $oYDO;
        $this->_sSql = $sSql;
        $this->_aParameters = $aParameters;

        if (is_array($mResultsTypesOrResultType)) {
            $this->_aResultsTypes = $mResultsTypesOrResultType;
        } else {
            $this->_iResultType = $mResultsTypesOrResultType;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        if (!isset($this->_iCurrentIndex) || ($this->_iCurrentIndex !== 0)) {
            $this->_iCurrentIndex = -1;
            unset($this->_mCurrentResult);
            $this->_iNumberOfResults = 0;

            $this->_oPDOStatement = $this->_oYDO->exec($this->_sSql, $this->_aParameters, Query::TYPE_SELECT);

            if ($this->_oPDOStatement === null) {
                unset($this->_iCurrentIndex);
            } else {
                $this->_iNumberOfResults = $this->_oPDOStatement->rowCount();
                $this->next();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->_mCurrentResult;
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->_iCurrentIndex;
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $aRow = $this->_oPDOStatement->fetch(\PDO::FETCH_ASSOC);

        if ($aRow === false) {
            unset($this->_iCurrentIndex);
            $this->_mCurrentResult = null;
        } else {
            $this->_iCurrentIndex++;
            $this->_mCurrentResult = [];

            foreach ($aRow as $sColumnName=>$mValue) {
                if (isset($this->_aResultsTypes)) {
                    $this->_mCurrentResult[$sColumnName] = Data::decode($mValue, (isset($this->_aResultsTypes[$sColumnName]) ? $this->_aResultsTypes[$sColumnName] : null));
                } else {
                    $this->_mCurrentResult = Data::decode($mValue, $this->_iResultType);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return isset($this->_iCurrentIndex);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        if (!isset($this->_iNumberOfResults)) {
            $this->rewind();
        }

        return $this->_iNumberOfResults;
    }
}
