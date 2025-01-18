<?php

namespace RayanLevert\Model\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Connection;
use ReflectionClass;

#[CoversClass(Connection::class)]
class ConnectionTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function constructOnlyHost(): void
    {
        $o = new class('test-host') extends Connection
        {
            public function dsn(): string
            {
                return 'test-dsn';
            }
        };

        $this->assertSame('test-dsn', $o->dsn());

        $oRC = new ReflectionClass($o);

        $this->assertSame('test-host', $oRC->getProperty('host')->getValue($o));
        $this->assertNull($oRC->getProperty('username')->getValue($o));
        $this->assertNull($oRC->getProperty('password')->getValue($o));
        $this->assertSame([], $oRC->getProperty('options')->getValue($o));
    }

    #[Test]
    public function constructUsernameAndPassword(): void
    {
        $o = new class('test-host', 'test-username', 'test-password') extends Connection
        {
            public function dsn(): string
            {
                return $this->username . ':' . $this->password;
            }
        };

        $this->assertSame('test-username:test-password', $o->dsn());

        $oRC = new ReflectionClass($o);

        $this->assertSame('test-host', $oRC->getProperty('host')->getValue($o));
        $this->assertSame('test-host', $o->host);
        $this->assertSame('test-username', $oRC->getProperty('username')->getValue($o));
        $this->assertSame('test-username', $o->username);
        $this->assertSame('test-password', $oRC->getProperty('password')->getValue($o));
        $this->assertSame([], $oRC->getProperty('options')->getValue($o));
        $this->assertSame([], $o->options);
    }

    #[Test]
    public function constructOptions(): void
    {
        $o = new class('test-host', options: ['test-option' => 'test-value']) extends Connection
        {
            public function dsn(): string
            {
                return '';
            }
        };

        $this->assertNull($o->getOption('not-an-option'));
        $this->assertSame('defaultValue', $o->getOption('not-an-option', 'defaultValue'));
        $this->assertSame('test-value', $o->getOption('test-option'));
        $this->assertSame(['test-option' => 'test-value'], $o->options);

        $o->setOption('test-option-2', 'test-value-2');

        $this->assertSame('test-value-2', $o->getOption('test-option-2'));
        $this->assertSame(['test-option' => 'test-value', 'test-option-2' => 'test-value-2'], $o->options);

        $o = new class('test-host', options: ['test-option' => 'test-value', 'another-option' => []]) extends Connection
        {
            public function dsn(): string
            {
                return '';
            }
        };

        $this->assertSame(['test-option' => 'test-value'], $o->options);
    }

    #[Test]
    public function debugInfo(): void
    {
        $o = new class('test-host', 'test-username', 'test-password', ['test-option' => 'test-value']) extends Connection
        {
            public function dsn(): string
            {
                return '';
            }
        };

        $this->assertSame([
            'host' => 'test-host',
            'username' => 'test-username',
            'password' => '***',
            'options' => ['test-option' => 'test-value']
        ], $o->__debugInfo());
    }
}
