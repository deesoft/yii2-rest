<?php

namespace dee\rest;

use yii\db\Transaction;

/**
 * Description of TransactionTrait
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
trait TransactionTrait
{
    /**
     * List of transaction object
     * @var array
     */
    private $_transaction = [];

    /**
     * Begins a transaction.
     */
    protected function beginTransaction()
    {
        $modelClass = $this->modelClass;
        $transaction = $modelClass::getDb()->beginTransaction();

        $this->_transaction[] = $transaction;
    }

    /**
     * Commits a transaction.
     * @throws Exception if the transaction is not active
     */
    protected function commit()
    {
        $transaction = array_pop($this->_transaction);
        if ($transaction && $transaction instanceof Transaction) {
            $transaction->commit();
        }
    }

    /**
     * Rolls back a transaction.
     * @throws Exception if the transaction is not active
     */
    protected function rollback()
    {
        $transaction = array_pop($this->_transaction);
        if ($transaction && $transaction instanceof Transaction) {
            $transaction->rollBack();
        }
    }
}