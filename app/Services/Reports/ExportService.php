<?php

namespace App\Services\Reports;

use App\Models\Business;
use App\Models\User;
use App\Services\AuditLogger;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Stream a CSV download with UTF-8 BOM.
     */
    public function downloadCsv(string $filename, array $headers, iterable $rows, Business $business, User $user, string $reportType): StreamedResponse
    {
        AuditLogger::log(
            action: 'report.exported',
            entityType: 'Report',
            newValues: [
                'report_type' => $reportType,
                'format' => 'csv',
                'filename' => $filename,
            ],
            businessId: $business->id,
            userId: $user->id
        );

        $responseHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel / Unicode support
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write Headers
            fputcsv($handle, $headers);

            // Write Data Rows
            foreach ($rows as $row) {
                fputcsv($handle, (array)$row);
            }

            fclose($handle);
        }, $filename, $responseHeaders);
    }

    /**
     * Stream an Excel-compatible XML Workbook (.xls).
     */
    public function downloadExcel(string $filename, string $title, array $headers, iterable $rows, Business $business, User $user, string $reportType): StreamedResponse
    {
        AuditLogger::log(
            action: 'report.exported',
            entityType: 'Report',
            newValues: [
                'report_type' => $reportType,
                'format' => 'excel',
                'filename' => $filename,
            ],
            businessId: $business->id,
            userId: $user->id
        );

        $responseHeaders = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($title, $headers, $rows, $business) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
            $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
            $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
            $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
            $xml .= ' <Styles>' . "\n";
            $xml .= '  <Style ss:ID="Default" ss:Name="Normal"><Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/></Style>' . "\n";
            $xml .= '  <Style ss:ID="Header"><Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#0F172A" ss:Pattern="Solid"/></Style>' . "\n";
            $xml .= '  <Style ss:ID="Title"><Font ss:FontName="Calibri" ss:Size="14" ss:Bold="1" ss:Color="#0F172A"/></Style>' . "\n";
            $xml .= ' </Styles>' . "\n";
            $xml .= ' <Worksheet ss:Name="Report">' . "\n";
            $xml .= '  <Table>' . "\n";

            // Title Row
            $xml .= '   <Row ss:StyleID="Title"><Cell><Data ss:Type="String">' . htmlspecialchars($business->name . ' — ' . $title) . '</Data></Cell></Row>' . "\n";
            $xml .= '   <Row><Cell><Data ss:Type="String">Generated on: ' . now()->format('Y-m-d H:i:s') . '</Data></Cell></Row>' . "\n";
            $xml .= '   <Row></Row>' . "\n";

            // Header Row
            $xml .= '   <Row ss:StyleID="Header">' . "\n";
            foreach ($headers as $header) {
                $xml .= '    <Cell><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>' . "\n";
            }
            $xml .= '   </Row>' . "\n";

            // Data Rows
            foreach ($rows as $row) {
                $xml .= '   <Row>' . "\n";
                foreach ((array)$row as $cell) {
                    $type = is_numeric($cell) ? 'Number' : 'String';
                    $xml .= '    <Cell><Data ss:Type="' . $type . '">' . htmlspecialchars((string)$cell) . '</Data></Cell>' . "\n";
                }
                $xml .= '   </Row>' . "\n";
            }

            $xml .= '  </Table>' . "\n";
            $xml .= ' </Worksheet>' . "\n";
            $xml .= '</Workbook>';

            echo $xml;
        }, $filename, $responseHeaders);
    }
}
