<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class KuwaitiCasesExport implements FromArray, WithHeadings, WithColumnWidths, WithColumnFormatting, WithEvents, WithTitle
{
    private const MONEY_FORMAT = '#,##0.00';

    protected Collection $data;

    /**
     * Columns in display order.
     * key => [heading, width, type(text|money|center|wrap)]
     */
    protected array $columns = [
        'index'                => ['#', 6, 'center'],
        'date'                 => ['التاريخ', 13, 'center'],
        'name'                 => ['الاسم', 28, 'text'],
        'patient_ID'           => ['رقم الهوية', 15, 'center'],
        'gender'               => ['الجنس', 10, 'center'],
        'age'                  => ['العمر', 8, 'center'],
        'marital_status'       => ['الحالة الاجتماعية', 16, 'center'],
        'phone_number'         => ['رقم التواصل', 15, 'center'],
        'address'              => ['العنوان الحالي', 28, 'wrap'],
        'displacement_address' => ['العنوان السابق', 28, 'wrap'],
        'exam'                 => ['الفحص المطلوب', 26, 'text'],
        'exam_cost'            => ['تكلفة الفحص', 14, 'money'],
        'exam_need'            => ['الحاجة للفحص', 30, 'wrap'],
        'housing_status'       => ['حالة النزوح والسكن', 30, 'wrap'],
        'income_status'        => ['الدخل', 28, 'wrap'],
        'family_burden'        => ['الأسرة', 24, 'wrap'],
        'total_score'          => ['إجمالي النقاط', 12, 'center'],
        'branch_name'          => ['الفرع', 18, 'text'],
        'eligible'             => ['رأي مدير المكتب', 16, 'center'],
        'need_percentage'      => ['نسبة الاحتياج', 14, 'center'],
        'branch_notes'         => ['ملاحظات الفرع', 36, 'wrap'],
        'management'           => ['موافقة الإدارة', 16, 'center'],
        'management_notes'     => ['ملاحظات الإدارة', 32, 'wrap'],
        'done'                 => ['تمت', 10, 'center'],
        'data_entry'           => ['مدخل البيانات', 18, 'text'],
        'case_description'     => ['وصف الحالة', 50, 'wrap'],
    ];

    public function __construct(Collection $data)
    {
        $this->data = $data->values();
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $item) {
            $item = (array) $item;
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
        return 'حالات المركز الكويتي';
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
            } elseif (in_array($key, ['patient_ID', 'phone_number', 'date'], true)) {
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
                $lastCol = Coordinate::stringFromColumnIndex(count($keys));
                $dataCount = count($this->data);
                $lastRow = $dataCount + 1;
                $headerRange = "A1:{$lastCol}1";

                $sheet->setRightToLeft(true);
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(11);

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
                $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");

                if ($dataCount > 0) {
                    $sheet->getStyle("A2:{$lastCol}{$lastRow}")->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    foreach ($keys as $i => $key) {
                        $letter = Coordinate::stringFromColumnIndex($i + 1);
                        $range = "{$letter}2:{$letter}{$lastRow}";
                        $type = $this->columns[$key][2];
                        if ($type === 'center' || $type === 'money') {
                            $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        } elseif ($type === 'wrap') {
                            $sheet->getStyle($range)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                        }
                    }

                    for ($r = 3; $r <= $lastRow; $r += 2) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EAF1F8');
                    }
                }

                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()
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
            case 'date':
                $d = $get('date');
                if (! $d) {
                    return '';
                }
                try {
                    return \Carbon\Carbon::parse($d)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return (string) $d;
                }
            case 'gender':
            case 'marital_status':
            case 'exam_need':
            case 'housing_status':
            case 'income_status':
            case 'family_burden':
            case 'eligible':
            case 'need_percentage':
                return $get($key.'_label') ?? '';
            case 'management':
                return $get('management_status_label') ?? '';
            case 'done':
                if (($label = $get('done_label')) !== null && $label !== '') {
                    return $label;
                }
                $done = $get('done');

                return $done === null || $done === '' ? '' : (filter_var($done, FILTER_VALIDATE_BOOLEAN) ? 'نعم' : 'لا');
            case 'exam_cost':
                $v = $get($key);

                return is_numeric($v) ? (float) $v : null;
            case 'patient_ID':
            case 'phone_number':
                $v = $get($key);

                return $v === null ? '' : (string) $v;
            default:
                return $get($key);
        }
    }
}
