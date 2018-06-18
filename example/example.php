<?php

use Nel\Misc\BnpFile;
use Nel\Misc\SheetId;
use Ryzom\Sheets\PackedSheetsLoader;
use Ryzom\Translation\Loader\UxtLoader;
use Ryzom\Translation\Loader\WordsLoader;
use Ryzom\Translation\StringsManager;

require __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/config.php')) {
	$config = include __DIR__.'/config.php';
} else {
	$config = include __DIR__.'/config.dist.php';
}

$sm = new StringsManager();
$sm->register(new UxtLoader());
$sm->register(new WordsLoader());

// loading sheet_id.bin
$bnp = new BnpFile($config['data'].'/leveldesign.bnp');
$data = $bnp->readFile('sheet_id.bin');
$sheetIds = new SheetId;
$sheetIds->load($data);

$bnp = new BnpFile($config['data'].'/gamedev.bnp');

// loading en.uxt file
$buffer = $bnp->readFile('en.uxt');
$sm->load('uxt', $buffer, 'en');
$strings = $sm->getStrings('uxt', 'en');

// all keys are in lowercase
$key = 'languagename';
$value = $strings[$key];
printf("%s = [%s]\n", $key, $value['name']);

// loading outpost_words_en.txt
$buffer = $bnp->readFile('outpost_words_en.txt');
$sm->load('outpost', $buffer, 'en');
$strings = $sm->getStrings('outpost', 'en');

$key = 'fyros_outpost_04';
$value = $strings[$key];
printf("%s\n  name = %s\n  description = %s\n", $key, $value['name'], $value['description']);

/*****************************************************************************/
echo "\n";
$psLoader = new PackedSheetsLoader($config['data']);
printf("+ loading sitem.packed_sheets file...\n");
$sitem = $psLoader->load('sitem');

$sheetId = 5603886;
$sheetName = $sheetIds->getSheetIdName($sheetId);
/** @var $item \Ryzom\Sheets\Client\ItemSheet */
$item = $sitem->get($sheetId);
printf(
	"+ %d ($sheetName): item type: %d, variant: %d, icons: (%s, %s), maleShape: %s\n",
	$sheetId, $sheetName, $item->ItemType, $item->MapVariant, $item->Icon[0], $item->Icon[1], $item->MaleShape
);
/*****************************************************************************/
$world = $psLoader->load('world');
//print_r($world);

/** @var \Ryzom\Sheets\ContinentLandmarks $lmconts */
$lmconts = $psLoader->load('lmconts');
/** @var \Ryzom\Sheets\Client\CContinent[] $continents */
$continents = $lmconts->get('continents');
foreach ($continents as $key => $cont) {
	echo "+ [{$cont->Name}]\n";
	foreach ($cont->ContLandMarks as $lm) {
		echo "  > {{$lm->TitleText}} {{$lm->Pos->X}, {$lm->Pos->Y}}\n";
	}
}

/*****************************************************************************/
$count = count($bnp->getFileNames());
echo "Iterating bnp... total files: $count, display first 10\n";
$c = 0;
foreach ($bnp as $filename => $filecontent) {
	printf("[%d] %s: size: %d\n", ++$c, $filename, strlen($filecontent));
	if ($c > 11) {
		break;
	}
}

