<?php

namespace Dukhanin\Panel\Tests\Unit\Files;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Dukhanin\Panel\Files\File;
use Dukhanin\Panel\Tests\TestCase;

class FileQueryBuilderTest extends TestCase
{
    use DatabaseTransactions;

    public function testFindManyOrdered()
    {
        ($file1 = new File)->save();
        ($file2 = new File)->save();
        ($file3 = new File)->save();

        $foundIds = File::findManyOrdered($ids = [$file1->id, $file2->id, $file3->id])->pluck('id')->toArray();
        $this->assertEquals($foundIds, $ids);

        $foundIds = File::findManyOrdered($ids = [$file3->id, $file2->id, $file1->id])->pluck('id')->toArray();
        $this->assertEquals($foundIds, $ids);
    }
}
