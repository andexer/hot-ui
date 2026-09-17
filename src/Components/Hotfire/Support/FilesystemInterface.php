<?php

declare(strict_types=1);

namespace Components\Hotfire\Support;

/**
 * Filesystem interface for dependency inversion.
 * 
 * This abstraction allows Hotfire to work with different filesystem
 * implementations (local, virtual, cloud, etc.) without modifying
 * the core logic.
 * 
 * Implements Dependency Inversion Principle (DIP): high-level modules
 * should not depend on low-level modules; both should depend on abstractions.
 */
interface FilesystemInterface
{
    /**
     * Checks if a file or directory exists.
     * 
     * @param string $path The path to check
     * @return bool True if the path exists, false otherwise
     */
    public function exists(string $path): bool;

    /**
     * Checks if a path is readable.
     * 
     * @param string $path The path to check
     * @return bool True if the path is readable, false otherwise
     */
    public function isReadable(string $path): bool;

    /**
     * Checks if a path is writable.
     * 
     * @param string $path The path to check
     * @return bool True if the path is writable, false otherwise
     */
    public function isWritable(string $path): bool;

    /**
     * Checks if a path is a file.
     * 
     * @param string $path The path to check
     * @return bool True if the path is a file, false otherwise
     */
    public function isFile(string $path): bool;

    /**
     * Checks if a path is a directory.
     * 
     * @param string $path The path to check
     * @return bool True if the path is a directory, false otherwise
     */
    public function isDirectory(string $path): bool;

    /**
     * Reads the contents of a file.
     * 
     * @param string $path The path to read
     * @return string The file contents
     * @throws FilesystemException If the file cannot be read
     */
    public function read(string $path): string;

    /**
     * Writes content to a file.
     * 
     * @param string $path The path to write to
     * @param string $content The content to write
     * @return void
     * @throws FilesystemException If the file cannot be written
     */
    public function write(string $path, string $content): void;

    /**
     * Creates a directory recursively.
     * 
     * @param string $path The directory path to create
     * @param int $mode The permission mode (octal)
     * @return void
     * @throws FilesystemException If the directory cannot be created
     */
    public function createDirectory(string $path, int $mode = 0o755): void;

    /**
     * Ensures a directory exists, creating it if necessary.
     * 
     * @param string $path The directory path to ensure
     * @return bool True if the directory exists or was created, false otherwise
     */
    public function ensureDirectory(string $path): bool;
}
