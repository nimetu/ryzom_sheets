<?php
namespace Ryzom\Sheets;

use Nel\Misc\MemStream;
use Nel\Misc\SheetId;
use Ryzom\Sheets\Client\SkilltreeSheet;

class SheetsManagerTest extends \PHPUnit\Framework\TestCase {
    const SKILL_TREE_ID = 36;

    /** @var SheetId */
    private $sheetId;

    /** @var PackedSheetsLoader */
    private $psLoader;

    /** @var SheetsManager */
    private $sheetsManager;

    public function setUp(): void {
        $sheets = array(
            self::SKILL_TREE_ID => 'skills.skill_tree',
        );
        $mem = new MemStream();
        $nb = count($sheets);
        $mem->serial_uint32($nb);
        foreach($sheets as $k => $v) {
            $mem->serial_uint32($k);
            $mem->serial_string($v);
        }

        $this->sheetId = new SheetId;
        $this->sheetId->load($mem->getBuffer());
        $this->psLoader = new PackedSheetsLoader(__DIR__.'/_files');
        $this->sheetsManager = new SheetsManager($this->sheetId, $this->psLoader);
    }

    public function testDeprecatedGet() {
        /** @var SkilltreeSheet */
        $result = $this->sheetsManager->findById(self::SKILL_TREE_ID);

        $this->assertNotEmpty($result, 'Returned skilltree should not be empty');
        $sf = $result->get(0);
        $this->assertNotEmpty($sf, 'Expected to get SC skill');
        $this->assertEquals($sf->SkillCode, 'SC', 'Expected SC, got '.$sf->SkillCode);
    }

    public function testFind() {
        /** @var SkilltreeSheet */
        $result = $this->sheetsManager->findById(self::SKILL_TREE_ID);

        $this->assertNotEmpty($result, 'Returned skilltree should not be empty');
        $sf = $result->find('sf');
        $this->assertNotEmpty($sf, 'Expected to get SF skill');
    }
}
