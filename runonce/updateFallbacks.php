<?php

use ContaoCommunityAlliance\Contao\LanguageRelations\LanguageRelations;

// Install DB if not there yet
if (!\Database::getInstance()->tableExists('tl_cca_lr_relation')) {
    \Database::getInstance()->query(file_get_contents(TL_ROOT . '/system/modules/language-relations/config/database.sql'));
}

$arrFallbackRoots = \Database::getInstance()->query("SELECT id FROM tl_page WHERE type='root' AND fallback='1'")->fetchEach('id');

$arrIdsToUpdate = \Database::getInstance()->getChildRecords($arrFallbackRoots);
foreach ($arrIdsToUpdate as $id) {
    LanguageRelations::createReflectionRelations($id);
    LanguageRelations::createIntermediateRelations($id);
}