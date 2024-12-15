<?php

namespace BadMushroom\DigStatsReader;

use Exception;

/**
 * Class TagReader
 *
 * The TagReader class is responsible for reading and parsing structured binary
 * data from a file into a more parseable format.
 *
 * Example Usage
 * ```
 * $handle = fopen('/full/path/level.dat', 'rb');
 * $reader = new TagReader($handle);
 * ```
 */
class TagReader
{
    private $handle;

    public const TAG_END = 0;
    public const TAG_BYTE = 1;
    public const TAG_SHORT = 2;
    public const TAG_INT = 3;
    public const TAG_LONG = 4;
    public const TAG_FLOAT = 5;
    public const TAG_DOUBLE = 6;
    public const TAG_BYTE_ARRAY = 7;
    public const TAG_STRING = 8;
    public const TAG_LIST = 9;
    public const TAG_COMPOUND = 10;
    public const TAG_INT_ARRAY = 11;
    public const TAG_LONG_ARRAY = 12;

    /**
     * Create a new TagReader instance.
     *
     * @param resource $handle The file handle to read from.
     */
    public function __construct($handle)
    {
        $this->handle = $handle;
    }

    /**
     * Create a new TagReader instance from a Gzip-compressed file.
     */
    public static function fromDatFile(string $filePath): self
    {
        $data = file_get_contents($filePath);

        if ($data === false) {
            throw new Exception("Failed to read file: $filePath");
        }

        $decompressedData = gzdecode($data);

        if ($decompressedData === false) {
            throw new Exception("Failed to decompress Gzip file: $filePath");
        }

        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $decompressedData);
        rewind($handle);

        return new self($handle);
    }

    /**
     * Read a tag from the handle.
     */
    public function readTag(): ?Tag
    {
        $type = ord($this->readBytes(1));

        if ($type === self::TAG_END) {
            return null;
        }

        $name = $this->readTagName();
        $value = $this->readTagValue($type);

        return new Tag($type, $name, $value);
    }

    /**
     * Read a tag from the handle and return its value.
     */
    private function readTagName(): string
    {
        $nameLengthData = $this->readBytes(2);
        $nameLength = unpack('n', $nameLengthData)[1];

        return $nameLength > 0 ? $this->readBytes($nameLength) : '';
    }

    /**
     * Get value of a tag based on its type.
     */
    private function readTagValue(int $type): mixed
    {
        switch ($type) {
            case self::TAG_BYTE:
                $value = ord($this->readBytes(1));
                break;
            case self::TAG_SHORT:
                $value = unpack('n', $this->readBytes(2))[1];
                break;
            case self::TAG_INT:
                $value = unpack('N', $this->readBytes(4))[1];
                break;
            case self::TAG_LONG:
                $longBytes = $this->readBytes(8);
                $value = PHP_INT_SIZE >= 8
                    ? unpack('J', $longBytes)[1]
                    : gmp_import(strrev($longBytes), 1, GMP_LITTLE_ENDIAN);
                break;
            case self::TAG_FLOAT:
                $value = unpack('f', strrev($this->readBytes(4)))[1];
                break;
            case self::TAG_DOUBLE:
                $value = unpack('d', strrev($this->readBytes(8)))[1];
                break;
            case self::TAG_BYTE_ARRAY:
                $length = unpack('N', $this->readBytes(4))[1];
                $value = $this->readBytes($length);
                break;
            case self::TAG_STRING:
                $length = unpack('n', $this->readBytes(2))[1];
                $value = $length > 0 ? $this->readBytes($length) : '';
                break;
            case self::TAG_LIST:
                $value = $this->readTagList();
                break;
            case self::TAG_COMPOUND:
                $value = $this->readTagCompound();
                break;
            case self::TAG_INT_ARRAY:
                $value = $this->readTagIntArray();
                break;
            case self::TAG_LONG_ARRAY:
                $value = $this->readTagLongArray();
                break;
            default:
                throw new Exception("Unsupported or unknown tag type: $type");
        }

        return $value;
    }

    /**
     * Read a list of tags.
     */
    private function readTagList(): array
    {
        $listType = ord($this->readBytes(1));
        $listLength = unpack('N', $this->readBytes(4))[1];
        $list = [];

        for ($i = 0; $i < $listLength; $i++) {
            $list[] = $this->readTagValue($listType);
        }

        return $list;
    }

    /**
     * Read a compound tag.
     */
    private function readTagCompound(): array
    {
        $compound = [];

        while (($childTag = $this->readTag()) !== null) {
            $compound[$childTag->name] = $childTag->value;
        }

        return $compound;
    }

    /**
     * Read an array of integers.
     */
    private function readTagIntArray(): array
    {
        $length = unpack('N', $this->readBytes(4))[1];
        $intArray = [];

        for ($i = 0; $i < $length; $i++) {
            $intArray[] = unpack('N', $this->readBytes(4))[1];
        }

        return $intArray;
    }

    /**
     * Read an array of long integers.
     */
    private function readTagLongArray(): array
    {
        $length = unpack('N', $this->readBytes(4))[1];
        $longArray = [];

        for ($i = 0; $i < $length; $i++) {
            $longBytes = $this->readBytes(8);
            $longArray[] = PHP_INT_SIZE >= 8
                ? unpack('J', $longBytes)[1]
                : gmp_import(strrev($longBytes), 1, GMP_LITTLE_ENDIAN);
        }

        return $longArray;
    }

    /**
     * Read a specified number of bytes from the handle.
     */
    private function readBytes(int $length): string
    {
        $data = fread($this->handle, $length);

        if (strlen($data) < $length) {
            throw new Exception("Failed to read $length bytes from handle.");
        }

        return $data;
    }
}
