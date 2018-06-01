<?php

namespace Addons\Func\Encrypt;

class Tea {

	const UINT32_MAX = 0xFFFFFFFF;
    const BYTE_1 = 0xFF;
    const BYTE_2 = 0xFF00;
    const BYTE_3 = 0xFF0000;
    const BYTE_4 = 0xFF000000;

    const RSHIFT_5 = 0x07FFFFFF;

    const delta = 0x9E3779B9;

    private $k0 = 0, $k1 = 0, $k2 = 0, $k3 = 0;

    private $loops = 32;

	function __construct()
	{
		$this->setKey(str_repeat(chr(0), 16));
	}

	public function encrypt($data) {
        $data_len = strlen($data);
        if (0 == $data_len) {
            return '';
        }

        $group_len = 8;
        $residues = $data_len % $group_len;

        if ($residues > 0) {
            $pad_len = $group_len - $residues;
             $data .= str_repeat("\0", $pad_len);
             $data_len += $pad_len;
        }

        $result = array(chr($residues));
        for ($i = 0; $i < $data_len; $i += $group_len) {
            $result[] = $this->encrypt_group(substr($data, $i, $group_len));
        }

        return implode('', $result);
    }

    public function decrypt($data) {
        $group_len = 8;
        $data_len = strlen($data);
        if ($data_len % $group_len != 1) {
            return '';
        }

        $residues = ord($data{0});
        $result = array();
        for ($i = 1; $i < $data_len; $i += $group_len) {
            $result[] = $this->decrypt_group(substr($data, $i, $group_len));
        }

        if ($residues > 0) {
            $lastpos = count($result) - 1;
            $result[$lastpos] = substr($result[$lastpos], 0, $residues);
        }
        return implode('', $result);
    }

    /**
     * 设置密钥
     * @param string $key 密钥
     * @return 密钥长度不为16个byte时，抛出异常
     */
    public function setKey($key) {
        if (strlen($key) != 16)
        	throw new \RuntimeException('Key size must be 16 bytes');

        $this->k0 = static::bytes_to_uint32(substr($key, 0, 4));
        $this->k1 = static::bytes_to_uint32(substr($key, 4, 4));
        $this->k2 = static::bytes_to_uint32(substr($key, 8, 4));
        $this->k3 = static::bytes_to_uint32(substr($key, 12, 4));
    }

    /**
     * 设置加密的轮数，默认为32轮
     * @param int $loops 加密轮数
     * @return boolean 轮数为16、32、64时，返回true，否则返回false
     */
    public function setLoops($loops) {
        switch ($loops) {
            case 16:
            case 32:
            case 64:
                $this->loops = $loops;
                return true;
        }
        return false;
    }

    /**
     * 加密一组明文
     * @param string $v 需要加密的明文
     * @return string 返回密文
     */
    private function encrypt_group($v) {
        $v0 = static::bytes_to_uint32(substr($v, 0, 4));
        $v1 = static::bytes_to_uint32(substr($v, 4));
        $sum = 0;
        for ($i = 0; $i < $this->loops; ++$i) {
            $sum = static::toUInt32($sum + static::delta);

            $v0_xor_1 = static::toUInt32(static::toUInt32($v1 << 4) + $this->k0);
            $v0_xor_2 = static::toUInt32($v1 + $sum);
            $v0_xor_3 = static::toUInt32(($v1 >> 5 & static::RSHIFT_5) + $this->k1);
            $v0 = static::toUInt32( $v0 + static::toUInt32($v0_xor_1 ^ $v0_xor_2 ^ $v0_xor_3) );

            $v1_xor_1 = static::toUInt32(static::toUInt32($v0 << 4) + $this->k2);
            $v1_xor_2 = static::toUInt32($v0 + $sum);
            $v1_xor_3 = static::toUInt32(($v0 >> 5 & static::RSHIFT_5) + $this->k3);
            $v1 = static::toUInt32( $v1 + static::toUInt32($v1_xor_1 ^ $v1_xor_2 ^ $v1_xor_3) );
        }
        return static::long_to_bytes($v0, 4) . static::long_to_bytes($v1, 4);
    }

    /**
     * 解密一组密文
     * @param string $v 要解密的密文
     * @return string 返回明文
     */
    private function decrypt_group($v) {
        $v0 = static::bytes_to_uint32(substr($v, 0, 4));
        $v1 = static::bytes_to_uint32(substr($v, 4));
        $sum = 0xC6EF3720;
        for ($i = 0; $i < $this->loops; ++$i) {

            $v1_xor_1 = static::toUInt32(static::toUInt32($v0 << 4) + $this->k2);
            $v1_xor_2 = static::toUInt32($v0 + $sum);
            $v1_xor_3 = static::toUInt32(($v0 >> 5 & static::RSHIFT_5) + $this->k3);
            $v1 = static::toUInt32( $v1 - static::toUInt32($v1_xor_1 ^ $v1_xor_2 ^ $v1_xor_3) );

            $v0_xor_1 = static::toUInt32(static::toUInt32($v1 << 4) + $this->k0);
            $v0_xor_2 = static::toUInt32($v1 + $sum);
            $v0_xor_3 = static::toUInt32(($v1 >> 5 & static::RSHIFT_5) + $this->k1);
            $v0 = static::toUInt32( $v0 - static::toUInt32($v0_xor_1 ^ $v0_xor_2 ^ $v0_xor_3) );

            $sum = static::toUInt32($sum - static::delta);
        }
        return static::long_to_bytes($v0, 4) . static::long_to_bytes($v1, 4);
    }

    /**
     * 将 long 类型的 $n 转为 byte 数组，如果 len 为 4，则只返回低32位的4个byte
     * @param int $n 需要转换的long
     * @param int $len 若为4，则只返回低32位的4个byte，否则返回8个byte
     * @return string 转换后byte数组
     */
    private static function long_to_bytes($n, $len) {
        $a = (static::BYTE_4 & $n) >> 24;
        $b = (static::BYTE_3 & $n) >> 16;
        $c = (static::BYTE_2 & $n) >> 8;
        $d = static::BYTE_1 & $n;
        $p4 = pack('CCCC', $a, $b, $c, $d);
        if (4 == $len) {
            return $p4;
        }
        return static::long_to_bytes($n >> 32, 4) . $p4;
    }

    /**
     * 将4个byte转为 Unsigned Integer 32，以 long 形式返回
     * @param string $bs 需要转换的字节
     * @return int 返回 long
     */
    private static function bytes_to_uint32($bs) {
        $a = (0xFFFFFFFF & ord($bs{0})) << 24;
        $b = (0xFFFFFFFF & ord($bs{1})) << 16;
        $c = (0xFFFFFFFF & ord($bs{2})) << 8;
        $d = ord($bs{3});
        return $a + $b + $c + $d;
    }

    /**
     * 将long的高32位清除，只保留低32位，低32位视为Unsigned Integer
     * @param int $n 需要清除的long
     * @return int 返回高32位全为0的long
     */
    private static function toUInt32($n) {
        return $n & static::UINT32_MAX;
    }
}
