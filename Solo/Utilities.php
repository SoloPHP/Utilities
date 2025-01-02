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

    /**
     * Generates a unique numeric identifier.
     *
     * This function generates a numeric identifier as a string,
     * suitable for use as a unique identifier in the database.
     * The value is guaranteed to be between `10^($length - 1)` and `10^$length - 1`.
     *
     * @param int $length The length of the numeric identifier. Default is 6.
     * @return string A numeric identifier of the specified length.
     * @throws Exception If it was not possible to gather sufficient entropy.
     */
    public static function generateId(int $length = 6): string
    {
        $min = (int)pow(10, $length - 1);
        $max = (int)pow(10, $length) - 1;

        while (true) {
            try {
                return (string)random_int($min, $max);
            } catch (Exception $e) {
                // Continue trying until successful
            }
        }
    }

    /**
     * Generates a random password of the specified length.
     *
     * This method uses cryptographically secure random number generation.
     * In case of an exception during the `random_int` call, it will retry until successful.
     *
     * @param int $length Length of the generated password. Default is 8.
     * @return string The generated password.
     * @throws Exception If the length is less than 1.
     */
    public static function generatePassword(int $length = 8): string
    {
        if ($length < 1) {
            throw new Exception('Password length must be at least 1.');
        }

        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            while (true) {
                try {
                    $index = random_int(0, $maxIndex);
                    $password .= $characters[$index];
                    break; // Exit loop if successful
                } catch (Exception $e) {
                    // Retry on failure
                }
            }
        }

        return $password;
    }

    /**
     * Check if the given string is gzip encoded.
     *
     * This function inspects the first two bytes of the string to check if they match the
     * gzip signature (0x1f8b), which is a standard identifier for gzip-compressed data.
     *
     * @param string $data The string to check.
     * @return bool Returns true if the string is gzip encoded, false otherwise.
     */
    public static function isGzipEncoded(string $data): bool
    {
        return substr($data, 0, 2) === "\x1f\x8b";
    }

}