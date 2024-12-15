# DigStats Reader

Library for reading Minecraft's NBT data.

## Example Usage

## Technical Notes

### NBT (Named Binary Tag)

It is the file format used by Minecraft to store structured game data in a compact, efficient, and hierarchical manner. NBT is designed to handle the vast amounts of data Minecraft needs to track, such as player stats, world metadata, entity data, and block states.

**TAG Data Format**

| Tag Type | Name            | Length/Format                          | Notes                                           |
|----------|-----------------|-----------------------------------------|------------------------------------------------|
| 0        | TAG_End         | No data                                | Marks the end of a TAG_Compound.               |
| 1        | TAG_Byte        | 1 byte                                 | 8-bit signed integer.                          |
| 2        | TAG_Short       | 2 bytes                                | 16-bit signed integer.                         |
| 3        | TAG_Int         | 4 bytes                                | 32-bit signed integer.                         |
| 4        | TAG_Long        | 8 bytes                                | 64-bit signed integer.                         |
| 5        | TAG_Float       | 4 bytes                                | 32-bit floating-point number.                  |
| 6        | TAG_Double      | 8 bytes                                | 64-bit floating-point number.                  |
| 7        | TAG_Byte_Array  | 4 bytes (length) + length bytes        | Array of bytes.                                |
| 8        | TAG_String      | 2 bytes (length) + length UTF-8 chars  | UTF-8 string.                                  |
| 9        | TAG_List        | 1 byte (type) + 4 bytes (length) + data | List of unnamed tags of the same type.         |
| 10       | TAG_Compound    | Series of named tags + TAG_End         | Compound structure.                            |
| 11       | TAG_Int_Array   | 4 bytes (length) + length * 4 bytes    | Array of 32-bit integers.                      |
| 12       | TAG_Long_Array  | 4 bytes (length) + length * 8 bytes    | Array of 64-bit integers.                      |

