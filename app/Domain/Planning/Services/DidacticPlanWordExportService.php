<?php

namespace App\Domain\Planning\Services;

use App\Models\DidacticPlan;
use RuntimeException;
use ZipArchive;

class DidacticPlanWordExportService
{
    public function export(DidacticPlan $plan, array $summary): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'plan_docx_');

        if ($tempFile === false) {
            throw new RuntimeException('No fue posible crear un archivo temporal para la exportacion.');
        }

        $docxPath = $tempFile.'.docx';
        @unlink($docxPath);

        $zip = new ZipArchive();

        if ($zip->open($docxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No fue posible abrir el archivo DOCX para escritura.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('docProps/core.xml', $this->coreXml($summary));
        $zip->addFromString('docProps/app.xml', $this->appXml());
        $zip->addFromString('word/_rels/document.xml.rels', $this->documentRelsXml());
        $zip->addFromString('word/styles.xml', $this->stylesXml());
        $zip->addFromString('word/settings.xml', $this->settingsXml());
        $zip->addFromString('word/document.xml', $this->documentXml($plan, $summary));
        $zip->close();

        @unlink($tempFile);

        return $docxPath;
    }

    protected function documentXml(DidacticPlan $plan, array $summary): string
    {
        $parts = [];
        $parts[] = $this->paragraph('Seguimiento SD', 'Brand');
        $parts[] = $this->paragraph('Formato Institucional de Secuencia Didactica', 'DocumentTitle');
        $parts[] = $this->paragraph('Documento academico exportado en formato Word nativo', 'Subtitle');
        $parts[] = $this->paragraph('');

        $parts[] = $this->table([
            [
                ['text' => 'Folio', 'style' => 'TableLabel'],
                ['text' => $summary['folio'] ?? 'Sin folio'],
                ['text' => 'Estado', 'style' => 'TableLabel'],
                ['text' => $summary['status']['name'] ?? 'Sin estatus'],
            ],
            [
                ['text' => 'Asignatura', 'style' => 'TableLabel'],
                ['text' => $summary['subject'] ?? 'Sin asignatura'],
                ['text' => 'Docente', 'style' => 'TableLabel'],
                ['text' => $summary['teacher'] ?? 'Sin docente'],
            ],
            [
                ['text' => 'Carrera', 'style' => 'TableLabel'],
                ['text' => $summary['career'] ?? 'Sin carrera'],
                ['text' => 'Grupo / Periodo', 'style' => 'TableLabel'],
                ['text' => ($summary['group'] ?? 'Sin grupo').' / '.($summary['period'] ?? 'Sin periodo')],
            ],
            [
                ['text' => 'Avance total', 'style' => 'TableLabel'],
                ['text' => ($summary['summary']['progress'] ?? 0).'%'],
                ['text' => 'Evaluacion total', 'style' => 'TableLabel'],
                ['text' => ($summary['summary']['evaluation'] ?? 0).'%'],
            ],
        ], [2000, 3500, 2000, 3500]);

        $parts[] = $this->sectionTitle('1. Objetivo General');
        $parts[] = $this->box($summary['general_objective'] ?? 'Sin captura');

        $parts[] = $this->sectionTitle('2. Datos Generales del Curso');
        $parts[] = $this->table([
            [
                ['text' => 'Intencion del curso', 'style' => 'TableLabel'],
                ['text' => $summary['course_intent'] ?: 'Sin captura', 'colspan' => 3],
            ],
            [
                ['text' => 'Notas metodologicas', 'style' => 'TableLabel'],
                ['text' => $summary['methodology_notes'] ?: 'Sin captura', 'colspan' => 3],
            ],
            [
                ['text' => 'Observaciones generales', 'style' => 'TableLabel'],
                ['text' => $summary['general_observations'] ?: 'Sin captura', 'colspan' => 3],
            ],
        ], [2200, 8800, 0, 0]);

        $parts[] = $this->sectionTitle('3. Desarrollo por Unidades y Modulos');
        foreach ($summary['units'] as $unit) {
            $parts[] = $this->paragraph(
                'Unidad '.($unit['unit_number'] ?? '').' · '.($unit['title'] ?? 'Sin titulo'),
                'Heading3'
            );
            $parts[] = $this->paragraph('Objetivo de aprendizaje: '.($unit['learning_objective'] ?? 'Sin captura'));
            $parts[] = $this->paragraph(
                'Horas planeadas: '.($unit['planned_hours'] ?? 0).' · Avance: '.($unit['progress_percentage'] ?? 0).'%',
                'Muted'
            );

            $moduleRows = [[
                ['text' => 'Modulo', 'style' => 'TableHeader'],
                ['text' => 'Titulo', 'style' => 'TableHeader'],
                ['text' => 'Tema / descripcion', 'style' => 'TableHeader'],
                ['text' => 'Horas', 'style' => 'TableHeader'],
            ]];

            foreach ($unit['modules'] as $module) {
                $moduleRows[] = [
                    ['text' => (string) ($module['module_number'] ?? '')],
                    ['text' => $module['title'] ?? ''],
                    ['text' => $module['topic_description'] ?? ''],
                    ['text' => ($module['theoretical_hours'] ?? 0).' T / '.($module['practical_hours'] ?? 0).' P'],
                ];
            }

            $parts[] = $this->table($moduleRows, [1200, 2800, 5000, 1800]);
            $parts[] = $this->paragraph('');
        }

        $parts[] = $this->sectionTitle('4. Criterios de Evaluacion');
        $criteriaRows = [[
            ['text' => 'Criterio', 'style' => 'TableHeader'],
            ['text' => 'Tipo', 'style' => 'TableHeader'],
            ['text' => 'Descripcion y evidencia', 'style' => 'TableHeader'],
            ['text' => 'Peso', 'style' => 'TableHeader'],
        ]];
        foreach ($summary['evaluation_criteria'] as $criterion) {
            $criteriaRows[] = [
                ['text' => $criterion['criterion_name'] ?? ''],
                ['text' => $criterion['criterion_type'] ?? ''],
                ['text' => 'Descripcion: '.($criterion['description'] ?? '')."\n"
                    .'Evidencia: '.($criterion['evidence_description'] ?? '')."\n"
                    .'Instrumento: '.($criterion['instrument_description'] ?? '')],
                ['text' => ($criterion['weight_percentage'] ?? 0).'%'],
            ];
        }
        $parts[] = $this->table($criteriaRows, [2600, 1700, 5600, 1100]);

        if (!empty($summary['references'])) {
            $parts[] = $this->sectionTitle('5. Referencias');
            $referenceRows = [[
                ['text' => 'Tipo', 'style' => 'TableHeader'],
                ['text' => 'Referencia', 'style' => 'TableHeader'],
            ]];
            foreach ($summary['references'] as $reference) {
                $referenceRows[] = [
                    ['text' => $reference['reference_type'] ?? ''],
                    ['text' => $reference['citation'] ?? ''],
                ];
            }
            $parts[] = $this->table($referenceRows, [2200, 8800]);
        }

        if (!empty($summary['review_comments'])) {
            $parts[] = $this->sectionTitle('6. Seguimiento de Observaciones');
            foreach ($summary['review_comments'] as $comment) {
                $parts[] = $this->paragraph(
                    ($comment['field_label'] ?? 'Observacion').' · '.($comment['comment_status_code'] ?? 'OPEN'),
                    'Heading3'
                );
                $parts[] = $this->paragraph('Observacion: '.($comment['comment_text'] ?? ''));
                $parts[] = $this->paragraph('Antes: '.($comment['observed_value_snapshot'] ?: 'Sin captura'));

                if (!empty($comment['teacher_response'])) {
                    $parts[] = $this->paragraph('Respuesta del docente: '.$comment['teacher_response']);
                }

                if (!empty($comment['updated_value_snapshot'])) {
                    $parts[] = $this->paragraph('Despues: '.$comment['updated_value_snapshot']);
                }

                $parts[] = $this->paragraph('');
            }
        }

        $parts[] = $this->sectionTitle('7. Validacion y Firmas');
        $parts[] = $this->table([
            [
                ['text' => "\n\n\nElaboro\n".($summary['teacher'] ?? ''), 'style' => 'Centered'],
                ['text' => "\n\n\nReviso\nArea academica", 'style' => 'Centered'],
                ['text' => "\n\n\nAutorizo\nDireccion academica", 'style' => 'Centered'],
            ],
        ], [3333, 3333, 3334], true);

        $parts[] = $this->paragraph('');
        $parts[] = $this->paragraph('Documento generado desde Seguimiento SD · '.now()->format('d/m/Y H:i'), 'Footer');

        $parts[] = $this->sectionProperties();

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" '
            .'xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" '
            .'xmlns:o="urn:schemas-microsoft-com:office:office" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
            .'xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" '
            .'xmlns:v="urn:schemas-microsoft-com:vml" '
            .'xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" '
            .'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '
            .'xmlns:w10="urn:schemas-microsoft-com:office:word" '
            .'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
            .'xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" '
            .'xmlns:w15="http://schemas.microsoft.com/office/word/2012/wordml" '
            .'xmlns:w16se="http://schemas.microsoft.com/office/word/2015/wordml/symex" '
            .'xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" '
            .'xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" '
            .'xmlns:wne="http://schemas.microsoft.com/office/2006/wordml" '
            .'xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape" mc:Ignorable="w14 w15 wp14">'
            .'<w:body>'.implode('', $parts).'</w:body></w:document>';
    }

    protected function paragraph(string $text, ?string $style = null): string
    {
        $styleXml = $style ? '<w:pPr><w:pStyle w:val="'.$style.'"/></w:pPr>' : '';
        $runs = [];
        $lines = preg_split("/\r\n|\n|\r/", $text) ?: [''];

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $runs[] = '<w:r><w:br/></w:r>';
            }

            $preserve = preg_match('/^\s|\s$/', $line) ? ' xml:space="preserve"' : '';
            $runs[] = '<w:r><w:t'.$preserve.'>'.$this->escape($line).'</w:t></w:r>';
        }

        if ($runs === []) {
            $runs[] = '<w:r><w:t></w:t></w:r>';
        }

        return '<w:p>'.$styleXml.implode('', $runs).'</w:p>';
    }

    protected function sectionTitle(string $text): string
    {
        return $this->paragraph($text, 'SectionTitle');
    }

    protected function box(string $text): string
    {
        return $this->table([
            [
                ['text' => $text],
            ],
        ], [11000], false, 'D9E2F3');
    }

    protected function table(array $rows, array $widths, bool $centered = false, string $shade = ''): string
    {
        $xmlRows = [];

        foreach ($rows as $row) {
            $cells = [];
            $columnIndex = 0;

            foreach ($row as $cell) {
                $colspan = max((int) ($cell['colspan'] ?? 1), 1);
                $width = array_sum(array_slice($widths, $columnIndex, $colspan));
                $columnIndex += $colspan;
                $style = $cell['style'] ?? null;

                $cellPr = '<w:tcPr><w:tcW w:w="'.$width.'" w:type="dxa"/>';

                if ($colspan > 1) {
                    $cellPr .= '<w:gridSpan w:val="'.$colspan.'"/>';
                }

                if ($shade !== '') {
                    $cellPr .= '<w:shd w:val="clear" w:color="auto" w:fill="'.$shade.'"/>';
                }

                if ($style === 'TableHeader') {
                    $cellPr .= '<w:shd w:val="clear" w:color="auto" w:fill="E2E8F0"/>';
                }

                $cellPr .= '</w:tcPr>';
                $paragraphStyle = $style === 'Centered' ? 'Centered' : null;
                $text = (string) ($cell['text'] ?? '');
                $cells[] = '<w:tc>'.$cellPr.$this->paragraph($text, $paragraphStyle).'</w:tc>';
            }

            $xmlRows[] = '<w:tr>'.implode('', $cells).'</w:tr>';
        }

        $grid = '';
        foreach ($widths as $width) {
            if ($width > 0) {
                $grid .= '<w:gridCol w:w="'.$width.'"/>';
            }
        }

        $alignment = $centered ? '<w:jc w:val="center"/>' : '';

        return '<w:tbl>'
            .'<w:tblPr>'
            .'<w:tblStyle w:val="TableGrid"/>'
            .'<w:tblW w:w="0" w:type="auto"/>'
            .$alignment
            .'<w:tblBorders>'
            .'<w:top w:val="single" w:sz="6" w:space="0" w:color="CBD5E1"/>'
            .'<w:left w:val="single" w:sz="6" w:space="0" w:color="CBD5E1"/>'
            .'<w:bottom w:val="single" w:sz="6" w:space="0" w:color="CBD5E1"/>'
            .'<w:right w:val="single" w:sz="6" w:space="0" w:color="CBD5E1"/>'
            .'<w:insideH w:val="single" w:sz="6" w:space="0" w:color="CBD5E1"/>'
            .'<w:insideV w:val="single" w:sz="6" w:space="0" w:color="CBD5E1"/>'
            .'</w:tblBorders>'
            .'</w:tblPr>'
            .'<w:tblGrid>'.$grid.'</w:tblGrid>'
            .implode('', $xmlRows)
            .'</w:tbl>';
    }

    protected function sectionProperties(): string
    {
        return '<w:sectPr>'
            .'<w:pgSz w:w="11906" w:h="16838"/>'
            .'<w:pgMar w:top="850" w:right="900" w:bottom="1020" w:left="900" w:header="708" w:footer="708" w:gutter="0"/>'
            .'</w:sectPr>';
    }

    protected function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
    <w:name w:val="Normal"/>
    <w:qFormat/>
    <w:rPr>
      <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
      <w:sz w:val="21"/>
      <w:color w:val="0F172A"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Brand">
    <w:name w:val="Brand"/>
    <w:rPr>
      <w:b/>
      <w:color w:val="0F766E"/>
      <w:sz w:val="18"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="DocumentTitle">
    <w:name w:val="DocumentTitle"/>
    <w:rPr>
      <w:b/>
      <w:sz w:val="34"/>
      <w:color w:val="0F172A"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Subtitle">
    <w:name w:val="Subtitle"/>
    <w:rPr>
      <w:sz w:val="19"/>
      <w:color w:val="475569"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="SectionTitle">
    <w:name w:val="SectionTitle"/>
    <w:pPr>
      <w:spacing w:before="220" w:after="120"/>
      <w:shd w:val="clear" w:color="auto" w:fill="E2E8F0"/>
      <w:ind w:left="120"/>
    </w:pPr>
    <w:rPr>
      <w:b/>
      <w:sz w:val="23"/>
      <w:color w:val="0F172A"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading3">
    <w:name w:val="Heading3"/>
    <w:pPr>
      <w:spacing w:before="140" w:after="80"/>
    </w:pPr>
    <w:rPr>
      <w:b/>
      <w:sz w:val="24"/>
      <w:color w:val="1E293B"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Muted">
    <w:name w:val="Muted"/>
    <w:rPr>
      <w:sz w:val="18"/>
      <w:color w:val="64748B"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Footer">
    <w:name w:val="Footer"/>
    <w:pPr>
      <w:jc w:val="right"/>
      <w:spacing w:before="120"/>
    </w:pPr>
    <w:rPr>
      <w:sz w:val="16"/>
      <w:color w:val="64748B"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Centered">
    <w:name w:val="Centered"/>
    <w:pPr>
      <w:jc w:val="center"/>
    </w:pPr>
  </w:style>
</w:styles>
XML;
    }

    protected function settingsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:zoom w:percent="100"/>
  <w:defaultTabStop w:val="720"/>
  <w:compat/>
</w:settings>
XML;
    }

    protected function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
  <Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
XML;
    }

    protected function rootRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML;
    }

    protected function documentRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/>
</Relationships>
XML;
    }

    protected function coreXml(array $summary): string
    {
        $title = $this->escape($summary['folio'] ?? 'Planeacion');
        $created = now()->toAtomString();

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>{$title}</dc:title>
  <dc:creator>Seguimiento SD</dc:creator>
  <cp:lastModifiedBy>Seguimiento SD</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">{$created}</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">{$created}</dcterms:modified>
</cp:coreProperties>
XML;
    }

    protected function appXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Seguimiento SD</Application>
</Properties>
XML;
    }

    protected function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
