<?php

namespace App\Exports;

use App\Models\Record;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RecordsExport implements FromCollection,WithHeadings
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                $item['DT_RowIndex'],
                $item['date'],
                $item['name'],
                $item['financier_number'],
                $item['financier'],
                $item['age'],
                $item['patient_ID'],
                $item['phone_number1'],
                $item['phone_number2'],
                $item['operation'],
                $item['doctor'],
                $item['amount'],
                $item['doctor_share'],
                $item['anesthesiologists_share'],
                $item['anesthesia'],
                $item['bed'],
                $item['private'],
                $item['notes'],
                $item['notes_2'],
                Record::DISPLACEMENT_STATUSES[$item['displacement_status'] ?? null] ?? '',
                Record::INCOME_STATUSES[$item['income_status'] ?? null] ?? '',
                Record::FAMILY_BURDENS[$item['family_burden'] ?? null] ?? '',
                array_key_exists('distributed', $item) && !is_null($item['distributed']) ? ($item['distributed'] ? 'نعم' : 'لا') : '',
                $item['user_name'],
                $item['done'],
            ];
        });
    }

    // إضافة العناوين
    public function headings(): array
    {
        return [
            '#',
            'التاريخ',
            'الاسم',
            'رقم الممول',
            'الممول',
            'العمر',
            'رقم الهوية',
            'رقم الجوال 1',
            'رقم الجوال 2',
            'العملية',
            'الدكتور',
            'المبلغ',
            'حصة الطبيب',
            'حصة الطبيب التخدير',
            'طبيب التخدير',
            'المبيت',
            'خاص',
            'ملاحظات',
            'ملاحظات ثانوية',
            'حالة النزوح والسكن وتضرر البيت',
            'مصادر الدخل والوضع المالي',
            'حجم الأسرة والحالات الخاصة',
            'تم التوزيع',
            'المستخدم',
            'تمت العملية',
        ];
    }
}
