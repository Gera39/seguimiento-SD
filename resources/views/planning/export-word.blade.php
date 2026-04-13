<!DOCTYPE html>
<html lang="es" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="UTF-8">
    <title>{{ $summary['folio'] }}</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page {
            margin: 1.5cm 1.6cm 1.8cm 1.6cm;
        }

        body {
            margin: 0;
            font-family: Calibri, Arial, sans-serif;
            color: #0f172a;
            font-size: 10.5pt;
            line-height: 1.4;
            background: #ffffff;
        }

        .document {
            border: 1px solid #94a3b8;
        }

        .header-band {
            border-bottom: 3px solid #0f766e;
            background: #ecfeff;
            padding: 14px 18px 12px;
        }

        .header-table,
        .meta-table,
        .matrix-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand-box {
            width: 72px;
            height: 72px;
            border: 1px solid #0f766e;
            background: #ccfbf1;
            text-align: center;
            vertical-align: middle;
            font-size: 19pt;
            font-weight: 700;
            color: #115e59;
        }

        .header-title {
            font-size: 17pt;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 3px;
        }

        .header-subtitle {
            font-size: 9.5pt;
            color: #334155;
            margin: 0;
        }

        .folio-box {
            border: 1px solid #99f6e4;
            background: #f0fdfa;
            padding: 8px 10px;
            text-align: right;
        }

        .folio-label {
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #0f766e;
        }

        .folio-value {
            font-size: 11pt;
            font-weight: 700;
            color: #0f172a;
            margin-top: 3px;
        }

        .content {
            padding: 16px 18px 20px;
        }

        .section-title {
            margin: 18px 0 10px;
            padding: 8px 10px;
            background: #e2e8f0;
            border-left: 5px solid #0f766e;
            font-size: 11.5pt;
            font-weight: 700;
            color: #0f172a;
        }

        .meta-table td,
        .matrix-table th,
        .matrix-table td,
        .signature-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 9px;
            vertical-align: top;
        }

        .meta-table .label,
        .matrix-table th {
            background: #f8fafc;
            font-weight: 700;
        }

        .label {
            width: 18%;
            color: #334155;
        }

        .hero-block {
            margin-top: 12px;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }

        .hero-label {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #0f766e;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .muted {
            color: #475569;
        }

        .unit-card,
        .comment-card {
            margin-bottom: 12px;
            border: 1px solid #cbd5e1;
        }

        .unit-card-header,
        .comment-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-weight: 700;
            color: #0f172a;
        }

        .unit-card-body,
        .comment-card-body {
            padding: 10px;
        }

        .status-chip {
            display: inline-block;
            border: 1px solid #94a3b8;
            padding: 3px 10px;
            font-size: 8.5pt;
            font-weight: 700;
            color: #334155;
            background: #ffffff;
        }

        .small {
            font-size: 9pt;
        }

        .spacer {
            height: 8px;
        }

        .signature-line {
            height: 52px;
            vertical-align: bottom;
        }

        .footer-note {
            margin-top: 18px;
            font-size: 8.5pt;
            color: #64748b;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="document">
        <div class="header-band">
            <table class="header-table">
                <tr>
                    <td style="width: 82px;">
                        <div class="brand-box">SD</div>
                    </td>
                    <td style="padding: 0 14px;">
                        <p class="header-title">Formato Institucional de Secuencia Didactica</p>
                        <p class="header-subtitle">Seguimiento academico · Planeacion didactica exportada para Word</p>
                    </td>
                    <td style="width: 180px;">
                        <div class="folio-box">
                            <div class="folio-label">Folio</div>
                            <div class="folio-value">{{ $summary['folio'] }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="content">
            <table class="meta-table">
                <tr>
                    <td class="label">Asignatura</td>
                    <td>{{ $summary['subject'] ?? 'Sin asignatura' }}</td>
                    <td class="label">Estado</td>
                    <td><span class="status-chip">{{ $summary['status']['name'] ?? 'Sin estatus' }}</span></td>
                </tr>
                <tr>
                    <td class="label">Docente</td>
                    <td>{{ $summary['teacher'] }}</td>
                    <td class="label">Carrera</td>
                    <td>{{ $summary['career'] }}</td>
                </tr>
                <tr>
                    <td class="label">Grupo</td>
                    <td>{{ $summary['group'] }}</td>
                    <td class="label">Periodo</td>
                    <td>{{ $summary['period'] }}</td>
                </tr>
                <tr>
                    <td class="label">Avance total</td>
                    <td>{{ $summary['summary']['progress'] }}%</td>
                    <td class="label">Evaluacion total</td>
                    <td>{{ $summary['summary']['evaluation'] }}%</td>
                </tr>
            </table>

            <div class="hero-block">
                <div class="hero-label">Objetivo general</div>
                <div>{{ $summary['general_objective'] }}</div>
            </div>

            <div class="section-title">1. Datos Generales del Curso</div>
            <table class="meta-table">
                <tr>
                    <td class="label">Intencion del curso</td>
                    <td>{{ $summary['course_intent'] ?: 'Sin captura' }}</td>
                </tr>
                <tr>
                    <td class="label">Notas metodologicas</td>
                    <td>{{ $summary['methodology_notes'] ?: 'Sin captura' }}</td>
                </tr>
                <tr>
                    <td class="label">Observaciones generales</td>
                    <td>{{ $summary['general_observations'] ?: 'Sin captura' }}</td>
                </tr>
            </table>

            <div class="section-title">2. Desarrollo por Unidades y Modulos</div>
            @foreach ($summary['units'] as $unit)
                <div class="unit-card">
                    <div class="unit-card-header">
                        Unidad {{ $unit['unit_number'] }} · {{ $unit['title'] }}
                    </div>
                    <div class="unit-card-body">
                        <p><strong>Objetivo de aprendizaje:</strong> {{ $unit['learning_objective'] }}</p>
                        <p class="small muted">
                            Horas planeadas: {{ $unit['planned_hours'] }} · Avance: {{ $unit['progress_percentage'] }}%
                        </p>

                        <table class="matrix-table">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">Modulo</th>
                                    <th style="width: 26%;">Titulo</th>
                                    <th>Tema / descripcion</th>
                                    <th style="width: 18%;">Horas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($unit['modules'] as $module)
                                    <tr>
                                        <td>{{ $module['module_number'] }}</td>
                                        <td>{{ $module['title'] }}</td>
                                        <td>{{ $module['topic_description'] }}</td>
                                        <td>{{ $module['theoretical_hours'] }} T / {{ $module['practical_hours'] }} P</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <div class="section-title">3. Criterios de Evaluacion</div>
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th style="width: 24%;">Criterio</th>
                        <th style="width: 15%;">Tipo</th>
                        <th>Descripcion y evidencia</th>
                        <th style="width: 12%;">Peso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary['evaluation_criteria'] as $criterion)
                        <tr>
                            <td>{{ $criterion['criterion_name'] }}</td>
                            <td>{{ $criterion['criterion_type'] }}</td>
                            <td>
                                <strong>Descripcion:</strong> {{ $criterion['description'] }}<br>
                                <strong>Evidencia:</strong> {{ $criterion['evidence_description'] }}<br>
                                <strong>Instrumento:</strong> {{ $criterion['instrument_description'] }}
                            </td>
                            <td>{{ $criterion['weight_percentage'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if (!empty($summary['references']))
                <div class="section-title">4. Referencias</div>
                <table class="matrix-table">
                    <thead>
                        <tr>
                            <th style="width: 18%;">Tipo</th>
                            <th>Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summary['references'] as $reference)
                            <tr>
                                <td>{{ $reference['reference_type'] }}</td>
                                <td>{{ $reference['citation'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if (!empty($summary['review_comments']))
                <div class="section-title">5. Seguimiento de Observaciones</div>
                @foreach ($summary['review_comments'] as $comment)
                    <div class="comment-card">
                        <div class="comment-card-header">
                            {{ $comment['field_label'] }} · {{ $comment['comment_status_code'] }}
                        </div>
                        <div class="comment-card-body">
                            <p><strong>Observacion:</strong> {{ $comment['comment_text'] }}</p>
                            <p><strong>Antes:</strong> {{ $comment['observed_value_snapshot'] ?: 'Sin captura' }}</p>
                            @if (!empty($comment['teacher_response']))
                                <p><strong>Respuesta del docente:</strong> {{ $comment['teacher_response'] }}</p>
                            @endif
                            @if (!empty($comment['updated_value_snapshot']))
                                <p><strong>Despues:</strong> {{ $comment['updated_value_snapshot'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="section-title">6. Validacion y Firmas</div>
            <table class="signature-table">
                <tr>
                    <td class="signature-line" style="width: 33.33%;"></td>
                    <td class="signature-line" style="width: 33.33%;"></td>
                    <td class="signature-line" style="width: 33.33%;"></td>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <strong>Elaboro</strong><br>
                        {{ $summary['teacher'] }}
                    </td>
                    <td style="text-align: center;">
                        <strong>Reviso</strong><br>
                        Area academica
                    </td>
                    <td style="text-align: center;">
                        <strong>Autorizo</strong><br>
                        Direccion academica
                    </td>
                </tr>
            </table>

            <p class="footer-note">
                Documento generado desde Seguimiento SD · {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>
    </div>
</body>
</html>
