<?php

namespace Solo;

use Exception;

class Utilities
{
    /**
     * Check if a given value is serialized.
     *
     * This function checks if the provided data is in a serialized format.
     * It can optionally perform strict checking to ensure that the format
     * matches exactly what is expected for serialized data.
     *
     * @param mixed $data The value to check.
     * @param bool $strict Whether to enforce strict checking. Default is true.
     * @return bool True if the value is serialized, false otherwise.
     */
    public static function isSerialized($data, bool $strict = true): bool
    {
        // If it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }

        $data = trim($data);
        if ($data === 'N;') {
            return true;
        }
        if (strlen($data) < 4 || $data[1] !== ':') {
            return false;
        }

        if ($strict) {
            $lastChar = substr($data, -1);
            if ($lastChar !== ';' && $lastChar !== '}') {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            if ($semicolon === false && $brace === false) {
                return false;
            }
            if ($semicolon !== false && $semicolon < 3) {
                return false;
            }
            if ($brace !== false && $brace < 4) {
                return false;
            }
        }

        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    return substr($data, -2, 1) === '"';
                } else {
                    return strpos($data, '"') !== false;
                }
            case 'a':
            case 'O':
            case 'E':
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
            default:
                return false;
        }
    }

    /**
     * Generates a version 4 UUID.
     *
     * This function generates a UUID (Universally Unique Identifier) version 4,
     * which is based on random numbers. The generated UUID is compliant with
     * RFC 4122.
     *
     * @link https://tools.ietf.org/html/rfc4122
     * @return string A string representation of a UUID version 4.
     * @throws Exception If it was not possible to gather sufficient entropy.
     */
    public static function generateUUID(): string
    {
        while (true) {
            try {
                $data = random_bytes(16);

                $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
                $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

                return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
            } catch (Exception $e) {
                // Continue trying until successful
            }
        }
    }

    /**
     * Generates a unique random numeric code of specified length.
     *
     * This function generates a random numeric code of a specified length.
     * The code is guaranteed to be between the range of `10^($length - 1)` and `10^$length - 1`.
     *
     * @param int $length The length of the numeric code to generate. Default is 10.
     * @return int A random numeric code of the specified length.
     * @throws Exception If it was not possible to gather sufficient entropy.
     */
    public static function generateCode(int $length = 10): int
    {
        $min = (int)pow(10, $length - 1);
        $max = (int)pow(10, $length) - 1;

        while (true) {
            try {
                return random_int($min, $max);
            } catch (Exception $e) {
                // Continue trying until successful
            }
        }
    }
}