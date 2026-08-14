<?php
$zip = new ZipArchive;
if ($zip->open('Book1.xlsx') === TRUE) {
    $sharedStrings = [];
    if (($ssXml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
        $xml = simplexml_load_string($ssXml);
        foreach ($xml->si as $val) {
            $sharedStrings[] = (string)($val->t ?? $val->r->t ?? '');
        }
    }
    
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheetXml !== false) {
        $xml = simplexml_load_string($sheetXml);
        $rowNum = 0;
        foreach ($xml->sheetData->row as $row) {
            $rowNum++;
            $rowData = [];
            foreach ($row->c as $cell) {
                $r = (string)$cell['r'];
                $t = (string)$cell['t'];
                $v = (string)$cell->v;
                if ($t === 's' && isset($sharedStrings[(int)$v])) {
                    $val = $sharedStrings[(int)$v];
                } else {
                    $val = $v;
                }
                $rowData[$r] = $val;
            }
            echo "Row $rowNum: " . json_encode($rowData) . "\n";
            if ($rowNum > 16) break;
        }
    }
    $zip->close();
} else {
    echo "Failed to open Excel file.\n";
}
