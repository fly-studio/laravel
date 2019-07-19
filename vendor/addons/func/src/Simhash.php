<?php

namespace Addons\Func;

class Simhash
{
	/**
	 * 通过分词计算simhash
	 * @param array $set ['词1', '词2', '词1'] 或 ['词1' => 2, '词2' => 1]
	 * @param int $length  返回的bit长度，一般如下值：32, 64, 96, 128
	 * @return string 二进制文本，使用varbinary存储
	 */
	public static function hash(array $set, int $length = 128)
	{
		if ($length > 128 || $length < 8 || $length % 8 != 0)
			throw new \InvalidArgumentException('parameter#1 [length] must => 8 && <= 128 and multiple by 8');

		$boxes = array_fill(0, $length, 0);

		if (is_int(key($set)))
			$dict = array_count_values($set);
		else
			$dict = $set;

		foreach ($dict as $element => $weight) {
			$gmp = gmp_import(hash('md5', $element, true), 16);

			for ($i = 0; $i < $length; $i++)
				$boxes[$i] += gmp_testbit($gmp, $i) ? $weight : -$weight;
		}

		$result = gmp_init(str_repeat('0', $length), 2);

		foreach ($boxes as $i => $box) {
			if ($box > 0)
				gmp_setbit($result, $i);
		}

		return gmp_export($result, $length / 8);
	}

	/**
	 * 计算两个Simhash的余弦相似度
	 * http://www.ruanyifeng.com/blog/2013/03/cosine_similarity.html
	 *
	 * a = []; // 矩阵
	 * b = [];
	 * (a[1] x b[1] + a[2] x b[2] + ...) / (sqrt(a[1] ^ 2 + a[2] ^ 2 + ...) x sqrt(b[1] ^ 2 + b[2] ^ 2 + ...))
	 *
	 * @param  string $a hash后的值
	 * @param  string $b hash后的值
	 * @return float     相似度
	 */
	public static function cos(string $a, string $b)
	{
		if (($length = strlen($a)) != strlen($b) || $length <= 0)
			throw new \RuntimeException('strlen($h1) != strlen($h2) or zero size.');

		$a_bin = gmp_import($a);
		$b_bin = gmp_import($b);

		$ab = 0;
		$aa = 0;
		$bb = 0;
		for($i = 0; $i < $length * 8; $i++)
		{
			$v1 = intval(gmp_testbit($a_bin, $i));
			$v2 = intval(gmp_testbit($b_bin, $i));
			$ab += $v1 * $v2;
			$aa += $v1 * $v1;
			$bb += $v2 * $v2;
		}

		return $ab / (sqrt($aa) * sqrt($bb));

	}

	/**
	 * 计算两个Simhash的汉明距离，然后得到相似度
	 * Hamming distance
	 *
	 * @param  string $a hash后的值
	 * @param  string $b hash后的值
	 * @return float 相似度
	 */
	public static function hamdist(string $a, string $b)
	{

		if (($length = strlen($a)) != strlen($b) || $length <= 0)
			throw new \RuntimeException('strlen($a) != strlen($b) or zero size.');

		$a_bin = gmp_import($a);
		$b_bin = gmp_import($b);

		return 1 - gmp_hamdist($a_bin, $b_bin) / ($length * 8);

		/*$dist = 0;
		for ($i = 0; $i < $length * 8; $i++) {
			if ( gmp_testbit($a_bin, $i) != gmp_testbit($b_bin, $i) )
				$dist++;
		}
		return ($length - $dist) / $length;*/
	}

}
