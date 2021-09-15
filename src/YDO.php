<?php

namespace YannDenis\YDO;

/**
 * @author Yann DENIS <denis.yann@gmail.com>
 */
class YDO
{
    const TYPE_BOOL             = 0;
    const TYPE_BOOLEAN          = 0;
    const TYPE_INT              = 1;
    const TYPE_INTEGER          = 1;
    const TYPE_DOUBLE           = 2;
    const TYPE_FLOAT            = 2;
    const TYPE_NUMBER           = 2;
    const TYPE_STR              = 3;
    const TYPE_STRING           = 3;
    const TYPE_SERIALIZE        = 4;
    const TYPE_SERIALIZED       = 4;
    const TYPE_DATE             = 5;
    const TYPE_DATETIME         = 6;

    const QUERY_TYPE_SELECT     = 0;
    const QUERY_TYPE_INSERT     = 1;
    const QUERY_TYPE_UPDATE     = 2;
    const QUERY_TYPE_DELETE     = 3;

    /**
     * @var \PDO
     */
    private $_oPDO;

    /**
     * Constructor.
     *
     * @param \PDO|string    $mDSNOrPDO  Database's DSN or existing PDO connection.
     * @param string|null   $sUsername  Username (useless with PDO connection).
     * @param string|null   $sPassword  Password (useless with PDO connection).
     * @param array         $aOptions   PDO's options (useless with PDO connection).
     */
    public function __construct($mDSNOrPDO , ?string $sUsername = null, ?string $sPassword = null, ?array $aOptions = null)
    {
        if ($mDSNOrPDO instanceof \PDO) {
            $this->_oPDO = $mDSNOrPDO;
        } else {
            $this->_oPDO = new \PDO($mDSNOrPDO, $sUsername, $sPassword, $aOptions);
        }
    }

    /**
     * Create new YDO instance from existing PDO connection.
     *
     * @return YDO
     */
    public static function createFromPDO(\PDO &$oPDO): YDO
    {
        return new YDO($oPDO);
    }

    /**
     * Start transation.
     */
    public function startTransaction(): void
    {
        // TODO Should we make cascade transactions?
        $this->_oPDO->beginTransaction();
    }

    /**
     * Stop transation.
     */
    public function stopTransaction(bool $bCommit = true): void
    {
        // TODO Should we check a transaction is started?
        if ($bCommit) {
            $this->_oPDO->commit();
        } else {
            $this->_oPDO->rollback();
        }
    }

    /**
     * Execute query and return iterator of results.
     *
     * @param string    $sSql           Query to execute.
     * @param array     $aValues        Values to bind.
     * @param array     $aResultsTypes  Types of returned values.
     *
     * @return ResultSet Iterator of results.
     */
    public function getResults(string $sSql, array $aValues = [], array $aResultsTypes = []): ResultSet
    {
        return new ResultSet($this, $sSql, $aValues, $aResultsTypes);
    }

    /**
     * Execute query and return single result.
     *
     * @param string    $sSql           Query to execute.
     * @param array     $aValues        Values to bind.
     * @param array     $aResultsTypes  Types of returned values.
     *
     * @return array Result.
     */
    public function getResult(string $sSql, array $aValues = [], array $aResultsTypes = []): ?array
    {
        $raResult = null;

        $oResultSet = new ResultSet($this, $sSql, $aValues, $aResultsTypes);

        if (count($oResultSet) > 0) {
            $raResult = $oResultSet->current();
        }

        return $raResult;
    }

    /**
     * Execute query and return iterator of single value.
     *
     * @param string        $sSql           Query to execute.
     * @param array         $aValues        Values to bind.
     * @param int|string    $mResultType    Type of returned values (YDO type or DataAdapter class name).
     *
     * @return ResultSet Iterator of values.
     */
    public function getValues(string $sSql, array $aValues = [], $mResultType = null): ResultSet
    {
        return new ResultSet($this, $sSql, $aValues, $mResultType);
    }

    /**
     * Execute query and return single value.
     *
     * @param string        $sSql           Query to execute.
     * @param array         $aValues        Values to bind.
     * @param int|string    $mResultType    Type of returned values (YDO type or DataAdapter class name).
     *
     * @return mixed Value.
     */
    public function getValue(string $sSql, array $aValues = [], $mResultType = null)
    {
        $rmData = null;

        $oPDOStatement = $this->exec($sSql, $aValues, Query::TYPE_SELECT);

        if ($oPDOStatement !== false) {
            if ($oPDOStatement->rowCount() > 0) {
                $rmData = Data::decode($oPDOStatement->fetchColumn(0), $mResultType);
            }
        }

        return $rmData;
    }

    /**
     * Execute query.
     *
     * @param string    $sSql           Query to execute.
     * @param array     $aValues        Values to bind.
     * @param int       $iQueryType     Type of query (select, insert, update or delete).
     *
     * @return \PDOStatement|bool
     */
    public function exec(string $sSql, ?array $aValues = null, int $iQueryType = Query::TYPE_SELECT): ?\PDOStatement
    {
        if ($aValues === null) {
            $aValues = [];
        }

        $aPreparedQueryAndValues = Query::prepareQueryAndValues($sSql, $aValues, $iQueryType);

        $roPDOStatement = $this->_oPDO->prepare($aPreparedQueryAndValues[0]);

        foreach ($aPreparedQueryAndValues[1] as $sValueName=>$aValueDetails) {
            $roPDOStatement->bindValue(':'.$sValueName, $aValueDetails[0], $aValueDetails[1]);
        }

        if ($roPDOStatement->execute() !== true) {
            $aErrorInfo = $roPDOStatement->errorInfo();

            throw new \Exception('Error while running query ['.$aErrorInfo[0].']: '.$aErrorInfo[2]."\n".$sSql."\n".var_export($aValues, true));
        }

        return $roPDOStatement;
    }
}
