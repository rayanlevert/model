<?php

namespace RayanLevert\Model\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Model;

#[CoversClass(Model::class)]
class ModelTest extends TestCase
{
    #[Test]
    public function tablePropertyIsInitialized(): void
    {
        $model = new class extends Model {
            protected function table(): string
            {
                return 'test_table';
            }
        };
        
        $this->assertSame('test_table', $model->getTable());
    }
} 