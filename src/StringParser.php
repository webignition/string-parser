<?php

declare(strict_types=1);

namespace webignition\StringParser;

/**
 * Parses a string one character at a time.
 *
 * Implemented as an integer-based state machine starting at state zero (self::STATE_UNNKNOWN).
 *
 * What to do as the input is parsed is dealt with by a collection of handlers passed into the constructor.
 * Each handler is a callable that is passed the current StringParser instance. This allows a handler to examine
 * the current/previous/next character and the character pointer and to set the state.
 *
 * What happens within a handler is up to the implementation. Commonly a handler will perform some logic to determine
 * if parsing should continue and possibly:
 *  - append the current character to the output
 *  - increment the pointer to move on to the next character
 *  - set the state to invoke a different handler for the continuing characters
 *
 * Refer to tests/Implementation/*Parser.php for examples of simple reference implementations.
 */
class StringParser
{
    public const STATE_UNKNOWN = 0;

    private int $state = self::STATE_UNKNOWN;

    /**
     * @var string[]
     */
    private array $characters = [];

    private string $output;
    private int $pointer = 0;
    private int $inputLength = 0;

    /**
     * @param \Closure[] $handlers
     */
    public function __construct(
        private array $handlers
    ) {
    }

    /**
     * @throws UnknownStateException
     */
    public function parse(string $input): string
    {
        $this->reset();

        $characters = preg_split('//u', $input, -1, PREG_SPLIT_NO_EMPTY);

        $this->characters = is_array($characters) ? $characters : [];
        $this->inputLength = count($this->characters);

        while ($this->pointer < $this->inputLength) {
            $handler = $this->handlers[$this->state] ?? null;
            if (null === $handler) {
                throw new UnknownStateException($this->state);
            }

            if ($handler instanceof \Closure) {
                ($handler)($this);
            }
        }

        return $this->output;
    }

    public function clearOutput(): void
    {
        $this->output = '';
    }

    public function stop(): void
    {
        $this->pointer = $this->inputLength;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function appendOutputString(): void
    {
        $this->output .= $this->getCurrentCharacter();
    }

    public function getCurrentCharacter(): ?string
    {
        return $this->getCharacter($this->pointer);
    }

    public function getPreviousCharacter(): ?string
    {
        return $this->getCharacter($this->pointer - 1);
    }

    public function getNextCharacter(): ?string
    {
        return $this->getCharacter($this->pointer + 1);
    }

    public function getPointer(): int
    {
        return $this->pointer;
    }

    public function incrementPointer(): void
    {
        ++$this->pointer;
    }

    public function isCurrentCharacterFirstCharacter(): bool
    {
        if (0 != $this->pointer) {
            return false;
        }

        return !is_null($this->getCurrentCharacter());
    }

    public function isCurrentCharacterLastCharacter(): bool
    {
        return is_null($this->getNextCharacter());
    }

    public function getInput(): string
    {
        return implode('', $this->characters);
    }

    private function getCharacter(int $index): ?string
    {
        return $this->characters[$index] ?? null;
    }

    private function reset(): void
    {
        $this->output = '';
        $this->pointer = 0;
        $this->state = self::STATE_UNKNOWN;
    }
}
