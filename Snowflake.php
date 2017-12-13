<?php

namespace Library;

/**
 * @description 参看 twitter snowflake 算法    二进制位数
 * @package     Library
 */
class Snowflake
{
    /**
     * 顺序号
     */
    const SEQUENCE_LEFT_BITS = 12;

    /**
     * 业务线左移 支撑2个
     */
    const BUSINESS_ID_LEFT_BITS = 2;

    /**
     * 数据中心左移位数 支撑4个
     */
    const DATA_CENTER_ID_LEFT_BITS = 2;

    /**
     * 机器标识左移位数，支撑4个
     */
    const MACHINE_ID_LEFt_BITS = 2;

    /**
     * 开始时间戳
     */
    const TIMESTAMP_STARTED_AT = 1513133104000;

    /**
     * @var
     */
    protected $mSequence;

    protected $mLastTimestamp;

    protected $mBusinessID;

    protected $mDataCenterID;

    protected $mMachineID;

    public function __construct(int $businessID, int $dataCenterID, int $machineID, int $sequence = 0)
    {
        if ($businessID <= 0 || $businessID > $this->_maxBusinessID()) {
            throw new \Exception("Business ID can't be greater than {$this->_maxBusinessID()} or less then 0");
        }

        if ($dataCenterID <= 0 || $dataCenterID > $this->_maxDataCenterID()) {
            throw new \Exception("Data center ID can't be greater than {$this->_maxDataCenterID()} or less than 0");
        }

        if ($machineID <= 0 || $machineID > $this->_maxMachineID()) {
            throw new \Exception("Machine ID can't be greater than {$this->_maxMachineID()} or less than 0");
        }

        if ($sequence <= 0 || $sequence > $this->_maxSequence()) {
            throw new \Exception("Sequence ID can't be greater than {$this->_maxMachineID()} or less than 0");
        }

        $this->mBusinessID = $businessID;
        $this->mDataCenterID = $dataCenterID;
        $this->mMachineID = $machineID;
        $this->mSequence = $sequence;
    }

    public function getTimestamp()
    {
        return floor(microtime(true) * 1000);
    }

    public function nextID()
    {
        $timestamp = $this->getTimestamp();
        if ($timestamp < $this->mLastTimestamp) {
            throw new \Exception(sprintf("Clock moved backwards. Refusing to generate id for &d milliseconds", $this->mLastTimestamp - $timestamp));
        }

        if ($timestamp == $this->mLastTimestamp) {
            $sequence = $this->mNextSequence() & $this->_maxSequence();
            if ($sequence == 0) {
                $timestamp = $this->mNextMillisecond($this->mLastTimestamp);
            }
        } else {
            $this->mSequence = 0;
            $sequence = $this->mNextSequence();
        }

        $this->mLastTimestamp = $timestamp;
        $time = floor($timestamp - self::TIMESTAMP_STARTED_AT) << $this->_timestampLeftBits();
        $buss = $this->mBusinessID << $this->_businessLeftBits();
        $dc = $this->mDataCenterID << $this->_dataCenterLeftBits();
        $machine = $this->mMachineID << $this->_machineLeftBits();

        return (string)$time | $buss | $dc | $machine | $sequence;
    }

    protected function mNextSequence()
    {
        return $this->mSequence++;
    }

    protected function mNextMillisecond($lastTimestamp)
    {
        $timestamp = $this->getTimestamp();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->getTimestamp();
        }

        return $timestamp;
    }

    private function _timestampLeftBits()
    {
        return self::SEQUENCE_LEFT_BITS + self::MACHINE_ID_LEFt_BITS + self::DATA_CENTER_ID_LEFT_BITS + self::BUSINESS_ID_LEFT_BITS;
    }

    private function _businessLeftBits()
    {
        return self::SEQUENCE_LEFT_BITS + self::MACHINE_ID_LEFt_BITS + self::DATA_CENTER_ID_LEFT_BITS;
    }

    private function _dataCenterLeftBits()
    {
        return self::SEQUENCE_LEFT_BITS + self::MACHINE_ID_LEFt_BITS;
    }

    private function _machineLeftBits()
    {
        return self::SEQUENCE_LEFT_BITS;
    }

    private function _maxBusinessID()
    {
        return -1 ^ (-1 << self::MACHINE_ID_LEFt_BITS);
    }

    private function _maxDataCenterID()
    {
        return -1 ^ (-1 << self::DATA_CENTER_ID_LEFT_BITS);
    }

    private function _maxMachineID()
    {
        return -1 ^ (-1 << self::MACHINE_ID_LEFt_BITS);
    }

    private function _maxSequence()
    {
        return -1 ^ (-1 << self::SEQUENCE_LEFT_BITS);
    }
}
