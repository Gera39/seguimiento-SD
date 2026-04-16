<?php

namespace App\Domain\Admin\Imports;

use DOMDocument;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class UserImportWorkbook
{
    public function create(array $headers, array $rows = []): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'user-import-template-');

        if ($tempPath === false) {
            throw new RuntimeException('No fue posible preparar el archivo temporal para la plantilla.');
        }

        $xlsxPath = $tempPath.'.xlsx';

        if (! @rename($tempPath, $xlsxPath)) {
            @unlink($tempPath);
            throw new RuntimeException('No fue posible preparar la plantilla Excel.');
        }

        $zip = new ZipArchive();

        if ($zip->open($xlsxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($xlsxPath);
            throw new RuntimeException('No fue posible generar la plantilla Excel.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($headers, $rows));
        $zip->close();

        return $xlsxPath;
    }

    public function read(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('No fue posible abrir el archivo Excel.');
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');

        if (! is_string($sheetXml) || $sheetXml === '') {
            $zip->close();
            throw new RuntimeException('La plantilla Excel debe contener una hoja principal.');
        }

        $sharedStrings = $this->extractSharedStrings($zip);
        $zip->close();

        return $this->parseWorksheet($sheetXml, $sharedStrings);
    }

    protected function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;
    }

    protected function rootRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    protected function workbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Usuarios" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;
    }

    protected function workbookRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    protected function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="1">
    <font>
      <sz val="11"/>
      <name val="Calibri"/>
    </font>
  </fonts>
  <fills count="1">
    <fill>
      <patternFill patternType="none"/>
    </fill>
  </fills>
  <borders count="1">
    <border/>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
  </cellXfs>
</styleSheet>
XML;
    }

    protected function worksheetXml(array $headers, array $rows): string
    {
        $sheetRows = [];
        $allRows = [$headers, ...$rows];

        foreach ($allRows as $rowIndex => $values) {
            $cells = [];

            foreach (array_values($values) as $columnIndex => $value) {
                $cellRef = $this->columnName($columnIndex + 1).($rowIndex + 1);
                $escaped = $this->escape((string) $value);
                $cells[] = '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.$escaped.'</t></is></c>';
            }

            $sheetRows[] = '<row r="'.($rowIndex + 1).'">'.implode('', $cells).'</row>';
        }

        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>
XML
            .implode('', $sheetRows).
            <<<'XML'
  </sheetData>
</worksheet>
XML;
    }

    protected function extractSharedStrings(ZipArchive $zip): array
    {
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');

        if (! is_string($sharedStringsXml) || $sharedStringsXml === '') {
            return [];
        }

        $document = new DOMDocument();
        $document->loadXML($sharedStringsXml);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $strings = [];

        foreach ($xpath->query('//main:si') as $stringNode) {
            $text = '';

            foreach ($xpath->query('.//main:t', $stringNode) as $textNode) {
                $text .= $textNode->textContent;
            }

            $strings[] = $text;
        }

        return $strings;
    }

    protected function parseWorksheet(string $worksheetXml, array $sharedStrings): array
    {
        $document = new DOMDocument();
        $document->loadXML($worksheetXml);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = [];

        foreach ($xpath->query('//main:sheetData/main:row') as $rowNode) {
            $indexedValues = [];
            $maxIndex = 0;

            foreach ($xpath->query('./main:c', $rowNode) as $cellNode) {
                $reference = $cellNode->attributes?->getNamedItem('r')?->nodeValue;
                $index = $this->columnIndexFromReference($reference ?? '');
                $maxIndex = max($maxIndex, $index);

                $type = $cellNode->attributes?->getNamedItem('t')?->nodeValue;
                $valueNode = $xpath->query('./main:v', $cellNode)->item(0);
                $inlineNode = $xpath->query('./main:is/main:t', $cellNode)->item(0);

                $value = match ($type) {
                    'inlineStr' => $inlineNode?->textContent ?? '',
                    's' => $sharedStrings[(int) ($valueNode?->textContent ?? -1)] ?? '',
                    'b' => ($valueNode?->textContent ?? '') === '1' ? '1' : '0',
                    default => $valueNode?->textContent ?? '',
                };

                $indexedValues[$index] = trim((string) $value);
            }

            $row = [];

            for ($index = 1; $index <= $maxIndex; $index++) {
                $row[] = $indexedValues[$index] ?? '';
            }

            $rows[] = $row;
        }

        if ($rows === []) {
            return [
                'headers' => [],
                'rows' => [],
            ];
        }

        $headers = array_map(static fn ($value) => trim((string) $value), array_shift($rows));
        $dataRows = array_values(array_filter($rows, static fn (array $row) => collect($row)->filter(
            static fn ($value) => trim((string) $value) !== '',
        )->isNotEmpty()));

        return [
            'headers' => $headers,
            'rows' => $dataRows,
        ];
    }

    protected function columnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    protected function columnIndexFromReference(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference));
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(1, $index);
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
