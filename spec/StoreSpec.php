<?php

namespace spec\Rb\Redux;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rb\Redux\Dispatcher\DispatcherInterface;
use Rb\Redux\Exception\MissingTypeException;
use Rb\Redux\Reducer\CallableReducer;
use Rb\Redux\Store;

class StoreSpec extends ObjectBehavior
{
    const INITIAL_STATE = [];

    function let(CallableReducer $reducer)
    {
        $reducer->reduce(self::INITIAL_STATE, Argument::any())->willReturn(self::INITIAL_STATE);

        $this->beConstructedThrough('create', [$reducer, self::INITIAL_STATE]);
    }

    function it_should_subscribe()
    {
        $listener = function () {};

        $unsubscribe = $this->subscribe($listener);
        $unsubscribe()->shouldReturn(true); // should unsubscribe the listener
        $unsubscribe()->shouldReturn(false); // should still be callable, but not do anything
    }

    function it_should_dispatch_and_fire_listener()
    {
        $action = ['type' => 'foo'];

        $listener = function () {};

        $unsubscribe = $this->subscribe($listener);
        $this->dispatch($action)->shouldReturn($action);
        $unsubscribe()->shouldReturn(true);

        $this->getState()->shouldEqual(self::INITIAL_STATE);
    }

    function it_should_throw_exeption_on_empty_type()
    {
        $action = [];

        $this->shouldThrow(MissingTypeException::class)->during('dispatch', [$action]);
    }

    function it_should_throw_exception_on_invalid_action_type()
    {
        $action = 'foo';

        $this->shouldThrow(\InvalidArgumentException::class)->during('dispatch', [$action]);
    }

    function it_should_replace_reducers(CallableReducer $reducerA, CallableReducer $reducerB)
    {
        $state = [];
        $action = ['type' => 'foo'];

        $reducerA->reduce($state, $action)->willReturn($state);
        $reducerA->reduce($state, ['type' => Store::INIT])->willReturn($state);

        $reducerB->reduce($state, $action)->willReturn($state);
        $reducerB->reduce($state, ['type' => Store::INIT])->willReturn($state);

        $this->beConstructedThrough('create', [$reducerA, $state]);

        $this->dispatch($action);
        $this->replaceReducer($reducerB);
        $this->dispatch($action);
    }

    function it_should_return_current_dispatcher(CallableReducer $reducer)
    {
        $state = [];

        $this->beConstructedThrough('create', [$reducer, $state]);

        $this->getCurrentDispatcher()->shouldHaveType(\Closure::class);
    }

    function it_should_replace_dispatcher(DispatcherInterface $dispatcher)
    {
        $this->getCurrentDispatcher()->shouldHaveType(\Closure::class);

        $this->replaceDispatcher($dispatcher);
        $this->getCurrentDispatcher()->shouldEqual($dispatcher);
    }
}
