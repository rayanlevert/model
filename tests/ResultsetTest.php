<?php

namespace RayanLevert\Model\Tests;

use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Exception;
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

        $this->assertNull($oResultset->current());
        $this->assertSame(0, $oResultset->count());

        $oIterator = $oResultset->getIterator();
        $this->assertInstanceOf(\Traversable::class, $oIterator);
        $this->assertSame(0, iterator_count($oIterator));
    }

    #[Test]
    public function oneRowModelExceptionTypeError(): void
    {
        $oStatement = $this->createMock(PDOStatement::class);
        $oStatement->expects($this->once())
            ->method('fetch')
            ->willReturn(['id' => 'not an integer']);

        $oResultset = new Resultset(new User(), $oStatement);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Setting property id failed, Cannot assign string to property');

        $oResultset->current();
    }

    #[Test]
    public function oneRowModelAssigned(): void
    {
        $oStatement = $this->createMock(PDOStatement::class);
        $oStatement->expects($this->once())
            ->method('fetch')
            ->willReturn(['id' => 1, 'last_name' => 'Doe', 'first_name' => 'John']);

        $oResultset = new Resultset(new User(), $oStatement);

        $oModel = $oResultset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame(1, $oModel->id);
        $this->assertSame('Doe', $oModel->lastName);
        $this->assertSame('John', $oModel->firstName);
    }

    #[Test]
    public function multipleRows(): void
    {
        $oStatement = $this->createMock(PDOStatement::class);
        $oStatement->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 1, 'last_name' => 'Doe', 'first_name' => 'John'
                ],
                [
                    'id' => 2, 'last_name' => 'Doe', 'first_name' => 'Jane'
                ]
            );

        $oResultset = new Resultset(new User(), $oStatement);

        $oModel = $oResultset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame(1, $oModel->id);
        $this->assertSame('Doe', $oModel->lastName);
        $this->assertSame('John', $oModel->firstName);

        $oModel = $oResultset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame(2, $oModel->id);
        $this->assertSame('Doe', $oModel->lastName);
        $this->assertSame('Jane', $oModel->firstName);
    }

    #[Test]
    public function oneRowRealStatement(): void
    {
        $oUser = new User();
        $oUser->lastName  = 'Doe';
        $oUser->firstName = 'John';

        $oUser->create();

        $oStatement = $this->dataObject->prepareAndExecute(new Statement('SELECT * FROM user WHERE id = ?', $oUser->id));

        $oUser = new User();
        $oResulset = new Resultset($oUser, $oStatement);

        $this->assertCount(1, $oResulset);

        $oModel = $oResulset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame($oUser->id, $oModel->id);
        $this->assertSame($oUser->lastName, $oModel->lastName);
        $this->assertSame($oUser->firstName, $oModel->firstName);

        $this->assertNull($oResulset->current());
    }

    #[Test]
    public function multipleRowsRealStatement(): void
    {
        $oUser = new User();
        $oUser->lastName = 'Doe';
        $oUser->firstName = 'John';
        $oUser->create();

        $oUser2 = new User();
        $oUser2->lastName = 'Doe';
        $oUser2->firstName = 'Jane';
        $oUser2->create();

        $oStatement = $this->dataObject->prepareAndExecute(new Statement('SELECT * FROM user'));

        $oResulset = new Resultset($oUser, $oStatement);
        $this->assertCount(2, $oResulset);

        $oModel = $oResulset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame($oUser->id, $oModel->id);
        $this->assertSame($oUser->lastName, $oModel->lastName);
        $this->assertSame($oUser->firstName, $oModel->firstName);

        $oModel = $oResulset->current();
        $this->assertInstanceOf(User::class, $oModel);
        $this->assertSame($oUser2->id, $oModel->id);
        $this->assertSame($oUser2->lastName, $oModel->lastName);
        $this->assertSame($oUser2->firstName, $oModel->firstName);

        $this->assertNull($oResulset->current());
    }
}
