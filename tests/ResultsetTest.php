<?php

namespace RayanLevert\Model\Tests;

use DateTimeImmutable;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Connections\Mysql;
use RayanLevert\Model\Exception;
use RayanLevert\Model\Queries\Mysql as QueriesMysql;
use RayanLevert\Model\Queries\Statement;
use RayanLevert\Model\Resultset;
use RayanLevert\Model\Tests\Fixtures\User;

#[CoversClass(Resultset::class)]
class ResultsetTest extends \RayanLevert\Model\Tests\TransactionTestCase
{
    #[Test]
    public function emptyStatement(): void
    {
        $oStatement = $this->createMock(PDOStatement::class);
        $oStatement->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $oResultset = new Resultset(new User(), $oStatement);

        $oResultset->next();
        $this->assertNull($oResultset->current());
        $this->assertSame(0, $oResultset->count());
    }

    #[Test]
    public function oneRowModelAssigned(): void
    {
        $oStatement = $this->createMock(PDOStatement::class);
        $oStatement->expects($this->once())
            ->method('fetch')
            ->willReturn(['id' => 1, 'last_name' => 'Doe', 'first_name' => 'John', 'age' => 20, 'height' => 1.80]);

        $oResultset = new Resultset(new User(), $oStatement);

        $oResultset->next();
        $oModel = $oResultset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame(1, $oModel->id);
        $this->assertSame('Doe', $oModel->lastName);
        $this->assertSame('John', $oModel->firstName);
        $this->assertSame(20, $oModel->age);
        $this->assertSame(1.80, $oModel->height);
    }

    #[Test]
    public function oneRowRealStatement(): void
    {
        $oUser = new User();
        $oUser->lastName  = 'Doe';
        $oUser->firstName = 'John';
        $oUser->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser->age       = 20;
        $oUser->height    = 1.80;

        $oUser->create();

        $oStatement = $this->dataObject->prepareAndExecute(new Statement('SELECT * FROM user WHERE id = ?', $oUser->id));

        $oUser = new User();
        $oResulset = new Resultset($oUser, $oStatement);
        $oResulset->next();

        $this->assertCount(1, $oResulset);

        $oModel = $oResulset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame($oUser->id, $oModel->id);
        $this->assertSame($oUser->lastName, $oModel->lastName);
        $this->assertSame($oUser->firstName, $oModel->firstName);
        $this->assertSame($oUser->createdAt, $oModel->createdAt);
        $this->assertSame(20, $oModel->age);
        $this->assertSame(1.80, $oModel->height);

        $oResulset->next();
        $this->assertNull($oResulset->current());
    }

    #[Test]
    public function multipleRowsRealStatement(): void
    {
        $oUser = new User();
        $oUser->lastName = 'Doe';
        $oUser->firstName = 'John';
        $oUser->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser->age       = 20;
        $oUser->height    = 1.80;
        $oUser->create();

        $oUser2 = new User();
        $oUser2->lastName = 'Doe';
        $oUser2->firstName = 'Jane';
        $oUser2->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser2->age       = 20;
        $oUser2->height    = 1.80;
        $oUser2->create();

        $oStatement = $this->dataObject->prepareAndExecute(new Statement('SELECT * FROM user'));
        $oResulset  = new Resultset($oUser, $oStatement);

        $this->assertCount(2, $oResulset);

        $oResulset->next();
        $oModel = $oResulset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame($oUser->id, $oModel->id);
        $this->assertSame($oUser->lastName, $oModel->lastName);
        $this->assertSame($oUser->firstName, $oModel->firstName);
        $this->assertSame($oUser->createdAt, $oModel->createdAt);
        $this->assertSame(20, $oModel->age);
        $this->assertSame(1.80, $oModel->height);

        $oResulset->next();
        $oModel = $oResulset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame($oUser2->id, $oModel->id);
        $this->assertSame($oUser2->lastName, $oModel->lastName);
        $this->assertSame($oUser2->firstName, $oModel->firstName);
        $this->assertEquals($oUser2->createdAt, $oModel->createdAt);
        $this->assertSame(20, $oModel->age);
        $this->assertSame(1.80, $oModel->height);

        $oResulset->next();
        $this->assertNull($oResulset->current());
    }

    #[Test]
    public function foreach(): void
    {
        $oUser = new User();
        $oUser->lastName  = 'Doe';
        $oUser->firstName = 'John';
        $oUser->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser->age       = 20;
        $oUser->height    = 1.80;
        $oUser->create();

        $oUser2 = new User();
        $oUser2->lastName  = 'Doe';
        $oUser2->firstName = 'Jane';
        $oUser2->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser2->age       = 20;
        $oUser2->height    = 1.80;
        $oUser2->create();

        $oStatement = $this->dataObject->prepareAndExecute(new Statement('SELECT * FROM user'));
        $oResulset  = new Resultset($oUser, $oStatement);

        $this->assertCount(2, $oResulset);

        foreach ($oResulset as $index => $oModel) {
            $this->assertInstanceOf(User::class, $oModel);
            $this->assertTrue($oResulset->valid());

            $oExpectModel = $index === 0 ? $oUser : $oUser2;

            $this->assertSame($oExpectModel->id, $oModel->id);
            $this->assertSame($oExpectModel->lastName, $oModel->lastName);
            $this->assertSame($oExpectModel->firstName, $oModel->firstName);
            $this->assertEquals($oExpectModel->createdAt, $oModel->createdAt);
            $this->assertSame($oExpectModel->age, $oModel->age);
            $this->assertSame($oExpectModel->height, $oModel->height);

            $this->assertSame($index, $oResulset->key());
        }

        $this->assertSame(19, $this->getCount());
        $this->assertFalse($oResulset->valid());
    }

    #[Test]
    public function next(): void
    {
        $oUser = new User();
        $oUser->lastName  = 'Doe';
        $oUser->firstName = 'John';
        $oUser->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser->age       = 20;
        $oUser->height    = 1.80;
        $oUser->create();

        $oUser2 = new User();
        $oUser2->lastName  = 'Doe';
        $oUser2->firstName = 'Jane';
        $oUser2->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser2->age       = 20;
        $oUser2->height    = 1.80;
        $oUser2->create();

        $oStatement = $this->dataObject->prepareAndExecute(new Statement('SELECT * FROM user'));
        $oResulset  = new Resultset($oUser, $oStatement);

        $this->assertCount(2, $oResulset);

        $oResulset->next();
        $oResulset->next();

        $oModel = $oResulset->current();

        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame($oUser2->id, $oModel->id);
        $this->assertSame($oUser2->lastName, $oModel->lastName);
        $this->assertSame($oUser2->firstName, $oModel->firstName);
        $this->assertEquals($oUser2->createdAt, $oModel->createdAt);
        $this->assertSame($oUser2->age, $oModel->age);
        $this->assertSame($oUser2->height, $oModel->height);

        $this->assertSame(2, $oResulset->key());

        $oResulset->next();
        $this->assertNull($oResulset->current());
        $this->assertFalse($oResulset->valid());

        $this->assertSame(2, $oResulset->key());

        $oUser3 = new User();
        $oUser3->lastName = 'Doe3';
        $oUser3->firstName = 'John3';
        $oUser3->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser3->age       = 20;
        $oUser3->height    = 1.80;
        $oUser3->create();

        // Doesn't re-execute the query, so the count is still 2
        $oResulset->rewind();
        $this->assertCount(2, $oResulset);

        $oResulset->next();
        $oModel = $oResulset->current();

        $this->assertNull($oModel);
    }

    #[Test]
    public function rewind(): void
    {
        $oUser = new User();
        $oUser->lastName  = 'Doe';
        $oUser->firstName = 'John';
        $oUser->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser->age       = 20;
        $oUser->height    = 1.80;
        $oUser->create();

        $oStatement = $this->dataObject->prepareAndExecute(new Statement('SELECT * FROM user'));
        $oResulset  = new Resultset($oUser, $oStatement);

        $this->assertCount(1, $oResulset);

        $oResulset->next();
        $oModel = $oResulset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame($oUser->id, $oModel->id);
        $this->assertSame($oUser->lastName, $oModel->lastName);
        $this->assertSame($oUser->firstName, $oModel->firstName);

        $this->assertTrue($oResulset->valid());
        $this->assertSame(1, $oResulset->key());

        $oResulset->next();
        $this->assertNull($oResulset->current());
        $this->assertFalse($oResulset->valid());

        $oUser2 = new User();
        $oUser2->lastName = 'Doe';
        $oUser2->firstName = 'Jane';
        $oUser2->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser2->age       = 20;
        $oUser2->height    = 1.80;
        $oUser2->create();

        $oResulset->rewind();
        $this->assertCount(1, $oResulset);

        $oResulset->next();
        $oModel = $oResulset->current();

        $this->assertNull($oModel);
        $this->assertFalse($oResulset->valid());
    }

    #[Test]
    public function first(): void
    {
        $oUser = new User();
        $oUser->lastName  = 'Doe';
        $oUser->firstName = 'John';
        $oUser->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser->age       = 20;
        $oUser->height    = 1.80;
        $oUser->create();

        $oUser2 = new User();
        $oUser2->lastName  = 'Doe2';
        $oUser2->firstName = 'Jane2';
        $oUser2->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser2->age       = 20;
        $oUser2->height    = 1.80;
        $oUser2->create();

        $oStatement = $this->dataObject->prepareAndExecute(new Statement('SELECT * FROM user'));
        $oResulset  = new Resultset($oUser, $oStatement);

        $oModel = $oResulset->first();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame($oUser->id, $oModel->id);
        $this->assertSame($oUser->lastName, $oModel->lastName);
        $this->assertSame($oUser->firstName, $oModel->firstName);
        $this->assertSame($oUser->age, $oModel->age);
        $this->assertSame($oUser->height, $oModel->height);

        $oResulset->next();
        $oModel = $oResulset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame($oUser2->id, $oModel->id);
    }

    #[Test]
    public function firstException(): void
    {
        $oUser = new User();
        $oUser->lastName  = 'Doe';
        $oUser->firstName = 'John';
        $oUser->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser->age       = 20;
        $oUser->height    = 1.80;
        $oUser->create();

        $oUser2 = new User();
        $oUser2->lastName  = 'Doe2';
        $oUser2->firstName = 'Jane2';
        $oUser2->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser2->age       = 20;
        $oUser2->height    = 1.80;
        $oUser2->create();

        $oStatement = $this->dataObject->prepareAndExecute(new Statement('SELECT * FROM user'));
        $oResulset  = new Resultset($oUser, $oStatement);

        $oResulset->next();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot fetch the first model if the resultset has already been looped');

        $oResulset->first();
    }

    /**
     * After a full {@see foreach} on a single row, {@see Resultset::key()} can still be 0 while the cursor is exhausted;
     * {@see Resultset::first()} must still reject use.
     */
    #[Test]
    public function firstExceptionAfterForeachExhaustedSingleRow(): void
    {
        $oUser = new User();
        $oUser->lastName  = 'Doe';
        $oUser->firstName = 'John';
        $oUser->createdAt = new DateTimeImmutable('2026-01-01 00:00:00');
        $oUser->age       = 20;
        $oUser->height    = 1.80;
        $oUser->create();

        $oStatement = $this->dataObject->prepareAndExecute(new Statement('SELECT * FROM user'));
        $oResulset  = new Resultset($oUser, $oStatement);

        foreach ($oResulset as $oModel) {
            $this->assertInstanceOf(User::class, $oModel);
        }

        $this->assertSame(0, $oResulset->key());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot fetch the first model if the resultset has already been looped');

        $oResulset->first();
    }

    /** Returns a working Connection to the database */
    protected function getConnection(): Mysql
    {
        return new Mysql('percona', 'root', 'root-password', ['dbname' => 'model']);
    }

    /** Returns a working Queries object to the database */
    protected function getQueries(): QueriesMysql
    {
        return new QueriesMysql();
    }
}
