<?php

namespace webignition\StringParser;

class StateHandler
{
    /**
     * @var \Closure(StringParser $instance): void
     */
    private \Closure $handler;

    /**
     * @param \Closure(StringParser $instance): void $handler
     */
    public function __construct(\Closure $handler)
    {
        $this->handler = $handler;
    }


    public function handle(ConcreteStringParser $instance): void
    {
        ($this->handler)($instance);
    }
}
