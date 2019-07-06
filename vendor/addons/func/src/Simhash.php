<?php

namespace Addons\Func;

class Simhash
{
	/**
	 * 通过分词计算simhash
	 * @param array  &$set ['词1', '词2', '词1'] 或 ['词1' => 2, '词2' => 1]
	 * @param int $length  返回的bit长度，一般如下值：32, 64, 128
	 * @return string 二进制文本，使用varbinary存储
	 */
	public static function hash(array $set, int $length = 128)
	{
		$boxes = array_fill(0, $length, 0);

		if (is_int(key($set)))
			$dict = array_count_values($set);
		else
			$dict = &$set;

		foreach ($dict as $element => $weight) {

			$hash = substr(hash('md5', $element), 0, $length / 8);
			$gmp = gmp_init($hash, 16);

			for ($i = 0; $i < $length; $i++)
				$boxes[$i] += gmp_testbit($gmp, $i) ? $weight : -$weight;
		}

		$result = gmp_init(str_repeat('0', $length), 2);

		foreach ($boxes as $i => $box) {
			if ($box > 0)
				gmp_setbit($result, $i);
		}

		return gmp_export($result);
	}

	/**
	 * 计算两个Simhash的汉明距离，然后得到相似度
	 * Hamming distance
	 *
	 * @param  string $h1
	 * @param  string $h2
	 * @return float
	 */
	public static function hamdist(string $h1, string $h2)
	{
		if (($length = strlen($h1)) != strlen($h2) || $length <= 0)
			throw new \RuntimeException('strlen($h1) != strlen($h2) or zero size.');

		$b1 = gmp_import($h1);
		$b2 = gmp_import($h2);

		return 1 - gmp_hamdist($b1, $b2) / ($length * 8);

		/*$dist = 0;
		for ($i = 0; $i < $length * 8; $i++) {
			if ( gmp_testbit($b1, $i) != gmp_testbit($b2, $i) )
				$dist++;
		}
		return ($length - $dist) / $length;*/
	}
}
