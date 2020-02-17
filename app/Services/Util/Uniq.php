<?php namespace App\Services\Util;

abstract class Uniq
{
	//初始时间码,开站设定一次,可用以下语句得到
	//floor(microtime(true) * 1000);
	private static $twepoch = 1380553425155;

	//数据中心,8表示可以有255个数据中心,最大取值为8
	private static $dataCenterIDBits = 8;

	//数据中心ID最大值
	//private static $maxDatacenterID = -1 ^ (-1 << $datacenterIDBits);

	//机器标识符,13表示可以有8191台机器,最大取值为13
	//当dataCenterIDBits与workerIDBits变大时,会产生负数,就不好玩了
	private static $workerIDBits = 13;

	//机器ID最大值
	//private static $maxWorkerId = -1 ^ (-1 << $workerIDBits);

	//毫秒内自增位
	private static $sequenceBits = 12;

	//机器ID偏左移12位
	//private static $workerIDShift = self::$sequenceBits;
	private static $workerIDShift = 12;

	//数据中心ID左移17位
	//private $dataCenterIDShift = $sequenceBits + $workerIDBits;

	//时间毫秒左移22位
	//private $timestampLeftShift = $sequenceBits + $workerIDBits + $datacenterIDBits;
	//private $sequenceMask = -1 ^ (-1 << $sequenceBits);

	private static $lastTimestamp = -1;

	private static $sequence = 0;

	public static function id($dataCenterID, $workerID)
	{
		//数据中心ID最大值
		$maxDataCenterID = -1 ^ (-1 << self::$dataCenterIDBits);

		//机器ID最大值
		$maxWorkerID = -1 ^ (-1 << self::$workerIDBits);

		//数据中心ID左移17位
		$dataCenterIDShift = self::$sequenceBits + self::$workerIDBits;

		//时间毫秒左移22位
		$timestampLeftShift = self::$sequenceBits + self::$workerIDBits + self::$dataCenterIDBits;
		$sequenceMask = -1 ^ (-1 << self::$sequenceBits);

		//检查workerID完整性
		if($workerID > $maxWorkerID || $workerID < 0)
		{
			return 0;
		}

		//检查数据中心完整性
		if($dataCenterID > $maxDataCenterID || $dataCenterID < 0)
		{
			return 0;
		}

		//echo sprintf("worker starting. timestamp left shift %d, datacenter id bits %d, maxDatacenterID:%d, worker id bits %d, maxWorkerID:%d, sequence bits %d, workerid %d", $timestampLeftShift, self::$dataCenterIDBits, $maxDataCenterID, self::$workerIDBits, $maxWorkerID, self::$sequenceBits, $workerID);

		$timestamp = self::timeGen();

		if($timestamp < self::$lastTimestamp)
		{
			return 0;
		}

		if (self::$lastTimestamp == $timestamp)
		{
			//当前毫秒内，则+1
			self::$sequence = (self::$sequence + 1) & $sequenceMask;
			if(self::$sequence == 0)
			{
				//当前毫秒内计数满了，则等待下一秒
				$timestamp = self::tilNextMillis(self::$lastTimestamp);
			}
		}
		else
		{
			self::$sequence = 0;
		}

		self::$lastTimestamp = $timestamp;

		$id = (($timestamp - self::$twepoch << $timestampLeftShift)) | ($dataCenterID << $dataCenterIDShift) | ($workerID << self::$workerIDShift) | (self::$sequence);

		return $id;
	}

	public static function timeGen()
	{
		return floor(microtime(true) * 1000);
	}

	public static function tilNextMillis($lastTimestamp)
	{
		$timestamp = self::timeGen();

		while($timestamp <= $lastTimestamp)
		{
			$timestamp = self::timeGen();
		}

		return $timestamp;
	}

	public static function getUUID()
	{
		return strtoupper(sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	        // 32 bits for "time_low"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

	        // 16 bits for "time_mid"
	        mt_rand( 0, 0xffff ),

	        // 16 bits for "time_hi_and_version",
	        // four most significant bits holds version number 4
	        mt_rand( 0, 0x0fff ) | 0x4000,

	        // 16 bits, 8 bits for "clk_seq_hi_res",
	        // 8 bits for "clk_seq_low",
	        // two most significant bits holds zero and one for variant DCE1.1
	        mt_rand( 0, 0x3fff ) | 0x8000,

	        // 48 bits for "node"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	    ));
	}
}