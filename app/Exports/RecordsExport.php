<?php

namespace App\Exports;

use App\Models\Record;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class RecordsExport implements FromArray, WithHeadings, WithColumnWidths, WithColumnFormatting, WithEvents, WithTitle
{
    private const MONEY_FORMAT = '#,##0.00';

    protected Collection $data;

    protected bool $includeFinancial;

    /**
     * Column definitions in display order.
     * key => [heading, width, type(text|money|center|wrap), financial-only?]
     */
    protected array $columns;

    public function __construct(Collection $data, bool $includeFinancial = true)
    {
        $this->data = $data;
        $this->includeFinancial = $includeFinancial;
        $this->columns = $this->buildColumns();
    }

    protected function buildColumns(): array
    {
        $cols = [
            'index'            => ['#', 6, 'center'],
            'done'             => ['تمت العملية', 12, 'center'],
            'date'             => ['التاريخ', 13, 'center'],
            'name'             => ['الاسم', 28, 'text'],
            'financier'        => ['الممول', 22, 'text'],
            'financier_number' => ['رقم الممول', 14, 'center'],
            'age'              => ['العمر', 8, 'center'],
            'patient_ID'       => ['رقم الهوية', 15, 'center'],
            'phone_number1'    => ['جوال 1', 15, 'center'],
            'phone_number2'    => ['جوال 2', 15, 'center'],
            'operation'        => ['العملية', 26, 'text'],
            'doctor'           => ['الدكتور', 22, 'text'],
            'eligible'         => ['يستحق', 10, 'center'],
            'branch_name'      => ['الفرع', 18, 'text'],
            'branch_notes'     => ['ملاحظات الفرع', 32, 'wrap'],
            'management'       => ['حالة موافقة الإدارة', 20, 'center'],
        ];

        if ($this->includeFinancial) {
            $cols += [
                'amount'                  => ['المبلغ', 14, 'money'],
                'doctor_share'            => ['حصة الطبيب', 14, 'money'],
                'anesthesia'              => ['طبيب التخدير', 20, 'text'],
                'anesthesiologists_share' => ['حصة طبيب التخدير', 16, 'money'],
                'bed'                     => ['المبيت', 12, 'money'],
                'private'                 => ['خاص', 12, 'money'],
            ];
        }

        if ($this->data->contains(fn ($item) => array_key_exists('address', $item))) {
            $cols['address'] = ['العنوان', 28, 'wrap'];
        }

        return $cols + [
            'displacement_status' => ['حالة النزوح', 24, 'text'],
            'income_status'       => ['الدخل', 24, 'text'],
            'family_burden'       => ['حجم الأسرة', 24, 'text'],
            'distributed'         => ['تم التوزيع', 12, 'center'],
            'notes'               => ['ملاحظات', 40, 'wrap'],
            'notes_2'             => ['ملاحظات ثانوية', 40, 'wrap'],
            'user_name'           => ['المستخدم', 18, 'text'],
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $item) {
            $row = [];
            foreach (array_keys($this->columns) as $key) {
                $row[] = $this->value($item, $key);
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        return array_column($this->columns, 0);
    }

    public function title(): string
    {
        return 'سجلات المرضى';
    }

    public function columnWidths(): array
    {
        $widths = [];
        foreach (array_keys($this->columns) as $i => $key) {
            $widths[Coordinate::stringFromColumnIndex($i + 1)] = $this->columns[$key][1];
        }

        return $widths;
    }

    public function columnFormats(): array
    {
        $formats = [];
        foreach (array_keys($this->columns) as $i => $key) {
            $letter = Coordinate::stringFromColumnIndex($i + 1);
            if ($this->columns[$key][2] === 'money') {
                $formats[$letter] = self::MONEY_FORMAT;
            } elseif (in_array($key, ['patient_ID', 'phone_number1', 'phone_number2', 'financier_number', 'date'], true)) {
                $formats[$letter] = NumberFormat::FORMAT_TEXT;
            }
        }

        return $formats;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $keys = array_keys($this->columns);
                $colCount = count($keys);
                $lastCol = Coordinate::stringFromColumnIndex($colCount);
                $dataCount = count($this->data);
                $lastDataRow = $dataCount + 1;
                $showTotals = $this->includeFinancial && $dataCount > 0;
                $lastRow = $showTotals ? $lastDataRow + 1 : $lastDataRow;
                $headerRange = "A1:{$lastCol}1";
                $fullRange = "A1:{$lastCol}{$lastRow}";

                $sheet->setRightToLeft(true);
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(11);

                // Header
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11, 'name' => 'Arial'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastCol}{$lastDataRow}");

                if ($dataCount > 0) {
                    $body = "A2:{$lastCol}{$lastDataRow}";
                    $sheet->getStyle($body)->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    foreach ($keys as $i => $key) {
                        $letter = Coordinate::stringFromColumnIndex($i + 1);
                        $range = "{$letter}2:{$letter}{$lastDataRow}";
                        $type = $this->columns[$key][2];
                        if ($type === 'center') {
                            $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        } elseif ($type === 'money') {
                            $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        } elseif ($type === 'wrap') {
                            $sheet->getStyle($range)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                        }
                    }

                    // Zebra stripes
                    for ($r = 3; $r <= $lastDataRow; $r += 2) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EAF1F8');
                    }
                }

                // Totals row
                if ($showTotals) {
                    $t = $lastRow;
                    $sheet->setCellValue("A{$t}", 'المجموع');
                    $sheet->mergeCells("A{$t}:" . Coordinate::stringFromColumnIndex(array_search('management', $keys) + 1) . $t);
                    foreach ($keys as $i => $key) {
                        if ($this->columns[$key][2] !== 'money') {
                            continue;
                        }
                        $letter = Coordinate::stringFromColumnIndex($i + 1);
                        $sheet->setCellValue("{$letter}{$t}", "=SUM({$letter}2:{$letter}{$lastDataRow})");
                        $sheet->getStyle("{$letter}{$t}")->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);
                    }
                    $sheet->getStyle("A{$t}:{$lastCol}{$t}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '1F4E78']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E2F3']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1F4E78']]],
                    ]);
                    $sheet->getRowDimension($t)->setRowHeight(24);
                }

                // Light borders
                $sheet->getStyle($fullRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('BFBFBF');
            },
        ];
    }

    protected function value(array $item, string $key)
    {
        $get = fn ($k) => $item[$k] ?? null;

        switch ($key) {
            case 'index':
                return $get('DT_RowIndex');
            case 'done':
                return $this->yesNo($get('done'), true);
            case 'eligible':
                $label = $get('eligible_label');
                if ($label !== null && $label !== '') {
                    return $label;
                }

                return $this->yesNo($get('eligible'));
            case 'management':
                return $item['management_status_label'] ?? '';
            case 'date':
                $d = $get('date');
                if (!$d) {
                    return '';
                }
                try {
                    return \Carbon\Carbon::parse($d)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return (string) $d;
                }
            case 'displacement_status':
                return Record::DISPLACEMENT_STATUSES[$get('displacement_status')] ?? '';
            case 'income_status':
                return Record::INCOME_STATUSES[$get('income_status')] ?? '';
            case 'family_burden':
                return Record::FAMILY_BURDENS[$get('family_burden')] ?? '';
            case 'distributed':
                return $this->yesNo($get('distributed'));
            case 'amount':
            case 'doctor_share':
            case 'anesthesiologists_share':
            case 'bed':
            case 'private':
                $v = $get($key);

                return is_numeric($v) ? (float) $v : null;
            case 'patient_ID':
            case 'phone_number1':
            case 'phone_number2':
            case 'financier_number':
                $v = $get($key);

                return $v === null ? '' : (string) $v;
            default:
                return $get($key);
        }
    }

    protected function yesNo($value, bool $emptyIsNo = false): string
    {
        if ($value === null || $value === '') {
            return $emptyIsNo ? 'لا' : '';
        }
        if (is_string($value)) {
            return in_array(trim($value), ['1', 'true', 'نعم'], true) ? 'نعم' : 'لا';
        }

        return $value ? 'نعم' : 'لا';
    }
}
