<?php

declare(strict_types=1);

namespace App\Service;

use RuntimeException;

use function file;
use function file_exists;
use function mb_strtolower;
use function preg_match;
use function preg_quote;
use function sprintf;
use function str_starts_with;
use function trim;

use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;

final class ContentModerationService
{
    /**
     * @var list<string>
     */
    private readonly array $profanityWords;

    public function __construct(
        private readonly string $profanityWordsFilePath,
    ) {
        $this->profanityWords = $this->loadProfanityWords();
    }

    /**
     * @return list<string>
     */
    public function getProfanityWords(): array
    {
        return $this->profanityWords;
    }

    public function containsProfanity(string $text): bool
    {
        $lowercaseText = mb_strtolower($text, 'UTF-8');

        foreach ($this->profanityWords as $word) {
            // Используем word boundary для точного совпадения слова
            // \b не работает корректно с кириллицей, поэтому используем (^|[^\wа-яёА-ЯЁ])...((?=[^\wа-яёА-ЯЁ])|$)
            $pattern = '/(^|[^\wа-яёА-ЯЁ])' . preg_quote($word, '/') . '(?=[^\wа-яёА-ЯЁ]|$)/ui';

            if (1 === preg_match($pattern, $lowercaseText)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load profanity words from the configured file.
     *
     * - Ignores empty lines
     * - Ignores comment lines (starting with #)
     * - Trims whitespace from each word
     * - Converts words to lowercase for case-insensitive matching
     *
     * @throws RuntimeException if the profanity words file cannot be read
     *
     * @return list<string>
     */
    private function loadProfanityWords(): array
    {
        if (!file_exists($this->profanityWordsFilePath)) {
            throw new RuntimeException(
                sprintf('Profanity words file not found: %s', $this->profanityWordsFilePath)
            );
        }

        $lines = file($this->profanityWordsFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (false === $lines) {
            throw new RuntimeException(
                sprintf('Failed to read profanity words file: %s', $this->profanityWordsFilePath)
            );
        }

        $words = [];

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if ('' === $trimmedLine || str_starts_with($trimmedLine, '#')) {
                continue;
            }

            $words[] = mb_strtolower($trimmedLine, 'UTF-8');
        }

        return $words;
    }
}
