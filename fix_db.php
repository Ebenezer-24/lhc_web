<?php
require_once 'lib/core/lhcore/lh.php';
erLhcoreClassSystem::init();

$db = ezcDbInstance::get();
$stmt = $db->prepare("ALTER TABLE lh_abstract_chat_variable ADD COLUMN case_insensitive TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 if case insensitive'");
$stmt->execute();

echo "Columna agregada exitosamente!";
