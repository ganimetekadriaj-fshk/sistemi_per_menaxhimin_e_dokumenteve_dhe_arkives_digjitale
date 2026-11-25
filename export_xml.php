<?php
// export_xml.php
require_once "auth_guard.php";
require_role("admin");
require_once "config.php";

header("Content-Type: application/xml; charset=utf-8");
header("Content-Disposition: inline; filename=\"arkiva_dokumentet.xml\"");

$stmt = $conn->query(
    "SELECT d.id, d.titulli, d.numri_protokollit, d.statusi, d.versioni, d.created_at,
            k.emri AS kategoria, k.kodi
     FROM dokumentet d
     INNER JOIN kategorite k ON d.kategoria_id = k.id
     ORDER BY d.id ASC"
);

$dom = new DOMDocument("1.0", "UTF-8");
$dom->formatOutput = true;

$root = $dom->createElement("arkiva_digjitale");
$dom->appendChild($root);

while ($row = $stmt->fetch()) {
    $docNode = $dom->createElement("dokument");
    foreach (["id", "numri_protokollit", "titulli", "kategoria", "kodi", "versioni", "statusi", "created_at"] as $field) {
        $el = $dom->createElement($field);
        $el->appendChild($dom->createTextNode((string)($row[$field] ?? "")));
        $docNode->appendChild($el);
    }
    $root->appendChild($docNode);
}

echo $dom->saveXML();